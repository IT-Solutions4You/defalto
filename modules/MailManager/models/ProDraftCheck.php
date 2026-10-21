<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * The check of a reply before it goes out (I15, fast model): an attachment the text
 * mentions but the mail does not carry, a wrong salutation or name, a question of the
 * customer left unanswered, an unsuitable tone, a promise (discount, price, deadline,
 * commitment — I16) and a conversation flagged as suspicious for the AI. It only warns —
 * sending never waits on it and never stops because of it. getFactSources() is what a
 * reply may rely on (the unverified data of an AI draft, I16).
 */
class MailManager_ProDraftCheck_Model
{
    /** Usage-log feature (a fast-tier task) and the AI settings switch */
    public const FEATURE = 'draftcheck';

    /** Issue kinds the answer may name */
    public const ISSUE_TYPES = ['attachment', 'name', 'question', 'tone', 'promise', 'history'];

    /** Output budget of the check — a reasoning model thinks within it too (600 was tight); the
     *  records' history (I20) makes it think longer */
    private const MAX_TOKENS = 8000;

    /** Characters of one message or record the facts are read from */
    private const MAX_FACT_CHARS = 20000;

    /** Linked records whose values count as verified facts */
    private const MAX_FACT_RECORDS = 10;

    /**
     * @return bool the assistant is on and the check is switched on
     */
    public static function isEnabled(): bool
    {
        return MailManager_ProAiConfig_Model::isFeatureEnabled(self::FEATURE);
    }

    /**
     * @param int    $threadId       the conversation answered, 0 = a new mail
     * @param string $subject
     * @param string $bodyHtml       the user's own text (no quote)
     * @param bool   $hasAttachments the mail carries files
     * @param string $recipients     the To field
     *
     * @return array of ['type', 'message'] — empty when nothing is wrong or the check is off
     * @throws Exception on a provider error
     */
    public static function check(int $threadId, string $subject, string $bodyHtml, bool $hasAttachments, string $recipients): array
    {
        $provider = self::isEnabled() ? MailManager_ProAiConfig_Model::getProvider() : null;
        $draft = trim(MailManager_ProMailText_Model::htmlToText($bodyHtml));

        if (!$provider || '' === $draft) {
            return [];
        }

        $language = (string)Users_Record_Model::getCurrentUserModel()->get('language');
        $customerText = self::getCustomerText($threadId);
        // the linked records' history — a reply that contradicts it is flagged (I20)
        $history = MailManager_ProRecordHistory_Model::buildPrompt(
            ['threadid' => $threadId],
            self::FEATURE,
            mb_strlen($customerText) + mb_strlen($draft)
        );
        $system = 'You check an email reply before it is sent and list only real problems: '
            . '"attachment" — the text says something is attached but the mail has no attachment; '
            . '"name" — the salutation or a name does not fit the recipient or the customer\'s message; '
            . '"question" — a question the customer asked is not answered; '
            . '"tone" — the tone is rude or clearly unsuitable for a customer; '
            . '"promise" — the reply promises a discount, a price, a deadline, a delivery date or another '
            . 'commitment (name it in the message) — the writer confirms it is meant. '
            . ('' !== $history['prompt']
                ? '"history" — the reply contradicts the CRM records block (a date, a status, a solution or a '
                    . 'promise a later comment changed — name both) or reveals an internal note to the customer. '
                : '')
            . 'Do not report style preferences or minor wording. Write each message briefly (max 15 words) '
            . 'in the language of the locale ' . $language . '. Respond with one JSON object only: '
            . '{"issues": [{"type": "attachment"|"name"|"question"|"tone"|"promise"'
            . ('' !== $history['prompt'] ? '|"history"' : '') . ', "message": "<short warning>"}]}';
        $user = 'The mail has attachments: ' . ($hasAttachments ? 'yes' : 'no')
            . "\n\nCustomer's latest message:\n"
            . MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_MAIL, $customerText)
            . $history['prompt']
            . "\n\nThe reply to check (subject and text):\n"
            . MailManager_ProAiGuard_Helper::wrap(
                MailManager_ProAiGuard_Helper::SOURCE_DRAFT,
                'Recipients: ' . $recipients . "\nSubject: " . $subject . "\n\n" . mb_substr($draft, 0, 8000)
            );

        $result = MailManager_ProAiClient_Model::complete($provider, self::FEATURE, $threadId, $system, $user, self::MAX_TOKENS);
        $parsed = MailManager_ProAiClient_Model::parseJsonObject((string)$result['text']);
        $issues = [];

        foreach ((array)($parsed['issues'] ?? []) as $issue) {
            $type = (string)($issue['type'] ?? '');
            $message = trim((string)($issue['message'] ?? ''));

            // the attachment warning is the model's guess — a mail with files never gets it
            if (in_array($type, self::ISSUE_TYPES, true) && '' !== $message && !('attachment' === $type && $hasAttachments)) {
                $issues[] = ['type' => $type, 'message' => mb_substr($message, 0, 200)];
            }
        }

        // mail of the conversation tried to instruct the AI — the writer double-checks the reply
        if ($result['suspicious'] || MailManager_ProAiSuspicion_Model::isFlagged($threadId)) {
            $issues[] = ['type' => 'suspicious', 'message' => vtranslate('LBL_AI_SUSPICIOUS_DRAFT', 'MailManager')];
        }

        return $issues;
    }

    /**
     * What a reply may rely on: the conversation (visible text), the values of the linked
     * records the user may see and the mailbox address.
     *
     * @param int   $threadId
     * @param array $mailbox mailbox row
     *
     * @return string plain text
     */
    public static function getFactSources(int $threadId, array $mailbox): string
    {
        $parts = [(string)($mailbox['email'] ?? '')];

        foreach ($threadId ? MailManager_ProThread_Model::getThreadMessages($threadId) : [] as $message) {
            $parts[] = (string)($message['subject'] ?? '');
            $parts[] = mb_substr(
                MailManager_ProAiGuard_Helper::visibleMailText((string)$message['body_plain'], (string)$message['body_html']),
                0,
                self::MAX_FACT_CHARS
            );
        }

        $byModule = [];

        foreach ($threadId ? MailManager_ProRelate_Model::getLinks($threadId) : [] as $link) {
            $byModule[$link['module']][] = (int)$link['crmid'];
        }

        $count = 0;

        foreach ($byModule as $module => $recordIds) {
            foreach (MailManager_ProAiAccess_Model::getVisibleIds($module, $recordIds) as $recordId) {
                if (++$count > self::MAX_FACT_RECORDS) {
                    break 2;
                }

                $values = Vtiger_Record_Model::getInstanceById($recordId, $module)->getData();
                $parts[] = mb_substr(decode_html(implode("\n", array_filter($values, 'is_scalar'))), 0, self::MAX_FACT_CHARS);
            }
        }

        // the comments of the records: dates and amounts a colleague wrote there are known facts (I20)
        $parts[] = MailManager_ProRecordHistory_Model::getFactText($threadId);

        return implode("\n", $parts);
    }

    /**
     * @param int $threadId
     *
     * @return string the new part of the customer's latest received mail, '' for none
     */
    private static function getCustomerText(int $threadId): string
    {
        $latest = null;

        foreach ($threadId ? MailManager_ProThread_Model::getThreadMessages($threadId) : [] as $message) {
            if ('in' === $message['direction'] && empty($message['is_draft'])) {
                $latest = $message;
            }
        }

        return $latest
            ? mb_substr(MailManager_ProReplyText_Model::forMessage((int)$latest['messageid'], (string)$latest['body_plain']), 0, 6000)
            : '';
    }
}

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
 * The AI part of the Workspace digest (I19):
 * - one line per conversation, written by the cron (stage "AI digest", task "digest", the
 *   fast agent) for the users who opened the digest lately — 10 conversations per call, at
 *   most MAX_NEW_PER_RUN per run, stored with the newest message and the reader language
 *   (df_mailmanager_thread ai_digest*), written again only after a new mail;
 * - the summary on top (task "digest_summary", the quality agent) from the section rows,
 *   kept per user for SUMMARY_TTL while the rows do not change.
 * Mail goes to the AI only wrapped (ProAiGuard) and visible; a conversation flagged as
 * suspicious never goes; what comes back is checked by our code.
 */
class MailManager_ProWorkspaceDigestAi_Model
{
    /** Usage-log features (= agent tasks) */
    public const FEATURE_LINES = 'digest';
    public const FEATURE_SUMMARY = 'digest_summary';

    private const BATCH = 10;
    private const EXCERPTS = 2;
    private const EXCERPT_CHARS = 600;
    private const MAX_NEW_PER_RUN = 60;
    private const LINE_CHARS = 300;

    /** Output budget of a batch of lines — reasoning models (Haiku 5.5) spend their thinking from it too;
     *  1500 left the answer empty (no usage logged, no line) on the first run of 2026-10-19;
     *  4000 was half used (2044) — the batch grows with the conversations */
    private const LINES_MAX_TOKENS = 10000;
    private const SUMMARY_CHARS = 1500;

    /** Output budget of the summary — the quality model's thinking counts too (1200 was tight) */
    private const SUMMARY_MAX_TOKENS = 3000;
    private const SUMMARY_ROWS = 15;
    private const SUMMARY_SECONDS = 30;
    private const SUMMARY_TTL = 600;

    /** Users who opened the digest within so many days get their lines from the cron */
    private const SEEN_DAYS = 14;

    /**
     * @return bool the AI assistant (summary feature) is on and a provider is set
     */
    public static function isEnabled(): bool
    {
        if (!MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_SUMMARY)) {
            return false;
        }

        try {
            return null !== MailManager_ProAiConfig_Model::getProvider();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return string ISO 639-1 code of the current user's language — the language of the lines.
     *                Read from the user (the cron switches users; the core's language is cached once)
     */
    public static function getLanguage(): string
    {
        $user = vglobal('current_user');
        $language = is_object($user) && !empty($user->column_fields['language'])
            ? (string)$user->column_fields['language']
            : (string)Vtiger_Language_Handler::getLanguage();
        $code = strtolower(substr($language, 0, 2));

        // the CRM names Czech by the country (cz_cz)
        return 'cz' === $code ? 'cs' : $code;
    }

    /**
     * Cron stage: the missing lines of the users who opened the digest lately.
     *
     * @param int $deadline unix time the run must finish by
     *
     * @return array log lines
     */
    public static function prepareLines(int $deadline): array
    {
        if (!self::isEnabled()) {
            return [];
        }

        $userIds = MailManager_ProUserSetting_Model::getUserIdsWith(
            MailManager_ProUserSetting_Model::DIGEST_SEEN,
            date('Y-m-d H:i:s', time() - self::SEEN_DAYS * 86400)
        );
        $previousUser = vglobal('current_user');
        $sent = 0;
        $written = 0;
        $log = [];

        try {
            foreach ($userIds as $userId) {
                // the budget counts the conversations sent, not the lines that came back
                if ($sent >= self::MAX_NEW_PER_RUN || $deadline - time() < MailManager_ProAiProvider_Helper::MIN_TIMEOUT) {
                    break;
                }

                // a user without his privileges file would stop the whole cron run (a bare require)
                if (!is_file('user_privileges/user_privileges_' . (int)$userId . '.php')) {
                    continue;
                }

                // the user's own view: his mailboxes, his permissions, his language
                $user = CRMEntity::getInstance('Users');
                $user->retrieveCurrentUserInfoFromFile($userId);

                if ('Active' !== (string)($user->column_fields['status'] ?? '')) {
                    continue;
                }

                vglobal('current_user', $user);
                $pending = [];

                foreach (MailManager_ProWorkspaceDigest_Model::getSections($userId)['sections'] as $items) {
                    foreach ($items as $item) {
                        if (MailManager_ProWorkspaceDigest_Model::LINE_PENDING === $item['line_state']) {
                            $pending[] = $item;
                        }
                    }
                }

                $sent += self::writeLines($pending, $deadline, self::MAX_NEW_PER_RUN - $sent, $log, $written);
            }
        } finally {
            vglobal('current_user', $previousUser);
        }

        if ($written) {
            $log[] = 'AI digest: ' . $written . ' line(s) written';
        }

        return $log;
    }

    /**
     * @param array $items    digest items without a valid line
     * @param int   $deadline
     * @param int   $limit    conversations this call may send
     * @param array $log      log lines (by reference)
     * @param int   $written  lines written (by reference, added to)
     *
     * @return int conversations sent to the AI
     */
    private static function writeLines(array $items, int $deadline, int $limit, array &$log, int &$written): int
    {
        // the full anonymization masks a conversation's own names — one conversation per call
        $batch = MailManager_ProAnonymizer_Helper::LEVEL_FULL === MailManager_ProAnonymizer_Helper::getLevel() ? 1 : self::BATCH;
        $sent = 0;

        foreach (array_chunk(array_slice($items, 0, max(0, $limit)), $batch) as $chunk) {
            if ($deadline - time() < MailManager_ProAiProvider_Helper::MIN_TIMEOUT) {
                break;
            }

            $sent += count($chunk);

            try {
                $written += self::writeBatch($chunk, $deadline, $log);
            } catch (MailManager_ProAiAccountException_Helper | MailManager_ProAiUnavailableException_Helper $e) {
                // the provider refuses or is away — the next run tries again
                $log[] = 'AI digest: ' . $e->getMessage();
                break;
            } catch (Exception $e) {
                // an empty or broken answer — each conversation alone (a smaller answer); one that
                // still fails gets an empty line for its mail, not asked again before a new one
                $log[] = 'AI digest: batch of ' . count($chunk) . ' failed (' . $e->getMessage() . ')';

                if (count($chunk) > 1) {
                    $written += self::writeOneByOne($chunk, $deadline, $log);
                } else {
                    MailManager_ProThread_Model::saveDigestLine((int)$chunk[0]['threadid'], '', (int)$chunk[0]['last_msgid'], self::getLanguage());
                }
            }
        }

        return $sent;
    }

    /**
     * The conversations of a failed batch, one call each.
     *
     * @param array $items
     * @param int   $deadline
     * @param array $log
     *
     * @return int lines written
     */
    private static function writeOneByOne(array $items, int $deadline, array &$log): int
    {
        $written = 0;

        foreach ($items as $item) {
            if ($deadline - time() < MailManager_ProAiProvider_Helper::MIN_TIMEOUT) {
                break;
            }

            try {
                $written += self::writeBatch([$item], $deadline, $log);
            } catch (MailManager_ProAiAccountException_Helper | MailManager_ProAiUnavailableException_Helper $e) {
                $log[] = 'AI digest: ' . $e->getMessage();
                break;
            } catch (Exception $e) {
                $log[] = 'AI digest: conversation ' . $item['threadid'] . ' failed (' . $e->getMessage() . ')';
                MailManager_ProThread_Model::saveDigestLine((int)$item['threadid'], '', (int)$item['last_msgid'], self::getLanguage());
            }
        }

        return $written;
    }

    /**
     * One AI call for up to BATCH conversations.
     *
     * @param array $items
     * @param int   $deadline
     * @param array $log
     *
     * @return int lines written
     * @throws Exception
     */
    private static function writeBatch(array $items, int $deadline, array &$log): int
    {
        $excerpts = MailManager_ProThread_Model::getDigestExcerpts(array_column($items, 'threadid'), self::EXCERPTS);
        $parts = [];
        $sent = [];

        foreach ($items as $item) {
            $threadId = (int)$item['threadid'];
            $text = self::buildConversationText($item, $excerpts[$threadId] ?? []);

            // text that addresses the AI never goes — the conversation is flagged instead
            // (one a user marked safe goes as it is)
            if (MailManager_ProAiSuspicion_Model::STATE_SAFE !== $item['suspicious_state']
                && (MailManager_ProAiGuard_Helper::isInjected($text)
                    || false !== strpos($text, MailManager_ProAiGuard_Helper::HIDDEN_NOTICE))) {
                MailManager_ProAiSuspicion_Model::flag($threadId, 'heuristic');

                continue;
            }

            $parts[] = 'Conversation ' . $threadId . ":\n"
                . MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_MAIL, $text, (string)$threadId);
            $sent[$threadId] = $item;
        }

        if (!$sent) {
            return 0;
        }

        $language = self::getLanguage();
        $result = MailManager_ProAiClient_Model::complete(
            MailManager_ProAiConfig_Model::getProvider(),
            self::FEATURE_LINES,
            1 === count($sent) ? (int)key($sent) : 0,
            'You write the Workspace digest of a CRM user: for each email conversation one short line of at most '
            . '18 words saying what the customer wants or what is still pending, so the user knows what to do '
            . 'without opening it. The line must fit "Waiting on" of the conversation: when we wrote last, say '
            . 'what we wait for from the customer (or what we promised) — never that the customer waits for us; '
            . 'when the customer wrote last, say what they want from us. The NEWEST mail decides. '
            . 'Write in ' . MailManager_ProThreadLanguage_Model::getPromptName($language) . '. '
            . 'Plain text, no greeting, no quotes of the mail. Respond with a single JSON object only: '
            . '{"<conversation id>": "<line>", ...} — exactly the conversation ids given.',
            implode("\n\n", $parts),
            self::LINES_MAX_TOKENS,
            $deadline
        );

        // the model reported an attempt in a batch — each conversation alone, so the right one is flagged
        if (!empty($result['suspicious'])) {
            if (count($sent) > 1) {
                $written = 0;

                foreach ($sent as $item) {
                    if ($deadline - time() < MailManager_ProAiProvider_Helper::MIN_TIMEOUT) {
                        break;
                    }

                    $written += self::writeBatch([$item], $deadline, $log);
                }

                return $written;
            }

            return 0;
        }

        $parsed = MailManager_ProAiClient_Model::parseJsonObject((string)$result['text']);

        if (!is_array($parsed)) {
            $log[] = 'AI digest: unparseable answer for ' . count($sent) . ' conversation(s)';
            $parsed = [];
        }

        $written = 0;

        // only the conversations we sent, only plain text of a sane length; a conversation the
        // answer left out gets an empty line for this mail — not asked again before a new one
        foreach ($sent as $threadId => $item) {
            $line = self::cleanText($parsed[$threadId] ?? ($parsed[(string)$threadId] ?? null), self::LINE_CHARS);
            MailManager_ProThread_Model::saveDigestLine((int)$threadId, $line, (int)$item['last_msgid'], $language);
            $written += '' !== $line ? 1 : 0;
        }

        return $written;
    }

    /**
     * @param array $item     digest item
     * @param array $messages the newest mails (ProThread_Model::getDigestExcerpts())
     *
     * @return string the conversation as the AI reads it — visible text only
     */
    private static function buildConversationText(array $item, array $messages): string
    {
        $lines = [
            'Subject: ' . $item['subject'],
            'Waiting on: ' . ('customer' === $item['waits_on'] ? 'the customer (we wrote last)' : 'us (the customer wrote last)'),
            'Days since the last mail: ' . $item['days'],
        ];

        foreach (array_values($messages) as $index => $message) {
            $lines[] = '';
            $lines[] = '--- ' . (0 === $index ? 'NEWEST mail, ' : 'Earlier mail, ')
                . ('out' === $message['direction'] ? 'our reply' : 'from the customer ' . decode_html((string)$message['from_name'])) . ' ---';
            $lines[] = mb_substr(
                MailManager_ProAiGuard_Helper::visibleMailText((string)$message['body_plain'], (string)$message['body_html']),
                0,
                self::EXCERPT_CHARS
            );
        }

        return implode("\n", $lines);
    }

    /**
     * The summary on top of the digest — kept SUMMARY_TTL while the rows stay the same.
     *
     * @param int   $userId
     * @param array $sections section => items (MailManager_ProWorkspaceDigest_Model::getSections())
     * @param bool  $refresh  compute it again whatever is kept
     *
     * @return string plain text, '' when there is nothing to say or the AI failed
     */
    public static function getSummary(int $userId, array $sections, bool $refresh): string
    {
        $rows = [];
        $threadIds = [];
        // what the kept summary depends on: the rows without the day count (it moves during
        // the day), in any order — opening a conversation must not make it new
        $keys = [];

        foreach ($sections as $section => $items) {
            foreach (array_slice($items, 0, self::SUMMARY_ROWS) as $item) {
                $priority = '' !== $item['priority'] ? $item['priority'] : 'normal';

                // nothing of a suspicious conversation goes to the AI — only that it is there
                if (MailManager_ProWorkspaceDigest_Model::LINE_SUSPICIOUS === $item['line_state']) {
                    $rows[] = implode(' | ', [$section, $item['mailbox_email'], '-', '(suspicious content, check by hand)', $item['days'] . ' days', $priority, '']);
                    $keys[] = implode('|', [$section, $item['threadid'], $item['last_at'], $priority, 'suspicious']);

                    continue;
                }

                $rows[] = implode(' | ', [
                    $section,
                    $item['mailbox_email'],
                    $item['contact'],
                    $item['subject'],
                    $item['days'] . ' days',
                    $priority,
                    $item['line'],
                ]);
                $threadIds[] = (int)$item['threadid'];
                $keys[] = implode('|', [$section, $item['threadid'], $item['last_at'], $priority, $item['line']]);
            }
        }

        if (!$rows) {
            return '';
        }

        $language = self::getLanguage();
        sort($keys);
        $hash = md5(implode("\n", $keys));
        $kept = json_decode(MailManager_ProUserSetting_Model::get($userId, MailManager_ProUserSetting_Model::DIGEST_SUMMARY), true);

        if (!$refresh && is_array($kept) && time() - (int)($kept['time'] ?? 0) < self::SUMMARY_TTL
            && ($kept['hash'] ?? '') === $hash && ($kept['lang'] ?? '') === $language) {
            return (string)$kept['text'];
        }

        try {
            $result = MailManager_ProAiClient_Model::complete(
                MailManager_ProAiConfig_Model::getProvider(),
                self::FEATURE_SUMMARY,
                0,
                'You write the top of a CRM user\'s Workspace digest: 2 to 4 short sentences in '
                . MailManager_ProThreadLanguage_Model::getPromptName($language) . ' saying what needs attention '
                . 'first — urgent ones, the longest waiting, customers who do not answer; name the mailbox and the '
                . 'customer where it helps. Plain text only, no lists, no headings. The rows are "section | mailbox | '
                . 'customer | subject | days since the last mail | priority | line": mine = waits for the user\'s '
                . 'answer, unassigned = nobody took it yet in a shared mailbox, customer = we wait for the customer.',
                MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_DATA, implode("\n", $rows), 'workspace'),
                self::SUMMARY_MAX_TOKENS,
                time() + self::SUMMARY_SECONDS,
                null,
                // the full anonymization: the names of all these conversations
                MailManager_ProAnonymizer_Helper::LEVEL_FULL === MailManager_ProAnonymizer_Helper::getLevel()
                    ? MailManager_ProAiDictionary_Model::getForThreads($threadIds)
                    : null
            );
        } catch (Exception $e) {
            return '';
        }

        // a summary the model flagged is not shown (nor kept)
        $text = empty($result['suspicious']) ? self::cleanText($result['text'], self::SUMMARY_CHARS, true) : '';
        MailManager_ProUserSetting_Model::set($userId, MailManager_ProUserSetting_Model::DIGEST_SUMMARY, (string)json_encode([
            'time' => time(),
            'hash' => $hash,
            'lang' => $language,
            'text' => $text,
        ], JSON_UNESCAPED_UNICODE));

        return $text;
    }

    /**
     * @param mixed $value     what the model answered
     * @param int   $maxChars
     * @param bool  $keepLines keep the line breaks (the summary)
     *
     * @return string plain text without HTML, '' for anything else
     */
    private static function cleanText($value, int $maxChars, bool $keepLines = false): string
    {
        if (!is_string($value)) {
            return '';
        }

        $text = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = $keepLines
            ? (string)preg_replace(["/[ \t]+/u", "/\n{3,}/u"], [' ', "\n\n"], $text)
            : (string)preg_replace('/\s+/u', ' ', $text);

        return trim(mb_substr(trim($text), 0, $maxChars));
    }
}

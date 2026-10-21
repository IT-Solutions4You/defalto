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
 * On-demand AI features of an open conversation: thread summary (cached on
 * the thread until a new message arrives) and a reply draft for the composer
 * (editable text — never sent automatically)
 */
class MailManager_ProAiAssist_Model
{
    public const TONES = ['mystyle', 'professional', 'friendly', 'short'];
    public const LANGUAGES = ['auto', 'sk', 'cz', 'en', 'de'];

    public const MAX_MESSAGES = 10;

    /** Output budget of a draft — a long draft to improve comes back whole, the quality model
     *  thinks within it too (2500 left little room) */
    private const DRAFT_MAX_TOKENS = 6000;
    private const BODY_CHARS = 2500;

    /* Style profile learning: sample size and prompt budget. The total is
       deliberately modest — style is visible from a couple dozen mails and
       the call must finish well within the 90s provider timeout */
    private const PROFILE_SAMPLES = 60;
    private const PROFILE_SAMPLE_CHARS = 900;
    private const PROFILE_TOTAL_CHARS = 25000;

    /**
     * Summary of the conversation — returns the cached one for free while no
     * new message arrived, otherwise one AI call refreshes the cache
     *
     * @param array $thread thread row
     * @return array ['success' => bool, 'summary' => string, 'cached' => bool, 'message' => string]
     */
    public static function summarize(array $thread)
    {
        if (!MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_SUMMARY)) {
            return [
                'success' => false,
                'summary' => '',
                'cached' => false,
                'message' => vtranslate('LBL_AI_SUMMARY_DISABLED', 'MailManager'),
            ];
        }

        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $threadId = (int)$thread['threadid'];

        $latestResult = $adb->pquery(
            // an unsent draft is not part of the conversation — saving one
            // must not invalidate the cached summary
            'SELECT MAX(messageid) AS latest FROM df_mailmanager_message WHERE threadid = ? AND is_draft = 0',
            [$threadId]
        );
        $latestMessageId = (int)$adb->query_result($latestResult, 0, 'latest');

        // the linked records' history the summary reads (I20) — the cached summary serves only
        // a reader with the same history (records he may see, no new comment since)
        $context = MailManager_ProRecordHistory_Model::getContextHash($threadId);

        if ((string)$thread['ai_summary'] !== '' && (int)$thread['ai_summary_msgid'] === $latestMessageId
            && (string)($thread['ai_summary_ctx'] ?? '') === $context) {
            return ['success' => true, 'summary' => (string)$thread['ai_summary'], 'cached' => true, 'message' => ''];
        }

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
            if (!$provider) {
                return [
                    'success' => false,
                    'summary' => '',
                    'cached' => false,
                    'message' => vtranslate('LBL_AI_DISABLED', 'MailManager'),
                ];
            }

            $conversation = self::buildConversationPrompt($thread);
            // the linked records' description, solution and comments as context (I20)
            $history = MailManager_ProRecordHistory_Model::buildPrompt(
                $thread,
                MailManager_ProAiConfig_Model::FEATURE_SUMMARY,
                mb_strlen($conversation)
            );
            $result = MailManager_ProAiClient_Model::complete(
                $provider,
                MailManager_ProAiConfig_Model::FEATURE_SUMMARY,
                $threadId,
                'You summarize email conversations for a CRM user. Reply with a concise summary of '
                . '2-4 sentences in the same language as the conversation. Cover the current status, '
                . 'the key requests and who is expected to act next. Plain text only, no headings.'
                . ('' !== $history['prompt']
                    ? ' The CRM records block (description, solution, comments of the linked records) is '
                        . 'context: use it for the status and what was already done, the summary is of the conversation.'
                    : ''),
                $conversation . $history['prompt'],
                2000
            );

            $summary = trim($result['text']);
            if ($summary === '') {
                return [
                    'success' => false,
                    'summary' => '',
                    'cached' => false,
                    'message' => vtranslate('LBL_AI_EMPTY_RESPONSE', 'MailManager'),
                ];
            }

            $adb->pquery(
                'UPDATE df_mailmanager_thread SET ai_summary = ?, ai_summary_msgid = ?, ai_summary_ctx = ? WHERE threadid = ?',
                [$summary, $latestMessageId, $context, $threadId]
            );

            return [
                'success' => true,
                'summary' => $summary,
                'cached' => false,
                'message' => '',
                // written by the backup provider while the main AI is down (I17)
                'backup' => (string)($result['backup'] ?? ''),
            ];
        } catch (Exception $e) {
            return ['success' => false, 'summary' => '', 'cached' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reply draft for the composer — HTML ready for CKEditor
     *
     * @param array $thread thread row
     * @param array $mailbox mailbox row
     * @param string $tone mystyle | professional | friendly | short
     * @param string $language auto | sk | cz | en | de
     * @param string $currentHtml the user's draft (editor HTML) — non-empty
     *                            switches to IMPROVE mode (correct/rewrite it)
     * @return array ['success' => bool, 'draft_html' => string, 'improved' => bool, 'message' => string]
     */
    public static function draft(array $thread, array $mailbox, $tone, $language, $currentHtml = '')
    {
        if (!MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_DRAFT)) {
            return [
                'success' => false,
                'draft_html' => '',
                'message' => vtranslate('LBL_AI_DRAFT_DISABLED', 'MailManager'),
            ];
        }
        // The user's own draft flips the call into IMPROVE mode — the AI
        // corrects/rewrites THEIR text instead of composing a new reply
        $currentText = trim(MailManager_ProMailText_Model::htmlToText((string)$currentHtml));
        $improve = $currentText !== '';

        if (!in_array($tone, self::TONES, true) || (!$improve && $tone === 'mystyle')) {
            // "keep my style" makes sense only when there IS a style to keep
            $tone = 'professional';
        }
        if (!in_array($language, self::LANGUAGES, true)) {
            $language = 'auto';
        }

        $toneInstructions = [
            'mystyle' => 'Keep the author\'s wording, structure and style — ONLY fix grammar, '
                . 'spelling and punctuation mistakes.',
            'professional' => 'Use a professional, courteous business tone.',
            'friendly' => 'Use a warm, friendly and personal tone.',
            'short' => 'Be as brief as possible — a few short sentences, no filler.',
        ];
        $languageNames = ['sk' => 'Slovak', 'cz' => 'Czech', 'en' => 'English', 'de' => 'German'];
        // auto = the language of the conversation (the customer's most
        // recent message) — a draft written in another language is
        // translated; an explicit language wins over everything
        // "The language of the customer's most recent message" alone left
        // the model guessing among the draft, our earlier replies and the
        // style guide (a Slovak draft to a German customer stayed Slovak).
        // The conversation's language is a stored fact (ThreadLanguage) and
        // named outright; while it cannot be told, the customer's own words
        // are handed over as the sample to go by
        $languageSample = '';
        $languageCode = '';
        if ($language !== 'auto') {
            $targetLanguage = $languageNames[$language];
        } else {
            $languageCode = MailManager_ProThreadLanguage_Model::resolve((int)$thread['threadid']);
            if ($languageCode !== '') {
                $targetLanguage = MailManager_ProThreadLanguage_Model::getPromptName($languageCode);
            } else {
                $languageSample = MailManager_ProThreadLanguage_Model::getSample((int)$thread['threadid']);
                $targetLanguage = $languageSample !== ''
                    ? 'the CUSTOMER LANGUAGE'
                    : 'the language of the customer\'s most recent message';
            }
        }
        $languageInstruction = $improve
            ? 'Deliver the result in ' . $targetLanguage
                . ' — translate the draft when it is written in another language.'
            : 'Write the reply in ' . $targetLanguage . '.';

        MailManager_ProInbox_Model::useUtf8mb4();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $knowledgeSources = [];
        // what the draft may rely on besides the conversation and the records (I16)
        $factSources = $currentText;
        $signName = trim((string)getUserFullName($currentUser->getId()));

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
            if (!$provider) {
                return [
                    'success' => false,
                    'draft_html' => '',
                    'message' => vtranslate('LBL_AI_DISABLED', 'MailManager'),
                ];
            }

            if ($improve) {
                $systemPrompt = 'You polish the user\'s draft reply to an email conversation, written '
                    . 'on behalf of the mailbox "' . $mailbox['name'] . '" <' . $mailbox['email'] . '>'
                    . ($signName !== '' ? ' by ' . $signName : '') . '. '
                    . $toneInstructions[$tone] . ' ' . $languageInstruction . ' '
                    . 'Preserve the meaning and every fact, number, date and commitment of the draft — '
                    . 'never add or remove information. Use the conversation only for the salutation '
                    . 'and to resolve ambiguous references. '
                    . 'A marker like [[IMG1]] stands for a picture in the draft — keep every marker '
                    . 'exactly as written, on its own line at the place it stands. '
                    . 'Output only the improved reply body as plain text (no subject line), paragraphs '
                    . 'separated by blank lines.';
                $userPrompt = self::buildConversationPrompt($thread)
                    . "\n\nMy draft reply to improve:\n"
                    . MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_DRAFT, $currentText);
            } else {
                $systemPrompt = 'You draft a reply to the most recent inbound message of an email conversation, '
                    . 'on behalf of the mailbox "' . $mailbox['name'] . '" <' . $mailbox['email'] . '>'
                    . ($signName !== '' ? ', written by ' . $signName : '') . '. '
                    . $toneInstructions[$tone] . ' ' . $languageInstruction . ' '
                    . 'Answer the open points of the conversation. Never invent facts, prices, dates or '
                    . 'commitments that are not present in the conversation. '
                    . 'Output only the reply body as plain text (no subject line), paragraphs separated '
                    . 'by blank lines, ending with an appropriate sign-off'
                    . ($signName !== '' ? ' and the name ' . $signName : '') . '.';
                $userPrompt = self::buildConversationPrompt($thread);

                // similar solved cases (past replies + closed tickets) —
                // deterministic topic-overlap search, no AI cost
                $similarCases = MailManager_ProAiTopics_Model::buildSimilarCasesPrompt($thread);

                // the company's and the writer's own knowledge on the topic
                $knowledge = MailManager_ProKnowledge_Model::buildPrompt(
                    $thread,
                    (int)$currentUser->getId()
                );

                // the description, solution and comments of the linked records go right after
                // the conversation, within what the agent's input leaves (I20)
                $history = MailManager_ProRecordHistory_Model::buildPrompt(
                    $thread,
                    MailManager_ProAiConfig_Model::FEATURE_DRAFT,
                    mb_strlen($userPrompt . $similarCases . $knowledge['prompt'])
                );

                if ('' !== $history['prompt']) {
                    $systemPrompt .= MailManager_ProRecordHistory_Model::getDraftRules();
                }

                $userPrompt .= $history['prompt'] . $similarCases . $knowledge['prompt'];
                $knowledgeSources = $knowledge['sources'];
                $factSources = $knowledge['prompt'] . "\n" . $history['facts'];
            }

            // The learned (and possibly hand-edited) mailbox style guide —
            // "my style" needs none, the style is already in the text
            $styleProfile = self::getStyleProfile((int)$mailbox['mailboxid']);
            if ($styleProfile !== '' && $tone !== 'mystyle') {
                $systemPrompt .= ' Match the author\'s writing style described in this guide — take the '
                    . 'style from it, never the language (the requested tone and language above win '
                    . 'on conflicts): ' . $styleProfile;
            }
            // The language is the last word of both prompts — the style guide
            // (full of the author's Slovak phrases) and the draft otherwise
            // pull a small model back to their language
            $systemPrompt .= ' LANGUAGE: the output must be entirely in ' . $targetLanguage
                . ', whatever the language of the draft or the style guide.';
            $userPrompt .= "\n\nWrite the result in " . $targetLanguage . '.';
            if ($languageSample !== '') {
                // the customer's words stay in the user message, wrapped — never in the system prompt (I16)
                $systemPrompt .= ' The CUSTOMER LANGUAGE is the language the sample of the customer\'s latest '
                    . 'message at the end of the user message is written in — tell it from the sample alone, '
                    . 'not from the draft, our earlier replies or the style guide.';
                $userPrompt .= "\n\nSample of the customer's latest message (for the CUSTOMER LANGUAGE):\n"
                    . MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_MAIL, $languageSample, 'language sample');
            }

            $result = MailManager_ProAiClient_Model::complete(
                $provider,
                MailManager_ProAiConfig_Model::FEATURE_DRAFT,
                (int)$thread['threadid'],
                $systemPrompt,
                $userPrompt,
                self::DRAFT_MAX_TOKENS
            );

            // The model copies literal entities ("&iacute;") from cached mail
            // bodies into the draft; decoding is safe — the draft is TEXT and
            // textToHtml() re-escapes whatever needs escaping
            $draft = trim(html_entity_decode($result['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($draft === '') {
                return [
                    'success' => false,
                    'draft_html' => '',
                    'improved' => false,
                    'message' => vtranslate('LBL_AI_EMPTY_RESPONSE', 'MailManager'),
                ];
            }

            // facts of the draft found nowhere in the conversation, the records or the
            // knowledge are highlighted for the user to check (I16) — never blocked
            $unverified = MailManager_ProDraftFacts_Helper::getUnverified(
                $draft,
                MailManager_ProDraftCheck_Model::getFactSources((int)$thread['threadid'], $mailbox) . "\n" . $factSources
            );

            return [
                'success' => true,
                'draft_html' => MailManager_ProDraftFacts_Helper::highlight(self::textToHtml($draft), $unverified),
                'unverified' => array_column($unverified, 'value'),
                // written by the backup provider while the main AI is down (I17)
                'backup' => (string)($result['backup'] ?? ''),
                // mail of the conversation tried to instruct the AI — the user checks the draft twice
                'suspicious' => !empty($result['suspicious'])
                    || MailManager_ProAiSuspicion_Model::isFlagged((int)$thread['threadid']),
                // improved = the result REPLACES the user's text (undo kept in JS)
                'improved' => $improve,
                // the conversation's language the "Auto" choice went by
                'language' => MailManager_ProThreadLanguage_Model::getName(
                    $languageCode,
                    (string)Vtiger_Language_Handler::getLanguage()
                ),
                // what the knowledge gave the draft — shown under it
                'sources' => $knowledgeSources,
                'message' => '',
            ];
        } catch (Exception $e) {
            return ['success' => false, 'draft_html' => '', 'improved' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Conversation context for the prompts — the newest messages, oldest first
     *
     * @param array $thread
     * @return string
     */
    /**
     * Learns the mailbox writing style from its SENT mail: one AI call over
     * a sample of outgoing messages produces a compact editable style guide,
     * stored in the config and attached to every draft prompt. Transparent
     * learning — the admin sees and can correct what was learned
     *
     * The guide is written in the language of the user who asked for it (My
     * Preferences) — he reads and edits it; "the language the author uses
     * most" gave a Spanish guide to a user of a Slovak CRM
     *
     * @param array $mailbox mailbox row
     * @return array ['success' => bool, 'profile' => string, 'message' => string]
     */
    public static function generateStyleProfile(array $mailbox)
    {
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT body_html, body_plain FROM df_mailmanager_message
             WHERE mailboxid = ? AND direction = 'out' AND is_draft = 0
             ORDER BY sent_at DESC LIMIT " . self::PROFILE_SAMPLES,
            [$mailbox['mailboxid']]
        );

        $samples = [];
        $total = 0;
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count && $total < self::PROFILE_TOTAL_CHARS; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            // the quoted original below a reply is not the author's writing
            $html = preg_replace('#<blockquote\b[^>]*>.*?</blockquote>#is', '', (string)$row['body_html']);
            $text = trim($html !== ''
                ? MailManager_ProMailText_Model::htmlToText($html)
                : (string)$row['body_plain']);
            if (mb_strlen($text) < 60) {
                continue;
            }
            $text = mb_substr($text, 0, self::PROFILE_SAMPLE_CHARS);
            $samples[] = $text;
            $total += mb_strlen($text);
        }

        if (count($samples) < 3) {
            return [
                'success' => false,
                'profile' => '',
                'message' => vtranslate('LBL_STYLE_NOT_ENOUGH', 'MailManager'),
            ];
        }

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
            if (!$provider) {
                return [
                    'success' => false,
                    'profile' => '',
                    'message' => vtranslate('LBL_AI_DISABLED', 'MailManager'),
                ];
            }

            $result = MailManager_ProAiClient_Model::complete(
                $provider,
                'styleprofile',
                0,
                'You analyze how the author of the following sent emails writes and produce a compact '
                . 'STYLE GUIDE another writer could follow to sound exactly like them. Cover: salutations '
                . 'and closings per language they use, sign-off/signature, formality level, typical '
                . 'sentence length and structure, characteristic phrases, and language habits. '
                . 'Describe only the STYLE — never quote customer facts, names, prices or case details. '
                . 'At most 250 words, plain-text bullet points. Write the guide itself in '
                . self::getGuideLanguage() . ' — whatever languages the emails are in (the phrases '
                . 'the author uses are quoted as they are).',
                "Sent emails of the author:\n\n" . implode("\n", array_map(function ($sample) {
                    return MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_MAIL, $sample);
                }, $samples)),
                2000
            );

            $profile = trim($result['text']);
            if ($profile === '') {
                return [
                    'success' => false,
                    'profile' => '',
                    'message' => vtranslate('LBL_AI_EMPTY_RESPONSE', 'MailManager'),
                ];
            }

            MailManager_ProAiConfig_Model::set('ai_style_profile_' . (int)$mailbox['mailboxid'], $profile);

            return ['success' => true, 'profile' => $profile, 'message' => ''];
        } catch (Exception $e) {
            return ['success' => false, 'profile' => '', 'message' => $e->getMessage()];
        }
    }

    /**
     * @return string the current user's CRM language for a prompt — the name
     *                of the installed language pack plus its locale code
     */
    private static function getGuideLanguage()
    {
        $prefix = (string)Vtiger_Language_Handler::getLanguage();
        $label = trim((string)Vtiger_Language_Handler::getLanguageLabel($prefix));

        return ($label !== '' ? '"' . $label . '" ' : '') . '(locale ' . $prefix . ')';
    }

    /**
     * Saves the style guide of a mailbox edited by hand.
     *
     * @param int    $mailboxId
     * @param string $profile
     *
     * @return void
     */
    public static function saveStyleProfile(int $mailboxId, string $profile): void
    {
        MailManager_ProAiConfig_Model::set('ai_style_profile_' . $mailboxId, trim($profile));
    }

    /**
     * The stored (possibly hand-edited) style guide of a mailbox
     *
     * @param int $mailboxId
     * @return string '' when none was generated
     */
    public static function getStyleProfile($mailboxId)
    {
        return trim(MailManager_ProAiConfig_Model::get('ai_style_profile_' . (int)$mailboxId));
    }

    public static function buildConversationPrompt(array $thread)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT direction, from_name, from_email, sent_at, body_plain, body_html
             FROM df_mailmanager_message WHERE threadid = ? AND is_draft = 0
             ORDER BY sent_at DESC LIMIT ' . self::MAX_MESSAGES,
            [$thread['threadid']]
        );

        $messages = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $messages[] = $adb->raw_query_result_rowdata($result, $i);
        }
        $messages = array_reverse($messages);

        // every message is third-party text (our replies quote the customer) — the
        // visible text only, inside the untrusted tags (I16)
        $lines = [MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_MAIL, (string)$thread['subject'], 'subject'), ''];
        foreach ($messages as $index => $message) {
            // the sender's name is the sender's text too — inside the wrapper
            $from = trim($message['from_name'] . ' <' . $message['from_email'] . '>');
            $lines[] = '--- ' . ($message['direction'] === 'out' ? 'Our reply' : 'Customer message') . ' ---';
            $lines[] = MailManager_ProAiGuard_Helper::wrap(
                MailManager_ProAiGuard_Helper::SOURCE_MAIL,
                'From: ' . $from . "\nDate: " . $message['sent_at'] . "\n\n" . mb_substr(
                    MailManager_ProAiGuard_Helper::visibleMailText((string)$message['body_plain'], (string)$message['body_html']),
                    0,
                    self::BODY_CHARS
                ),
                'message ' . ($index + 1)
            );
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Plain text → simple HTML paragraphs for CKEditor
     *
     * @param string $text
     * @return string
     */
    public static function textToHtml($text)
    {
        $paragraphs = preg_split("/\n{2,}/", str_replace("\r\n", "\n", $text)) ?: [];
        $html = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph !== '') {
                $html .= '<p>' . nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8'), false) . '</p>';
            }
        }

        return $html;
    }
}

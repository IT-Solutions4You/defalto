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
 * Translation of a received message into the reader's language — the
 * language of his CRM (My Preferences). Asked for by a click, never by
 * itself; a translation is kept per message and language, so the next
 * opening (or a colleague reading the same language) costs nothing
 */
class MailManager_ProTranslation_Model
{
    /** Text of one message handed over */
    private const MAX_CHARS = 12000;

    /** Output budget — a whole translation of MAX_CHARS (about 4000 tokens) and the thinking of
     *  a reasoning model; 4000 could cut a long mail */
    private const MAX_TOKENS = 12000;

    /**
     * @return string ISO 639-1 code of the current user's CRM language
     */
    public static function getUserLanguage()
    {
        $code = strtolower(substr((string)Vtiger_Language_Handler::getLanguage(), 0, 2));

        // the CRM names Czech by the country (cz_cz)
        return $code === 'cz' ? 'cs' : $code;
    }

    /**
     * Offer the translation in this conversation? — the AI feature is on and
     * the conversation is not in the reader's language (or its language is
     * not known yet)
     *
     * @param array $thread thread row
     * @return bool
     */
    public static function isOffered(array $thread)
    {
        if (!MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_TRANSLATE)) {
            return false;
        }
        $language = MailManager_ProThreadLanguage_Model::get((int)$thread['threadid']);

        return $language === '' || $language !== self::getUserLanguage();
    }

    /**
     * @param array $thread thread row (the mailbox access is checked by the caller)
     * @param int $messageId a received message of the thread
     * @return array ['success' => bool, 'html' => string, 'language' => string, 'message' => string]
     */
    public static function translate(array $thread, $messageId)
    {
        $failed = ['success' => false, 'html' => '', 'language' => '', 'message' => ''];
        if (!self::isOffered($thread)) {
            return $failed;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT messageid FROM df_mailmanager_message
             WHERE messageid = ? AND threadid = ? AND direction = 'in'",
            [(int)$messageId, (int)$thread['threadid']]
        );
        if (!$adb->num_rows($result)) {
            return $failed;
        }

        $language = self::getUserLanguage();
        $languageName = MailManager_ProThreadLanguage_Model::getName(
            $language,
            (string)Vtiger_Language_Handler::getLanguage()
        );
        MailManager_ProInbox_Model::useUtf8mb4();
        $cached = $adb->pquery(
            'SELECT body FROM df_mailmanager_translation WHERE messageid = ? AND language = ?',
            [(int)$messageId, $language]
        );
        if ($adb->num_rows($cached)) {
            return [
                'success' => true,
                'html' => MailManager_ProAiAssist_Model::textToHtml((string)$adb->query_result($cached, 0, 'body')),
                'language' => $languageName,
                'message' => '',
            ];
        }

        // what the sender wrote now — the quoted history is a translation of its own
        $text = trim(MailManager_ProReplyText_Model::forMessage((int)$messageId));
        if ($text === '') {
            return $failed;
        }

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
            if (!$provider) {
                return ['message' => vtranslate('LBL_AI_DISABLED', 'MailManager')] + $failed;
            }
            $completion = MailManager_ProAiClient_Model::complete(
                $provider,
                MailManager_ProAiConfig_Model::FEATURE_TRANSLATE,
                (int)$thread['threadid'],
                'You translate an email into ' . MailManager_ProThreadLanguage_Model::getPromptName($language)
                . '. Translate the whole text faithfully — keep the paragraphs, lists and line breaks, keep '
                . 'names, numbers, codes, addresses and links exactly as they are. Output only the '
                . 'translation as plain text, nothing else.',
                mb_substr($text, 0, self::MAX_CHARS),
                self::MAX_TOKENS
            );
        } catch (Exception $e) {
            return ['message' => $e->getMessage()] + $failed;
        }
        $translation = trim(html_entity_decode((string)$completion['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($translation === '') {
            return ['message' => vtranslate('LBL_AI_EMPTY_RESPONSE', 'MailManager')] + $failed;
        }

        $adb->pquery(
            'INSERT INTO df_mailmanager_translation (messageid, language, body, createdtime) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE body = ?',
            [(int)$messageId, $language, $translation, date('Y-m-d H:i:s'), $translation]
        );

        return [
            'success' => true,
            'html' => MailManager_ProAiAssist_Model::textToHtml($translation),
            'language' => $languageName,
            'message' => '',
        ];
    }
}

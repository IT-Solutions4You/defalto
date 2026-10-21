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
 * The language of a conversation — the language the customer wrote the first
 * received mail in. Told once (by the AI sorting, which reads that mail
 * anyway, or by one small AI call at the first draft), stored with the
 * conversation and then handed to the reply prompts as a plain fact; the
 * first language stays, later mail in another one does not change it
 */
class MailManager_ProThreadLanguage_Model
{
    /** Output budget of the detection — two letters, but a reasoning model thinks within it too */
    private const MAX_TOKENS = 4000;

    /**
     * @param mixed $value what the AI answered
     * @return string ISO 639-1 code (two lowercase letters), '' when it is none
     */
    public static function normalize($value)
    {
        $code = strtolower(trim((string)$value, " \t\n\r\0\x0B\"'.`"));

        return preg_match('/^[a-z]{2}$/', $code) ? $code : '';
    }

    /**
     * Stores the language of a conversation that has none yet — the first one stays
     *
     * @param int $threadId
     * @param string $code ISO 639-1 code
     * @return void
     */
    public static function store($threadId, $code)
    {
        $code = self::normalize($code);
        if ($code === '' || !$threadId) {
            return;
        }
        PearDatabase::getInstance()->pquery(
            "UPDATE df_mailmanager_thread SET customer_language = ?
             WHERE threadid = ? AND (customer_language IS NULL OR customer_language = '')",
            [$code, (int)$threadId]
        );
    }

    /**
     * @param int $threadId
     * @return string the stored ISO 639-1 code, '' when not known yet
     */
    public static function get($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT customer_language FROM df_mailmanager_thread WHERE threadid = ?',
            [(int)$threadId]
        );

        return $adb->num_rows($result) ? self::normalize($adb->query_result($result, 0, 'customer_language')) : '';
    }

    /**
     * The language of the conversation — the stored one, otherwise told now
     * from the customer's newest mail (one small AI call) and stored
     *
     * @param int $threadId
     * @return string ISO 639-1 code, '' when it cannot be told (nobody wrote
     *                yet, the AI is off or failed)
     */
    public static function resolve($threadId)
    {
        $code = self::get($threadId);
        if ($code !== '') {
            return $code;
        }
        $sample = self::getSample($threadId);
        if ($sample === '') {
            return '';
        }

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
            if (!$provider) {
                return '';
            }
            $result = MailManager_ProAiClient_Model::complete(
                $provider,
                // a short detection — the fast model
                'language',
                (int)$threadId,
                'You tell the language a text is written in. Answer with its ISO 639-1 code only '
                . '(two lowercase letters, e.g. en, de, sk) — nothing else.',
                $sample,
                self::MAX_TOKENS
            );
            $code = self::normalize($result['text']);
        } catch (Exception $e) {
            return '';
        }
        self::store($threadId, $code);

        return $code;
    }

    /**
     * The customer's own words of the newest received message (without the
     * quoted history) — what a language is told from
     *
     * @param int $threadId
     * @return string one line, at most 300 characters; '' when nobody wrote yet
     */
    public static function getSample($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT messageid FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' AND is_draft = 0 ORDER BY sent_at DESC LIMIT 1",
            [(int)$threadId]
        );
        if (!$adb->num_rows($result)) {
            return '';
        }
        $text = MailManager_ProReplyText_Model::forMessage((int)$adb->query_result($result, 0, 'messageid'));
        $text = trim(preg_replace('/\s+/u', ' ', str_replace('"', '', (string)$text)));

        return mb_substr($text, 0, 300);
    }

    /**
     * @param string $code ISO 639-1 code
     * @param string $locale language the name is given in ('en', 'sk_sk'…)
     * @return string the language's name, the upper-cased code without the intl extension
     */
    public static function getName($code, $locale = 'en')
    {
        $code = self::normalize($code);
        if ($code === '') {
            return '';
        }
        $name = class_exists('Locale') ? (string)Locale::getDisplayLanguage($code, $locale) : '';

        return $name !== '' && strcasecmp($name, $code) !== 0 ? $name : strtoupper($code);
    }

    /**
     * @param string $code ISO 639-1 code
     * @return string how a prompt names the language — its English name and the code
     */
    public static function getPromptName($code)
    {
        $code = self::normalize($code);
        $name = self::getName($code, 'en');

        return ($name !== strtoupper($code) ? $name . ' — ' : '') . 'the language with the ISO 639-1 code "' . $code . '"';
    }

    /**
     * @param int $threadId
     * @return string the language's name for the current user, '' when not known yet
     */
    public static function getDisplayName($threadId)
    {
        return self::getName(self::get($threadId), (string)Vtiger_Language_Handler::getLanguage());
    }
}

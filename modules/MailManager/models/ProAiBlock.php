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
 * The AI provider refuses the account (no credit, rejected key, limit): the module
 * remembers it. The background jobs stop asking for a while instead of failing every
 * mail, the inbox tells the user why nothing gets sorted and what to do. The first
 * answer the provider gives again lifts it.
 */
class MailManager_ProAiBlock_Model
{
    private const KEY_REASON = 'ai_block_reason';
    private const KEY_MESSAGE = 'ai_block_message';
    private const KEY_TIME = 'ai_block_time';

    /** Seconds the background jobs leave the provider alone before the next try */
    private const RETRY_AFTER = 600;

    /** @var bool the user asked for this run himself: try the provider whatever the wait */
    private static bool $forced = false;

    /**
     * Remembers an account refusal.
     *
     * @param MailManager_ProAiAccountException_Helper $exception refusal of the provider
     *
     * @return void
     */
    public static function record(MailManager_ProAiAccountException_Helper $exception): void
    {
        MailManager_ProAiConfig_Model::set(self::KEY_REASON, $exception->getReason());
        MailManager_ProAiConfig_Model::set(self::KEY_MESSAGE, mb_substr($exception->getMessage(), 0, 500));
        MailManager_ProAiConfig_Model::set(self::KEY_TIME, (string)time());
    }

    /**
     * Lifts the block: the provider answered, whatever blocked the account is gone.
     *
     * @return void
     */
    public static function clear(): void
    {
        if ('' !== MailManager_ProAiConfig_Model::get(self::KEY_REASON)) {
            MailManager_ProAiConfig_Model::set(self::KEY_REASON, '');
        }
    }

    /**
     * Marks a run the user started himself (Refresh): it asks the provider at once.
     *
     * @param bool $forced false = back to waiting (the provider just refused again)
     *
     * @return void
     */
    public static function force(bool $forced = true): void
    {
        self::$forced = $forced;
    }

    /**
     * Returns the refusal the background jobs still wait out.
     *
     * @return MailManager_ProAiAccountException_Helper|null null when the provider may be asked
     */
    public static function getWaiting(): ?MailManager_ProAiAccountException_Helper
    {
        $reason = MailManager_ProAiConfig_Model::get(self::KEY_REASON);

        if ('' === $reason || self::$forced
            || time() - (int)MailManager_ProAiConfig_Model::get(self::KEY_TIME) >= self::RETRY_AFTER) {
            return null;
        }

        return new MailManager_ProAiAccountException_Helper(
            $reason,
            MailManager_ProAiConfig_Model::get(self::KEY_MESSAGE)
        );
    }

    /**
     * Returns what the inbox shows the user: why the AI does nothing and what helps.
     *
     * @return array|null ['reason', 'title', 'text', 'hint', 'detail' (the provider's words),
     *                    'settings_url' (admins only)], null when nothing blocks the AI
     */
    public static function describe(): ?array
    {
        $reason = MailManager_ProAiConfig_Model::get(self::KEY_REASON);

        if ('' === $reason) {
            return null;
        }

        $module = 'MailManager';
        $key = 'LBL_AI_BLOCK_' . strtoupper($reason);
        $isAdmin = Users_Record_Model::getCurrentUserModel()->isAdminUser();

        return [
            'reason' => $reason,
            'title' => vtranslate($key . '_TITLE', $module),
            'text' => vtranslate($key . '_TEXT', $module),
            // an admin can act, everybody else has to tell one
            'hint' => vtranslate($key . ($isAdmin ? '_HINT_ADMIN' : '_HINT_USER'), $module),
            'detail' => MailManager_ProAiConfig_Model::get(self::KEY_MESSAGE),
            'settings_url' => $isAdmin ? 'index.php?module=MailManager&parent=Settings&view=AISettings' : '',
        ];
    }
}

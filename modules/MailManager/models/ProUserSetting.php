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
 * The one store of a user's own Mail Manager Pro settings (not tied to a
 * mailbox): rows of df_mailmanager_config named "user.<id>.<setting>", so no
 * schema of its own is needed. Every per-user setting goes through here.
 */
class MailManager_ProUserSetting_Model
{
    /** Browser notifications of new mail: '1' on, '0' off (I06) */
    public const NOTIFY = 'notify';

    /** The list tab the inbox opens with, one of MailManager_ProThreadList_Model::LIST_FILTERS (I02) */
    public const LIST_TAB = 'list_tab';

    /** Seconds a sent mail waits for "Undo": 0, 5, 10 or 20 (I12) */
    public const SEND_DELAY = 'send_delay';

    /** The "Undo" times a user can pick, in seconds (0 = off) */
    public const SEND_DELAYS = [0, 5, 10, 20];

    /** When the user last opened the Workspace digest — the cron prepares AI lines for these users (I19) */
    public const DIGEST_SEEN = 'digest_seen';

    /** The AI summary on top of the digest: JSON time, input hash, language, text (I19) */
    public const DIGEST_SUMMARY = 'digest_summary';

    /**
     * @param int    $userId
     * @param string $name    one of the constants
     * @param string $default returned when the user has not set it
     *
     * @return string
     */
    public static function get(int $userId, string $name, string $default = ''): string
    {
        return (new MailManager_ProConfig_Model())->getValue(self::getKey($userId, $name)) ?? $default;
    }

    /**
     * @param int    $userId
     * @param string $name  one of the constants
     * @param string $value
     *
     * @return void
     */
    public static function set(int $userId, string $name, string $value): void
    {
        (new MailManager_ProConfig_Model())->saveValue(self::getKey($userId, $name), $value);
    }

    /**
     * @param string $name     setting name
     * @param string $minValue values compared as strings (e.g. a datetime), '' = any value
     *
     * @return int[] the users who have the setting, at least $minValue
     */
    public static function getUserIdsWith(string $name, string $minValue = ''): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT name FROM ' . MailManager_Install_Model::PRO_MARKER_TABLE . ' WHERE name LIKE ? AND value >= ?',
            ['user.%.' . $name, $minValue]
        );
        $userIds = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            if (preg_match('/^user\.(\d+)\./', (string)$row['name'], $match)) {
                $userIds[] = (int)$match[1];
            }
        }

        return array_values(array_unique($userIds));
    }

    /**
     * @param int    $userId
     * @param string $name
     *
     * @return string the config row name
     */
    private static function getKey(int $userId, string $name): string
    {
        return 'user.' . $userId . '.' . $name;
    }

    /**
     * Saves the "Undo" delay after Send of a user (My settings) — an unknown value
     * turns the delay off.
     *
     * @param int $userId
     * @param int $delay seconds, one of SEND_DELAYS
     *
     * @return void
     */
    public static function saveSendDelay(int $userId, int $delay): void
    {
        if (!in_array($delay, self::SEND_DELAYS, true)) {
            $delay = 0;
        }

        self::set($userId, self::SEND_DELAY, (string)$delay);
    }
}

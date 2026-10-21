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
 * Display formatting of mail data — dates per the user preferences,
 * address lists and sizes
 */
class MailManager_ProMailFormat_Model
{
    /**
     * The time of a mail as the CRM keeps its own times. Mail times are
     * stored in UTC (the Date header converted); the core takes a stored time
     * for one in the server's time zone (date_default_timezone_get) — on a
     * server not running in UTC every mail showed hours off
     *
     * @param string $utcDateTime Y-m-d H:i:s in UTC
     * @return string Y-m-d H:i:s in the server's time zone, '' for none
     */
    public static function utcToDbTime($utcDateTime)
    {
        if (empty($utcDateTime)) {
            return '';
        }

        return DateTimeField::convertTimeZone($utcDateTime, 'UTC', DateTimeField::getDBTimeZone())
            ->format('Y-m-d H:i:s');
    }

    /**
     * @param string $dbDateTime Y-m-d H:i:s in the server's time zone (a CRM time)
     * @return string the same moment in UTC — comparable with mail times, '' for none
     */
    public static function dbToUtcTime($dbDateTime)
    {
        if (empty($dbDateTime)) {
            return '';
        }

        return DateTimeField::convertTimeZone($dbDateTime, DateTimeField::getDBTimeZone(), 'UTC')
            ->format('Y-m-d H:i:s');
    }

    /**
     * A wall-clock time the user picked (<input type="datetime-local">) in the user's
     * CRM time zone — not the browser's — as UTC
     *
     * @param string $userDateTime Y-m-d\TH:i or Y-m-d H:i[:s]
     * @param Users|null $user whose time zone, null = the current user
     *
     * @return string Y-m-d H:i:s UTC, '' when the value is not a date and time
     */
    public static function userInputToUtc(string $userDateTime, $user = null): string
    {
        $value = str_replace('T', ' ', trim($userDateTime));

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/', $value)) {
            return '';
        }

        return DateTimeField::convertTimeZone($value, self::getUserTimeZone($user), 'UTC')->format('Y-m-d H:i:s');
    }

    /**
     * @param string $utcDateTime Y-m-d H:i:s UTC
     * @param Users|null $user whose time zone, null = the current user
     *
     * @return string the value for <input type="datetime-local"> in the user's time zone
     */
    public static function utcToUserInput(string $utcDateTime, $user = null): string
    {
        if ('' === trim($utcDateTime)) {
            return '';
        }

        return DateTimeField::convertTimeZone($utcDateTime, 'UTC', self::getUserTimeZone($user))->format('Y-m-d\TH:i');
    }

    /**
     * @param Users|null $user null = the current user
     *
     * @return string the user's time zone, the CRM default without one
     */
    private static function getUserTimeZone($user = null): string
    {
        $user = $user ?: vglobal('current_user');

        return (string)($user->time_zone ?? '') ?: (string)(vglobal('default_timezone') ?: 'UTC');
    }

    /**
     * Mail time (UTC) → logged-in user format (date_format + 12/24h + timezone)
     *
     * @param string $utcDateTime
     * @return string
     */
    public static function formatDateTime($utcDateTime)
    {
        if (empty($utcDateTime)) {
            return '';
        }
        $current_user = vglobal('current_user');

        $moment = DateTimeField::convertToUserTimeZone(self::utcToDbTime($utcDateTime));
        $now = DateTimeField::convertToUserTimeZone(date('Y-m-d H:i:s'));

        // Time in the user's 12/24h preference, without seconds
        $time = $moment->format(($current_user->hour_format ?? '24') === '12' ? 'g:i A' : 'H:i');

        // Tiered display (Gmail style): today = time only, yesterday = word,
        // this year = weekday + date without the year, older = full date
        $today = $now->format('Y-m-d');
        $currentYear = $now->format('Y');
        $yesterday = (clone $now)->modify('-1 day')->format('Y-m-d');

        if ($moment->format('Y-m-d') === $today) {
            return $time;
        }
        if ($moment->format('Y-m-d') === $yesterday) {
            return vtranslate('LBL_YESTERDAY', 'Vtiger') . ' ' . $time;
        }

        // The user's vtiger date format (dd.mm.yyyy / mm-dd-yyyy / ...) mapped
        // to a PHP pattern; the current year is dropped to make room
        $pattern = str_replace(['dd', 'mm', 'yyyy'], ['d', 'm', 'Y'], (string)($current_user->date_format ?: 'dd-mm-yyyy'));
        if ($moment->format('Y') === $currentYear) {
            $weekday = vtranslate('LBL_DAY_' . $moment->format('N'), 'MailManager');
            $shortPattern = trim(str_replace('Y', '', $pattern), '.-/ ');

            return $weekday . ' ' . $moment->format($shortPattern) . ' ' . $time;
        }

        return $moment->format($pattern) . ' ' . $time;
    }

    /**
     * @param string $json stored address list
     * @return string comma separated display
     */
    public static function formatAddressList($json)
    {
        $addresses = json_decode((string)$json, true) ?: [];
        $parts = [];
        foreach ($addresses as $address) {
            $parts[] = $address['name'] !== '' ? $address['name'] : $address['email'];
        }

        return implode(', ', $parts);
    }

    /**
     * @param int $bytes
     * @return string human readable size
     */
    public static function formatSize($bytes)
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024) . ' KB';
        }

        return $bytes . ' B';
    }
}

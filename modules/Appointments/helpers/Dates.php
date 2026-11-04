<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Dates_Helper
{
    public static function getCurrentDatabaseDatetime(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }

    public static function getTodayDateForUser(): string
    {
        return self::getCurrentUserDateTime()->format('Y-m-d');
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function getTodayDatabaseRangeForUser(): array
    {
        $date = self::getTodayDateForUser();
        $endDate = (new DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');

        return self::getDatabaseRangeForUser($date, $endDate);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function getDatabaseRangeForUser(string $startDate, string $endDate): array
    {
        $timeZone = self::getCurrentUserTimeZone();
        $start = new DateTimeImmutable($startDate . ' 00:00:00', $timeZone);
        $end = new DateTimeImmutable($endDate . ' 00:00:00', $timeZone);
        $databaseTimeZone = new DateTimeZone('UTC');

        return [
            $start->setTimezone($databaseTimeZone)->format('Y-m-d H:i:s'),
            $end->setTimezone($databaseTimeZone)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public static function getCurrentDatetimeForUser(): string
    {
        $date = DateTimeField::convertToUserTimeZone(date('Y-m-d H:i:s'));

        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public static function getTodayDatetimes(): string
    {
        return self::getTodayDatetime() . ',' . self::getTodayEndDatetime();
    }

    /**
     * @return string
     */
    public static function getTodayDatetimesForUser(): string
    {
        return self::getTodayDatetimeForUser() . ',' . self::getTodayEndDatetimeForUser();
    }

    /**
     * @return string
     */
    public static function getTodayDatetime(): string
    {
        return date('Y-m-d H:i:s', strtotime('today'));
    }

    /**
     * @return string
     */
    public static function getTodayDatetimeForUser(): string
    {
        $today = DateTimeField::convertToUserTimeZone(self::getTodayDatetime());

        return $today->format('Y-m-d H:i:s');
    }

    public static function getTodayEndDatetime(): string
    {
        return date('Y-m-d H:i:s', strtotime('tomorrow') - 1);
    }

    /**
     * @return string
     */
    public static function getTodayEndDatetimeForUser(): string
    {
        $date = DateTimeField::convertToUserTimeZone(self::getTodayEndDatetime());

        return $date->format('Y-m-d H:i:s');
    }

    protected static function getCurrentUserDateTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', self::getCurrentUserTimeZone());
    }

    protected static function getCurrentUserTimeZone(): DateTimeZone
    {
        global $default_timezone;

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $timeZone = $currentUser->get('time_zone') ?: $default_timezone;

        return new DateTimeZone($timeZone);
    }
}

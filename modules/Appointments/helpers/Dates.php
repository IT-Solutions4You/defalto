<?php

class Appointments_Dates_Helper
{
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
}
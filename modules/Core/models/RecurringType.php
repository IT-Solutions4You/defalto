<?php
/********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_RecurringType_Model
{
    public $dayofweek_to_rpt = [];
    public $enddate;
    public bool $isAllDay = false;
    public $recur_freq;
    public $recur_type;
    public $recurringdates = [];
    public $recurringtimes = [];
    public $recurringenddate;
    public $reminder;
    public $repeat_monthby;
    public $rptmonth_datevalue;
    public $rptmonth_daytype;
    public $startdate;

    /**
     * Constructor for Core_RecurringType_Model
     *
     * @param array $repeat_arr - array contains recurring info
     */
    public function __construct($repeat_arr)
    {
        $st_date = explode("-", $repeat_arr["startdate"]);
        $st_time = explode(":", $repeat_arr["starttime"]);
        $end_date = explode("-", $repeat_arr["enddate"]);
        $end_time = explode(":", $repeat_arr['endtime']);
        $recurringenddate = explode("-", $repeat_arr["recurringenddate"]);

        $start_date = [
            'day'   => $st_date[2],
            'month' => $st_date[1],
            'year'  => $st_date[0],
            'hour'  => $st_time[0],
            'min'   => $st_time[1],
        ];
        $end_date = [
            'day'   => $end_date[2],
            'month' => $end_date[1],
            'year'  => $end_date[0],
            'hour'  => $end_time[0],
            'min'   => $end_time[1],
        ];
        $recurringenddate = [
            'day'   => $recurringenddate[2],
            'month' => $recurringenddate[1],
            'year'  => $recurringenddate[0],
        ];
        $this->startdate = new Core_Datetime_Model($start_date, true);
        $this->enddate = new Core_Datetime_Model($end_date, true);
        $this->recurringenddate = new Core_Datetime_Model($recurringenddate, true);
        $this->isAllDay = 'Yes' === ($repeat_arr['is_all_day'] ?? '')
            || 1 === (int)($repeat_arr['is_all_day'] ?? 0);

        $this->recur_type = $repeat_arr['type'];
        $this->recur_freq = $repeat_arr['repeat_frequency'];
        if (empty($this->recur_freq)) {
            $this->recur_freq = 1;
        }
        $this->dayofweek_to_rpt = $repeat_arr['dayofweek_to_repeat'];
        $this->repeat_monthby = $repeat_arr['repeatmonth_type'];
        if (isset($repeat_arr['repeatmonth_date'])) {
            $this->rptmonth_datevalue = $repeat_arr['repeatmonth_date'];
        }
        $this->rptmonth_daytype = $repeat_arr['repeatmonth_daytype'];

        $this->recurringdates = $this->_getRecurringDates();
    }

    /**
     *  Function to get recurring dates depending on the recurring type
     *  return  array   $recurringDates     -  Recurring Dates in format
     *    Recurring date will be returned in DB Time Zone, as well as DB format
     */
    public function _getRecurringDates()
    {
        $startdateObj = $this->startdate;
        $startdate = $startdateObj->get_DB_formatted_date();
        $recurringDates[] = $startdate;
        $this->recurringtimes[] = $startdateObj->get_formatted_time();
        $tempdateObj = $startdateObj;
        $tempdate = $startdate;
        $enddate = $this->enddate->get_DB_formatted_date();

        if ($this->recur_type == 'Weekly') {
            return $this->getWeeklyRecurringDates();
        }

        $dbDateTime = strtotime($startdate);
        $userDateTime = strtotime($startdateObj->get_userTimezone_formatted_date());
        $dateDiff = $dbDateTime - $userDateTime;
        if ($dateDiff < 0) {
            $dayDiff = $dateDiff / 3600 / 24;
        } elseif ($dateDiff > 0) {
            $dayDiff = $dateDiff / 3600 / 24;
        }

        while ($tempdate <= $enddate) {
            $date = $tempdateObj->get_Date();
            $month = $tempdateObj->getMonth();
            $year = $tempdateObj->getYear();

            if ($this->recur_type == 'Daily') {
                if (isset($this->recur_freq)) {
                    $index = $date + $this->recur_freq - 1;
                } else {
                    $index = $date;
                }
                $tempdateObj = $this->startdate->getThismonthDaysbyIndex($index, '', $month, $year);
                $tempdate = $tempdateObj->get_DB_formatted_date();
                if ($tempdate <= $enddate) {
                    $recurringDates[] = $tempdate;
                }
            } elseif ($this->recur_type == 'Monthly') {
                if ($this->repeat_monthby == 'date' || $this->repeat_monthby == 'day') {
                    $tempdateObj = $this->getMonthlyOccurrence($tempdateObj, (int)($dayDiff ?? 0));
                } else {
                    $date_arr = [
                        'day'   => $date,
                        'month' => $month + 1,
                        'year'  => $year,
                    ];
                    $tempdateObj = new Core_Datetime_Model($date_arr, true);
                }
                $tempdate = $tempdateObj->get_DB_formatted_date();
                if ($this->isMonthlyOccurrenceWithinEndDate($tempdateObj)) {
                    $recurringDates[] = $tempdate;
                }
            } elseif ($this->recur_type == 'Yearly') {
                if (isset($this->recur_freq)) {
                    $index = $year + $this->recur_freq;
                } else {
                    $index = $year + 1;
                }
                if ($index > 2037 || $index < 1970) {
                    print("<font color='red'>" . getTranslatedString('LBL_CAL_LIMIT_MSG') . "</font>");
                    exit;
                }
                $date_arr = [
                    'day'   => $date,
                    'month' => $month,
                    'year'  => $index,
                ];
                $tempdateObj = new Core_Datetime_Model($date_arr, true);
                $tempdate = $tempdateObj->get_DB_formatted_date();
                if ($tempdate <= $enddate) {
                    $recurringDates[] = $tempdate;
                }
            } else {
                die("Recurring Type " . $this->recur_type . " is not defined");
            }
        }

        return $recurringDates;
    }

    protected function getWeeklyRecurringDates(): array
    {
        $databaseTimeZone = new DateTimeZone(DateTimeField::getDBTimeZone());
        $databaseStartDateTime = $this->startdate->get_DB_formatted_date()
            . ' '
            . $this->startdate->get_formatted_time();
        $databaseEndDateTime = $this->enddate->get_DB_formatted_date()
            . ' '
            . $this->enddate->get_formatted_time();
        $userStartDateTime = DateTimeField::convertToUserTimeZone($databaseStartDateTime);
        $userEndDateTime = DateTimeField::convertToUserTimeZone($databaseEndDateTime);

        if ($this->isAllDay) {
            $userTimeZone = $userStartDateTime->getTimezone();
            $userStartDateTime = new DateTime(
                $this->startdate->get_DB_formatted_date() . ' 00:00:00',
                $userTimeZone
            );
            $userEndDateTime = new DateTime(
                $this->enddate->get_DB_formatted_date() . ' 00:00:00',
                $userTimeZone
            );
        }

        $userEndDate = $userEndDateTime->format('Y-m-d');
        $startDayOfWeek = (int)$userStartDateTime->format('w');
        $weekStart = (clone $userStartDateTime)->modify('-' . $startDayOfWeek . ' days');
        $frequency = max(1, (int)$this->recur_freq);
        $daysOfWeek = $this->dayofweek_to_rpt;
        $recurringDates = [$this->startdate->get_DB_formatted_date()];

        if (php7_count($daysOfWeek) == 0) {
            $daysOfWeek[] = $startDayOfWeek;
        }

        sort($daysOfWeek, SORT_NUMERIC);

        while ($weekStart->format('Y-m-d') <= $userEndDate) {
            foreach ($daysOfWeek as $dayOfWeek) {
                $userOccurrence = (clone $weekStart)->modify('+' . (int)$dayOfWeek . ' days');

                if ($userOccurrence <= $userStartDateTime || $userOccurrence->format('Y-m-d') > $userEndDate) {
                    continue;
                }

                $databaseOccurrence = clone $userOccurrence;

                if (!$this->isAllDay) {
                    $databaseOccurrence->setTimezone($databaseTimeZone);
                }

                $recurringDates[] = $databaseOccurrence->format('Y-m-d');
                $this->recurringtimes[] = $this->isAllDay ? '00:00' : $databaseOccurrence->format('H:i');
            }

            $weekStart->modify('+' . $frequency . ' weeks');
        }

        return $recurringDates;
    }

    public function getRecurringTime(int $index): string
    {
        return $this->recurringtimes[$index] ?? $this->startdate->get_formatted_time();
    }

    protected function getMonthlyOccurrence(Core_Datetime_Model $databaseDate, int $dayDiff): Core_Datetime_Model
    {
        $databaseTime = $this->startdate->get_formatted_time();
        $userStartDateTime = DateTimeField::convertToUserTimeZone(
            $this->startdate->get_DB_formatted_date() . ' ' . $databaseTime
        );
        $userDateTime = DateTimeField::convertToUserTimeZone(
            $databaseDate->get_DB_formatted_date() . ' ' . $databaseTime
        );
        $frequency = max(1, (int)$this->recur_freq);
        $year = (int)$userDateTime->format('Y');
        $month = (int)$userDateTime->format('n');
        $candidate = $this->getMonthlyCandidate($year, $month, $dayDiff, $userStartDateTime);

        if ($candidate->format('Y-m-d') <= $userDateTime->format('Y-m-d')) {
            $month += $frequency;
            $candidate = $this->getMonthlyCandidate($year, $month, $dayDiff, $userStartDateTime);
        }

        $databaseCandidate = $this->getDatabaseDateForUserDate($candidate, $databaseTime);
        $date = [
            'day'   => $databaseCandidate->format('j'),
            'month' => $databaseCandidate->format('n'),
            'year'  => $databaseCandidate->format('Y'),
        ];

        return new Core_Datetime_Model($date, true);
    }

    protected function getMonthlyCandidate(
        int $year,
        int $month,
        int $dayDiff,
        DateTime $userStartDateTime
    ): DateTimeImmutable {
        $monthStart = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->setDate($year, $month, 1)
            ->setTime(0, 0);

        if ($this->repeat_monthby == 'date') {
            $userDay = (int)$this->rptmonth_datevalue - $dayDiff;
            $startMonthDays = (int)$userStartDateTime->format('t');

            if ($userDay < 1) {
                $userDay = $startMonthDays;
            } elseif ($userDay > $startMonthDays) {
                $userDay = 1;
            }

            return $monthStart->setDate(
                (int)$monthStart->format('Y'),
                (int)$monthStart->format('n'),
                min($userDay, (int)$monthStart->format('t'))
            );
        }

        $userDayOfWeek = ((int)$this->dayofweek_to_rpt[0] - $dayDiff) % 7;

        if ($userDayOfWeek < 0) {
            $userDayOfWeek += 7;
        }

        if ($this->rptmonth_daytype == 'last') {
            $candidate = $monthStart->modify('last day of this month');
            $daysBack = ((int)$candidate->format('w') - $userDayOfWeek + 7) % 7;

            return $candidate->modify('-' . $daysBack . ' days');
        }

        $daysForward = ($userDayOfWeek - (int)$monthStart->format('w') + 7) % 7;

        return $monthStart->modify('+' . $daysForward . ' days');
    }

    protected function getDatabaseDateForUserDate(
        DateTimeImmutable $userDate,
        string $databaseTime
    ): DateTimeImmutable {
        foreach ([-1, 0, 1] as $dayOffset) {
            $databaseDate = $userDate->modify($dayOffset . ' days');
            $convertedDate = DateTimeField::convertToUserTimeZone(
                $databaseDate->format('Y-m-d') . ' ' . $databaseTime
            );

            if ($convertedDate->format('Y-m-d') == $userDate->format('Y-m-d')) {
                return $databaseDate;
            }
        }

        return $userDate;
    }

    protected function isMonthlyOccurrenceWithinEndDate(Core_Datetime_Model $databaseDate): bool
    {
        $userDate = DateTimeField::convertToUserTimeZone(
            $databaseDate->get_DB_formatted_date() . ' ' . $this->startdate->get_formatted_time()
        );
        $userEndDate = DateTimeField::convertToUserTimeZone(
            $this->enddate->get_DB_formatted_date() . ' ' . $this->enddate->get_formatted_time()
        );

        return $userDate->format('Y-m-d') <= $userEndDate->format('Y-m-d');
    }

    public static function fromDBRequest($resultRow)
    {
        // All the information from the database is received in DB Time zone

        $repeatInfo = [];

        $repeatInfo['startdate'] = $startDate = $resultRow['date_start'];
        $repeatInfo['starttime'] = $startTime = $resultRow['time_start'];
        $repeatInfo['enddate'] = $endDate = $resultRow['due_date'];
        $repeatInfo['endtime'] = $endTime = $resultRow['time_end'];

        $repeatInfo['type'] = $resultRow['recurringtype'];
        $repeatInfo['repeat_frequency'] = $resultRow['recurringfreq'];
        $repeatInfo['recurringenddate'] = $resultRow['recurringenddate'];
        $repeatInfo['is_all_day'] = $resultRow['is_all_day'] ?? false;

        $recurringInfoString = $resultRow['recurringinfo'];
        $recurringInfo = explode('::', $recurringInfoString);

        if ($repeatInfo['type'] == 'Weekly') {
            $startIndex = 1; // 0 is for Recurring Type
            $length = php7_count($recurringInfo);
            $j = 0;
            for ($i = $startIndex; $i < $length; ++$i) {
                $repeatInfo['dayofweek_to_repeat'][$j++] = $recurringInfo[$i];
            }
        } elseif ($repeatInfo['type'] == 'Monthly') {
            $repeatInfo['repeatmonth_type'] = $recurringInfo[1];
            if ($repeatInfo['repeatmonth_type'] == 'date') {
                $repeatInfo['repeatmonth_date'] = $recurringInfo[2];
            } else {
                $repeatInfo['repeatmonth_daytype'] = $recurringInfo[2];
                $repeatInfo['dayofweek_to_repeat'][0] = $recurringInfo[3];
            }
        }

        return new static($repeatInfo);
    }

    public static function getRecurringObjValue(?array $request = null): ?static
    {
        $request ??= $_REQUEST;
        $recurringType = $request['recurringtype'] ?? null;

        if (empty($recurringType) || '--None--' === $recurringType) {
            return null;
        }

        $dayFlags = [
            'sun_flag',
            'mon_flag',
            'tue_flag',
            'wed_flag',
            'thu_flag',
            'fri_flag',
            'sat_flag',
        ];
        $recurringData = array_fill_keys($dayFlags, false);
        $recurringData['startdate'] = $request['date_start'] ?? '';
        $recurringData['starttime'] = $request['time_start'] ?? '';
        $recurringData['endtime'] = $request['time_end'] ?? '';
        $recurringData['type'] = $recurringType;
        $recurringData['is_all_day'] = 'Yes' === ($request['is_all_day'] ?? '')
            || 1 === (int)($request['is_all_day'] ?? 0);

        if (!empty($request['calendar_repeat_limit_date'])) {
            $recurringData['enddate'] = $request['calendar_repeat_limit_date'];
            $recurringData['recurringenddate'] = $request['calendar_repeat_limit_date'];
        } else {
            $recurringData['enddate'] = $request['due_date'] ?? '';
        }

        if ('Weekly' === $recurringType) {
            foreach ($dayFlags as $dayFlag) {
                if (isset($request[$dayFlag]) && $request[$dayFlag] != null) {
                    $recurringData[$dayFlag] = true;
                }
            }
        } elseif ('Monthly' === $recurringType) {
            $recurringData['repeatmonth_type'] = $request['repeatMonth'] ?? null;

            if ('date' === $recurringData['repeatmonth_type']) {
                $recurringData['repeatmonth_date'] = $request['repeatMonth_date'] ?? 1;
            } elseif ('day' === $recurringData['repeatmonth_type']) {
                $recurringData['repeatmonth_daytype'] = $request['repeatMonth_daytype'] ?? null;
                $repeatMonthDay = (int)($request['repeatMonth_day'] ?? 0);

                if (isset($dayFlags[$repeatMonthDay])) {
                    $recurringData[$dayFlags[$repeatMonthDay]] = true;
                }
            }
        }

        if (isset($request['repeat_frequency']) && null !== $request['repeat_frequency']) {
            $recurringData['repeat_frequency'] = $request['repeat_frequency'];
        }

        return static::fromUserRequest($recurringData);
    }

    public static function fromUserRequest($requestArray)
    {
        $isAllDay = 'Yes' === ($requestArray['is_all_day'] ?? '')
            || 1 === (int)($requestArray['is_all_day'] ?? 0);

        // All the information from the user is received in User Time zone.
        // All-day values are floating calendar dates and must not be converted.
        if ($isAllDay) {
            $startDate = $requestArray['startdate'];
            $startTime = $requestArray['starttime'];
            $endDate = $requestArray['enddate'];
            $endTime = $requestArray['endtime'];
        } else {
            $startDateObj = DateTimeField::convertToDBTimeZone($requestArray["startdate"] . ' ' . $requestArray['starttime']);
            $requestArray['startdate'] = $startDate = $startDateObj->format('Y-m-d');
            $requestArray['starttime'] = $startTime = $startDateObj->format('H:i');
            $endDateObj = DateTimeField::convertToDBTimeZone($requestArray["enddate"] . ' ' . $requestArray['endtime']);
            $requestArray['enddate'] = $endDate = $endDateObj->format('Y-m-d');
            $requestArray['endtime'] = $endTime = $endDateObj->format('H:i');

            if (!empty($requestArray["recurringenddate"])) {
                $reccurringDateObj = DateTimeField::convertToDBTimeZone($requestArray["recurringenddate"] . ' ' . $requestArray['endtime']);
                $requestArray['recurringenddate'] = $reccurringDateObj->format('Y-m-d');
            }
        }

        if ($requestArray['sun_flag']) {
            $requestArray['dayofweek_to_repeat'][] = 0;
        }
        if ($requestArray['mon_flag']) {
            $requestArray['dayofweek_to_repeat'][] = 1;
        }
        if ($requestArray['tue_flag']) {
            $requestArray['dayofweek_to_repeat'][] = 2;
        }
        if ($requestArray['wed_flag']) {
            $requestArray['dayofweek_to_repeat'][] = 3;
        }
        if ($requestArray['thu_flag']) {
            $requestArray['dayofweek_to_repeat'][] = 4;
        }
        if ($requestArray['fri_flag']) {
            $requestArray['dayofweek_to_repeat'][] = 5;
        }
        if ($requestArray['sat_flag']) {
            $requestArray['dayofweek_to_repeat'][] = 6;
        }

        if ($requestArray['type'] == 'Weekly') {
            if ($requestArray['dayofweek_to_repeat'] != null) {
                $userStartDateTime = DateTimeField::convertToUserTimeZone($startDate . ' ' . $startTime);
                $dayOfWeek = $requestArray['dayofweek_to_repeat'];
                $dbDaysOfWeek = [];
                for ($i = 0; $i < php7_count($dayOfWeek); ++$i) {
                    $selectedDayOfWeek = $dayOfWeek[$i];
                    $currentDayOfWeek = $userStartDateTime->format('w');
                    $newDate = $userStartDateTime->format('d') + ($selectedDayOfWeek - $currentDayOfWeek);
                    $userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $newDate);
                    $dbDaysOfWeek[] = $userStartDateTime->format('w');
                }
                $requestArray['dayofweek_to_repeat'] = $dbDaysOfWeek;
            }
        } elseif ($requestArray['type'] == 'Monthly') {
            $userStartDateTime = DateTimeField::convertToUserTimeZone($startDate . ' ' . $startTime);
            if ($requestArray['repeatmonth_type'] == 'date') {
                $dayOfMonth = $requestArray['repeatmonth_date'];
                $userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $dayOfMonth);
                $userStartDateTime->setTimezone(new DateTimeZone(DateTimeField::getDBTimeZone()));
                $requestArray['repeatmonth_date'] = $userStartDateTime->format('d');
            } else {
                $dayOfWeek = $requestArray['dayofweek_to_repeat'][0];
                if ($requestArray['repeatmonth_daytype'] == 'first') {
                    $userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), 1);
                    $dayOfWeekForFirstDay = $userStartDateTime->format('N');
                    if ($dayOfWeekForFirstDay < $dayOfWeek) {
                        $date = $dayOfWeek - $dayOfWeekForFirstDay + 1;
                    } else {
                        $date = (7 - $dayOfWeekForFirstDay) + $dayOfWeek + 1;
                    }
                } elseif ($requestArray['repeatmonth_daytype'] == 'last') {
                    $daysInMonth = $userStartDateTime->format('t');
                    $userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $daysInMonth);
                    $dayOfWeekForLastDay = $userStartDateTime->format('N');
                    if ($dayOfWeekForLastDay < $dayOfWeek) {
                        $date = $daysInMonth - 7 + ($dayOfWeek - $dayOfWeekForLastDay);
                    } else {
                        $date = $daysInMonth - ($dayOfWeekForLastDay - $dayOfWeek);
                    }
                }
                $userStartDateTime->setDate($userStartDateTime->format('Y'), $userStartDateTime->format('m'), $date);
                $userStartDateTime->setTimezone(new DateTimeZone(DateTimeField::getDBTimeZone()));
                $requestArray['dayofweek_to_repeat'][0] = (int)$userStartDateTime->format('N') % 7;
            }
        }

        return new static($requestArray);
    }

    public function getDBRecurringInfoString()
    {
        $recurringType = $this->getRecurringType();
        $recurringInfo = '';
        if ($recurringType == 'Daily' || $recurringType == 'Yearly') {
            $recurringInfo = $recurringType;
        } elseif ($recurringType == 'Weekly') {
            if ($this->dayofweek_to_rpt != null) {
                $recurringInfo = $recurringType . '::' . implode('::', $this->dayofweek_to_rpt);
            } else {
                $recurringInfo = $recurringType;
            }
        } elseif ($recurringType == 'Monthly') {
            $recurringInfo = $recurringType . '::' . $this->repeat_monthby;
            if ($this->repeat_monthby == 'date') {
                $recurringInfo = $recurringInfo . '::' . $this->rptmonth_datevalue;
            } else {
                $recurringInfo = $recurringInfo . '::' . $this->rptmonth_daytype . '::' . $this->dayofweek_to_rpt[0];
            }
        }

        return $recurringInfo;
    }

    /** Function to get first day of the month(like first Monday or Friday and etc.)
     *
     * @param $dayofweek -- day of the week to repeat the event :: Type string
     * @param $dateObj   -- date object  :: Type Core_Datetime_Model Object
     *                   return $dateObj -- the date object on which the event repeats :: Type Core_Datetime_Model Object
     */
    public function getFistdayofmonth($dayofweek, &$dateObj)
    {
        if ($dayofweek < $dateObj->dayofweek) {
            $index = (7 - $dateObj->dayofweek) + $dayofweek;
            $day = 1 + $index;
            $month = $dateObj->month;
            $year = $dateObj->year;
            $dateObj = $dateObj->getThismonthDaysbyIndex($index, $day, $month, $year);
        } else {
            $index = $dayofweek - $dateObj->dayofweek;
            $day = 1 + $index;
            $month = $dateObj->month;
            $year = $dateObj->year;
            $dateObj = $dateObj->getThismonthDaysbyIndex($index, $day, $month, $year);
        }

        return $dateObj;
    }

    /** Function to get last day of the month(like last Monday or Friday and etc.)
     *
     * @param $dayofweek -- day of the week to repeat the event :: Type string
     * @param $dateObj   -- date object  :: Type Core_Datetime_Model Object
     *                   return $dateObj -- the date object on which the event repeats :: Type Core_Datetime_Model Object
     */
    public function getLastdayofmonth($dayofweek, &$dateObj)
    {
        if ($dayofweek == $dateObj->dayofweek) {
            return $dateObj;
        } else {
            if ($dayofweek > $dateObj->dayofweek) {
                $day = $dateObj->day - 7 + ($dayofweek - $dateObj->dayofweek);
            } else {
                $day = $dateObj->day - ($dateObj->dayofweek - $dayofweek);
            }
            $index = $day - 1;
            $month = $dateObj->month;
            $year = $dateObj->year;
            $dateObj = $dateObj->getThismonthDaysbyIndex($index, $day, $month, $year);

            return $dateObj;
        }
    }

    public function getRecurringEndDate()
    {
        return $this->recurringenddate;
    }

    public function getRecurringFrequency()
    {
        return $this->recur_freq;
    }

    public function getRecurringType()
    {
        return $this->recur_type;
    }

    public function getUserRecurringInfo()
    {
        $recurringType = $this->getRecurringType();
        $recurringInfo = [];

        if ($recurringType == 'Weekly') {
            if ($this->dayofweek_to_rpt != null) {
                $recurringInfo['dayofweek_to_repeat'] = $this->dayofweek_to_rpt;
            }
        } elseif ($recurringType == 'Monthly') {
            $dbStartDateTime = new DateTime($this->startdate->get_DB_formatted_date() . ' ' . $this->startdate->get_formatted_time());
            $recurringInfo['repeatmonth_type'] = $this->repeat_monthby;
            if ($this->repeat_monthby == 'date') {
                $dayOfMonth = $this->rptmonth_datevalue;
                $dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), $dayOfMonth);
                $userStartDateTime = DateTimeField::convertToUserTimeZone($dbStartDateTime->format('Y-m-d') . ' ' . $dbStartDateTime->format('H:i'));
                $recurringInfo['repeatmonth_date'] = $userStartDateTime->format('d');
            } else {
                $dayOfWeek = $this->dayofweek_to_rpt[0];
                $recurringInfo['repeatmonth_daytype'] = $this->rptmonth_daytype;
                if ($this->rptmonth_daytype == 'first') {
                    $dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), 1);
                    $dayOfWeekForFirstDay = $dbStartDateTime->format('N');
                    if ($dayOfWeekForFirstDay < $dayOfWeek) {
                        $date = $dayOfWeek - $dayOfWeekForFirstDay + 1;
                    } else {
                        $date = (7 - $dayOfWeekForFirstDay) + $dayOfWeek + 1;
                    }
                } elseif ($this->rptmonth_daytype == 'last') {
                    $daysInMonth = $dbStartDateTime->format('t');
                    $dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), $daysInMonth);
                    $dayOfWeekForLastDay = $dbStartDateTime->format('N');
                    if ($dayOfWeekForLastDay < $dayOfWeek) {
                        $date = $daysInMonth - 7 + ($dayOfWeek - $dayOfWeekForLastDay);
                    } else {
                        $date = $daysInMonth - ($dayOfWeekForLastDay - $dayOfWeek);
                    }
                }
                $dbStartDateTime->setDate($dbStartDateTime->format('Y'), $dbStartDateTime->format('m'), $date);
                $userStartDateTime = DateTimeField::convertToUserTimeZone($dbStartDateTime->format('Y-m-d') . ' ' . $dbStartDateTime->format('H:i'));
                $recurringInfo['dayofweek_to_repeat'][0] = (int)$userStartDateTime->format('N') % 7;
            }
        }

        return $recurringInfo;
    }
}

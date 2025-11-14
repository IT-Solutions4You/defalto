<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Record_Model extends Vtiger_Record_Model
{
    public static array $isAllDay = [];

    /**
     * @return string
     */
    public function getActivityTypeIcon(): string
    {
        /** @var Appointments_Module_Model $module */
        $module = $this->getModule();

        return $module->getModuleIcon('', $this->get('calendar_type'));
    }

    /**
     * @return string
     */
    public function getTimes(): string
    {
        $isAllDay = 'Yes' === $this->get('is_all_day');
        $startDatetime = $this->get('datetime_start');
        $endDatetime = $this->get('datetime_end');

        return Vtiger_Util_Helper::formatDatesIntoTimesRange($startDatetime, $endDatetime, $isAllDay);
    }

    /**
     * @return bool
     */
    public function isRecurringEnabled(): bool
    {
        $recurrence = Appointments_Recurrence_Model::getInstanceByRecord(intval($this->getId()));

        return $recurrence && $recurrence->isExists();
    }

    /**
     * @throws Exception
     */
    public static function isAllDay(int $record): bool
    {
        if (!isset(self::$isAllDay[$record])) {
            self::$isAllDay[$record] = 1 === self::getAllDay($record);
        }

        return self::$isAllDay[$record];
    }

    /**
     * @param int $record
     * @return int
     * @throws Exception
     */
    public static function getAllDay(int $record): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT is_all_day FROM its4you_calendar WHERE its4you_calendar_id=?', [$record]);
        $value = $adb->query_result($result, 0, 'is_all_day');

        return (int)$value;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getDateInfo(string $name): array
    {
        $value = $this->get($name);
        [$date, $time] = explode(' ', $value);
        $displayDate = Vtiger_Util_Helper::formatDateIntoStrings($value);

        $userModel = Users_Record_Model::getCurrentUserModel();
        $formattedTime = DateTimeField::convertToUserTimeZone($value)->format('H:i');

        if (12 === (int)$userModel->get('hour_format')) {
            $formattedTime = Vtiger_Time_UIType::getTimeValueInAMorPM($formattedTime);
        }

        if (1 === (int)$this->get('is_all_day')) {
            $time = $formattedTime = null;
        }

        return [
            'date'         => $date,
            'display_date' => $displayDate,
            'time'         => $time,
            'display_time' => $formattedTime,
        ];
    }
}
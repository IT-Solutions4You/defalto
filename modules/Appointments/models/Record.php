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

        return $module->getModuleIcon('', $this->get('calendar_type') ?? '');
    }

    /**
     * @return string
     */
    public function getTimes(): string
    {
        $isAllDay = $this->isAllDayEvent();
        $startDatetime = $this->get('datetime_start');
        $endDatetime = $this->get('datetime_end');

        if ($isAllDay) {
            $startDatetime = $this->getAllDayStorageDate('datetime_start') . ' 00:00:00';
            $endDatetime = $this->getAllDayStorageDate('datetime_end') . ' 00:00:00';
            $endDatetime = (new DateTimeImmutable($endDatetime))->modify('+1 day')->format('Y-m-d 00:00:00');
        }

        return Vtiger_Util_Helper::formatDatesIntoTimesRange($startDatetime, $endDatetime, $isAllDay);
    }

    /**
     * @return bool
     */
    public function isAllDayEvent(): bool
    {
        return 'Yes' === $this->get('is_all_day') || 1 === (int)$this->get('is_all_day');
    }

    /**
     * @param string $startDate
     * @param string $endDate
     *
     * @return void
     */
    public function setAllDayDates(string $startDate, string $endDate): void
    {
        $startDate = $this->getAllDayDateTime($startDate);
        $endDate = $this->getAllDayDateTime($endDate);

        $this->set('datetime_start', $startDate);
        $this->set('datetime_end', $endDate);
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    public function getAllDayDate(string $fieldName): string
    {
        $value = (string)$this->get($fieldName);

        if ('' === trim($value)) {
            return '';
        }

        return preg_split('/\s+/', trim($value), 2)[0];
    }

    /**
     * Return the literal database calendar date entered by the user.
     *
     * @param string $fieldName
     *
     * @return string
     */
    public function getAllDayStorageDate(string $fieldName): string
    {
        return $this->getAllDayDate($fieldName);
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

        return 'Yes' === $value || 1 === (int)$value ? 1 : 0;
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
        $isAllDay = $this->isAllDayEvent();

        if ($isAllDay) {
            $date = $this->getAllDayDate($name);
        }

        $displayDate = $isAllDay
            ? Vtiger_Date_UIType::getDisplayDateValue($date)
            : Vtiger_Util_Helper::formatDateIntoStrings($value);

        $userModel = Users_Record_Model::getCurrentUserModel();
        $formattedTime = $isAllDay ? null : DateTimeField::convertToUserTimeZone($value)->format('H:i');

        if (!$isAllDay && 12 === (int)$userModel->get('hour_format')) {
            $formattedTime = Vtiger_Time_UIType::getTimeValueInAMorPM($formattedTime);
        }

        if ($isAllDay) {
            $time = $formattedTime = null;
        }

        return [
            'date'         => $date,
            'display_date' => $displayDate,
            'time'         => $time,
            'display_time' => $formattedTime,
        ];
    }

    /**
     * @param string $value
     * @return string
     */
    protected function getAllDayDateTime(string $value): string
    {
        $value = trim($value);

        if ('' === $value) {
            return '';
        }

        $date = preg_split('/\s+/', $value, 2)[0];

        return DateTimeField::convertToDBFormat($date) . ' 00:00:00';
    }

}

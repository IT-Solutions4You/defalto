<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Appointments_Record_Model extends Vtiger_Record_Model
{
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
     * @param string $name
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
            'date' => $date,
            'display_date' => $displayDate,
            'time' => $time,
            'display_time' => $formattedTime,
        ];
    }
}
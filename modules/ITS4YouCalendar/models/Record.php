<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Record_Model extends Vtiger_Record_Model
{
    public function retrieveDurationHours()
    {
        $datetimeEndTime = strtotime(Vtiger_Datetime_UIType::getDBDateTimeValue($this->get('datetime_end')));
        $datetimeStartTime = strtotime(Vtiger_Datetime_UIType::getDBDateTimeValue($this->get('datetime_start')));

        $this->set('duration_hours', ($datetimeEndTime - $datetimeStartTime) / 60 / 60);
    }

    /**
     * @return string
     */
    public function getActivityTypeIcon(): string
    {
        $calendarType = strtolower($this->get('calendar_type'));

        if ('email' === $calendarType) {
            return 'fa fa-envelope fa-lg';
        }

        return 'vicon-' . $calendarType;
    }

    /**
     * @return bool
     */
    public function isRecurringEnabled(): bool
    {
        $recurrence = ITS4YouCalendar_Recurrence_Model::getInstanceByRecord(intval($this->getId()));

        return $recurrence && $recurrence->isExists();
    }
}
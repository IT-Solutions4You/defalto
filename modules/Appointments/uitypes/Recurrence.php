<?php

class Appointments_Recurrence_UIType extends Vtiger_Recurrence_UIType
{
    /**
     * @return string
     */
    public function getTomorrowDate(): string
    {
        return DateTimeField::convertToUserFormat(date('Y-m-d', strtotime('+1 day')));
    }
}
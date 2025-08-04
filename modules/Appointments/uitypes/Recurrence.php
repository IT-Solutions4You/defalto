<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

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
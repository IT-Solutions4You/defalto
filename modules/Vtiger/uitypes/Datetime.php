<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Vtiger_Datetime_UIType extends Vtiger_Date_UIType
{
    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/DateTime.tpl';
    }

    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     *
     * @param <Object> $value
     *
     * @return <Object>
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $dateValue = '--';

        if ($value != '') {
            $dateTimeValue = self::getDisplayDateTimeValue($value);
            [$startDate, $startTime] = explode(' ', $dateTimeValue);

            $currentUser = Users_Record_Model::getCurrentUserModel();
            if ($currentUser->get('hour_format') == '12') {
                $startTime = Vtiger_Time_UIType::getTimeValueInAMorPM($startTime);
            }

            $dateValue = "$startDate $startTime";
        }

        return $dateValue;
    }

    /**
     * Function to get Date and Time value for Display
     *
     * @param <type> $date
     *
     * @return <String>
     */
    public static function getDisplayDateTimeValue($date)
    {
        $date = new DateTimeField($date);

        return $date->getDisplayDateTimeValue();
    }

    /**
     * Function to get Date and Time value for Display
     *
     * @param <type> $date
     *
     * @return <String>
     */
    public static function getDBDateTimeValue($date)
    {
        $date = new DateTimeField($date);

        return $date->getDBInsertDateTimeValue();
    }

    /**
     * Function to get the datetime value in user preferred hour format
     *
     * @param <type> $dateTime
     *
     * @return <String> date and time with hour format
     */
    public static function getDateTimeValue($dateTime)
    {
        return Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($dateTime);
    }

    public function getDBInsertValue($value)
    {
        $result = explode(' ', $value);
        //If database value is date, then fall back to parent
        if (!$result[1]) {
            return parent::getDBInsertValue($value);
        } else {
            return $this->getDBDateTimeValue($value);
        }
    }
}
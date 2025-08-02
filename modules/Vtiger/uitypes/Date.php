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

class Vtiger_Date_UIType extends Vtiger_Base_UIType
{
    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/Date.tpl';
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
        if (empty($value)) {
            return $value;
        } else {
            $dateValue = self::getDisplayDateValue($value);
        }

        if ($dateValue == '--') {
            return "";
        } else {
            return $dateValue;
        }
    }

    /**
     * Function to get the Value of the field in the format, the user provides it on Save
     *
     * @param <Object> $value
     *
     * @return <Object>
     */
    public function getUserRequestValue($value)
    {
        return $this->getDisplayValue($value);
    }

    /**
     * Function to get the DB Insert Value, for the current field type with given User Value
     *
     * @param <Object> $value
     *
     * @return <Object>
     */
    public function getDBInsertValue($value)
    {
        return self::getDBInsertedValue($value);
    }

    /**
     * Function converts the date to database format
     *
     * @param <String> $value
     *
     * @return <String>
     */
    public static function getDBInsertedValue($value)
    {
        return DateTimeField::convertToDBFormat($value);
    }

    /**
     * Function to get the display value in edit view
     *
     * @param $value
     *
     * @return converted value
     */
    public function getEditViewDisplayValue($value)
    {
        if ($value == null) {
            return $value;
        }

        if (empty($value) || $value === ' ') {
            $value = trim($value);
            $fieldInstance = $this->get('field')->getWebserviceFieldObject();
            $moduleName = $this->get('field')->getModule()->getName();
            $fieldName = $fieldInstance->getFieldName();

            //Restricted Fields for to show Default Value
            if (($fieldName === 'birthday' && $moduleName === 'Contacts')
                || ($fieldName === 'validtill' && $moduleName === 'Quotes')
                || $moduleName === 'Products') {
                return $value;
            }

            //Special Condition for field 'support_end_date' in Contacts Module
            if ($fieldName === 'support_end_date' && $moduleName === 'Contacts') {
                $value = DateTimeField::convertToUserFormat(date('Y-m-d', strtotime("+1 year")));
            } elseif ($fieldName === 'support_start_date' && $moduleName === 'Contacts') {
                $value = DateTimeField::convertToUserFormat(date('Y-m-d'));
            }
        } else {
            $value = DateTimeField::convertToUserFormat($value);
        }

        return $value;
    }

    /**
     * Function to get Date value for Display
     *
     * @param <type> $date
     *
     * @return <String>
     */
    public static function getDisplayDateValue($date)
    {
        $date = new DateTimeField($date);

        return $date->getDisplayDate();
    }

    /**
     * Function to get DateTime value for Display
     *
     * @param <type> $dateTime
     *
     * @return <String>
     */
    public static function getDisplayDateTimeValue($dateTime)
    {
        $date = new DateTimeField($dateTime);

        return $date->getDisplayDateTimeValue();
    }

    public function getListSearchTemplateName()
    {
        return 'uitypes/DateFieldSearchView.tpl';
    }
}
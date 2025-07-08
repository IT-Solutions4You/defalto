<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Currency_UIType extends Vtiger_Base_UIType
{

    /**
     * Function converts User currency format to database format
     * @param <Object> $value - Currency value
     * @param <User Object> $user
     * @param <Boolean> $skipConversion
     */
    public static function convertToDBFormat($value, $user = null, $skipConversion = false)
    {
        return CurrencyField::convertToDBFormat($value, $user, $skipConversion);
    }

    /**
     * Function to get the DB Insert Value, for the current field type with given User Value
     * @param <Object> $value
     * @return Number
     */
    public function getDBInsertValue($value)
    {
        return self::convertToDBFormat($value, null, true);
    }

    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     * @param $value
     * @param false $record
     * @param false $recordInstance
     * @return string|null
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        if (empty($value)) {
            return '0';
        }

        global $currency_user;

        return CurrencyField::convertToUserFormat($value, $currency_user, true);
    }

    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/Currency.tpl';
    }

    /**
     * Function to transform display value for currency field
     * @param $value
     * @param null|object $user Current User
     * @param Boolean $skipConversion Skip Conversion
     * @return String user format value
     */
    public static function transformDisplayValue($value, $user = null, $skipConversion = false)
    {
        return CurrencyField::convertToUserFormat($value, $user, $skipConversion);
    }

    /**
     * @param string|float $value
     * @param object|null $user
     * @param bool $skipConversion
     * @return float|string
     */
    public static function transformEditViewDisplayValue($value, $user = null, $skipConversion = false)
    {
        return CurrencyField::convertToUserFormatForEdit($value, $user, $skipConversion);
    }
}
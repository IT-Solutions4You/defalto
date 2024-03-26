<?php
/*
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Vtiger_Number_UIType extends Vtiger_Base_UIType {

    /**
     * @return string
     */
    public function getTemplateName() {
        return 'uitypes/Number.tpl';
    }

    /**
     * @param mixed $value
     * @param int $record
     * @param object $recordInstance
     * @return string
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $value = strip_tags($value);

        if (empty($value)) {
            return '0';
        }

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $value = CurrencyField::convertToUserFormat($value, $currentUser, true);

        if ($currentUser->isEmpty('truncate_trailing_zeros')) {
            $value = rtrim(rtrim($value, '0'), $currentUser->get('currency_decimal_separator'));
        }

        return $value;
    }


    /**
     * @param mixed $value
     * @return float
     */
    public function getEditViewDisplayValue($value)
    {
        if (empty($value)) {
            return '';
        }

        $value = CurrencyField::convertToUserFormatForEdit($value, null, true, false);

        return decimalFormat($value);
    }
}
<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Integer_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Number.tpl';
	}

    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $value = $value ? CurrencyField::convertToUserFormat($value, null, true) : 0;
        $currentUser = Users_Record_Model::getCurrentUserModel();

        return rtrim(rtrim($value, '0'), $currentUser->get('currency_decimal_separator'));
    }

    /**
     * @param float $value
     * @return float|int|string
     */
    public function getEditViewDisplayValue($value)
    {
        if (empty($value)) {
            return 0;
        }

        $value = CurrencyField::convertToUserFormatForEdit($value, null, true, false);

        return decimalFormat($value);
    }
}
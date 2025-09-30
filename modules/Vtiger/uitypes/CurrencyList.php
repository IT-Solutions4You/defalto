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

class Vtiger_CurrencyList_UIType extends Vtiger_Base_UIType
{
	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/CurrencyList.tpl';
	}

	public function getDisplayValue($value, $record = false, $recordInstance = false)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery(
			'SELECT currency_name FROM vtiger_currency_info WHERE currency_status = ? AND id = ?',
			['Active', $value]
		);
		if ($db->num_rows($result)) {
			return $db->query_result($result, 0, 'currency_name');
		}

		return $value;
	}

	public function getCurrenyListReferenceFieldName()
	{
		return 'currency_name';
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/CurrencyListFieldSearchView.tpl';
	}

    public static function transformDisplayValue($value, $record = false, $recordInstance = false)
    {
        $uiType = new self();

        return $uiType->getDisplayValue($value, $record, $recordInstance);
    }
}
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

class Vtiger_Multipicklist_UIType extends Vtiger_Base_UIType
{
    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/MultiPicklist.tpl';
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
        $moduleName = $this->get('field')->getModuleName();
        $value = explode(' |##| ', (string)$value);
        foreach ($value as $key => $val) {
            $value[$key] = vtranslate($val, $moduleName);
        }

        if (is_array($value)) {
            $value = implode(' |##| ', $value);
        }

        return str_ireplace(' |##| ', ', ', $value);
    }

    public function getDBInsertValue($value)
    {
        if (is_array($value)) {
            $value = implode(' |##| ', $value);
        }

        return $value;
    }

    public function getListSearchTemplateName()
    {
        return 'uitypes/MultiSelectFieldSearchView.tpl';
    }
}
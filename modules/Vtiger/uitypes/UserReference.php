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

class Vtiger_UserReference_UIType extends Vtiger_Base_UIType
{
    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/Reference.tpl';
    }

    /**
     * Function to get the display value in detail view
     *
     * @param <Integer> crmid of record
     *
     * @return <String>
     */
    public function getEditViewDisplayValue($value)
    {
        if ($value) {
            $userName = getOwnerName($value);

            return $userName;
        }
    }

    /**
     * Function to get display value
     *
     * @param <String> $value
     * @param <Number> $recordId
     *
     * @return <String> display value
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $displayValue = $this->getEditViewDisplayValue($value);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if ($currentUserModel->isAdminUser()) {
            $recordModel = Users_Record_Model::getCleanInstance('Users');
            $recordModel->set('id', $value);

            return '<a href="' . $recordModel->getDetailViewUrl() . '">' . textlength_check($displayValue) . '</a>';
        }

        return $displayValue;
    }
}
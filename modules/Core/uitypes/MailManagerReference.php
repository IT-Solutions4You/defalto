<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Core_MailManagerReference_UIType extends Vtiger_Base_UIType {

    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     * @param $value
     * @param false $record
     * @param false $recordInstance
     * @return int
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return (int)$value;
    }

    /**
     * Function to get the Template name for the current UI Type Object
     * @return string - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/MailManagerReference.tpl';
    }

    /**
     * Function to get the Template name for the current UI Type Object
     * @return string - Template Name
     */
    public function getDetailViewTemplateName()
    {
        return 'uitypes/MailManagerReferenceDetailView.tpl';
    }
}
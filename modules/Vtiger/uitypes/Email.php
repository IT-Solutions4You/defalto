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

class Vtiger_Email_UIType extends Vtiger_Base_UIType
{
    /**
     * Function to get the Template name for the current UI Type object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/Email.tpl';
    }

    public function getDisplayValue($value, $recordId = false, $recordInstance = false)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $internalMailer = $currentUser->get('internal_mailer');
        if ($value) {
            $moduleName = $this->get('field')->get('block')->module->name;
            $fieldName = $this->get('field')->get('name');
            if ($internalMailer == 1) {
                /**
                 *  We should not add "emailField" class to user name field.
                 *  If we do so, for sending mail from list view is taking that value as a TO field.
                 */
                if ($moduleName == "Users" && $fieldName == "user_name") {
                    $value = "<a class='cursorPointer' onclick=\"Vtiger_Helper_Js.getInternalMailer($recordId," .
                        "'$fieldName','$moduleName');\">" . ($value) . "</a>";
                } else {
                    $value = "<a class='emailField cursorPointer' onclick=\"Vtiger_Helper_Js.getInternalMailer($recordId," .
                        "'$fieldName','$moduleName');\">" . ($value) . "</a>";
                }
            } else {
                if ($moduleName == "Users" && $fieldName == "user_name") {
                    $value = "<a class='cursorPointer'  href='mailto:" . $value . "'>" . ($value) . "</a>";
                } else {
                    $value = "<a class='emailField cursorPointer'  href='mailto:" . $value . "'>" . ($value) . "</a>";
                }
            }
        }

        return $value;
    }
}
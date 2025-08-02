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

class Settings_MailConverter_RuleField_Model extends Vtiger_Field_Model
{
    public function getFieldDataType()
    {
        return $this->get('datatype');
    }

    public function getPickListValues()
    {
        $fieldName = $this->getName();
        $pickListValues = [];
        if ($fieldName == 'subject') {
            $optionList = ['Contains', 'Not Contains', 'Equals', 'Not Equals', 'Has Ticket Number', 'Begins With', 'Ends With', 'Regex'];
            foreach ($optionList as $option) {
                $pickListValues[$option] = vtranslate($option, 'Settings::MailConverter');
            }
        } elseif ($fieldName == 'body') {
            $optionList = ['Contains', 'Not Contains', 'Equals', 'Not Equals', 'Begins With', 'Ends With'];
            foreach ($optionList as $option) {
                $pickListValues[$option] = vtranslate($option, 'Settings::MailConverter');
            }
        } elseif ($fieldName == 'action') {
            $optionList = [
                'CREATE_HelpDesk_FROM',
                'UPDATE_HelpDesk_SUBJECT',
                'CREATE_Leads_SUBJECT',
                'CREATE_Contacts_SUBJECT',
                'CREATE_Accounts_SUBJECT',
                'LINK_Contacts_FROM',
                'LINK_Contacts_TO',
                'LINK_Leads_FROM',
                'LINK_Leads_TO',
                'LINK_Accounts_FROM',
                'LINK_Accounts_TO'
            ];
            $optionListCustom = Settings_MailConverter_RuleRecord_Model::getCustomActions();
            $optionList = array_merge($optionList, $optionListCustom);
            foreach ($optionList as $option) {
                $pickListValues[$option] = vtranslate($option, 'Settings::MailConverter');
            }
        }

        return $pickListValues;
    }

    public function getRadioOptions()
    {
        $fieldName = $this->getName();
        if ($fieldName == 'matchusing') {
            $options['AND'] = vtranslate('LBL_ALL_CONDITIONS', 'Settings::MailConverter');
            $options['OR'] = vtranslate('LBL_ANY_CONDITIONS', 'Settings::MailConverter');
        }

        return $options;
    }
}
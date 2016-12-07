<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_RuleField_Model extends Vtiger_Field_Model {
    
	public function getFieldDataType() {
        return $this->get('datatype');
    }
	
	public function getPickListValues($fieldName = false) {
        if(!$fieldName)
            $fieldName = $this->getName();
        $pickListValues = array();
        if($fieldName == 'subject') {
            $optionList = array('Contains', 'Not Contains', 'Equals', 'Not Equals', 'Has Ticket Number', 'Begins With', 'Ends With', 'Regex');
            foreach($optionList as $option) {
                $pickListValues[$option] = vtranslate($option, 'Settings::MailConverter');
            }
        }else if ($fieldName == 'body') {
            $optionList = array('Contains', 'Not Contains', 'Equals', 'Not Equals', 'Begins With', 'Ends With');
            foreach($optionList as $option) {
				$pickListValues[$option] = vtranslate($option, 'Settings::MailConverter');
			}
		} else if ($fieldName == 'action') {
			$optionList = array(
				'HelpDesk' => array('CREATE_HelpDesk_FROM', 'LINK_HelpDesk_FROM', 'LINK_HelpDesk_TO', 'CREATE_HelpDeskNoContact_FROM', 'UPDATE_HelpDesk_SUBJECT'), 
				'Leads' => array('CREATE_Leads_SUBJECT', 'LINK_Leads_FROM', 'LINK_Leads_TO'),
				'Contacts' => array('CREATE_Contacts_SUBJECT', 'LINK_Contacts_FROM', 'LINK_Contacts_TO'),
				'Accounts' => array('CREATE_Accounts_SUBJECT', 'LINK_Accounts_FROM', 'LINK_Accounts_TO'), 
				'Potentials' => array('CREATE_Potentials_SUBJECT', 'LINK_Potentials_FROM', 'LINK_Potentials_TO', 'CREATE_PotentialsNoContact_SUBJECT'), 
			);
			foreach ($optionList as $module => $option) {
				foreach ($option as $value) {
					$pickListValues[$value] = vtranslate($value, 'Settings::MailConverter');
				}
            }
		}
        return $pickListValues;
    }
	
	public function getRadioOptions($qualifiedModule = 'Settings::MailConverter') {
        $fieldName = $this->getName();
        if($fieldName == 'matchusing') {
            $options['AND'] = vtranslate('LBL_ALL_CONDITIONS',$qualifiedModule);
            $options['OR'] = vtranslate('LBL_ANY_CONDITIONS',$qualifiedModule);
        }
        return $options;
    }
}	
?>

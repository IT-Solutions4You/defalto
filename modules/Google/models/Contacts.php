<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~/modules/WSAPP/synclib/models/SyncRecordModel.php');

class Google_Contacts_Model extends WSAPP_SyncRecordModel {

    /**
     * return id of Google Record
     * @return mixed id
     */
    public function getId()
    {
        return $this->data['entity']['resourceName'] ? : $this->data['entity']['id']['$t'];
    }

    /**
     * return modified time of Google Record
     * @return <date> modified time
     */
    public function getModifiedTime()
    {
        $updateTime = $this->data['entity']['metadata']['sources'][0]['updateTime'] ? : $this->data['entity']['updated']['$t'];
        $updateTime = $updateTime ? : str_replace(' ', 'T', date('Y-m-d H:i:s'));

        return $this->vtigerFormat($updateTime);
    }

    function getNamePrefix()
    {
        return $this->data['entity']['names'][0]['honorificPrefix'];
    }

    /**
     * return first name of Google Record
     * @return mixed $first name
     */
    function getFirstName()
    {
        return $this->data['entity']['names'][0]['givenName'];
    }

    /**
     * return Lastname of Google Record
     * @return mixed Last name
     */
    function getLastName()
    {
        return $this->data['entity']['names'][0]['familyName'];
    }

    /**
     * return Emails of Google Record
     * @return array emails
     */
    function getEmails()
    {
        $arr = $this->data['entity']['emailAddresses'];
        $emails = [];

        if (is_array($arr)) {
            foreach ($arr as $email) {
                $labelEmail = $email['type'];
                $emails[$labelEmail] = $email['value'];
            }
        }

        return $emails;
    }

    /**
     * return Phone number of Google Record
     * @return array phone numbers
     */
    function getPhones()
    {
        $arr = $this->data['entity']['phoneNumbers'];
        $phones = [];

        if (is_array($arr)) {
            foreach ($arr as $phone) {
                $phoneNo = $phone['value'];
                $labelPhone = $phone['type'];
                $phones[$labelPhone] = $phoneNo;
            }
        }

        return $phones;
    }

    /**
     * return Addresss of Google Record
     * @return array Addresses
     */
    function getAddresses()
    {
        $arr = $this->data['entity']['addresses'];
        $addresses = [];

        if (is_array($arr)) {
            foreach ($arr as $address) {
                $structuredAddress = [
                    'street'           => $address['streetAddress'],
                    'pobox'            => $address['poBox'],
                    'postcode'         => $address['postalCode'],
                    'city'             => $address['city'],
                    'region'           => $address['region'],
                    'country'          => $address['country'],
                    'formattedAddress' => $address['formattedValue']
                ];
                $labelAddress = $address['type'];
                $addresses[$labelAddress] = $structuredAddress;
            }
        }

        return $addresses;
    }

    function getUserDefineFieldsValues()
    {
        $fieldValues = [];
        $userDefinedFields = $this->data['entity']['userDefined'];

        if (is_array($userDefinedFields) && php7_count($userDefinedFields)) {
            foreach ($userDefinedFields as $userDefinedField) {
                $fieldName = $userDefinedField['key'];
                $fieldValues[$fieldName] = $userDefinedField['value'];
            }
        }

        return $fieldValues;
    }

    function getBirthday()
    {
        if ($this->data['entity']['birthdays'][0]['date']) {
            $birthDate = $this->data['entity']['birthdays'][0]['date']['year'] . '-' . $this->data['entity']['birthdays'][0]['date']['month'] . '-' . $this->data['entity']['birthdays'][0]['date']['day'];
        } else {
            $date = $this->data['entity']['birthdays'][0]['text'];
            $date = explode('/', $date);
            $birthDate = $date[2] . '-' . $date[0] . '-' . $date[1];
        }

        return $birthDate;
    }

    function getTitle()
    {
        return $this->data['entity']['organizations'][0]['title'];
    }
    
    function getAccountName($userId) {
        $description = false;
        $orgName = $this->data['entity']['organizations'][0]['name'];

        if(empty($orgName)) {
            $contactsModel = Vtiger_Module_Model::getInstance('Contacts');
            $accountFieldInstance = Vtiger_Field_Model::getInstance('account_id', $contactsModel);
            if($accountFieldInstance->isMandatory()) {
                $orgName = '????';
                $description = 'This Organization is created to support Google Contacts Synchronization. Since Organization Name is mandatory !';
            }
        }
        if(!empty($orgName)) {
            $db = PearDatabase::getInstance();
            $result = $db->pquery("SELECT crmid FROM vtiger_crmentity WHERE label = ? AND deleted = ? AND setype = ?", array($orgName, 0, 'Accounts'));
            if($db->num_rows($result) < 1) {
				try {
					$accountModel = Vtiger_Module_Model::getInstance('Accounts');
					$recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
				
					$fieldInstances = Vtiger_Field_Model::getAllForModule($accountModel);
					foreach($fieldInstances as $blockInstance) {
						foreach($blockInstance as $fieldInstance) {
							$fieldName = $fieldInstance->getName();
							$fieldValue = $recordModel->get($fieldName);
							if(empty($fieldValue)) {
								$defaultValue = $fieldInstance->getDefaultFieldValue();
								if($defaultValue) {
									$recordModel->set($fieldName, decode_html($defaultValue));
								}
								if($fieldInstance->isMandatory() && !$defaultValue) {
									$randomValue = Vtiger_Util_Helper::getDefaultMandatoryValue($fieldInstance->getFieldDataType());
									if($fieldInstance->getFieldDataType() == 'picklist' || $fieldInstance->getFieldDataType() == 'multipicklist') {
										$picklistValues = $fieldInstance->getPicklistValues();
										$randomValue = reset($picklistValues);
									}
									$recordModel->set($fieldName, $randomValue);
								}
							}
						}
					}
					$recordModel->set('mode', '');
					$recordModel->set('accountname', $orgName);
					$recordModel->set('assigned_user_id', $userId);
					$recordModel->set('source', 'GOOGLE');
					if($description) {
						$recordModel->set('description', $description);
					}
					$recordModel->save();
				} catch (Exception $e) {
					//TODO - Review
				}
            }
            return $orgName;
        }
        return false;
    }
    
    function getDescription() {
        return $this->data['entity']['content']['$t'];
    }

    /**
     * Returns the Google_Contacts_Model of Google Record
     * @param <array> $recordValues
     * @return Google_Contacts_Model
     */
    public static function getInstanceFromValues($recordValues) {
        $model = new Google_Contacts_Model($recordValues);
        return $model;
    }

    /**
     * converts the Google Format date to 
     * @param <date> $date Google Date
     * @return <date> Vtiger date Format
     */
    public static function vtigerFormat($date) {
        [$date, $timestring] = explode('T', $date);
        [$time, $tz] = explode('.', $timestring);

        return $date . " " . $time;
    }
}
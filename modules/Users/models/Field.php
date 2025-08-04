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

/**
 * User Field Model Class
 */
class Users_Field_Model extends Vtiger_Field_Model
{
    /**
     * Function to check whether the current field is read-only
     * @return <Boolean> - true/false
     */
    public function isReadOnly()
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $readonlyUiTypes = [
            self::UITYPE_USER_ROLE,
            self::UITYPE_USER_USERNAME,
            self::UITYPE_USER_IS_ADMIN,
            self::UITYPE_USER_STATUS,
            self::UITYPE_USER_PROFILE,
        ];

        return !$currentUserModel->isAdminUser() && in_array($this->getUIType(), $readonlyUiTypes);
    }

    /**
     * Function to check if the field is shown in detail view
     * @return <Boolean> - true/false
     */
    public function isViewEnabled()
    {
        if ($this->getDisplayType() == '4' || in_array($this->get('presence'), [1, 3])) {
            return false;
        }

        return true;
    }

    /**
     * Function to get the Webservice Field data type
     * @return <String> Data type of the field
     * @throws Exception
     */
    public function getFieldDataType()
    {
        $uiType = $this->getUIType();

        if ($uiType == self::UITYPE_USER_PASSWORD) {
            return 'password';
        } elseif (in_array($uiType, [self::UITYPE_USER_PICKLIST, self::UITYPE_USER_STATUS])) {
            return 'picklist';
        } elseif ($uiType == self::UITYPE_USER_REPORTS_TO) {
            return 'userReference';
        } elseif ($uiType == self::UITYPE_USER_ROLE) {
            return 'userRole';
        } elseif ($uiType == self::UITYPE_USER_IMAGE) {
            return 'image';
        } elseif ($uiType == self::UITYPE_USER_THEME) {
            return 'theme';
        } elseif ($uiType == self::UITYPE_USER_PROFILE) {
            return 'profile';
        }

        return parent::getFieldDataType();
    }

    /**
     * Function to check whether field is ajax editable'
     * @return bool
     * @throws Exception
     */
    public function isAjaxEditable()
    {
        return !(!$this->isEditable() || in_array(
                $this->getUIType(),
                [self::UITYPE_USER_PROFILE, self::UITYPE_USER_IMAGE, self::UITYPE_USER_USERNAME, self::UITYPE_USER_ROLE, self::UITYPE_USER_REPORTS_TO]
            ) || $this->getName() === 'signature');
    }

    /**
     * Function to get all the available picklist values for the current field
     * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
     */
    public function getPicklistValues()
    {
        $fieldName = $this->getName();

        if ($this->get('uitype') == 32) {
            if ($fieldName == 'language') {
                return Vtiger_Language_Handler::getAllLanguages();
            } elseif ($fieldName == 'defaultlandingpage') {
                $db = PearDatabase::getInstance();
                $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
                $presence = [0];
                $restrictedModules = ['Integration', 'Dashboard', 'ModComments'];
                $query = 'SELECT name, tablabel, tabid FROM vtiger_tab WHERE presence IN (' . generateQuestionMarks(
                        $presence
                    ) . ') AND isentitytype = ? AND name NOT IN (' . generateQuestionMarks($restrictedModules) . ')';
                $result = $db->pquery($query, [$presence, '1', $restrictedModules]);
                $numOfRows = $db->num_rows($result);
                $moduleData = ['Home' => vtranslate('Home', 'Home')];

                for ($i = 0; $i < $numOfRows; $i++) {
                    $tabId = $db->query_result($result, $i, 'tabid');
                    // check the module access permission, if user has permission then show it in default module list
                    if ($currentUserPriviligesModel->hasModulePermission($tabId)) {
                        $moduleName = $db->query_result($result, $i, 'name');
                        $moduleLabel = $db->query_result($result, $i, 'tablabel');
                        $moduleData[$moduleName] = vtranslate($moduleLabel, $moduleName);
                    }
                }

                return $moduleData;
            }
        } elseif ($this->get('uitype') == 115) {
            $db = PearDatabase::getInstance();
            $query = 'SELECT ' . $this->getFieldName() . ' FROM vtiger_' . $this->getFieldName();
            $result = $db->pquery($query, []);
            $num_rows = $db->num_rows($result);
            $fieldPickListValues = [];

            for ($i = 0; $i < $num_rows; $i++) {
                $picklistValue = $db->query_result($result, $i, $this->getFieldName());
                $fieldPickListValues[$picklistValue] = vtranslate($picklistValue, $this->getModuleName());
            }

            return $fieldPickListValues;
        }

        $calendarFields = [
            'defaulteventstatus' => 'calendar_status',
            'defaultactivitytype' => 'calendar_type',
        ];

        if (!empty($calendarFields[$fieldName])) {
            $moduleModel = Vtiger_Module_Model::getInstance('Appointments');

            return Vtiger_Field_Model::getInstance($calendarFields[$fieldName], $moduleModel)->getPicklistValues();
        }

        return parent::getPicklistValues();
    }

    /**
     * Function to get all the available picklist values for the current field
     * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
     */
    public function getEditablePicklistValues()
    {
        return $this->getPicklistValues();
    }

    /**
     * Function to returns all skins(themes)
     * @return <Array>
     */
    public function getAllSkins()
    {
        return Vtiger_Theme::getAllSkins();
    }

    /**
     * Function to retieve display value for a value
     *
     * @param <String> $value - value which need to be converted to display value
     *
     * @return <String> - converted display value
     */
    public function getDisplayValue($value, $recordId = false, $recordInstance = false)
    {
        if ($this->get('uitype') == 32) {
            return Vtiger_Language_Handler::getLanguageLabel($value);
        }
        $fieldName = $this->getFieldName();
        if (($fieldName == 'currency_decimal_separator' || $fieldName == 'currency_grouping_separator') && ($value == "&nbsp;")) {
            return vtranslate('Space', 'Users');
        }

        return parent::getDisplayValue($value, $recordId);
    }

    /**
     * Function returns all the User Roles
     * @return
     */
    public function getAllRoles()
    {
        $roleModels = Settings_Roles_Record_Model::getAll();
        $roles = [];
        foreach ($roleModels as $roleId => $roleModel) {
            $roleName = $roleModel->getName();
            $roles[$roleName] = $roleId;
        }

        return $roles;
    }

    /**
     * Function to check whether this field editable or not
     * return <boolen> true/false
     */
    public function isEditable()
    {
        $isEditable = $this->get('editable');
        if (!$isEditable) {
            $this->set('editable', parent::isEditable());
        }

        return $this->get('editable');
    }

    /**
     * Function which will check if empty piclist option should be given
     */
    public function isEmptyPicklistOptionAllowed()
    {
        if ($this->getFieldName() == 'reminder_interval') {
            return true;
        }

        return false;
    }

    public function getPicklistDetails()
    {
        if ($this->get('uitype') == 98) {
            $picklistValues = $this->getAllRoles();
            $picklistValues = array_flip($picklistValues);
        } else {
            $picklistValues = $this->getPicklistValues();
        }

        $pickListDetails = [];
        foreach ($picklistValues as $value => $transValue) {
            $pickListDetails[] = ['label' => $transValue, 'value' => $value];
        }

        return $pickListDetails;
    }

    /**
     * @return array
     */
    public function getAllProfiles(): array
    {
        $profileModels = Settings_Profiles_Record_Model::getAll();
        $profiles = [];

        foreach ($profileModels as $profileId => $profileModel) {
            $profileName = $profileModel->getName();
            $profiles[$profileName] = $profileId;
        }

        return $profiles;
    }
}
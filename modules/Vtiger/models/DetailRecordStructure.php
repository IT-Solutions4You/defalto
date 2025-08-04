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
 * Vtiger Detail View Record Structure Model
 */
class Vtiger_DetailRecordStructure_Model extends Vtiger_RecordStructure_Model
{
    private $picklistValueMap = [];
    private $picklistRoleMap = [];

    /**
     * Function to get the values in stuctured format
     * @return <array> - values in structure array('block'=>array(fieldinfo));
     */
    public function getStructure()
    {
        $currentUsersModel = Users_Record_Model::getCurrentUserModel();
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $values = [];
        $recordModel = $this->getRecord();
        $recordExists = !empty($recordModel);
        $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();
        foreach ($blockModelList as $blockLabel => $blockModel) {
            $fieldModelList = $blockModel->getFields();
            if (!empty ($fieldModelList)) {
                $values[$blockLabel] = [];
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->isViewableInDetailView()) {
                        if ($recordExists) {
                            $value = $recordModel->get($fieldName);
                            if (!$currentUsersModel->isAdminUser() && ($fieldModel->getFieldDataType() == 'picklist' || $fieldModel->getFieldDataType() == 'multipicklist')) {
                                $value = decode_html($value);
                                $this->setupAccessiblePicklistValueList($fieldModel);
                            }
                            $fieldModel->set('fieldvalue', $value);
                        }
                        $values[$blockLabel][$fieldName] = $fieldModel;
                    }
                }
            }
        }
        $this->structuredValues = $values;

        return $values;
    }

    public function setupAccessiblePicklistValueList($fieldModel)
    {
        $db = PearDatabase::getInstance();
        $currentUsersModel = Users_Record_Model::getCurrentUserModel();
        $roleId = $currentUsersModel->getRole();
        $name = $fieldModel->getName();
        $isRoleBased = vtws_isRoleBasedPicklist($name);
        $this->picklistRoleMap[$name] = $isRoleBased;
        if ($this->picklistRoleMap[$name]) {
            $this->picklistValueMap[$name] = $fieldModel->getPicklistValues();
        }
    }
}
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
 * Vtiger Summary View Record Structure Model
 */
class Vtiger_SummaryRecordStructure_Model extends Vtiger_DetailRecordStructure_Model
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
        $summaryFieldsList = $this->getModule()->getSummaryViewFieldsList();

        //For Calendar module getSummaryViewFieldsList() returns empty array. On changing that API Calendar related tab header
        //field changes. In related tab if summary fields are empty, it is depending of getRelatedListFields(). So added same here.
        if (empty($summaryFieldsList)) {
            $fieldModuleModel = $this->getModule();
            $summaryFieldsListNames = $fieldModuleModel->getRelatedListFields();
            foreach ($summaryFieldsListNames as $summaryFieldsListName) {
                $summaryFieldsList[$summaryFieldsListName] = $fieldModuleModel->getField($summaryFieldsListName);
            }
        }

        $recordModel = $this->getRecord();
        $summaryFields = [];

        if ($summaryFieldsList) {
            $fields = [];
            $fieldsMaxSequence = count($summaryFieldsList);

            foreach ($summaryFieldsList as $fieldName => $fieldModel) {
                if ($fieldModel->isViewableInDetailView()) {
                    $fieldModel->set('fieldvalue', $recordModel->get($fieldName));

                    if (!$currentUsersModel->isAdminUser() && ($fieldModel->getFieldDataType() == 'picklist' || $fieldModel->getFieldDataType() == 'multipicklist')) {
                        $this->setupAccessiblePicklistValueList($fieldName);
                    }

                    $fieldsMaxSequence++;
                    $sequence = (int)$fieldModel->get('summaryfieldsequence') ?: $fieldsMaxSequence;
                    $fields[$sequence] = $fieldModel;
                }
            }

            ksort($fields);

            foreach ($fields as $fieldModel) {
                $summaryFields[$fieldModel->getName()] = $fieldModel;
            }
        }

        return [
            'SUMMARY_FIELDS' => $summaryFields,
        ];
    }

    public function setupAccessiblePicklistValueList($name)
    {
        $db = PearDatabase::getInstance();
        $currentUsersModel = Users_Record_Model::getCurrentUserModel();
        $roleId = $currentUsersModel->getRole();
        $isRoleBased = vtws_isRoleBasedPicklist($name);
        $this->picklistRoleMap[$name] = $isRoleBased;
        if ($this->picklistRoleMap[$name]) {
            $this->picklistValueMap[$name] = getAssignedPicklistValues($name, $roleId, $db);
        }
    }
}
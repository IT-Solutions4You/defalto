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

class Users_EditRecordStructure_Model extends Vtiger_EditRecordStructure_Model
{
    /**
     * Function to get the values in stuctured format
     * @return <array> - values in structure array('block'=>array(fieldinfo));
     */
    public function getStructure()
    {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $values = [];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $recordModel = $this->getRecord();
        $recordId = $recordModel->getId();
        $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();
        foreach ($blockModelList as $blockLabel => $blockModel) {
            $fieldModelList = $blockModel->getFields();
            if ($fieldModelList) {
                $values[$blockLabel] = [];
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->get('uitype') == 115) {
                        $fieldModel->set('editable', false);
                    }
                    if (empty($recordId) && ($fieldModel->get('uitype') == 99 || $fieldModel->get('uitype') == 106)) {
                        $fieldModel->set('editable', true);
                    }
                    //Is Admin field is editable when the record user != current user
                    if (in_array($fieldModel->get('uitype'), [156]) && $currentUserModel->getId() !== $recordId) {
                        $fieldModel->set('editable', true);
                        if ($fieldModel->get('uitype') == 156) {
                            $fieldValue = false;
                            $defaultValue = $fieldModel->getDefaultFieldValue();
                            if ($recordModel->get($fieldName) === 'on') {
                                $fieldValue = true;
                            }
                            $recordModel->set($fieldName, $fieldValue);
                        }
                    }
                    if ($fieldName == 'is_owner') {
                        $fieldModel->set('editable', false);
                    } elseif ($fieldName == 'reports_to_id' && !$currentUserModel->isAdminUser()) {
                        continue;
                    }
                    if ($fieldModel->isEditable() && $fieldName != 'is_owner') {
                        if ($recordModel->get($fieldName) != '') {
                            $fieldModel->set('fieldvalue', $recordModel->get($fieldName));
                        } else {
                            $defaultValue = $fieldModel->getDefaultFieldValue();
                            if (!empty($defaultValue) && !$recordId) {
                                $fieldModel->set('fieldvalue', $defaultValue);
                            }
                        }

                        if (!$recordId && $fieldModel->get('uitype') == 99) {
                            $fieldModel->set('editable', true);
                            $values[$blockLabel][$fieldName] = $fieldModel;
                        } elseif ($fieldModel->get('uitype') != 99) {
                            $values[$blockLabel][$fieldName] = $fieldModel;
                        }
                    }
                }
            }
        }
        $this->structuredValues = $values;

        return $values;
    }
}
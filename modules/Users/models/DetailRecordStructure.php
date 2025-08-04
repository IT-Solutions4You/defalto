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

class Users_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model
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
            if (!empty ($fieldModelList)) {
                $values[$blockLabel] = [];
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    //Is Admin and Status fields are Ajax editable when the record user != current user
                    if (in_array($fieldModel->get('uitype'), [156, 115]) && $currentUserModel->getId() !== $recordId) {
                        $fieldModel->set('editable', true);
                        if ($fieldModel->get('uitype') == 156) {
                            $fieldValue = false;
                            if ($recordModel->get($fieldName) === 'on') {
                                $fieldValue = true;
                            }
                            $recordModel->set($fieldName, $fieldValue);
                        }
                    }
                    if ($fieldModel->isViewableInDetailView()) {
                        if ($recordId) {
                            $fieldModel->set('fieldvalue', $recordModel->get($fieldName));
                        }
                        $values[$blockLabel][$fieldName] = $fieldModel;
                    }
                }
            }
        }
        $this->structuredValues = $values;

        return $values;
    }
}
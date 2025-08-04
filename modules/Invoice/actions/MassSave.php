<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
class Invoice_MassSave_Action extends Inventory_MassSave_Action
{
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        try {
            vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode', false));
            $moduleName = $request->getModule();
            $recordModels = $this->getRecordModelsFromRequest($request);

            foreach ($recordModels as $recordId => $recordModel) {
                if (Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
                    //Inventory line items getting wiped out
                    $_REQUEST['action'] = 'MassEditSave';
                    $recordModel->save();
                }
            }
            vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);
            $response->setResult(true);
        } catch (DuplicateException $e) {
            $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }
        $response->emit();
    }

    /**
     * Function to get the record model based on the request parameters
     *
     * @param Vtiger_Request $request
     *
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    public function getRecordModelsFromRequest(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $recordIds = $this->getRecordsListFromRequest($request);
        $recordModels = [];

        $fieldModelList = $moduleModel->getFields();
        foreach ($recordIds as $recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
            $recordModel->set('id', $recordId);
            $recordModel->set('mode', 'edit');

            foreach ($fieldModelList as $fieldName => $fieldModel) {
                $fieldValue = $request->get($fieldName, null);
                $fieldDataType = $fieldModel->getFieldDataType();

                if ($fieldDataType == 'time') {
                    $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                } elseif ($fieldDataType === 'date') {
                    $fieldValue = $fieldModel->getUITypeModel()->getDBInsertValue($fieldValue);
                }

                if (isset($fieldValue) && $fieldValue != null) {
                    if (!is_array($fieldValue)) {
                        $fieldValue = trim($fieldValue);
                    }
                    $recordModel->set($fieldName, $fieldValue);
                }
            }
            $recordModels[$recordId] = $recordModel;
        }

        return $recordModels;
    }
}
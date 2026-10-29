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

class Vtiger_MassSave_Action extends Vtiger_Mass_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'EditView'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        try {
            vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode', false));
            $moduleName = $request->getModule();
            $recordModels = $this->getRecordModelsFromRequest($request);
            $actionPermission = Core_RecordActionPermission_Model::getInstance();
            $actionPermission->preload(array_keys($recordModels));
            $fieldNames = $this->getSubmittedFieldNames($request, Vtiger_Module_Model::getInstance($moduleName));
            $skipped = [];

            foreach ($recordModels as $recordId => $recordModel) {
                // Records denied by the profile or by record action rules (edit, locked fields, status values) are skipped
                $denyReason = Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)
                    ? $actionPermission->getChangeDenyReason($recordModel, $fieldNames)
                    : ($actionPermission->getDenyReason((int)$recordId, Core_RecordActionPermission_Model::ACTION_EDIT) ?? vtranslate('LBL_PERMISSION_DENIED'));

                if (null === $denyReason) {
                    $recordModel->save();
                } else {
                    $skipped[$recordId] = $denyReason;
                }
            }

            vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);
            $response->setResult(empty($skipped) ? true : ['message' => $actionPermission->getSkippedMessage($skipped)]);
        } catch (DuplicateException $e) {
            $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }
        $response->emit();
    }

    /**
     * Returns the fields submitted for the mass update.
     *
     * @param Vtiger_Request      $request
     * @param Vtiger_Module_Model $moduleModel
     *
     * @return array field names
     */
    protected function getSubmittedFieldNames(Vtiger_Request $request, Vtiger_Module_Model $moduleModel): array
    {
        $fieldNames = [];

        foreach (array_keys($moduleModel->getFields()) as $fieldName) {
            if ($request->has($fieldName)) {
                $fieldNames[] = $fieldName;
            }
        }

        return $fieldNames;
    }

    /**
     * Function to get the updated record models
     *
     * @param Vtiger_Request $request
     *
     * @return array of Vtiger_Record_Model
     */
    protected function getRecordModelsFromRequest(Vtiger_Request $request)
    {
        $recordIds = $this->getRecordsListFromRequest($request);
        $recordModels = [];

        foreach ($recordIds as $recordId) {
            $recordModels[$recordId] = $this->getUpdatedRecord($request, $recordId);
        }

        return $recordModels;
    }

    private function getUpdatedRecord(Vtiger_Request $request, $recordId)
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $recordModel->set('mode', 'edit');
        $fieldModelList = $recordModel->getModule()->getFields();

        foreach ($fieldModelList as $fieldName => $fieldModel) {
            if ($request->has($fieldName)) {
                $fieldValue = $request->get($fieldName, null);
                $fieldDataType = $fieldModel->getFieldDataType();
                if ($fieldDataType == 'time') {
                    $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                }

                if (!is_array($fieldValue)) {
                    $fieldValue = trim($fieldValue);
                }
                $recordModel->set($fieldName, $fieldValue);
            }
        }

        return $recordModel;
    }
}
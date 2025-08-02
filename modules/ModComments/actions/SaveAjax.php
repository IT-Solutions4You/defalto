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

class ModComments_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $record = $request->get('record');
        //Do not allow ajax edit of existing comments
        if ($record) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $userId = $currentUserModel->getId();
        $request->set('assigned_user_id', $userId);
        $request->set('userid', $userId);

        $recordModel = $this->saveRecord($request);

        $fieldModelList = $recordModel->getModule()->getFields();
        $result = [];
        foreach ($fieldModelList as $fieldName => $fieldModel) {
            if ($fieldModel->isViewable()) {
                $fieldValue = $recordModel->get($fieldName);
                $result[$fieldName] = ['value' => $fieldValue, 'display_value' => $fieldModel->getDisplayValue($fieldValue)];
            }
        }
        $result['id'] = $result['_recordId'] = $recordModel->getId();
        $result['_recordLabel'] = $recordModel->getName();

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }

    /**
     * Function to save record
     *
     * @param <Vtiger_Request> $request - values of the record
     *
     * @return <RecordModel> - record Model of saved record
     */
    public function saveRecord($request)
    {
        $recordModel = $this->getRecordModelFromRequest($request);

        $recordModel->save();
        if ($request->get('relationOperation')) {
            $parentModuleName = $request->get('sourceModule');
            $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
            $parentRecordId = $request->get('sourceRecord');
            $relatedModule = $recordModel->getModule();
            $relatedRecordId = $recordModel->getId();

            $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
            $relationModel->addRelation($parentRecordId, $relatedRecordId);
        }

        return $recordModel;
    }

    /**
     * Function to get the record model based on the request parameters
     *
     * @param Vtiger_Request $request
     *
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    public function getRecordModelFromRequest(Vtiger_Request $request)
    {
        $recordModel = parent::getRecordModelFromRequest($request);
        $recordModel->set('is_private', $request->get('is_private'));

        return $recordModel;
    }
}
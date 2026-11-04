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

class ModComments_Save_Action extends Vtiger_Save_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $moduleModel = Vtiger_Module_Model::getInstance('ModComments');

        if ($request->get('record')) {
            parent::checkPermission($request);

            $recordId = $request->get('record');

            if (!Users_Privileges_Model::isPermitted('ModComments', 'DetailView', $recordId)
                || !Users_Privileges_Model::isPermitted('ModComments', 'EditView', $recordId)) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'ModComments');
            $relatedRecordId = $moduleModel->getViewableRelatedRecordId($recordModel->get('related_to'));

            if (false === $relatedRecordId) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $request->set('related_to', $relatedRecordId);
            $request->set('parent_comments', $recordModel->get('parent_comments'));

            return true;
        }

        $relatedRecordId = $moduleModel->getPermittedRelatedRecordId($request->get('related_to'));

        if (false === $relatedRecordId) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        if ($request->get('relationOperation')) {
            $sourceRecordId = $moduleModel->getPermittedRelatedRecordId($request->get('sourceRecord'));
            $sourceModule = getSalesEntityType($relatedRecordId);

            if ($sourceRecordId !== $relatedRecordId || $request->get('sourceModule') !== $sourceModule) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        $request->set('related_to', $relatedRecordId);

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $request->set('assigned_user_id', $currentUserModel->getId());
        $request->set('userid', $currentUserModel->getId());

        $recordModel = $this->saveRecord($request);
        $responseFieldsToSent = ['reasontoedit', 'commentcontent'];
        $fieldModelList = $recordModel->getModule()->getFields();
        foreach ($responseFieldsToSent as $fieldName) {
            $fieldModel = $fieldModelList[$fieldName];
            $fieldValue = $recordModel->get($fieldName);
            $result[$fieldName] = $fieldModel->getDisplayValue(Vtiger_Util_Helper::toSafeHTML($fieldValue));
        }

        $result['success'] = true;
        $result['modifiedtime'] = Vtiger_Util_Helper::formatDateDiffInStrings($recordModel->get('modifiedtime'));
        $result['modifiedtimetitle'] = Vtiger_Util_Helper::formatDateTimeIntoDayString($recordModel->get('modifiedtime'));

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
    protected function getRecordModelFromRequest(Vtiger_Request $request)
    {
        $recordModel = parent::getRecordModelFromRequest($request);

        $recordModel->set('reasontoedit', $request->getRaw('reasontoedit'));

        return $recordModel;
    }
}

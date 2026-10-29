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

class ModComments_MassSaveAjax_Action extends Vtiger_Mass_Action
{
    private array $relatedRecordIds = [];

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $moduleModel = Vtiger_Module_Model::getInstance('ModComments');
        $recordIds = (array)$this->getRecordsListFromRequest($request);
        $this->relatedRecordIds = [];

        if (!$moduleModel->isPermitted('CreateView')) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        foreach ($recordIds as $recordId) {
            $relatedRecordId = $moduleModel->getPermittedRelatedRecordId($recordId);

            if (false === $relatedRecordId) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $this->relatedRecordIds[] = $relatedRecordId;
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $recordModels = $this->getRecordModelsFromRequest($request);
        foreach ($recordModels as $recordId => $recordModel) {
            $recordModel->save();
        }
    }

    /**
     * Function to get the record model based on the request parameters
     *
     * @param Vtiger_Request $request
     *
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    private function getRecordModelsFromRequest(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordModels = [];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        foreach ($this->relatedRecordIds as $recordId) {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $recordModel->set('mode', '');
            $recordModel->set('commentcontent', $request->getRaw('commentcontent'));
            $recordModel->set('related_to', $recordId);
            $recordModel->set('assigned_user_id', $currentUserModel->getId());
            $recordModel->set('userid', $currentUserModel->getId());
            $recordModels[$recordId] = $recordModel;
        }

        return $recordModels;
    }
}

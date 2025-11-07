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
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
            throw new Exception(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
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
        $recordIds = $this->getRecordsListFromRequest($request);
        $recordModels = [];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        foreach ($recordIds as $recordId) {
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
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

class Settings_Webforms_CheckDuplicate_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        $moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if (!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $qualifiedModuleName = $request->getModule(false);

        if ($recordId) {
            $recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
        } else {
            $recordModel = Settings_Webforms_Record_Model::getCleanInstance($qualifiedModuleName);
        }
        $recordModel->set('name', $request->get('name'));

        if (!$recordModel->checkDuplicate()) {
            $result = ['success' => false];
        } else {
            $result = ['success' => true, 'message' => vtranslate('LBL_DUPLICATES_EXIST', $qualifiedModuleName)];
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
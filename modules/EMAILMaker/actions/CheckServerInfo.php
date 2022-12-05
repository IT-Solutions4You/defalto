<?php
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class EMAILMaker_CheckServerInfo_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();
        $response = new Vtiger_Response();

        $result = $db->pquery('SELECT 1 FROM vtiger_systems WHERE server_type = ?', array('email'));
        if ($db->num_rows($result)) {
            $response->setResult(true);
        } else {
            $response->setResult(false);
        }
        return $response;
    }
}
<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_CheckServerInfo_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new Exception(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();
        $response = new Vtiger_Response();

        $result = $db->pquery('SELECT 1 FROM vtiger_systems WHERE server_type = ?', ['email']);
        if ($db->num_rows($result)) {
            $response->setResult(true);
        } else {
            $response->setResult(false);
        }

        return $response;
    }
}
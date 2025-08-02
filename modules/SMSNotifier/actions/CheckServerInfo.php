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

class SMSNotifier_CheckServerInfo_Action extends Vtiger_Action_Controller
{
    function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new Exception(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    function process(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();
        $response = new Vtiger_Response();

        $result = $db->pquery('SELECT 1 FROM vtiger_smsnotifier_servers WHERE isactive = 1', []);
        if ($db->num_rows($result)) {
            $response->setResult(true);
        } else {
            $response->setResult(false);
        }

        return $response;
    }
}
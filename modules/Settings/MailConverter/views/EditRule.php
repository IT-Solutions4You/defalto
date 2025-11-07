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

class Settings_MailConverter_EditRule_View extends Settings_Vtiger_IndexAjax_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $scannerId = $request->get('scannerId');

        if (!$scannerId) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $scannerId = $request->get('scannerId');
        $qualifiedModuleName = $request->getModule(false);
        $moduleName = $request->getModule();

        if ($recordId) {
            $recordModel = Settings_MailConverter_RuleRecord_Model::getInstanceById($recordId);
        } else {
            $recordModel = Settings_MailConverter_RuleRecord_Model::getCleanInstance($scannerId);
            $recordModel->set('matchusing', 'AND');
        }

        $assignedTo = Settings_MailConverter_RuleRecord_Model::getAssignedTo($scannerId, $recordId);
        $viewer = $this->getViewer($request);

        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('MODULE_MODEL', new Settings_MailConverter_Module_Model());

        $viewer->assign('SCANNER_ID', $scannerId);
        $viewer->assign('SCANNER_MODEL', Settings_MailConverter_Record_Model::getInstanceById($scannerId));

        $viewer->assign('DEFAULT_OPTIONS', Settings_MailConverter_RuleRecord_Model::getDefaultConditions());
        $viewer->assign('DEFAULT_ACTIONS', Settings_MailConverter_RuleRecord_Model::getDefaultActions());

        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('ASSIGNED_USER', $assignedTo[0]);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('RuleEditView.tpl', $qualifiedModuleName);
    }
}
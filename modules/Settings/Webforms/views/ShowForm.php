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

require_once 'modules/Webforms/config.captcha.php';

class Settings_Webforms_ShowForm_View extends Settings_Vtiger_IndexAjax_View
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        $recordId = $request->get('record');
        $moduleModel = Vtiger_Module_Model::getInstance($request->getModule());

        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$recordId || !$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        global $captchaConfig;
        $recordId = $request->get('record');
        $qualifiedModuleName = $request->getModule(false);
        $moduleName = $request->getModule();

        $recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
        $selectedFieldsList = $recordModel->getSelectedFieldsList('showForm');

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('SELECTED_FIELD_MODELS_LIST', $selectedFieldsList);
        $siteUrl = vglobal('site_URL');
        if ($siteUrl[strlen($siteUrl) - 1] != '/') {
            $siteUrl .= '/';
        }
        $viewer->assign('ACTION_PATH', $siteUrl . 'modules/Webforms/capture.php');
        $viewer->assign('CAPTCHA_PATH', $siteUrl . 'modules/Settings/Webforms/actions/CheckCaptcha.php');
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('DOCUMENT_FILE_FIELDS', $recordModel->getFileFields());
        $viewer->assign('ALLOWED_ALL_FILES_SIZE', $recordModel->getModule()->allowedAllFilesSize());
        $viewer->assign('CAPTCHA_CONFIG', $captchaConfig);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        echo $viewer->view('ShowForm.tpl', $qualifiedModuleName);
    }
}
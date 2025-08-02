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

class Settings_Roles_Popup_View extends Vtiger_Footer_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        if (!$currentUser->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

        $sourceRecord = $request->get('src_record');

        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();

        $sourceRole = Settings_Roles_Record_Model::getInstanceById($sourceRecord);
        $rootRole = Settings_Roles_Record_Model::getBaseRole();
        $allRoles = Settings_Roles_Record_Model::getAll();

        $viewer->assign('SOURCE_ROLE', $sourceRole);
        $viewer->assign('ROOT_ROLE', $rootRole);
        $viewer->assign('ROLES', $allRoles);

        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('COMPANY_LOGO', $companyLogo);

        $viewer->view('Popup.tpl', $qualifiedModuleName);
    }

    /**
     * Function to get the list of Script models to be included
     *
     * @param Vtiger_Request $request
     *
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Settings.Vtiger.resources.Popup',
            "modules.Settings.$moduleName.resources.Popup",
            "modules.Settings.$moduleName.resources.$moduleName",
            'libraries.jquery.jquery_windowmsg',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
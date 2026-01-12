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

class Settings_Webforms_List_View extends Settings_Vtiger_List_View
{
    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('DESCRIPTION', 'LBL_ALLOWS_YOU_TO_MANAGE_WEBFORMS');
        parent::preProcess($request, false);
    }

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

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Vtiger.resources.List',
            'modules.Settings.Vtiger.resources.List',
            "modules.Settings.$moduleName.resources.List",
            "modules.Settings.$moduleName.resources.Edit",
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/floatThead/jquery.floatThead.js",
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $headerCssInstances = parent::getHeaderCss($request);

        $cssFileNames = [
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css",
        ];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }
}
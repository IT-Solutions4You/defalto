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

class Settings_Roles_Index_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $rootRole = Settings_Roles_Record_Model::getBaseRole();
        $allRoles = Settings_Roles_Record_Model::getAll();

        $viewer->assign('ROOT_ROLE', $rootRole);
        $viewer->assign('ROLES', $allRoles);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->view('Index.tpl', $qualifiedModuleName);
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
            'modules.Settings.Vtiger.resources.Index',
            "modules.Settings.$moduleName.resources.Index",
            'modules.Settings.Vtiger.resources.Popup',
            "modules.Settings.$moduleName.resources.Popup",
            'libraries.jquery.jquery_windowmsg',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    /**
     * Function to get the list of Css models to be included
     *
     * @param Vtiger_Request $request
     *
     * @return <Array> - List of Vtiger_CssScript_Model instances
     */
    function getHeaderCss(Vtiger_Request $request)
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();

        $cssFileNames = [
            'libraries.jquery.jqTree.jqtree'
        ];

        $cssStyleInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssStyleInstances);

        return $headerCssInstances;
    }
}
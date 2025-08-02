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

class Vtiger_Extension_View extends Vtiger_List_View
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);

        return $permissions;
    }

    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $moduleName = $request->get('extensionModule');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        if (empty($moduleModel)) {
            throw new Exception(vtranslate('LBL_HANDLER_NOT_FOUND'));
        }

        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
        if (!$permission) {
            throw new Exception(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        return true;
    }

    /**
     * Function to get extension view instance from view name and module
     *
     * @param <Vtiger_Request> $request
     *
     * @return $extensionViewClass
     */
    function getExtensionViewInstance(Vtiger_Request $request)
    {
        $extensionModule = $request->get('extensionModule');
        $extensionView = $request->get('extensionView');
        $extensionViewClass = Vtiger_Loader::getComponentClassName('view', $extensionView, $extensionModule);
        $extensionViewInstance = new $extensionViewClass();

        return $extensionViewInstance;
    }

    /**
     * Function to get the extension links for a module
     *
     * @param <string> $module
     *
     * @return <array> $links
     */
    static function getExtensionLinks($module)
    {
        $linkParams = ['MODULE' => $module, 'ACTION' => 'LIST'];
        $links = Vtiger_Link_Model::getAllByType(getTabid($module), ['EXTENSIONLINK'], $linkParams);

        return $links['EXTENSIONLINK'];
    }

    function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $viewer->assign('EXTENSION_MODULE', $request->get('extensionModule'));
        $viewer->assign('EXTENSION_VIEW', $request->get('extensionView'));
        $viewer->assign('VIEWID', '');

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    function process(Vtiger_Request $request)
    {
        $extensionViewInstance = $this->getExtensionViewInstance($request);
        $extensionViewInstance->process($request);
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
        $extensionViewInstance = $this->getExtensionViewInstance($request);

        $jsFileNames = [
            'modules.Vtiger.resources.Extension',
            'modules.Vtiger.resources.ExtensionCommon',
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/floatThead/jquery.floatThead.js",
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        $jsScriptInstances = $extensionViewInstance->getHeaderScripts($request);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        return true;
    }
}
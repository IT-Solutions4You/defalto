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

class Settings_Profiles_EditAjax_View extends Settings_Profiles_Edit_View
{
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        return true;
    }

    public function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        echo $this->getContents($request);
    }

    public function getContents(Vtiger_Request $request)
    {
        $this->initialize($request);

        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('SHOW_EXISTING_PROFILES', true);

        return $viewer->view('EditViewContents.tpl', $qualifiedModuleName, true);
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
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.Settings.Profiles.resources.Profiles",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return $jsScriptInstances;
    }
}
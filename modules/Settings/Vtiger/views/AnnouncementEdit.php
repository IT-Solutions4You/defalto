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

class Settings_Vtiger_AnnouncementEdit_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $annoucementModel = Settings_Vtiger_Announcement_Model::getInstanceByCreator($currentUser);

        $qualifiedModuleName = $request->getModule(false);

        $viewer = $this->getViewer($request);

        $viewer->assign('ANNOUNCEMENT', $annoucementModel);
        $viewer->view('Announcement.tpl', $qualifiedModuleName);
    }

    function getPageTitle(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);

        return vtranslate('LBL_ANNOUNCEMENT', $qualifiedModuleName);
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
            "modules.Settings.$moduleName.resources.Announcement",
            "modules.Settings.$moduleName.resources.AnnouncementEdit"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
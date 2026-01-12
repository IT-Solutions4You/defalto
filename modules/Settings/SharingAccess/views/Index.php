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

class Settings_SharingAccess_Index_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

        $viewer->assign('ALL_MODULES', Settings_SharingAccess_Module_Model::getAll(true));
        $viewer->assign('ALL_ACTIONS', Settings_SharingAccess_Action_Model::getAll());
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('DEPENDENT_MODULES', Settings_SharingAccess_Module_Model::getDependentModules());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Index.tpl', $qualifiedModuleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Settings.Vtiger.resources.Index',
            "modules.Settings.$moduleName.resources.Index"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
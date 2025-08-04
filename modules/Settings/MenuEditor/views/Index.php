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

class Settings_MenuEditor_Index_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $allModelsList = Vtiger_Menu_Model::getAll(true);
        $menuModelStructure = Vtiger_MenuStructure_Model::getInstanceFromMenuList($allModelsList);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('ALL_MODULES', $menuModelStructure->getMore());
        $viewer->assign('SELECTED_MODULES', $menuModelStructure->getTop());
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);

        $mappedModuleList = Settings_MenuEditor_Module_Model::getAllVisibleModules();
        $viewer->assign('APP_MAPPED_MODULES', $mappedModuleList);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        $viewer->view('Index.tpl', $qualifiedModuleName);
    }
}
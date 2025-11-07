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

class Settings_PickListDependency_List_View extends Settings_Vtiger_List_View
{
    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
        $forModule = $request->get('formodule');
        $viewer = $this->getViewer($request);
        $viewer->assign('PICKLIST_MODULES_LIST', $moduleModelList);
        $viewer->assign('FOR_MODULE', $forModule);
        parent::preProcess($request, $display);
    }

    public function process(Vtiger_Request $request)
    {
        if ($request->isAjax()) {
            $moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
            $forModule = $request->get('formodule');

            $viewer = $this->getViewer($request);
            $viewer->assign('PICKLIST_MODULES_LIST', $moduleModelList);
            $viewer->assign('FOR_MODULE', $forModule);

            $this->initializeListViewContents($request, $viewer);
            $viewer->view('ListViewHeader.tpl', $request->getModule(false));
        }
        parent::process($request);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            '~libraries/jquery/malihu-custom-scrollbar/js/jquery.mCustomScrollbar.concat.min.js',
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/floatThead/jquery.floatThead.js",
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js",
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
            '~/libraries/jquery/malihu-custom-scrollbar/css/jquery.mCustomScrollbar.css',
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css",
        ];
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }

    /*
     * Function to initialize the required data in smarty to display the List View Contents
     */
    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        parent::initializeListViewContents($request, $viewer);
        $viewer->assign('SHOW_LISTVIEW_CHECKBOX', false);
        $viewer->assign('LISTVIEW_ACTIONS_ENABLED', true);
    }
}
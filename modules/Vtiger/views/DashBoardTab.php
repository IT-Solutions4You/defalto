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

class Vtiger_DashboardTab_View extends Vtiger_Index_View
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showDashBoardAddTabForm');
        $this->exposeMethod('getTabContents');
        $this->exposeMethod('showDashBoardTabList');
    }

    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        if ($request->get('module') != 'Dashboard') {
            $request->set('custom_module', 'Dashboard');
            $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'DetailView'];
        } else {
            $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];
        }

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    function showDashBoardAddTabForm($request)
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign("MODULE", $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showDashBoardAddTabForm', $request->getModule(), $viewer, $request);

        echo $viewer->view('AddDashBoardTabForm.tpl', $moduleName, true);
    }

    function getTabContents($request)
    {
        $moduleName = $request->getModule();
        $tabId = $request->get("tabid");

        $dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
        $dashBoardModel->set("tabid", $tabId);

        $widgets = $dashBoardModel->getDashboards($moduleName);
        $selectableWidgets = $dashBoardModel->getSelectableDashboard();
        $dashBoardTabInfo = $dashBoardModel->getTabInfo($tabId);

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign("MODULE", $moduleName);
        $viewer->assign('WIDGETS', $widgets);
        $viewer->assign('SELECTABLE_WIDGETS', $selectableWidgets);
        $viewer->assign('TABID', $tabId);

        $viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'getTabContents', $request->getModule(), $viewer, $request);

        echo $viewer->view('dashboards/DashBoardTabContents.tpl', $moduleName, true);
    }

    public function showDashBoardTabList(Vtiger_Request $request)
    {
        $viewer = $this->getViwer($request);
        $moduleName = $this->getModule();

        $dashBoardModel = new Vtiger_DashBoard_Model();
        $dashBoardTabs = $dashBoardModel->getActiveTabs();

        $viewer->assign('DASHBOARD_TABS', $dashBoardTabs);
        $viewer->assign('MODULE', $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showDashBoardTabList', $request->getModule(), $viewer, $request);

        $viewer->view('DashBoardTabList.tpl', $moduleName);
    }
}
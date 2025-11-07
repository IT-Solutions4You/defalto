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

class Vtiger_Dashboard_View extends Vtiger_Index_View
{
    protected static $selectable_dashboards;

    public function requiresPermission(\Vtiger_Request $request)
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

    function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
        //check profile permissions for Dashboards
        $moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
        if ($permission) {
            // TODO : Need to optimize the widget which are retrieving twice
            $dashboardTabs = $dashBoardModel->getActiveTabs();
            if ($request->get("tabid")) {
                $tabid = $request->get("tabid");
            } else {
                // If no tab, then select first tab of the user
                $tabid = $dashboardTabs[0]["id"];
            }
            $dashBoardModel->set("tabid", $tabid);
            $widgets = $dashBoardModel->getSelectableDashboard();
            self::$selectable_dashboards = $widgets;
        } else {
            $widgets = [];
        }
        $viewer->assign('MODULE_PERMISSION', $permission);
        $viewer->assign('WIDGETS', $widgets);
        $viewer->assign('MODULE_NAME', $moduleName);
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function preProcessTplName(Vtiger_Request $request)
    {
        return 'dashboards/DashBoardPreProcess.tpl';
    }

    function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);

        //check profile permissions for Dashboards
        $moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
        if ($permission) {
            // TODO : Need to optimize the widget which are retrieving twice
            $dashboardTabs = $dashBoardModel->getActiveTabs();
            if ($request->get("tabid")) {
                $tabid = $request->get("tabid");
            } else {
                // If no tab, then select first tab of the user
                $tabid = $dashboardTabs[0]["id"];
            }
            $dashBoardModel->set("tabid", $tabid);
            $widgets = $dashBoardModel->getDashboards($moduleName);
        } else {
            return;
        }

        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('WIDGETS', $widgets);
        $viewer->assign('DASHBOARD_TABS', $dashboardTabs);
        $viewer->assign('DASHBOARD_TABS_LIMIT', $dashBoardModel->dashboardTabLimit);
        $viewer->assign('SELECTED_TAB', $tabid);
        if (self::$selectable_dashboards) {
            $viewer->assign('SELECTABLE_WIDGETS', self::$selectable_dashboards);
        }
        $viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('TABID', $tabid);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('dashboards/DashBoardContents.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            '~/vendor/defalto/libraries/chartjs/dist/chart.umd.min.js',
            '~/vendor/defalto/libraries/chartjs-chart-funnel/build/index.umd.min.js',
            '~/vendor/defalto/libraries/gridster/jquery.gridster.min.js',
            'modules.Vtiger.resources.DashBoard',
            'modules.' . $moduleName . '.resources.DashBoard',
            'modules.Vtiger.resources.dashboards.Widget',
            'modules.Vtiger.resources.Detail',
            'modules.Vtiger.resources.CkEditor',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $parentHeaderCssScriptInstances = parent::getHeaderCss($request);

        $headerCss = [
            '~/vendor/defalto/libraries/gridster/jquery.gridster.min.css',
        ];
        $cssScripts = $this->checkAndConvertCssStyles($headerCss);
        $headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances, $cssScripts);

        return $headerCssScriptInstances;
    }
}
<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Dashboard_View extends Vtiger_Index_View {

	protected static $selectable_dashboards;

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		if($request->get('module') != 'Dashboard'){
			$request->set('custom_module', 'Dashboard');
			$permissions[] = array('module_parameter' => 'custom_module', 'action' => 'DetailView');
		}else{
			$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		}
		return $permissions;
	}
	
	function preProcess(Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
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
			$widgets = array();
		}
		$viewer->assign('MODULE_PERMISSION', $permission);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULE_NAME', $moduleName);
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	function preProcessTplName(Vtiger_Request $request) {
		return 'dashboards/DashBoardPreProcess.tpl';
	}

	function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);

		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			// TODO : Need to optimize the widget which are retrieving twice
		   $dashboardTabs = $dashBoardModel->getActiveTabs();
		   if($request->get("tabid")){
			   $tabid = $request->get("tabid");
		   } else {
			   // If no tab, then select first tab of the user
			   $tabid = $dashboardTabs[0]["id"];
		   }
		   $dashBoardModel->set("tabid",$tabid);
			$widgets = $dashBoardModel->getDashboards($moduleName);
		} else {
			return;
		}

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('DASHBOARD_TABS', $dashboardTabs);
		$viewer->assign('DASHBOARD_TABS_LIMIT', $dashBoardModel->dashboardTabLimit);
		$viewer->assign('SELECTED_TAB',$tabid);
        if (self::$selectable_dashboards) {
			$viewer->assign('SELECTABLE_WIDGETS', self::$selectable_dashboards);
		}
		$viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('TABID',$tabid);
		$viewer->view('dashboards/DashBoardContents.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request) {
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'~/libraries/jquery/gridster/jquery.gridster.min.js',
			'~/libraries/jquery/jqplot/jquery.jqplot.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.pieRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.barRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.pointLabels.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.funnelRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.barRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.logAxisRenderer.min.js',
			'~/libraries/jquery/VtJqplotInterface.js',
			'~/libraries/jquery/vtchart.js',
			'~layouts/'.Vtiger_Viewer::getDefaultLayoutName().'/lib/jquery/gridster/jquery.gridster.min.js',
			'~/libraries/jquery/vtchart.js',
			'modules.Vtiger.resources.DashBoard',
			'modules.'.$moduleName.'.resources.DashBoard',
			'modules.Vtiger.resources.dashboards.Widget',
			'~/layouts/'.Vtiger_Viewer::getDefaultLayoutName().'/modules/Vtiger/resources/Detail.js',
			"modules.Vtiger.resources.CkEditor",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_CssScript_Model instances
	 */
	public function getHeaderCss(Vtiger_Request $request) {
		$parentHeaderCssScriptInstances = parent::getHeaderCss($request);

		$headerCss = array(
			'~layouts/'.Vtiger_Viewer::getDefaultLayoutName().'/lib/jquery/gridster/jquery.gridster.min.css',
			'~libraries/jquery/jqplot/jquery.jqplot.min.css'
		);
		$cssScripts = $this->checkAndConvertCssStyles($headerCss);
		$headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances , $cssScripts);
		return $headerCssScriptInstances;
	}
}
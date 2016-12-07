<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_PivotDetail_View extends Vtiger_Index_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$record = $request->get('record');
		$reportModel = Reports_Pivot_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$owner = $reportModel->get('owner');
		$sharingType = $reportModel->get('sharingtype');

		$isRecordShared = true;
		if(($currentUserPriviligesModel->id != $owner) && $sharingType == "Private"){
			$isRecordShared = $reportModel->isRecordHasViewAccess($sharingType);
		}

		if (!$isRecordShared || !$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	function preProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$detailViewModel = Reports_DetailView_Model::getInstance($moduleName, $recordId);
		$reportModel = $detailViewModel->getRecord();
		$viewer->assign('REPORT_NAME', $reportModel->getName());
		parent::preProcess($request);

		$page = $request->get('page');
		$reportModel->setModule('Reports');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		//$pagingModel->set('limit', self::REPORT_LIMIT);
		$pivotReportModel = Reports_Pivot_Model::getInstanceById($recordId);

		$this->reportData = $pivotReportModel->getPivotReportData();

		$primaryModule = $reportModel->getPrimaryModule();
		$secondaryModules = $reportModel->getSecondaryModules();
		$primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
		$permission = $userPrivilegesModel->hasModulePermission($primaryModuleModel->getId());

		if (!$permission) {
			$viewer->assign('MODULE', $primaryModule);
			$viewer->assign('MESSAGE', vtranslate('LBL_PERMISSION_DENIED'));
			$viewer->view('OperationNotPermitted.tpl', $primaryModule);
			exit;
		}

		$detailViewLinks = $detailViewModel->getDetailViewLinks();

		// Advanced filter conditions
		$viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
		$viewer->assign('PRIMARY_MODULE', $primaryModule);
		$viewer->assign('SECONDARY_MODULES', $reportModel->getSecondaryModules());

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($reportModel);
		$primaryModuleRecordStructure = $recordStructureInstance->getPrimaryModuleRecordStructure();
		$secondaryModuleRecordStructures = $recordStructureInstance->getSecondaryModuleRecordStructure();

		$viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
		$viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);

		$secondaryModuleIsCalendar = strpos($secondaryModules, 'Calendar');
		if (($primaryModule == 'Calendar') || ($secondaryModuleIsCalendar !== FALSE)) {
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else {
			$advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
		$dateFilters = Vtiger_Field_Model::getDateFilterTypes();
		foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
			$comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
			$comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
			$comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $module);
			$dateFilters[$comparatorKey] = $comparatorInfo;
		}
		$viewer->assign('DATE_FILTERS', $dateFilters);
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('DETAILVIEW_ACTIONS', $detailViewModel->getDetailViewActions());
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('MODULE', $moduleName);

		//Pivot Report Details
		$primaryModuleFields = $reportModel->getPrimaryModuleFields();
		$secondaryModuleFields = $reportModel->getSecondaryModuleFields();
		$viewer->assign('PRIMARY_MODULE_FIELDS', $primaryModuleFields);
		$viewer->assign('SECONDARY_MODULE_FIELDS', $secondaryModuleFields);

		$viewer->assign('CALCULATION_FIELDS', $reportModel->getModuleCalculationFieldsForReport());

		$dataFields = Zend_Json::decode(decode_html($reportModel->getReportTypeInfo()));
		$viewer->assign('SELECTED_ROW_FIELDS', $dataFields['rows']);
		$viewer->assign('SELECTED_COLUMN_FIELDS', $dataFields['columns']);
		$viewer->assign('SELECTED_DATA_FIELDS', $dataFields['functions']);

		$viewer->view('PivotReportHeader.tpl', $moduleName);
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$this->getReport($request);
	}

	function getReport(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$page = $request->get('page');

		$data = $this->reportData;
		if (empty($data)) {
			$pivotReportModel = Reports_Pivot_Model::getInstanceById($recordId);
			$pivotReportModel->setModule('Reports');
			$data = $pivotReportModel->getPivotReportData();
		}

		$viewer->assign('DATA', json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);

		$viewer->view('PivotTable.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$libraryFileNames = array(
			'~libraries/jquery/pivot/js/jbPivot.min.js',
			'~libraries/jquery/pivot/js/group_distinct.js',
			'~libraries/jquery/pivot/js/agregate_average.js',
			'~libraries/jquery/pivot/js/agregate_count.js',
			'~libraries/jquery/pivot/js/agregate_distinct.js',
			'~libraries/jquery/pivot/js/agregate_sum.js',
			'~libraries/jquery/pivot/js/agregate_min.js',
			'~libraries/jquery/pivot/js/agregate_max.js',
			'~libraries/jquery/pivot/js/formatter_default.js');

		$LibraryJsScriptInstances = $this->checkAndConvertJsScripts($libraryFileNames);

		$jsFileNames = array(
			'modules.Vtiger.resources.Detail',
			"modules.$moduleName.resources.Detail",
			"modules.$moduleName.resources.Edit",
			"modules.$moduleName.resources.PivotEdit3",
			"modules.$moduleName.resources.PivotDetail",
			"modules.$moduleName.resources.VtPivotDataFormatter",

		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($LibraryJsScriptInstances,$headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_CssScript_Model instances
	 */
	function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~/libraries/jquery/pivot/css/jbPivot.min.css',
			//'~/libraries/jquery/pivot/css/jbpivot.min.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}

}

<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_PivotEdit_View extends Reports_Edit_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('pivotStep1');
		$this->exposeMethod('PivotStep2');
		$this->exposeMethod('pivotStep3');
	}

	function checkPermission(Vtiger_Request $request) {
		parent::checkPermission($request);
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		$record = $request->get('record');
		if ($record) {
			$reportModel = Reports_Record_Model::getCleanInstance($record);
			if (!$reportModel->isEditable()) {
				throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
			}
		}
	}

	public function preProcess(Vtiger_Request $request) {
		parent::preProcess($request);
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			exit;
		}
		$this->pivotStep1($request);
	}

	function pivotStep1(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$reportModel = Reports_Pivot_Model::getCleanInstance($record);
		if(!$reportModel->has('folderid')){
			$reportModel->set('folderid',$request->get('folder'));
		}
		$data = $request->getAll();
		foreach ($data as $name => $value) {
			$reportModel->set($name, $value);
		}

		$modulesList = $reportModel->getModulesList();
		if (!empty($record)) {
			$viewer->assign('MODE', 'edit');
		} else {
			$firstModuleName = reset($modulesList);
			if($firstModuleName)
				$reportModel->setPrimaryModule($firstModuleName);
			$viewer->assign('MODE', '');
		}

		$reportModuleModel = $reportModel->getModule();
		$reportFolderModels = $reportModuleModel->getFolders();

		$relatedModules = $reportModel->getReportRelatedModules();

		foreach ($relatedModules as $primaryModule => $relatedModuleList) {
			$translatedRelatedModules = array();

			foreach($relatedModuleList as $relatedModuleName) {
				$translatedRelatedModules[$relatedModuleName] = vtranslate($relatedModuleName, $relatedModuleName);
			}
			$relatedModules[$primaryModule] = $translatedRelatedModules;
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$viewer->assign('SCHEDULEDREPORTS', $reportModel->getScheduledReport());
		$viewer->assign('MODULELIST', $modulesList);
		$viewer->assign('RELATED_MODULES', $relatedModules);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('REPORT_FOLDERS', $reportFolderModels);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_USER', $currentUserModel);
		$viewer->assign('ROLES', Settings_Roles_Record_Model::getAll());
		$admin = Users::getActiveAdminUser();
		$viewer->assign('ACTIVE_ADMIN', $admin);
		$viewer->assign('TYPE', 'Pivot');

		//Sharing access to users and groups
		$sharedMembers = $reportModel->getMembers();
		$viewer->assign('SELECTED_MEMBERS_GROUP', $sharedMembers);
		$viewer->assign('MEMBER_GROUPS',  Settings_Groups_Member_Model::getAll());

		if ($request->get('isDuplicate')) {
			$viewer->assign('IS_DUPLICATE', true);
		}
		$viewer->view('PivotStep1.tpl', $request->getModule());
	}

	function PivotStep2(Vtiger_request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$reportModel = Reports_Record_Model::getCleanInstance($record);
		if (!empty($record)) {
			$viewer->assign('SELECTED_STANDARD_FILTER_FIELDS', $reportModel->getSelectedStandardFilter());
			$viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
		}
		$data = $request->getAll();
		foreach ($data as $name => $value) {
			if($name == 'schdayoftheweek' || $name == 'schdayofthemonth' || $name == 'schannualdates' || $name == 'recipients') {
				if(is_string($value)) {
					$value = array($value);
				}
			}
			$reportModel->set($name, $value);
		}
		$primaryModule = $request->get('primary_module');
		$secondaryModules = $request->get('secondary_modules');
		$reportModel->setPrimaryModule($primaryModule);
		if(!empty($secondaryModules)){
			$secondaryModules = implode(':', $secondaryModules);
			$reportModel->setSecondaryModule($secondaryModules);

			$secondaryModules = explode(':',$secondaryModules);
		}else{
			$secondaryModules = array();
		}

		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('PRIMARY_MODULE',$primaryModule);

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($reportModel);
		$primaryModuleRecordStructure = $recordStructureInstance->getPrimaryModuleRecordStructure();
		/** With out checking whether secondary is removed from related module field we should not send  
		*  secondary module info to next step 
		*/ 
		if($secondaryModules){ 
			$secondaryModuleRecordStructures = $recordStructureInstance->getSecondaryModuleRecordStructure();
		} 

		//TODO : We need to remove "update_log" field from "HelpDesk" module in New Look
		// after removing old look we need to remove this field from crm
		if($primaryModule == 'HelpDesk'){
			foreach($primaryModuleRecordStructure as $blockLabel => $blockFields){
				foreach($blockFields as $field => $object){
					if($field == 'update_log'){
						unset($primaryModuleRecordStructure[$blockLabel][$field]);
					}
				}
			}
		}

		if(!empty($secondaryModuleRecordStructures)){
			foreach($secondaryModuleRecordStructures as $module => $structure){
				if($module == 'HelpDesk'){
					foreach($structure as $blockLabel => $blockFields){
						foreach($blockFields as $field => $object){
							if($field == 'update_log'){
								unset($secondaryModuleRecordStructures[$module][$blockLabel][$field]);
							}
						}
					}
				}
			}
		}
		// End

		$viewer->assign('SECONDARY_MODULES',$secondaryModules);
		$viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
		$viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);
		$dateFilters = Vtiger_Field_Model::getDateFilterTypes();
		foreach($dateFilters as $comparatorKey => $comparatorInfo) {
			$comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
			$comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
			$comparatorInfo['label'] = vtranslate($comparatorInfo['label'],$moduleName);
			$dateFilters[$comparatorKey] = $comparatorInfo;
		}
		$viewer->assign('DATE_FILTERS', $dateFilters);

		if(($primaryModule == 'Calendar') || (in_array('Calendar', $secondaryModules))){
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else{
			$advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
		$viewer->assign('MODULE', $moduleName);

		$calculationFields = $reportModel->get('calculation_fields');
		if($calculationFields) {
			$calculationFields = Zend_Json::decode($calculationFields);
			$viewer->assign('LINEITEM_FIELD_IN_CALCULATION', $reportModel->showLineItemFieldsInFilter($calculationFields));
		}
		if ($request->get('isDuplicate')) {
			$viewer->assign('IS_DUPLICATE', true);
		}
		$viewer->view('PivotStep2.tpl', $request->getModule());
	}

	function PivotStep3(Vtiger_request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$data = $request->getAll();
		foreach ($data as $name => $value) {
			if($name == 'schdayoftheweek' || $name == 'schdayofthemonth' || $name == 'schannualdates' || $name == 'recipients' || $name == 'members') {
				$value = Zend_Json::decode($value);
				if(!is_array($value)) {
					$value = array($value);
				}
			}
			$reportModel->set($name, $value);
		}

		$primaryModule = $request->get('primary_module');
		$reportModel->setPrimaryModule($primaryModule);
		$secondaryModules = $request->get('secondary_modules');
		if(!empty($secondaryModules)){
			$secondaryModules = implode(':', $secondaryModules);
			$reportModel->setSecondaryModule($secondaryModules);
			$secondaryModules = explode(':',$secondaryModules);
		}else{
			$secondaryModules = array();
			$reportModel->setSecondaryModule('');
		}
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('PRIMARY_MODULE',$primaryModule);
		$viewer->assign('SECONDARY_MODULES',$secondaryModules);
		$viewer->assign('MODULE', $moduleName);

		$primaryModuleFields = $reportModel->getPrimaryModuleFields();
		/** With out checking whether secondary is removed from related module field we should not send  
		 *  secondary module info to next step 
		 */ 
		if($secondaryModules){ 
			$secondaryModuleFields = $reportModel->getSecondaryModuleFields();
		} 
		$viewer->assign('SECONDARY_MODULES',$secondaryModules);
		$viewer->assign('PRIMARY_MODULE_FIELDS', $primaryModuleFields);
		$viewer->assign('SECONDARY_MODULE_FIELDS', $secondaryModuleFields);

		$viewer->assign('CALCULATION_FIELDS', $reportModel->getModuleCalculationFieldsForReport());

		$dataFields = Zend_Json::decode(decode_html($reportModel->getReportTypeInfo()));
		$viewer->assign('SELECTED_ROW_FIELDS', $dataFields['rows']);
		$viewer->assign('SELECTED_COLUMN_FIELDS', $dataFields['columns']);
		$viewer->assign('SELECTED_DATA_FIELDS', $dataFields['functions']);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		if ($request->get('isDuplicate')) {
			$viewer->assign('IS_DUPLICATE', true);
		}

		$viewer->view('PivotStep3.tpl', $moduleName);
	}

	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.resources.PivotEdit",
			"modules.$moduleName.resources.PivotEdit1",
			"modules.$moduleName.resources.PivotEdit2",
			"modules.$moduleName.resources.PivotEdit3"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}

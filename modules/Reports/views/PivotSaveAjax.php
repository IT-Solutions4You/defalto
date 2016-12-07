<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_PivotSaveAjax_View extends Vtiger_IndexAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$record = $request->get('record');
		if (!$record) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);
		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$record = $request->get('record');
		$reportModel = Reports_Pivot_Model::getInstanceById($record);

		$reportModel->setModule('Reports');

		$reportModel->set('advancedFilter', $request->get('advanced_filter'));

		$data = array();
		$data['rows'] = $request->get('rows');
		$data['columns'] = $request->get('columns');
		$data['functions'] = $request->get('data_fields');
		$reportModel->set('reporttypedata', Zend_Json::encode($data));

		if ($mode === 'save') {
			$reportModel->saveAdvancedFilters();
			$reportModel->saveReportType();
		}
	}

}

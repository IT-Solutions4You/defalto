<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Services_QuickCreateAjax_View extends Products_QuickCreateAjax_View {

	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$moduleName = $request->getModule();
		$moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
		unset($headerScriptInstances[$moduleEditFile]);

		$jsFileNames = array(
			'modules.Products.resources.Edit',
		);

		$jsFileNames[] = $moduleEditFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}

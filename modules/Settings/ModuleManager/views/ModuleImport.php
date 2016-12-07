<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_ModuleManager_ModuleImport_View extends Settings_Vtiger_Index_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('step2');
		$this->exposeMethod('step3');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('EXTENSIONS', Settings_ModuleManager_Extension_Model::getAll());
		$viewer->view('Step1.tpl', $qualifiedModuleName);
	}

	public function step2(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$upgradeError = true;
		$qualifiedModuleName = $request->getModule(false);
		$extensionId = $request->get('extensionId');
		$moduleAction = $request->get('moduleAction');	//Import/Upgrade

		$extensionModel = Settings_ModuleManager_Extension_Model::getInstanceById($extensionId);
		if ($extensionModel) {
			$package = $extensionModel->getPackage();
			if ($package) {
				$importedModuleName = $package->getModuleName();
				$isLanguagePackage = $package->isLanguageType();

				if ($moduleAction === 'Upgrade') {
					$targetModuleName = $request->get('extensionName');
					if(($isLanguagePackage && (trim($package->xpath_value('prefix')) == $targetModuleName)) || (!$isLanguagePackage && $importedModuleName === $targetModuleName)) {
						$upgradeError = false;
					}
				} else {
					$upgradeError = false;
				}
				if (!$upgradeError) {
					if(!$isLanguagePackage) {
						$moduleModel = Vtiger_Module_Model::getInstance($importedModuleName);
						$viewer->assign('MODULE_EXISTS', ($moduleModel)? true :false);
						$viewer->assign('MODULE_DIR_NAME', '../modules/'. $importedModuleName);

						if (!$extensionModel->isUpgradable()) {
							$viewer->assign('SAME_VERSION', true);
						}
					}

					$viewer->assign('EXTENSION_ID', $extensionId);
					$viewer->assign('MODULE_NAME', $importedModuleName);
					$viewer->assign('MODULE_ACTION', $moduleAction);

					$viewer->assign('MODULE_TYPE', $package->type());
					$viewer->assign('FILE_NAME', $extensionModel->getFileName());
					$viewer->assign('MODULE_LICENSE', (string)$package->getLicense());
					$viewer->assign('SUPPORTED_VTVERSION', $package->getDependentVtigerVersion());
					
				} else {
					$viewer->assign('ERROR', true);
					$viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_FILE', $qualifiedModuleName));
				}
			} else {
				$viewer->assign('ERROR', true);
				$viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_FILE', $qualifiedModuleName));
			}
		} else {
			$viewer->assign('ERROR', true);
			$viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_FILE', $qualifiedModuleName));
		}

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step2.tpl', $qualifiedModuleName);
	}

	public function step3(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		global $Vtiger_Utils_Log;
		$viewer->assign('VTIGER_UTILS_LOG', $Vtiger_Utils_Log);
		$Vtiger_Utils_Log = true;
		$qualifiedModuleName = $request->getModule(false);

		$fileName = $request->get('fileName');
		$moduleType = $request->get('moduleType');
		$extensionId = $request->get('extensionId');
		$targetModuleName = $request->get('targetModule');
		$moduleAction = $request->get('moduleAction');

		if ($extensionId) {
			if ($moduleAction !== 'Upgrade') {
				$extensionModel = Settings_ModuleManager_Extension_Model::getInstanceById($extensionId, $fileName);
				$extensionModel->installTrackDetails();
			}

			if (strtolower($moduleType) === 'language') {
				$package = new Vtiger_Language();
			} else {
				$package = new Vtiger_Package();
			}

			$viewer->assign('MODULE_ACTION', $moduleAction);
			$viewer->assign('MODULE_PACKAGE', $package);
			$viewer->assign('TARGET_MODULE_INSTANCE', Vtiger_Module_Model::getInstance($targetModuleName));
			$viewer->assign('MODULE_FILE_NAME', Settings_ModuleManager_Extension_Model::getUploadDirectory(). '/' .$fileName);
		} else {
			$viewer->assign('ERROR', true);
			$viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_MODULE_INFO', $qualifiedModuleName));
		}
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step3.tpl', $qualifiedModuleName);
	}
    
    /**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.ModuleImport"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
    
    public function validateRequest(Vtiger_Request $request) {
        $request->validateReadAccess();
    }
}
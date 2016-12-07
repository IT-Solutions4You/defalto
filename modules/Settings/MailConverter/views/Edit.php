<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MailConverter_Edit_View extends Settings_Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
    }
    
    public function preProcess(Vtiger_Request $request) {
		parent::preProcess($request);
		$recordId = $request->get('record');
		$mode = $request->get('mode');
		if (!$mode)
		    $mode = "step1";
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();
        
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
		if ($recordId) {
            $recordModel =  call_user_func_array(array($modelClassName,'getInstanceById'),array($recordId));
		} else {
            $recordModel = call_user_func_array(array($modelClassName,'getCleanInstance'),array());
		}
		$viewer = $this->getViewer($request);
	
		if ($recordId)
		    $viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('CREATE', $request->get('create'));
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE_MODEL', $recordModel->getModule());
		$viewer->assign('STEP', $mode);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
	
		$viewer->view('EditHeader.tpl', $qualifiedModuleName);
    }

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if (!empty($mode)) {
		    $this->invokeExposedMethod($mode, $request);
		    return;
		}
    }
    
    public function step1(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
	
		$viewer->view('Step1.tpl', $qualifiedModuleName);
	}
	
	public function step2(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
        $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
        $folders = $moduleModel->getFolders($recordId);
		$viewer = $this->getViewer($request);
        if(is_array($folders))
            $viewer->assign('FOLDERS', $folders);
        else if($folders)
            $viewer->assign('IMAP_ERROR', $folders);
        else
            $viewer->assign('CONNECTION_ERROR', true);
	
		$viewer->view('Step2.tpl', $qualifiedModuleName);
    }

    public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
        $module = $request->getModule();
	
		$jsFileNames = array(
		    "modules.Settings.$module.resources.Edit"
		);
	
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
    }

}
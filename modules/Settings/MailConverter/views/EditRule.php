<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MailConverter_EditRule_View extends Settings_Vtiger_Index_View {
    
    function __construct() {
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
    }

	public function checkPermission(Vtiger_Request $request) {
		parent::checkPermission($request);
		$scannerId = $request->get('scannerId');

		if(!$scannerId) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
		}
	}
    
    public function preProcess(Vtiger_Request $request) {
		parent::preProcess($request);
		$recordId = $request->get('record');
        $scannerId = $request->get('scannerId');
		$qualifiedModuleName = $request->getModule(false);
        $moduleName = $request->getModule();
		$mode = $request->get('mode');
        $ruleRecordModel = Vtiger_Loader::getComponentClassName('Model', 'RuleRecord', $qualifiedModuleName);
        $ruleRecordModel = new $ruleRecordModel();
        $viewer = $this->getViewer($request);
		if (empty($mode)) {
		    $mode = "step1";
        }
		if ($recordId) {
           $ruleRecordModel = call_user_func_array(array($ruleRecordModel,'getInstanceById'),array($recordId));
		} else {
            $ruleRecordModel = call_user_func_array(array($ruleRecordModel,'getCleanInstance'),array($scannerId));
		}
        $moduleModel =  Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
        $recordModel = Settings_Vtiger_Record_Model::getInstance($qualifiedModuleName);
        $ruleExists = $ruleRecordModel->get('ruleid');
        
		$viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('RULE_EXISTS', $ruleExists);
		$viewer->assign('RECORD_MODEL', $ruleRecordModel);
		$viewer->assign('MODULE_MODEL',new $moduleModel());
		$viewer->assign('SCANNER_ID', $scannerId);
		$viewer->assign('SCANNER_MODEL', call_user_func_array(array($recordModel,'getInstanceById'),array($scannerId)));
		$viewer->assign('DEFAULT_OPTIONS', call_user_func_array(array($ruleRecordModel,'getDefaultConditions'),array()));
		$viewer->assign('DEFAULT_ACTIONS',call_user_func_array(array($ruleRecordModel,'getDefaultActions'),array()));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('STEP', $mode);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->view('EditRuleHeader.tpl', $qualifiedModuleName);
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
		$viewer->view('RuleStep1.tpl', $qualifiedModuleName);
	}
    
    public function step2(Vtiger_Request $request) {
        $recordId = $request->get('record');
		$scannerId = $request->get('scannerId');
        $qualifiedModuleName = $request->getModule(false);
        
        $ruleRecordModelClassName = Vtiger_Loader::getComponentClassName('Model', 'RuleRecord', $qualifiedModuleName);
        $ruleRecordModel  = new $ruleRecordModelClassName();
        $assignedTo = $ruleRecordModel->getAssignedTo($scannerId, $recordId);
        
        
        $bodyRule = Settings_MailConverter_BodyRule_Model::getCleanInstance($qualifiedModuleName);
        $bodyRule->set('scannerid', $scannerId);
        $bodyRule->set('ruleid', $recordId);
        $bodyRuleExists = $bodyRule->bodyRuleExists();
        if(!empty($recordId) && $bodyRuleExists) {
            $delimiter = $bodyRule->getDelimiter();
            $body = $bodyRule->getBody();
            $mappingData = $bodyRule->getMapping();
                           
            $recordModel = call_user_func_array(array($ruleRecordModel,'getInstanceById'),array($recordId));
            $action = $recordModel->get('action');
            
            $moduleFields = $bodyRule->getModuleFields($action);
            $bodyFields = $bodyRule->parseBody($body, $delimiter);
            $mappedBodyFields = array_keys($mappingData);
            $bodyFields = array_unique(array_merge($bodyFields, $mappedBodyFields));
        }
        
        $viewer = $this->getViewer($request);
		$viewer->assign('ASSIGNED_USER', $assignedTo[0]);
        $viewer->assign('DATA', $request->getAll());
        if(!empty($recordId) && $bodyRuleExists) {
            $viewer->assign('BODY_RULE_EXISTS', $bodyRuleExists);
            $viewer->assign('DELIMITER', $delimiter);
            $viewer->assign('BODY', $body);
            $viewer->assign('MODULE_FIELDS', $moduleFields);
            $viewer->assign('BODY_FIELDS', $bodyFields);
            $viewer->assign('MAPPING', $mappingData);
        }
        $viewer->view('RuleStep2.tpl', $qualifiedModuleName);
        
    }
}
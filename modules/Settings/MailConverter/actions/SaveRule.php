<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_SaveRule_Action extends Settings_Vtiger_Index_Action {

	public function checkPermission(Vtiger_Request $request) {
		parent::checkPermission($request);
		$recordId = $request->get('record');
		$scannerId = $request->get('scannerId');

		if (!$scannerId) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
		}
	}

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$scannerId = $request->get('scannerId');
		$action = $request->get('action1');
		$request->set('action', $action);
		$qualifiedModuleName = $request->getModule(false);
        $modelClassName = Vtiger_Loader::getComponentClassName('Model','RuleRecord', $qualifiedModuleName);
		if ($recordId) {
            $recordModel = call_user_func_array(array($modelClassName,'getInstanceById'), array($recordId));
		} else {
            $recordModel = call_user_func_array(array($modelClassName,'getCleanInstance'), array($scannerId));
		}
		$fieldsList = $recordModel->getFields();
		foreach ($fieldsList as $fieldName) {
			$recordModel->set($fieldName, $request->get($fieldName));
		}
		$recordModel->set('newAction', $request->get('action'));
        
        $ruleId = $recordModel->save();
        
        $status = $this->saveBodyRule($ruleId, $request);
        $response = new Vtiger_Response();
        if($status) {
            $response->setResult(array('message' => vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName), 'id' => $ruleId, 'scannerId' => $scannerId));
        } else {
            $response->setError(vtranslate('LBL_MULTIPLE_FIELDS_MAPPED', $qualifiedModuleName));
        }
		$response->emit();
	}

    public function saveBodyRule($ruleId, $request) {
        $mailBody = $request->get('mailBody');
        $scannerId = $request->get('scannerId');
        $qualifiedModule = $request->getModule(false);
        $bodyRuleModel = Settings_MailConverter_BodyRule_Model::getCleanInstance($qualifiedModule);
        
        if(empty($mailBody)) {
            $bodyRuleModel->deleteBodyRule($scannerId, $ruleId);
            return true;
        }
        $delimiter = $request->get('delimeter');
        $mappingData = $request->get('mappingData');
        $action = $request->get('action');
        
        foreach($mappingData as $key => $value) {
            if($value != ' ') {
                $mapFields[$key] = $value;
            }
        }
        
        if(count($mapFields) == count(array_unique($mapFields))) {
            $bodyRuleModel->set('scannerId', $scannerId);
            $bodyRuleModel->set('ruleId', $ruleId);
            $bodyRuleModel->set('delimiter', $delimiter);
            $bodyRuleModel->set('filedsMapping', $mapFields);
            $bodyRuleModel->set('action', $action);
            $bodyRuleModel->set('body', $mailBody);
            $bodyRuleModel->saveBodyRule();
            return true;
        } else {
            return false;
        }
    }
    
    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }
}
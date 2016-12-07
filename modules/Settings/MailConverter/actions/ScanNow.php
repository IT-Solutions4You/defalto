<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_ScanNow_Action extends Settings_Vtiger_Index_Action {

	public function checkPermission(Vtiger_Request $request) {
		parent::checkPermission($request);
		$recordId = $request->get('record');

		if (!$recordId) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
		}
	}

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_MailConverter_Record_Model::getInstanceById($recordId);
		$status = $recordModel->scanNow();

		$response = new Vtiger_Response();
		if (is_bool($status) && $status) {
			$result = array('message'=> vtranslate('LBL_SCANNED_SUCCESSFULLY', $qualifiedModuleName));
            $recordModel = Settings_MailConverter_Record_Model::getInstanceById($recordId);
            $result['id'] = $recordModel->getId();
            $lastScanTime = $recordModel->getLastScanTime();
            $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
            $foldersScanned = $moduleModel->getScannedFolders($recordId);
            $scanTimeMsg = vtranslate('LBL_LAST_SCAN_AT', $qualifiedModuleName).$lastScanTime.'<br>'.
                    vtranslate('LBL_FOLDERS_SCANNED', $qualifiedModuleName).' : <strong>'.implode(', ', $foldersScanned).'</strong>';
            $result['lastScanMessage'] = $scanTimeMsg;
			$response->setResult($result);
		} else if($status) {
            $response->setError($status);
        } else {
            $errorMsg = $recordModel->get('errorMsg');
            $response->setError(vtranslate($errorMsg, $qualifiedModuleName));
		}
		$response->emit();
	}
}
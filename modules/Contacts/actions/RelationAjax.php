<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Contacts_RelationAjax_Action extends Vtiger_RelationAjax_Action {

	function getParentRecordInfo($request) {
		$moduleName = $request->get('module');
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('id'), $moduleName);
		$moduleModel = $recordModel->getModule();
		$autoFillData = $moduleModel->getAutoFillModuleAndField($moduleName);
		if ($autoFillData) {
			foreach ($autoFillData as $data) {
				$autoFillModule = $data['module'];
				$autoFillFieldName = $data['fieldname'];
				$autofillRecordId = $recordModel->get($autoFillFieldName);

				//added to handle extra relation for cases sla
				if ($autoFillFieldName == 'slaid') {
					//if contact doesn't have related sla then fetch contact related sla
					if (!$autofillRecordId) {
						$account_id = $recordModel->get('account_id');
						if ($account_id) {
							$accountsRecordModel = Vtiger_Record_Model::getInstanceById($account_id, 'Accounts');
							$autofillRecordId = $accountsRecordModel->get($autoFillFieldName);
						}
						//if organization doesn't have related sla get default sla
						if (!$autofillRecordId) {
							include_once 'modules/Settings/SLA/models/Record.php';
							$autofillRecordId = Settings_SLA_Record_Model::getDefaultSLA();
						}
					}
				}

				$autoFillNameArray = getEntityName($autoFillModule, $autofillRecordId);
				$autoFillName = $autoFillNameArray[$autofillRecordId];

				$resultData[] = array('id' => $request->get('id'),
					'name' => decode_html($recordModel->getName()),
					'parent_id' => array('name' => decode_html($autoFillName),
										'id' => $autofillRecordId,
										'module' => $autoFillModule));
			}

			$resultData['name'] = decode_html($recordModel->getName());
			$result[$request->get('id')] = $resultData;
		} else {
			$resultData = array('id' => $request->get('id'),
				'name' => decode_html($recordModel->getName()),
				'info' => $recordModel->getRawData());
			$result[$request->get('id')] = $resultData;
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
?>

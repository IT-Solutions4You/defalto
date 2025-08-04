<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Contacts_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	function getParentRecordInfo($request)
	{
		$moduleName = $request->get('module');
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('id'), $moduleName);
		$moduleModel = $recordModel->getModule();
		$autoFillData = $moduleModel->getAutoFillModuleAndField($moduleName);
		if ($autoFillData) {
			foreach ($autoFillData as $data) {
				$autoFillModule = $data['module'];
				$autoFillFieldName = $data['fieldname'];
				$autofillRecordId = $recordModel->get($autoFillFieldName);

				$autoFillNameArray = getEntityName($autoFillModule, $autofillRecordId);
				$autoFillName = $autoFillNameArray[$autofillRecordId];

				$resultData[] = [
					'id'        => $request->get('id'),
					'name'      => decode_html($recordModel->getName()),
					'parent_id' => [
						'name'   => decode_html($autoFillName),
						'id'     => $autofillRecordId,
						'module' => $autoFillModule
					]
				];
			}

			$resultData['name'] = decode_html($recordModel->getName());
			$result[$request->get('id')] = $resultData;
		} else {
			$resultData = [
				'id'   => $request->get('id'),
				'name' => decode_html($recordModel->getName()),
				'info' => $recordModel->getRawData()
			];
			$result[$request->get('id')] = $resultData;
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
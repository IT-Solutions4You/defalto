<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (defined('VTIGER_UPGRADE')) {
	global $adb;

	$query = 'SELECT DISTINCT profileid FROM vtiger_profile2utility';
	$result = $adb->pquery($query, []);

	$tabIdsList = [getTabid('ProjectMilestone'), getTabid('PriceBooks')];
	$actionIdPerms = [5 => 1, 6 => 1, 10 => 1];

	for ($i = 0; $i < $adb->num_rows($result); $i++) {
		$profileId = $adb->query_result($result, $i, 'profileid');

		foreach ($tabIdsList as $tabId) {
			foreach ($actionIdPerms as $actionId => $permission) {
				$isExist = $adb->pquery('SELECT 1 FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', [$profileId, $tabId, $actionId]);
				if ($adb->num_rows($isExist)) {
					$query = 'UPDATE vtiger_profile2utility SET permission=? WHERE profileid=? AND tabid=? AND activityid=?';
				} else {
					$query = 'INSERT INTO vtiger_profile2utility(permission, profileid, tabid, activityid) VALUES (?, ?, ?, ?)';
				}
				Migration_Index_View::ExecuteQuery($query, [$permission, $profileId, $tabId, $actionId]);
			}
		}
	}
}

chdir(dirname(__FILE__) . '/../../../');
require_once 'includes/main/WebUI.php';

$pickListFieldName = 'no_of_currency_decimals';
$moduleModel = Settings_Picklist_Module_Model::getInstance('Users');
$fieldModel = Vtiger_Field_Model::getInstance($pickListFieldName, $moduleModel);

if ($fieldModel) {
	$moduleModel->addPickListValues($fieldModel, 0);
	$moduleModel->addPickListValues($fieldModel, 1);

	$pickListValues = Vtiger_Util_Helper::getPickListValues($pickListFieldName);
	$moduleModel->updateSequence($pickListFieldName, $pickListValues);
}
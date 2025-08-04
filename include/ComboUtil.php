<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once('include/utils/CommonUtils.php');
require_once('include/database/PearDatabase.php');
/** Function to  returns the combo field values in array format
 *
 * @param $combofieldNames -- combofieldNames:: Type string array
 * @returns $comboFieldArray -- comboFieldArray:: Type string array
 */
function getComboArray($combofieldNames)
{
	global $log, $mod_strings;
	$log->debug("Entering getComboArray(" . $combofieldNames . ") method ...");
	global $adb, $current_user;
	$roleid = $current_user->roleid;
	$comboFieldArray = [];
	foreach ($combofieldNames as $tableName => $arrayName) {
		$fldArrName = $arrayName;
		$arrayName = [];

		$sql = "select $tableName from vtiger_$tableName";
		$params = [];
		if (!is_admin($current_user)) {
			$subrole = getRoleSubordinates($roleid);
			if (php7_count($subrole) > 0) {
				$roleids = $subrole;
				array_push($roleids, $roleid);
			} else {
				$roleids = $roleid;
			}
			$sql = "select distinct $tableName from vtiger_$tableName  inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$tableName.picklist_valueid where roleid in(" . generateQuestionMarks(
					$roleids
				) . ") order by sortid";
			$params = [$roleids];
		}
		$result = $adb->pquery($sql, $params);
		while ($row = $adb->fetch_array($result)) {
			$val = $row[$tableName];
			$arrayName[$val] = getTranslatedString($val);
		}
		$comboFieldArray[$fldArrName] = $arrayName;
	}
	$log->debug("Exiting getComboArray method ...");

	return $comboFieldArray;
}

function getUniquePicklistID()
{
	global $adb;

	/*$sql="select id from vtiger_picklistvalues_seq";
	$picklistvalue_id = $adb->query_result($adb->pquery($sql, array()),0,'id');

	$qry = "update vtiger_picklistvalues_seq set id =?";
	$adb->pquery($qry, array(++$picklistvalue_id));*/

	return $adb->getUniqueID('vtiger_picklistvalues');
}
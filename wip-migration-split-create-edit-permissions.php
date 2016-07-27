<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'include/utils/utils.php';
$adb = PearDatabase::getInstance();

$actionMappingResult = $adb->pquery('SELECT 1 FROM vtiger_actionmapping WHERE actionname=?', array('CreateView'));
if (!$adb->num_rows($actionMappingResult)) {
	$adb->pquery('INSERT INTO vtiger_actionmapping VALUES(?, ?, ?)', array(7, 'CreateView', 0));
}

$createActionResult = $adb->pquery('SELECT * FROM vtiger_profile2standardpermissions WHERE operation=?', array(1));
$query = 'INSERT INTO vtiger_profile2standardpermissions VALUES';
while($rowData = $adb->fetch_array($createActionResult)) {
	$tabId			= $rowData['tabid'];
	$profileId		= $rowData['profileid'];
	$permissions	= $rowData['permissions'];
	$query .= "('$profileId', '$tabId', '7', '$permissions'),";
}
$adb->pquery(trim($query, ','), array());

require_once './modules/Users/CreateUserPrivilegeFile.php';
$usersResult = $adb->pquery('SELECT id FROM vtiger_users', array());
$numOfRows = $adb->num_rows($usersResult);
$userIdsList = array();
for($i=0; $i<$numOfRows; $i++) {
	$userId = $adb->query_result($usersResult, $i, 'id');
	createUserPrivilegesfile($userId);
}

echo '<br>Successfully updated create and edit permissions<br>';
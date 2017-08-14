<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

if (defined('VTIGER_UPGRADE')) {
	global $current_user;
	$db = PearDatabase::getInstance();

	//START::Workflow task's template path
	$pathsList = array();
	$result = $db->pquery('SELECT classname FROM com_vtiger_workflow_tasktypes', array());
	while($rowData = $db->fetch_row($result)) {
		$className = $rowData['classname'];
		if ($className) {
			$pathsList[$className] = vtemplate_path("Tasks/$className.tpl", 'Settings:Workflows');
		}
	}

	if ($pathsList) {
		$updateQuery = 'UPDATE com_vtiger_workflow_tasktypes SET templatepath = CASE';
		foreach ($pathsList as $className => $templatePath) {
			$updateQuery .= " WHEN classname='$className' THEN '$templatePath'";
		}
		$updateQuery .= ' ELSE templatepath END';
		$db->pquery($updateQuery, array());
	}
	//END::Workflow task's template path

	//START::Duplication Prevention
	$vtigerFieldColumns = $db->getColumnNames('vtiger_field');
	if (!in_array('isunique', $vtigerFieldColumns)) {
		$db->pquery('ALTER TABLE vtiger_field ADD COLUMN isunique BOOLEAN DEFAULT 0');
	}

	$vtigerTabColumns = $db->getColumnNames('vtiger_tab');
	if (!in_array('issyncable', $vtigerTabColumns)) {
		$db->pquery('ALTER TABLE vtiger_tab ADD COLUMN issyncable BOOLEAN DEFAULT 0');
	}
	if (!in_array('allowduplicates', $vtigerTabColumns)) {
		$db->pquery('ALTER TABLE vtiger_tab ADD COLUMN allowduplicates BOOLEAN DEFAULT 1');
	}
	if (!in_array('sync_action_for_duplicates', $vtigerTabColumns)) {
		$db->pquery('ALTER TABLE vtiger_tab ADD COLUMN sync_action_for_duplicates INT(1) DEFAULT 1');
	}

	//Start - Enable prevention for Accounts module
	$accounts = 'Accounts';
	$db->pquery('UPDATE vtiger_field SET isunique=? WHERE fieldname=? AND tabid=(SELECT tabid FROM vtiger_tab WHERE name=?)', array(1, 'accountname', $accounts));
	$db->pquery('UPDATE vtiger_tab SET allowduplicates=? WHERE name=?', array(0, $accounts));
	//End - Enable prevention for Accounts module

	$db->pquery('UPDATE vtiger_tab SET issyncable=1', array());
	$em = new VTEventsManager($db);
	$em->registerHandler('vtiger.entity.beforesave', 'modules/Vtiger/handlers/CheckDuplicateHandler.php', 'CheckDuplicateHandler');

	$em = new VTEventsManager($db);
	$em->registerHandler('vtiger.entity.beforerestore', 'modules/Vtiger/handlers/CheckDuplicateHandler.php', 'CheckDuplicateHandler');
	echo '<br>Succecssfully handled duplications<br>';
	//END::Duplication Prevention

	//Update existing package modules
	Install_Utils_Model::installModules();
}
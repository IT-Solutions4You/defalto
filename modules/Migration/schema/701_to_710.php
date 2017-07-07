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

	//Update existing package modules
	Install_Utils_Model::installModules();
}
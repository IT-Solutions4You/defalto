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

	//START - Enable prevention for Accounts module
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

	//START::Webform Attachements
	if (!Vtiger_Utils::CheckTable('vtiger_webform_file_fields')) {
		$db->pquery('CREATE TABLE IF NOT EXISTS vtiger_webform_file_fields(id INT(19) NOT NULL AUTO_INCREMENT, webformid INT(19) NOT NULL, fieldname VARCHAR(100) NOT NULL, fieldlabel VARCHAR(100) NOT NULL, required INT(1) NOT NULL DEFAULT 0, PRIMARY KEY (id), KEY fk_vtiger_webforms (webformid), CONSTRAINT fk_vtiger_webforms FOREIGN KEY (webformid) REFERENCES vtiger_webforms (id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=UTF8;', array());
	}

	$result = $db->pquery('SELECT 1 FROM vtiger_ws_operation WHERE name=?', array('add_related'));
	if (!$db->num_rows($result)) {
		$operationId = vtws_addWebserviceOperation('add_related', 'include/Webservices/AddRelated.php', 'vtws_add_related', 'POST');
		vtws_addWebserviceOperationParam($operationId, 'sourceRecordId', 'string', 1);
		vtws_addWebserviceOperationParam($operationId, 'relatedRecordId', 'string', 2);
		vtws_addWebserviceOperationParam($operationId, 'relationIdLabel', 'string', 3);
	}
	echo '<br>Succecssfully added Webforms attachements<br>';
	//END::Webform Attachements

	//START::Tag fields are pointed to cf table for the modules Assets, Services etc..
	$fieldName = 'tags';
	$moduleModels = Vtiger_Module_Model::getAll();
	foreach ($moduleModels as $moduleModel) {
		$baseTableId = $moduleModel->basetableid;
		if ($baseTableId) {
			$baseTableName = $moduleModel->basetable;
			$customTableName = $baseTableName.'cf';
			$customTableColumns = $db->getColumnNames($customTableName);
			if (in_array($fieldName, $customTableColumns)) {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
				$db->pquery("UPDATE vtiger_field SET tablename=? WHERE fieldid=?", array($baseTableName, $fieldModel->id));
				$db->pquery("ALTER TABLE $baseTableName ADD COLUMN $fieldName VARCHAR(1)", array());

				$db->pquery("UPDATE $baseTableName, $customTableName SET $baseTableName.tags=$customTableName.tags WHERE $baseTableName.$baseTableId=$customTableName.$baseTableId", array());
				$db->pquery("ALTER TABLE $customTableName DROP COLUMN $fieldName", array());
			}
		}
	}
	echo '<br>Succecssfully generalized tag fields<br>';
	//START::Tag fields are pointed to cf table for the modules Assets, Services etc..

	//START::Follow & unfollow features
	$em = new VTEventsManager($db);
	$em->registerHandler('vtiger.entity.aftersave', 'modules/Vtiger/handlers/FollowRecordHandler.php', 'FollowRecordHandler');
	//END::Follow & unfollow features

	//START::Reordering Timezones
	$fieldName = 'time_zone';
	$userModuleModel = Vtiger_Module_Model::getInstance('Users');
	$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $userModuleModel);
	if ($fieldModel) {
		$picklistValues = $fieldModel->getPicklistValues();

		$utcTimezones = preg_grep('/\(UTC\)/', $picklistValues);
		asort($utcTimezones);

		$utcPlusTimezones = preg_grep('/\(UTC\+/', $picklistValues);
		asort($utcPlusTimezones);

		$utcMinusTimezones = preg_grep('/\(UTC\-/', $picklistValues);
		arsort($utcMinusTimezones);

		$timeZones = array_merge($utcMinusTimezones, $utcTimezones, $utcPlusTimezones);
		$originalPicklistValues = array_flip(Vtiger_Util_Helper::getPickListValues($fieldName));

		$orderedPicklists = array();
		$i = 0;
		foreach ($timeZones as $timeZone => $value) {
			$orderedPicklists[$originalPicklistValues[$timeZone]] = $i++;
		}
		ksort($orderedPicklists);

		$moduleModel = new Settings_Picklist_Module_Model();
		$moduleModel->updateSequence($fieldName, $orderedPicklists);
		echo '<br>Succecssfully reordered timezones<br>';
	}
	//END::Reordering Timezones

	//START::Differentiate custom modules from Vtiger modules
	$vtigerTabColumns = $db->getColumnNames('vtiger_tab');
	if (!in_array('source', $vtigerTabColumns)) {
		$db->pquery('ALTER TABLE vtiger_tab ADD COLUMN source VARCHAR(255) DEFAULT "custom"', array());
	}
	$db->pquery('UPDATE vtiger_tab SET source=NULL', array());

	$pkgModules = array();
	$pkgFolder = 'pkg/vtiger/modules';
	$pkgHandle = opendir($pkgFolder);

	if ($pkgHandle) {
		while (($pkgModuleName = readdir($pkgHandle)) !== false) {
			$pkgModules[$pkgModuleName] = $pkgModuleName;

			$moduleHandle = opendir("$pkgFolder/$pkgModuleName");
			while (($innerModuleName = readdir($moduleHandle)) !== false) {
				if (is_dir("$pkgFolder/$pkgModuleName/$innerModuleName")) {
					$pkgModules[$innerModuleName] = $innerModuleName;
				}
			}
			closedir($moduleHandle);
		}
		closedir($pkgHandle);
		$pkgModules = array_keys($pkgModules);
	}

	$db->pquery('UPDATE vtiger_tab SET source="custom" WHERE version IS NOT NULL AND name NOT IN ('.generateQuestionMarks($pkgModules).')', $pkgModules);
	echo '<br>Succecssfully added source column vtiger tab table<br>';
	//END::Differentiate custom modules from Vtiger modules

	//Update existing package modules
	Install_Utils_Model::installModules();
}

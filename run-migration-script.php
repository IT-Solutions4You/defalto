<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

ini_set('error_reporting', 6135);
ini_set('display_errors', 'On');
require_once 'include/utils/utils.php';
require_once 'includes/runtime/LanguageHandler.php';
require_once 'includes/main/WebUI.php';
global $current_user;
$current_user = Users_Record_Model::getInstanceFromPreferenceFile(1);

//migration script started
$db = PearDatabase::getInstance();
$result = $db->pquery('SELECT workflow_id FROM com_vtiger_workflows WHERE test LIKE ? AND module_name=? AND defaultworkflow=?', array('%portal%', 'Contacts', 1));
if ($db->num_rows($result) == 1) {
	$workflowId = $db->query_result($result, 0, 'workflow_id');
	$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
	$workflowModel->set('execution_condition', 3);
	$conditions = array(
					array(
						'fieldname' => 'portal',
						'operation' => 'is',
						'value' => '1',
						'valuetype' => 'rawtext',
						'joincondition' => 'and',
						'groupjoin' => 'and',
						'groupid' => '0'
						),
						array(
							'fieldname' => 'email',
							'operation' => 'has changed',
							'value' => '',
							'valuetype' => 'rawtext',
							'joincondition' => 'and',
							'groupjoin' => 'and',
							'groupid' => '0',
							),
						array(
							'fieldname' => 'email',
							'operation' => 'is not empty',
							'value' => '',
							'valuetype' => 'rawtext',
							'joincondition' => '',
							'groupjoin' => 'and',
							'groupid' => '0'
						)
				);
	$workflowModel->set('conditions', $conditions);
	$workflowModel->set('filtersavedinnew', 6);
	$workflowModel->save();
	echo '<b>"#261 - vtiger_portalinfo doesnt update contact"</b> fixed';
}

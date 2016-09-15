<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

if(defined('VTIGER_UPGRADE')) {
global $adb; $db = $adb;

// Migration for - #117 - Convert lead field mapping NULL values and redundant rows
$phoneFieldId = getFieldid(getTabid('Leads'), 'phone');
$db->pquery('UPDATE vtiger_convertleadmapping SET editable=? WHERE leadfid=?', array(1, $phoneFieldId));

// Migration for #261 - vtiger_portalinfo doesn't update contact
$current_user = Users_Record_Model::getInstanceFromPreferenceFile(1);

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
$current_user = null;


}

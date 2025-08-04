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
    global $adb, $current_user;

    // Migration for - #141 - Separating Create/Edit into 2 separate Role/Profile permissions
    $actionMappingResult = $adb->pquery('SELECT 1 FROM vtiger_actionmapping WHERE actionname=?', ['CreateView']);
    if (!$adb->num_rows($actionMappingResult)) {
        $adb->pquery('INSERT INTO vtiger_actionmapping VALUES(?, ?, ?)', [7, 'CreateView', 0]);
    }

    $createActionResult = $adb->pquery('SELECT * FROM vtiger_profile2standardpermissions WHERE operation=?', [1]);

    while ($rowData = $adb->fetch_array($createActionResult)) {
        $tabId = $rowData['tabid'];
        $profileId = $rowData['profileid'];
        $permissions = $rowData['permissions'];

        $result = $adb->pquery('SELECT profileid FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', [$profileId, $tabId, 7]);

        if (!$adb->num_rows($result)) {
            $adb->pquery(
                'INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions) VALUES (?,?,?,?)',
                [$profileId, $tabId, 7, $permissions],
            );
        }
    }

    require_once 'modules/Users/CreateUserPrivilegeFile.php';
    $usersResult = $adb->pquery('SELECT id FROM vtiger_users', []);
    $numOfRows = $adb->num_rows($usersResult);
    $userIdsList = [];
    for ($i = 0; $i < $numOfRows; $i++) {
        $userId = $adb->query_result($usersResult, $i, 'id');
        createUserPrivilegesfile($userId);
    }

    echo '<br>#141 - Successfully updated create and edit permissions<br>';

    // Migration for #261 - vtiger_portalinfo doesn't update contact
    $columns = $adb->getColumnNames('com_vtiger_workflows');
    if (in_array('status', $columns)) {
        $adb->pquery('ALTER TABLE com_vtiger_workflows MODIFY COLUMN status TINYINT(1) DEFAULT 1', []);
        $adb->pquery('UPDATE com_vtiger_workflows SET status=? WHERE status IS NULL', [1]);
    } else {
        $adb->pquery('ALTER TABLE com_vtiger_workflows ADD COLUMN status TINYINT DEFAULT 1', []);
    }

    if (!in_array('workflowname', $columns)) {
        $adb->pquery('ALTER TABLE com_vtiger_workflows ADD COLUMN workflowname VARCHAR(100)', []);
    }
    $adb->pquery('UPDATE com_vtiger_workflows SET workflowname = summary', []);

    $result = $adb->pquery('SELECT workflow_id FROM com_vtiger_workflows WHERE test LIKE ? AND module_name=? AND defaultworkflow=?', ['%portal%', 'Contacts', 1]);
    if ($adb->num_rows($result) == 1) {
        $workflowId = $adb->query_result($result, 0, 'workflow_id');
        $workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
        $workflowModel->set('execution_condition', 3);
        $conditions = [
            [
                'fieldname'     => 'portal',
                'operation'     => 'is',
                'value'         => '1',
                'valuetype'     => 'rawtext',
                'joincondition' => 'and',
                'groupjoin'     => 'and',
                'groupid'       => '0'
            ],
            [
                'fieldname'     => 'email',
                'operation'     => 'is not empty',
                'value'         => '',
                'valuetype'     => 'rawtext',
                'joincondition' => '',
                'groupjoin'     => 'and',
                'groupid'       => '0'
            ]
        ];
        $workflowModel->set('conditions', $conditions);
        $workflowModel->set('filtersavedinnew', 6);
        $workflowModel->set('status', 1);
        $workflowModel->save();
        echo '<b>"#261 - vtiger_portalinfo doesnt update contact"</b> fixed';
    }
}
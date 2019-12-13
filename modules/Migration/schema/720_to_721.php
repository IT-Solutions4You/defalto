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
	global $current_user, $adb;
	$db = PearDatabase::getInstance();

    //Profile privileges supported for Emails Module
	$actions = array('Save', 'EditView', 'Delete', 'DetailView');
    $emailsTabId = getTabid('Emails');

    $actionIds = array();
    foreach($actions as $actionName) {
        array_push($actionIds, getActionid($actionName));
    }

    $profileIdsResult = $db->pquery("SELECT DISTINCT profileid FROM vtiger_profile", array());
    $profileIdCount = $db->num_rows($profileIdsResult);
    for($i = 0; $i < $profileIdCount; $i++) {
        $profileId = $db->query_result($profileIdsResult, $i, 'profileid');
        foreach($actionIds as $actionId) {
            $db->pquery("INSERT INTO vtiger_profile2standardpermissions VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE permissions = ?",
                    array($profileId, $emailsTabId, $actionId, 0, 0));
        }
        echo "Emails permission for profile id :: $profileId inserted into vtiger_profile2standardpermissions table.<br>";
    }
    echo 'All profiles permissions updated to Email Module';
    
    $db->pquery("UPDATE vtiger_tab SET ownedby = ? WHERE tabid = ?", array(0, $emailsTabId));
    echo "ownedby value updated to 0 for Emails in vtiger_tab table.<br>";
    
    vimport('~modules/Users/CreateUserPrivilegeFile.php');
    $usersResult = $db->pquery("SELECT id FROM vtiger_users", array());
    $usersCount = $db->num_rows($usersResult);
    for($i = 0; $i < $usersCount; $i++) {
        $userId = $db->query_result($usersResult, $i, 'id');
        createUserPrivilegesfile($userId); 
        createUserSharingPrivilegesfile($userId);
        echo "User privilege and sharing privilege files recreated for user id :: $userId.<br>";
    }
    
    //Default Email reports access count column update from varchar to integer
    $db->pquery('UPDATE vtiger_selectcolumn set columnname = ? where columnname=?', array('vtiger_email_track:access_count:Emails_Access_Count:access_count:I', 'vtiger_email_track:access_count:Emails_Access_Count:access_count:V'));
    $db->pquery('UPDATE vtiger_relcriteria set columnname = ?, comparator = ? where columnname=?', array('vtiger_email_track:access_count:Emails_Access_Count:access_count:I', 'ny', 'vtiger_email_track:access_count:Emails_Access_Count:access_count:V'));
    echo 'Email access count field data type updated to Int';
}
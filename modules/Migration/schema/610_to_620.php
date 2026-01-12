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
    Core_Install_Model::getInstance('module.postupdate', 'Google')->installModule();
}
if (defined('INSTALLATION_MODE')) {
    // Set of task to be taken care while specifically in installation mode.
}

$db = PearDatabase::getInstance();

//Handle migration for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/7552--senotesrel
Migration_Index_View::ExecuteQuery('DELETE from vtiger_senotesrel WHERE crmid NOT IN(select crmid from vtiger_crmentity)', []);
Migration_Index_View::ExecuteQuery(
    'ALTER TABLE vtiger_senotesrel ADD CONSTRAINT fk1_crmid FOREIGN KEY IF NOT EXISTS (crmid) REFERENCES vtiger_crmentity(crmid) ON DELETE CASCADE',
    []
);

/*141*/
//registering handlers for Google sync 
require_once 'includes/main/WebUI.php';
require_once 'modules/WSAPP/Utils.php';
if (file_exists("modules/Google")) {
    require_once 'modules/Google/connectors/Config.php';
    wsapp_RegisterHandler('Google_vtigerHandler', 'Google_Vtiger_Handler', 'modules/Google/handlers/Vtiger.php');
    wsapp_RegisterHandler('Google_vtigerSyncHandler', 'Google_VtigerSync_Handler', 'modules/Google/handlers/VtigerSync.php');

    //updating Google Sync Handler names 
    $names = ['Vtiger_GoogleContacts', 'Vtiger_GoogleCalendar'];
    $result = $db->pquery("SELECT stateencodedvalues FROM vtiger_wsapp_sync_state WHERE name IN (" . generateQuestionMarks($names) . ")", [$names]);
    $resultRows = $db->num_rows($result);
    $appKey = [];
    for ($i = 0; $i < $resultRows; $i++) {
        $stateValuesJson = $db->query_result($result, $i, 'stateencodedvalues');
        $stateValues = Zend_Json::decode(decode_html($stateValuesJson));
        $appKey[] = $stateValues['synctrackerid'];
    }

    if (!empty($appKey)) {
        $sql = 'UPDATE vtiger_wsapp SET name = ? WHERE appkey IN (' . generateQuestionMarks($appKey) . ')';
        $res = Migration_Index_View::ExecuteQuery($sql, ['Google_vtigerSyncHandler', $appKey]);
    }
}
//Ends 141

//Google Calendar sync changes
/**
 * Please refer this trac (http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/8354#comment:3)
 * for configuration of vtiger to Google OAuth2
 */
global $adb;

//(start)Migrating GoogleCalendar ClientIds in wsapp_recordmapping to support v3

$syncTrackerIds = [];

if (Vtiger_Utils::CheckTable('vtiger_wsapp_sync_state')) {
    $sql = 'SELECT stateencodedvalues from vtiger_wsapp_sync_state WHERE name = ?';
    $result = $db->pquery($sql, ['Vtiger_GoogleCalendar']);
    $num_of_rows = $adb->num_rows($result);

    for ($i = 0; $i < $num_of_rows; $i++) {
        $stateEncodedValues = $adb->query_result($result, $i, 'stateencodedvalues');
        $htmlDecodedStateEncodedValue = decode_html($stateEncodedValues);
        $stateDecodedValues = json_decode($htmlDecodedStateEncodedValue, true);
        if (is_array($stateDecodedValues) && isset($stateDecodedValues['synctrackerid'])) {
            $syncTrackerIds[] = $stateDecodedValues['synctrackerid'];
        }
    }
}

//$syncTrackerIds - list of all Calendar sync trackerIds

$appIds = [];

if (php7_count($syncTrackerIds)) {
    $sql = 'SELECT appid FROM vtiger_wsapp WHERE appkey IN (' . generateQuestionMarks($syncTrackerIds) . ')';
    $result = Migration_Index_View::ExecuteQuery($sql, $syncTrackerIds);

    $num_of_rows = $adb->num_rows($result);

    for ($i = 0; $i < $num_of_rows; $i++) {
        $appId = $adb->query_result($result, $i, 'appid');
        if ($appId) {
            $appIds[] = $appId;
        }
    }
}

//$appIds - list of all Calendarsync appids

if (php7_count($appIds)) {
    $sql = 'SELECT id,clientid FROM vtiger_wsapp_recordmapping WHERE appid IN (' . generateQuestionMarks($appIds) . ')';
    $result = Migration_Index_View::ExecuteQuery($sql, $appIds);

    $num_of_rows = $adb->num_rows($result);

    for ($i = 0; $i < $num_of_rows; $i++) {
        $id = $adb->query_result($result, $i, 'id');
        $clientid = $adb->query_result($result, $i, 'clientid');

        $parts = explode('/', $clientid);
        $newClientId = end($parts);

        Migration_Index_View::ExecuteQuery('UPDATE vtiger_wsapp_recordmapping SET clientid = ? WHERE id = ?', [$newClientId, $id]);
    }

    echo '<br> vtiger_wsapp_recordmapping clientid migration completed for CalendarSync';
}
//(end)
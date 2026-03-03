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

if (!defined('VTIGER_UPGRADE')) {
    die('Invalid entry point');
}
chdir(dirname(__FILE__) . '/../../../');
include_once 'modules/com_vtiger_workflow/VTTaskManager.inc';
include_once 'include/utils/utils.php';

if (defined('INSTALLATION_MODE')) {
    // Set of task to be taken care while specifically in installation mode.
}

global $adb;

$adb = PearDatabase::getInstance();
$columns = [
    'schtypeid'        => 'INT(10)',
    'schtime'          => 'TIME',
    'schdayofmonth'    => 'VARCHAR(100)',
    'schdayofweek'     => 'VARCHAR(100)',
    'schannualdates'   => 'VARCHAR(100)',
    'nexttrigger_time' => 'DATETIME',
];

foreach ($columns as $column => $type) {
    if (!columnExists($column, 'com_vtiger_workflows')) {
        $adb->pquery(sprintf('ALTER TABLE com_vtiger_workflows ADD %s %s', $column, $type));
    }
}

Migration_Index_View::ExecuteQuery(
    "CREATE TABLE IF NOT EXISTS vtiger_faqcf ( 
                                faqid int(19), 
                                PRIMARY KEY (faqid), 
                                CONSTRAINT fk_1_vtiger_faqcf FOREIGN KEY (faqid) REFERENCES vtiger_faq(id) ON DELETE CASCADE 
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    []
);

echo "FAQ cf created";

//73 starts
$query = 'SELECT 1 FROM vtiger_currencies WHERE currency_name=?';
$result = $adb->pquery($query, ['Sudanese Pound']);
if ($adb->num_rows($result) <= 0) {
    //Inserting Currency Sudanese Pound to vtiger_currencies
    Migration_Index_View::ExecuteQuery(
        'INSERT INTO vtiger_currencies (currencyid,currency_name,currency_code,currency_symbol) VALUES (' . $adb->getUniqueID("vtiger_currencies") . ',"Sudanese Pound","SDG","£")',
        []
    );
    Vtiger_Utils::AddColumn('vtiger_mailmanager_mailattachments', 'cid', 'VARCHAR(100)');
}
//73 ends
//75 starts
//create new table for feedback on removing old version
$adb->query("CREATE TABLE IF NOT EXISTS vtiger_feedback (userid INT(19), dontshow VARCHAR(19) default false);");

//75 ends

//77 starts
$sql = "ALTER TABLE vtiger_products MODIFY productname VARCHAR( 100 )";
Migration_Index_View::ExecuteQuery($sql, []);
echo "<br>Updated to varchar(100) for productname";

$result = $adb->pquery('SELECT 1 FROM vtiger_currencies WHERE currency_name = ?', ['CFA Franc BCEAO']);
if (!$adb->num_rows($result)) {
    Migration_Index_View::ExecuteQuery(
        'INSERT INTO vtiger_currencies (currencyid, currency_name, currency_code, currency_symbol) VALUES(?, ?, ?, ?)',
        [$adb->getUniqueID('vtiger_currencies'), 'CFA Franc BCEAO', 'XOF', 'CFA']
    );
}
$result = $adb->pquery('SELECT 1 FROM vtiger_currencies WHERE currency_name = ?', ['CFA Franc BEAC']);
if (!$adb->num_rows($result)) {
    Migration_Index_View::ExecuteQuery(
        'INSERT INTO vtiger_currencies (currencyid, currency_name, currency_code, currency_symbol) VALUES(?, ?, ?, ?)',
        [$adb->getUniqueID('vtiger_currencies'), 'CFA Franc BEAC', 'XAF', 'CFA']
    );
}
echo "<br>Added CFA Franc BCEAO and CFA Franc BEAC currencies";

$sql = "ALTER TABLE vtiger_loginhistory MODIFY user_name VARCHAR( 255 )";
Migration_Index_View::ExecuteQuery($sql, []);

//77 ends(Some function addGroupTaxTemplatesForQuotesAndPurchaseOrder)

//78 starts
//78 ends

//79 starts
Migration_Index_View::ExecuteQuery(
    "CREATE TABLE IF NOT EXISTS vtiger_shareduserinfo
						(userid INT(19) NOT NULL default 0, shareduserid INT(19) NOT NULL default 0,
						color VARCHAR(50), visible INT(19) default 1);",
    []
);

$assignedToId = Users::getActiveAdminId();
Migration_Index_View::ExecuteQuery("UPDATE vtiger_mailscanner_rules SET assigned_to=?", [$assignedToId]);
echo "<br> Adding assigned to, cc, bcc fields for mail scanner rules";

//79 ends

//82 ends

//84 starts

//To copy imagename saved in vtiger_attachments for products and contacts into respectively base table
//to support filters on imagename field
$productIdSql = 'SELECT productid,name FROM vtiger_seattachmentsrel INNER JOIN vtiger_attachments ON
                                        vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid INNER JOIN vtiger_products ON
                                        vtiger_products.productid = vtiger_seattachmentsrel.crmid';
$productIds = $adb->pquery($productIdSql, []);
$numOfRows = $adb->num_rows($productIds);

$productImageMap = [];
for ($i = 0; $i < $numOfRows; $i++) {
    $productId = $adb->query_result($productIds, $i, "productid");
    $imageName = decode_html($adb->query_result($productIds, $i, "name"));
    if (!empty($productImageMap[$productId])) {
        array_push($productImageMap[$productId], $imageName);
    } elseif (empty($productImageMap[$productId])) {
        $productImageMap[$productId] = [$imageName];
    }
}
foreach ($productImageMap as $productId => $imageNames) {
    $implodedNames = implode(",", $imageNames);
    Migration_Index_View::ExecuteQuery('UPDATE vtiger_products SET imagename = ? WHERE productid = ?', [$implodedNames, $productId]);
}
echo 'updating image information for products table is completed';

$ContactIdSql = 'SELECT contactid,name FROM vtiger_seattachmentsrel 
    INNER JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid 
    INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_seattachmentsrel.crmid';
$contactIds = $adb->pquery($ContactIdSql, []);
$numOfRows = $adb->num_rows($contactIds);

for ($i = 0; $i < $numOfRows; $i++) {
    $contactId = $adb->query_result($contactIds, $i, "contactid");
    $imageName = decode_html($adb->query_result($contactIds, $i, "name"));
    Migration_Index_View::ExecuteQuery('UPDATE vtiger_contactdetails SET imagename = ? WHERE contactid = ?', [$imageName, $contactId]);
}
echo 'updating image information for contacts table is completed';

//85 starts
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_account ALTER isconvertedfromlead SET DEFAULT ?', ['0']);
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_contactdetails ALTER isconvertedfromlead SET DEFAULT ?', ['0']);
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_potential ALTER isconvertedfromlead SET DEFAULT ?', ['0']);
Migration_Index_View::ExecuteQuery('Update vtiger_account SET isconvertedfromlead = ? where isconvertedfromlead is NULL', ['0']);
Migration_Index_View::ExecuteQuery('Update vtiger_contactdetails SET isconvertedfromlead = ? where isconvertedfromlead is NULL', ['0']);
Migration_Index_View::ExecuteQuery('Update vtiger_potential SET isconvertedfromlead = ? where isconvertedfromlead is NULL', ['0']);

//85 ends

//86 starts
//Duplicate of 85 script
//86 ends

//87 starts
$result = $adb->pquery('SELECT 1 FROM vtiger_currencies WHERE currency_name = ?', ['Haiti, Gourde']);
if (!$adb->num_rows($result)) {
    Migration_Index_View::ExecuteQuery(
        'INSERT INTO vtiger_currencies (currencyid, currency_name, currency_code, currency_symbol) VALUES(?, ?, ?, ?)',
        [$adb->getUniqueID('vtiger_currencies'), 'Haiti, Gourde', 'HTG', 'G']
    );
}
//87 ends   

//88 starts
Migration_Index_View::ExecuteQuery("UPDATE vtiger_currencies SET currency_symbol=? WHERE currency_code=?", ['₹', 'INR']);
Migration_Index_View::ExecuteQuery("UPDATE vtiger_currency_info SET currency_symbol=? WHERE currency_code=?", ['₹', 'INR']);

Migration_Index_View::ExecuteQuery(
    'UPDATE vtiger_projecttaskstatus set presence = 0 where projecttaskstatus in (?,?,?,?,?)',
    ['Open', 'In Progress', 'Completed', 'Deferred', 'Canceled']
);
echo '<br> made projecttaskstatus picklist values as non editable';

//88 ends

//89 starts
//89 ends

//91 starts
$pathToFile = "layouts/vlayout/modules/Products/PopupContents.tpl";
shell_exec("rm -rf $pathToFile");
echo "Removed Products PopupContents.tpl";
echo "<br>";

$pathToFile = "layouts/vlayout/modules/Products/PopupEntries.tpl";
shell_exec("rm -rf $pathToFile");
echo "Removed Products PopupEntries.tpl";
echo "<br>";
//91 ends

//92 starts
$result = $adb->pquery(
    "SELECT 1 FROM vtiger_eventhandlers WHERE event_name=? AND handler_class=?",
    ['vtiger.entity.aftersave', 'Vtiger_RecordLabelUpdater_Handler']
);
if ($adb->num_rows($result) <= 0) {
    $lastMaxCRMId = 0;
    do {
        $rs = $adb->pquery("SELECT crmid,setype FROM vtiger_crmentity WHERE crmid > ? LIMIT 500", [$lastMaxCRMId]);
        if (!$adb->num_rows($rs)) {
            break;
        }

        while ($row = $adb->fetch_array($rs)) {
            $imageType = stripos($row['setype'], 'image');
            $attachmentType = stripos($row['setype'], 'attachment');

            /**
             * TODO: Optimize underlying API to cache re-usable data, for speedy data.
             */
            if ($attachmentType || $imageType) {
                $labelInfo = $row['setype'];
            } else {
                $labelInfo = getEntityName($row['setype'], [intval($row['crmid'])]);
            }

            if ($labelInfo) {
                $label = html_entity_decode($labelInfo[$row['crmid']], ENT_QUOTES);

                Migration_Index_View::ExecuteQuery(
                    'UPDATE vtiger_crmentity SET label=? WHERE crmid=? AND setype=?',
                    [$label, $row['crmid'], $row['setype']]
                );
            }

            if (intval($row['crmid']) > $lastMaxCRMId) {
                $lastMaxCRMId = intval($row['crmid']);
            }
        }
        $rs = null;
        unset($rs);
    } while (true);

    $homeModule = Vtiger_Module::getInstance('Home');
    Vtiger_Event::register($homeModule, 'vtiger.entity.aftersave', 'Vtiger_RecordLabelUpdater_Handler', 'modules/Vtiger/handlers/RecordLabelUpdater.php');
    echo "Record Update Handler was updated successfully";
}
// To update the Campaign related status value in database as in language file
$updateQuery = "update vtiger_campaignrelstatus set campaignrelstatus=? where campaignrelstatus=?";
Migration_Index_View::ExecuteQuery($updateQuery, ['Contacted - Unsuccessful', 'Contacted - Unsuccessful']);
echo 'Campaign related status value is updated';
//92 ends

//93 starts
//93 ends

//94 starts
$result = $adb->pquery('SELECT 1 FROM vtiger_currencies WHERE currency_name = ?', ['Libya, Dinar']);
if (!$adb->num_rows($result)) {
    Migration_Index_View::ExecuteQuery(
        'INSERT INTO vtiger_currencies (currencyid, currency_name, currency_code, currency_symbol) VALUES(?, ?, ?, ?)',
        [$adb->getUniqueID('vtiger_currencies'), 'Libya, Dinar', 'LYD', 'LYD']
    );
}

global $root_directory;

$maxActionIdResult = $adb->pquery('SELECT MAX(actionid) AS maxid FROM vtiger_actionmapping', []);
$maxActionId = $adb->query_result($maxActionIdResult, 0, 'maxid');
Migration_Index_View::ExecuteQuery('INSERT INTO vtiger_actionmapping(actionid, actionname, securitycheck) VALUES(?,?,?)', [$maxActionId + 1, 'Print', '0']);
echo "<br> added print to vtiger_actionnmapping";
//94 ends

//95 starts
require_once 'vtlib/Vtiger/Module.php';

Migration_Index_View::ExecuteQuery("UPDATE vtiger_field SET presence=0 WHERE fieldname='unit_price' and columnname='unit_price'", []);
Migration_Index_View::ExecuteQuery("ALTER TABLE vtiger_portal ADD createdtime datetime", []);

$adb->query("CREATE TABLE IF NOT EXISTS vtiger_calendar_default_activitytypes (id INT(19), module VARCHAR(50), fieldname VARCHAR(50), defaultcolor VARCHAR(50));");

$adb->query("CREATE TABLE IF NOT EXISTS vtiger_calendar_user_activitytypes (id INT(19), defaultid INT(19), userid INT(19), color VARCHAR(50), visible INT(19) default 1);");

$result = Migration_Index_View::ExecuteQuery('SELECT * FROM vtiger_calendar_user_activitytypes', []);
if ($adb->num_rows($result) <= 0) {
    $queryResult = Migration_Index_View::ExecuteQuery('SELECT id, defaultcolor FROM vtiger_calendar_default_activitytypes', []);
    $numRows = $adb->num_rows($queryResult);
    for ($i = 0; $i < $numRows; $i++) {
        $row = $adb->query_result_rowdata($queryResult, $i);
        $activityIds[$row['id']] = $row['defaultcolor'];
    }

    $allUsers = Users_Record_Model::getAll(true);
    foreach ($allUsers as $userId => $userModel) {
        foreach ($activityIds as $activityId => $color) {
            Migration_Index_View::ExecuteQuery(
                'INSERT INTO vtiger_calendar_user_activitytypes (id, defaultid, userid, color) VALUES (?,?,?,?)',
                [$adb->getUniqueID('vtiger_calendar_user_activitytypes'), $activityId, $userId, $color]
            );
        }
    }
}

//95 ends

//96 starts
$entityModulesModels = Vtiger_Module_Model::getEntityModules();
$fieldNameToDelete = 'created_user_id';
if ($entityModulesModels) {
    foreach ($entityModulesModels as $moduleInstance) {
        if ($moduleInstance) {
            $module = $moduleInstance->name;
            $fieldInstance = Vtiger_Field::getInstance($fieldNameToDelete, $moduleInstance);
            if ($fieldInstance) {
                $fieldInstance->delete();
                echo "<br>";
                echo "For $module created by is removed";
            } else {
                echo "<br>";
                echo "For $module created by is not there";
            }
        } else {
            echo "Unable to find $module instance";
            echo '<br>';
        }
    }
}
//96 ends

//97 starts
$adb = PearDatabase::getInstance();
$handlers = ['modules/FieldFormulas/VTFieldFormulasEventHandler.inc'];
Migration_Index_View::ExecuteQuery('DELETE FROM vtiger_eventhandlers WHERE handler_path IN (' . generateQuestionMarks($handlers) . ')', $handlers);

//delete modtracker detail view links
Migration_Index_View::ExecuteQuery(
    'DELETE FROM vtiger_links WHERE linktype = ? AND handler_class = ? AND linkurl like "javascript:ModTrackerCommon.showhistory%"',
    ['DETAILVIEWBASIC', 'ModTracker']
);

//Added New field in mailmanager
Migration_Index_View::ExecuteQuery('ALTER TABLE vtiger_mail_accounts ADD COLUMN sent_folder VARCHAR(50)', []);
echo '<br>selected folder field added in mailmanager.<br>';

//97 ends

//Migrating PBXManager 5.4.0 to 6.x
if (!defined('INSTALLATION_MODE')) {
    $moduleInstance = Vtiger_Module_Model::getInstance('PBXManager');
    if (!$moduleInstance) {
        echo '<br>Installing PBX Manager starts<br>';
        Core_Install_Model::getInstance('module.postupdate', 'PBXManager')->installModule();
    } else {
        $result = $adb->pquery('SELECT server, port FROM vtiger_asterisk', []);
        $server = $adb->query_result($result, 0, 'server');

        $qualifiedModuleName = 'PBXManager';
        $recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
        $recordModel->set('gateway', $qualifiedModuleName);

        $connector = new PBXManager_PBXManager_Connector;
        foreach (PBXManager_PBXManager_Connector::getSettingsParameters() as $field => $type) {
            $fieldValue = "";
            if ($field == "webappurl") {
                $fieldValue = "http://" . $server . ":";
            }
            if ($field == "vtigersecretkey") {
                $fieldValue = uniqid(rand());
            }
            $recordModel->set($field, $fieldValue);
        }
        $recordModel->save();

        $modules = ['Contacts', 'Accounts', 'Leads'];
        $recordModel = new PBXManager_Record_Model;

        foreach ($modules as $module) {
            $moduleInstance = CRMEntity::getInstance($module);

            $query = $moduleInstance->buildSearchQueryForFieldTypes(['11']);
            $result = $adb->pquery($query, []);
            $rows = $adb->num_rows($result);

            for ($i = 0; $i < $rows; $i++) {
                $row = $adb->query_result_rowdata($result, $i);
                $crmid = $row['id'];

                foreach ($row as $name => $value) {
                    $values = [];
                    $values['crmid'] = $crmid;
                    $values['setype'] = $module;

                    if ($name != 'name' && !empty($value) && $name != 'id' && !is_numeric($name)
                        && $name != 'firstname' && $name != 'lastname') {
                        $values[$name] = $value;
                        $recordModel->receivePhoneLookUpRecord($name, $values, true);
                    }
                }
            }
        }
        //Data migrate from old columns to new columns in vtiger_pbxmanager
        $query = 'SELECT * FROM vtiger_pbxmanager';
        $result = $adb->pquery($query, []);
        $params = [];
        $rowCount = $adb->num_rows($result);
        for ($i = 0; $i < $rowCount; $i++) {
            $pbxmanagerid = $adb->query_result($result, $i, 'pbxmanagerid');
            $callfrom = $adb->query_result($result, $i, 'callfrom');
            $callto = $adb->query_result($result, $i, 'callto');
            $timeofcall = $adb->query_result($result, $i, 'timeofcall');
            $status = $adb->query_result($result, $i, 'status');
            $customer = PBXManager_Record_Model::lookUpRelatedWithNumber($callfrom);
            $userIdQuery = $adb->pquery('SELECT userid FROM vtiger_asteriskextensions WHERE asterisk_extension = ?', [$callto]);
            $user = $adb->query_result($userIdQuery, $i, 'userid');
            if ($status == 'outgoing') {
                $callstatus = 'outbound';
            } elseif ($status == 'incoming') {
                $callstatus = 'inbound';
            }
            //Update query
            $adb->pquery(
                'UPDATE vtiger_pbxmanager SET customer = ? AND user = ? AND totalduration = ? AND callstatus = ? WHERE pbxmanagerid = ?',
                [$customer, $user, $timeofcall, $callstatus, $pbxmanagerid]
            );
        }

        //Adding PBXManager PostUpdate API's
        //Query to fetch asterisk extension
        $extensionResult = $adb->pquery('SELECT userid, asterisk_extension FROM vtiger_asteriskextensions', []);
        for ($i = 0; $i < $adb->num_rows($extensionResult); $i++) {
            $userId = $adb->query_result($extensionResult, 0, 'userid');
            $extensionNumber = $adb->query_result($extensionResult, 0, 'asterisk_extension');
            $adb->pquery('UPDATE vtiger_users SET phone_crm_extension = ? WHERE id = ?', [$extensionNumber, $userId]);
        }
        //Add PBXManager Links

        $handlerInfo = [
            'path'   => 'modules/PBXManager/PBXManager.php',
            'class'  => 'PBXManager',
            'method' => 'checkLinkPermission'
        ];
        $headerScriptLinkType = 'HEADERSCRIPT';
        $incomingLinkLabel = 'Incoming Calls';
        Vtiger_Link::addLink(0, $headerScriptLinkType, $incomingLinkLabel, 'modules/PBXManager/resources/PBXManagerJS.js', '', '', $handlerInfo);
        echo '<br>Added PBXManager links<br>';

        //Add action mapping

        $adb = PearDatabase::getInstance();
        $module = new Vtiger_Module();
        $moduleInstance = $module->getInstance('PBXManager');

        //To add actionname as ReceiveIncomingcalls
        $maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM vtiger_actionmapping', []);
        if ($adb->num_rows($maxActionIdresult)) {
            $actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
        }
        $adb->pquery(
            'INSERT INTO vtiger_actionmapping 
                                 (actionid, actionname, securitycheck) VALUES(?,?,?)',
            [$actionId, 'ReceiveIncomingCalls', 0]
        );
        $moduleInstance->enableTools('ReceiveIncomingcalls');

        //To add actionname as MakeOutgoingCalls
        $maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM vtiger_actionmapping', []);
        if ($adb->num_rows($maxActionIdresult)) {
            $actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
        }
        $adb->pquery(
            'INSERT INTO vtiger_actionmapping 
                                 (actionid, actionname, securitycheck) VALUES(?,?,?)',
            [$actionId, 'MakeOutgoingCalls', 0]
        );
        $moduleInstance->enableTools('MakeOutgoingCalls');

        echo '<br>Added PBXManager action mapping<br>';

        //Add lookup events

        $adb = PearDatabase::getInstance();
        $EventManager = new VTEventsManager($adb);
        $createEvent = 'vtiger.entity.aftersave';
        $deleteEVent = 'vtiger.entity.afterdelete';
        $restoreEvent = 'vtiger.entity.afterrestore';
        $batchSaveEvent = 'vtiger.batchevent.save';
        $batchDeleteEvent = 'vtiger.batchevent.delete';
        $handler_path = 'modules/PBXManager/PBXManagerHandler.php';
        $className = 'PBXManagerHandler';
        $batchEventClassName = 'PBXManagerBatchHandler';
        $EventManager->registerHandler($createEvent, $handler_path, $className, '["VTEntityDelta"]');
        $EventManager->registerHandler($deleteEVent, $handler_path, $className);
        $EventManager->registerHandler($restoreEvent, $handler_path, $className);
        $EventManager->registerHandler($batchSaveEvent, $handler_path, $batchEventClassName);
        $EventManager->registerHandler($batchDeleteEvent, $handler_path, $batchEventClassName);

        echo 'Added PBXManager lookup events';

        //Existing Asterisk extension block removed from vtiger_users if exist
        $moduleInstance = Vtiger_Module_Model::getInstance('Users');
        $fieldInstance = $moduleInstance->getField('asterisk_extension');

        if (!empty($fieldInstance)) {
            $blockId = $fieldInstance->getBlockId();
            $fieldInstance->delete();
        }

        $fieldInstance = $moduleInstance->getField('use_asterisk');
        if (!empty($fieldInstance)) {
            $fieldInstance->delete();
        }
    }
}

//Hiding previous PBXManager fields. 
$tabId = getTabid('PBXManager');
Migration_Index_View::ExecuteQuery("UPDATE vtiger_field SET presence=? WHERE tabid=? AND fieldname=?;", [1, $tabId, "callfrom"]);
Migration_Index_View::ExecuteQuery("UPDATE vtiger_field SET presence=? WHERE tabid=? AND fieldname=?;", [1, $tabId, "callto"]);
Migration_Index_View::ExecuteQuery("UPDATE vtiger_field SET presence=? WHERE tabid=? AND fieldname=?;", [1, $tabId, "timeofcall"]);
Migration_Index_View::ExecuteQuery("UPDATE vtiger_field SET presence=? WHERE tabid=? AND fieldname=?;", [1, $tabId, "status"]);
echo '<br>Hiding previous PBXManager fields done.<br>';
//PBXManager porting ends.

//Add Column trial for vtiger_tab table if not exists
if (!columnExists('trial', 'vtiger_tab')) {
    $adb->pquery("ALTER TABLE vtiger_tab ADD trial INT(1) NOT NULL DEFAULT 0", []);
}

//Setting up is_owner for every admin user of CRM
$adb = PearDatabase::getInstance();
$idResult = $adb->pquery('SELECT id FROM vtiger_users WHERE is_admin = ? AND status=?', ['on', 'Active']);
if ($adb->num_rows($idResult) > 0) {
    for ($i = 0; $i <= $adb->num_rows($idResult); $i++) {
        $userid = $adb->query_result($idResult, $i, 'id');
        $adb->pquery('UPDATE vtiger_users SET is_owner=? WHERE id=?', [1, $userid]);
        echo '<br>Account Owner Informnation saved in vtiger';
        //Recreate user prvileges
        createUserPrivilegesfile($userId);
        echo '<br>User previleges file recreated aftter adding is_owner field';
    }
} else {
    echo '<br>Account Owner was not existed in this database';
}
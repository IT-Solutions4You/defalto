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
    global $current_user, $adb;
    $db = PearDatabase::getInstance();

    vimport('~modules/Users/CreateUserPrivilegeFile.php');
    $usersResult = $db->pquery("SELECT id FROM vtiger_users", []);
    $usersCount = $db->num_rows($usersResult);
    for ($i = 0; $i < $usersCount; $i++) {
        $userId = $db->query_result($usersResult, $i, 'id');
        createUserPrivilegesfile($userId);
        createUserSharingPrivilegesfile($userId);
        echo "User privilege and sharing privilege files recreated for user id :: $userId.<br>";
    }

    //#1184 => Register field delete event handler
    $em = new VTEventsManager($db);
    $em->registerHandler('vtiger.field.afterdelete', 'modules/Vtiger/handlers/FieldEventHandler.php', 'FieldEventHandler');

    $db->pquery('INSERT INTO vtiger_date_format (date_format, sortorderid, presence) VALUES (?, ?, ?)', ['dd.mm.yyyy', 3, 1]);
    $db->pquery('INSERT INTO vtiger_date_format (date_format, sortorderid, presence) VALUES (?, ?, ?)', ['dd/mm/yyyy', 4, 1]);

    //#1248 => updated vtiger_systems.server_password to TEXT
    $db->pquery('ALTER TABLE vtiger_systems MODIFY server_password text', []);

    //Migrate default module data from config editor to database
    $moduleModel = Settings_Vtiger_ConfigModule_Model::getInstance();
    $configFieldData = $moduleModel->getViewableData();
    $defaultModule = $configFieldData['default_module'] ?? 'Home';
    $allUsers = Users_Record_Model::getAll(true);
    $allUserIds = array_keys($allUsers);

    $db->pquery('UPDATE vtiger_users SET defaultlandingpage = ? WHERE id in (' . generateQuestionMarks($allUserIds) . ')', [$defaultModule, $allUserIds]);
    echo "Default landing page updated for all active users <br>";

    //Recalculating user-preivilege file, as defaultlandingpage and other preference changes should be updated
    foreach ($allUserIds as $userId) {
        createUserPrivilegesfile($userId);
        createUserSharingPrivilegesfile($userId);
    }
    echo "Re-calculated user privilege and sharing privileges files";

    //Adding beforeRelate and afterRelate event handlers
    $em = new VTEventsManager($db);
    $em->registerHandler('vtiger.entity.beforerelate', 'modules/Vtiger/handlers/RelateEntitesHandler.php', 'RelateEntitesHandler');
    echo '<br>Succecssfully added before relate handler<br>';

    $em->registerHandler('vtiger.entity.afterrelate', 'modules/Vtiger/handlers/RelateEntitesHandler.php', 'RelateEntitesHandler');
    echo '<br>Succecssfully added before relate handler<br>';

    //image uitype added for webservice fieldtype
    $sql = 'INSERT INTO vtiger_ws_fieldtype(uitype,fieldtype) VALUES (?,?)';
    $params = ['69', 'image'];
    $db->pquery($sql, $params);

    //Remove unwanted Files
    global $root_directory;
    $filesPath = [
        "/layouts/v7/lib/jquery/Lightweight-jQuery-In-page-Filtering-Plugin-instaFilta/demo.html",
        "/layouts/v7/lib/vt-icons/demo.html",
        "/layouts/v7/lib/jquery/daterangepicker/index.html",
        "/layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/index.html",
        "/layouts/v7/lib/jquery/timepicker/index.html",
        "/libraries/bootstrap/js/tests",
        "/libraries/jquery/colorpicker/index.html",
        "/libraries/jquery/jquery-ui/third-party/jQuery-UI-Date-Range-Picker/index.html",
        "/libraries/jquery/timepicker/index.html",
    ];
    foreach ($filesPath as $path) {
        $fileName = "$root_directory" . "$path";
        if (file_exists($fileName)) {
            shell_exec("rm -rf $fileName");
        }
    }
    echo "unwanted files..cleared.<br>";

    //update conditions column of vtiger_calendar_default_activitytypes
    $db->pquery('ALTER TABLE vtiger_calendar_default_activitytypes DROP COLUMN conditions', []);
    $db->pquery('ALTER TABLE vtiger_calendar_default_activitytypes ADD COLUMN conditions VARCHAR(255) DEFAULT ""', []);

    echo 'Conditions column in vtiger_calendar_default_activitytypes updated';
}
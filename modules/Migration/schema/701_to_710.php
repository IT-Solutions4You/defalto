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

    //START::Workflow task's template path
    $pathsList = [];
    $taskResult = $db->pquery('SELECT classname FROM com_vtiger_workflow_tasktypes', []);
    while ($rowData = $db->fetch_row($taskResult)) {
        $className = $rowData['classname'];
        if ($className) {
            $pathsList[$className] = vtemplate_path("Tasks/$className.tpl", 'Settings:Workflows');
        }
    }

    if ($pathsList) {
        $taskUpdateQuery = 'UPDATE com_vtiger_workflow_tasktypes SET templatepath = CASE';
        foreach ($pathsList as $className => $templatePath) {
            $taskUpdateQuery .= " WHEN classname='$className' THEN '$templatePath'";
        }
        $taskUpdateQuery .= ' ELSE templatepath END';
        $db->pquery($taskUpdateQuery, []);
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
    $db->pquery('UPDATE vtiger_tab SET allowduplicates=? WHERE name=?', [0, $accounts]);
    //End - Enable prevention for Accounts module

    $db->pquery('UPDATE vtiger_tab SET issyncable=1', []);
    $em = new VTEventsManager($db);
    $em->registerHandler('vtiger.entity.beforesave', 'modules/Vtiger/handlers/CheckDuplicateHandler.php', 'CheckDuplicateHandler');

    $em = new VTEventsManager($db);
    $em->registerHandler('vtiger.entity.beforerestore', 'modules/Vtiger/handlers/CheckDuplicateHandler.php', 'CheckDuplicateHandler');
    echo '<br>Succecssfully handled duplications<br>';
    //END::Duplication Prevention

    //START::Webform Attachements
    if (!Vtiger_Utils::CheckTable('vtiger_webform_file_fields')) {
        $db->pquery(
            'CREATE TABLE IF NOT EXISTS vtiger_webform_file_fields(id INT(19) NOT NULL AUTO_INCREMENT, webformid INT(19) NOT NULL, fieldname VARCHAR(100) NOT NULL, fieldlabel VARCHAR(100) NOT NULL, required INT(1) NOT NULL DEFAULT 0, PRIMARY KEY (id), KEY fk_vtiger_webforms (webformid), CONSTRAINT fk_vtiger_webforms FOREIGN KEY (webformid) REFERENCES vtiger_webforms (id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=UTF8;',
            []
        );
    }

    $operationResult = $db->pquery('SELECT 1 FROM vtiger_ws_operation WHERE name=?', ['add_related']);
    if (!$db->num_rows($operationResult)) {
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
    $restrictedModules = ['Dashboard', 'Home', 'Rss', 'Portal', 'Import'];
    foreach ($moduleModels as $moduleModel) {
        if (in_array($moduleModel->getName(), $restrictedModules)) {
            continue;
        }
        $moduleClass = CRMEntity::getInstance($moduleModel->getName());
        $baseTableId = isset($moduleClass->table_index) ? $moduleClass->table_index : null;
        if ($baseTableId) {
            $baseTableName = $moduleClass->table_name;
            $customTable = isset($moduleClass->customFieldTable) ? $moduleClass->customFieldTable : null;
            if (!$customTable) {
                continue;
            }
            $customTableName = $customTable[0];
            $customTableId = $customTable[1];
            $customTableColumns = $db->getColumnNames($customTableName);
            if (!empty($customTableColumns) && in_array($fieldName, $customTableColumns)) {
                $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
                $db->pquery("UPDATE vtiger_field SET tablename=? WHERE fieldid=?", [$baseTableName, $fieldModel->id]);
                $db->pquery("ALTER TABLE $baseTableName ADD COLUMN $fieldName VARCHAR(1)", []);

                $db->pquery(
                    "UPDATE $baseTableName, $customTableName SET $baseTableName.tags=$customTableName.tags WHERE $baseTableName.$baseTableId=$customTableName.$customTableId",
                    []
                );
                $db->pquery("ALTER TABLE $customTableName DROP COLUMN $fieldName", []);
            }
        }
    }
    echo '<br>Succecssfully generalized tag fields<br>';
    //END::Tag fields are pointed to cf table for the modules Assets, Services etc..

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

        $orderedPicklists = [];
        $i = 0;
        foreach ($timeZones as $timeZone => $value) {
            $orderedPicklists[$originalPicklistValues[$timeZone]] = $i++;
        }
        ksort($orderedPicklists);

        if (!empty($orderedPicklists)) {
            $moduleModel = new Settings_Picklist_Module_Model();
            $moduleModel->updateSequence($fieldName, $orderedPicklists);

            echo '<br>Succecssfully reordered timezones<br>';
        } else {
            echo '<br>Skipped reordered timezones<br>';
        }
    }
    //END::Reordering Timezones

    //START::Differentiate custom modules from Vtiger modules
    $vtigerTabColumns = $db->getColumnNames('vtiger_tab');
    if (!in_array('source', $vtigerTabColumns)) {
        $db->pquery('ALTER TABLE vtiger_tab ADD COLUMN source VARCHAR(255) DEFAULT "custom"', []);
    }
    $db->pquery('UPDATE vtiger_tab SET source=NULL', []);

    $packageModules = array_merge(['Project', 'ProjectTask', 'ProjectMilestone'], Install_Utils_Model::$registerModules); /* Projects zip is bundle */

    $db->pquery('UPDATE vtiger_tab SET source="custom" WHERE version IS NOT NULL AND name NOT IN (' . generateQuestionMarks($packageModules) . ')', $packageModules);
    echo '<br>Succecssfully added source column vtiger tab table<br>';
    //END::Differentiate custom modules from Vtiger modules

    //START::Centralize user field table for easy query with context of user across module
    $generalUserFieldTable = 'vtiger_crmentity_user_field';
    $migratedTables = [];
    $userTableResult = $db->pquery(
        'SELECT vtiger_tab.tabid, vtiger_tab.name, tablename, fieldid FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_field.tabid WHERE fieldname=?',
        ['starred']
    );
    while ($row = $db->fetch_array($userTableResult)) {
        $fieldId = $row['fieldid'];
        $moduleName = $row['name'];
        $oldTableName = $row['tablename'];

        $db->pquery('UPDATE vtiger_field SET tablename=? WHERE fieldid=? AND tablename=?', [$generalUserFieldTable, $fieldId, $oldTableName]);
        echo "Updated starred field for module $moduleName to point generic table => $generalUserFieldTable<br>";

        if (Vtiger_Utils::CheckTable($oldTableName)) {
            if (!in_array($oldTableName, $migratedTables)) {
                if ($oldTableName != $generalUserFieldTable) {
                    //Insert entries from module specific table to generic table for follow up records
                    $db->pquery(
                        "INSERT INTO $generalUserFieldTable (recordid, userid, starred) (SELECT recordid,userid,starred FROM $oldTableName INNER JOIN vtiger_crmentity ON $oldTableName.recordid = vtiger_crmentity.crmid)",
                        []
                    );
                    echo "entries moved from $oldTableName to $generalUserFieldTable table<br>";

                    //Drop module specific user table
                    $db->pquery("DROP TABLE $oldTableName", []);
                    echo "module specific user field table $oldTableName has been dropped<br>";
                    array_push($migratedTables, $oldTableName);
                }
            }
        }
    }
    echo '<br>Succesfully centralize user field table for easy query with context of user across module<br>';
    //END::Centralize user field table for easy query with context of user across module

    //START::Adding new parent TOOLS in menu
    $appsList = ['Tools' => ['Rss', 'Portal', 'RecycleBin']];
    foreach ($appsList as $appName => $appModules) {
        $menuInstance = Vtiger_Menu::getInstance($appName);
        foreach ($appModules as $moduleName) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            if ($moduleModel) {
                Settings_MenuEditor_Module_Model::addModuleToApp($moduleName, $appName);
                $menuInstance->addModule($moduleModel);
            }
        }
    }

    $tabResult1 = $db->pquery('SELECT tabid, name, parent FROM vtiger_tab WHERE presence IN (?, ?) AND source=?', [0, 2, 'custom']);
    while ($row = $db->fetch_row($tabResult1)) {
        $parentFromDb = $row['parent'];
        if ($parentFromDb) {
            $moduleName = $row['name'];
            $parentTabs = explode(',', $parentFromDb);
            foreach ($parentTabs as $parentTab) {
                Settings_MenuEditor_Module_Model::addModuleToApp($moduleName, $parentTab);
            }

            $menuTab = $parentTabs[0];
            $menuInstance = Vtiger_Menu::getInstance($menuTab);
            if ($menuInstance) {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
                $menuInstance->addModule($moduleModel);
            }
        }
    }

    //START::Supporting to store dashboard size
    $dashboardWidgetColumns = $db->getColumnNames('vtiger_module_dashboard_widgets');
    if (!in_array('size', $dashboardWidgetColumns)) {
        $db->pquery('ALTER TABLE vtiger_module_dashboard_widgets ADD COLUMN size VARCHAR(50)', []);
    }
    //END::Supporting to store dashboard size

    //START::Updating custom view and report columns, filters for createdtime and modifiedtime fields as typeofdata (T~...) is being transformed to (DT~...)
    $cvTables = ['vtiger_cvcolumnlist', 'vtiger_cvadvfilter'];
    foreach ($cvTables as $tableName) {
        $updatedColumnsList = [];
        $result = $db->pquery(
            "SELECT columnname FROM $tableName WHERE columnname LIKE ? OR columnname LIKE ?",
            ['vtiger_crmentity:createdtime%:T', 'vtiger_crmentity:modifiedtime%:T']
        );
        while ($rowData = $db->fetch_array($result)) {
            $columnName = $rowData['columnname'];
            if (!array_key_exists($columnName, $updatedColumnsList)) {
                if (preg_match('/vtiger_crmentity:createdtime:(\w*\:)*T/', $columnName) || preg_match('/vtiger_crmentity:modifiedtime:(\w*\:)*T/', $columnName)) {
                    $columnParts = explode(':', $columnName);
                    $lastKey = php7_count($columnParts) - 1;

                    if ($columnParts[$lastKey] == 'T') {
                        $columnParts[$lastKey] = 'DT';
                        $updatedColumnsList[$columnName] = implode(':', $columnParts);
                    }
                }
            }
        }

        if ($updatedColumnsList) {
            $cvQuery = "UPDATE $tableName SET columnname = CASE columnname";
            foreach ($updatedColumnsList as $oldColumnName => $newColumnName) {
                $cvQuery .= " WHEN '$oldColumnName' THEN '$newColumnName'";
            }
            $cvQuery .= ' ELSE columnname END';
            $db->pquery($cvQuery, []);
        }
        echo "<br>Succecssfully migrated columns in <b>$tableName</b> table<br>";
    }

    echo '<br>Succecssfully vtiger version updated to <b>7.1.0</b><br>';
}
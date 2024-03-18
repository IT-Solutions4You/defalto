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

    vimport('~modules/Users/CreateUserPrivilegeFile.php');
    $usersResult = $db->pquery("SELECT id FROM vtiger_users", array());
    $usersCount = $db->num_rows($usersResult);
    for($i = 0; $i < $usersCount; $i++) {
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
    $db->pquery('ALTER TABLE vtiger_systems MODIFY server_password text', array());
    
    //Migrate default module data from config editor to database
    $moduleModel = Settings_Vtiger_ConfigModule_Model::getInstance();
    $configFieldData = $moduleModel->getViewableData();
    $defaultModule = $configFieldData['default_module'];
    if(empty($defaultModule)){
        $defaultModule = 'Home';
    }

    $moduleInstance = Vtiger_Module_Model::getInstance('Users');
    $blockInstance = Vtiger_Block::getInstance('LBL_MORE_INFORMATION', $moduleInstance);
    if ($blockInstance) {
        $fieldInstance = Vtiger_Field::getInstance('defaultlandingpage', $moduleInstance);
        if (!$fieldInstance) {
            $fieldInstance = new Vtiger_Field();
            $fieldInstance->name		= 'defaultlandingpage';
            $fieldInstance->column		= 'defaultlandingpage';
            $fieldInstance->label		= 'Default Landing Page';
            $fieldInstance->table		= 'vtiger_users';
            $fieldInstance->columntype = 'VARCHAR(100)';
            $fieldInstance->defaultvalue = $defaultModule;
            $fieldInstance->typeofdata = 'V~O';
            $fieldInstance->uitype		= '32';
            $fieldInstance->presence	= '0';

            $blockInstance->addField($fieldInstance);
            $configModuleInstance = Settings_Vtiger_ConfigModule_Model::getInstance();
            $defaultModules = $configModuleInstance->getPicklistValues('default_module');
            $fieldInstance->setPicklistValues($defaultModules);
            echo "<br> Default landing page field added <br>";
        }
    }

    $allUsers = Users_Record_Model::getAll(true);
    $allUserIds = array_keys($allUsers);

    $db->pquery('UPDATE vtiger_users SET defaultlandingpage = ? WHERE id in ('. generateQuestionMarks($allUserIds) .')', array($defaultModule, $allUserIds));
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
    $params = array('69', 'image');
    $db->pquery($sql, $params);
    
    //add options to payment_duration field in SalesOrder module
    $moduleInstance = Vtiger_Module_Model::getInstance('SalesOrder');
    $fieldInstance = Vtiger_Field_Model::getInstance('payment_duration', $moduleInstance);
    $fieldInstance->setPicklistValues(array('Net 01 day', 'Net 05 days', 'Net 07 days', 'Net 10 days', 'Net 15 days'));
    
    $paymentList = array('Net 01 day' => '1', 'Net 05 days' => '2', 'Net 07 days' => '3', 'Net 10 days' => '4', 'Net 15 days' => '5',
                         'Net 30 days' => '6', 'Net 45 days' => '7', 'Net 60 days' => '8');
    $query = 'UPDATE vtiger_payment_duration SET sortorderid = CASE payment_duration';
    foreach ($paymentList as $label => $sortOrderId) {
        $query .= " WHEN '$label' THEN $sortOrderId ";
    }
    $query .= ' ELSE sortorderid END';
    $db->pquery($query, array());
    
    //Create new read-only field to display the date of the next invoice creation in recurring sales orders.
    $field  = new Vtiger_Field();
    $field->name = 'last_recurring_date';
    $field->label= 'Next Invoice Date';
    $field->column = 'last_recurring_date';
    $field->table = 'vtiger_invoice_recurring_info';
    $field->displaytype = 2;
    $field->uitype= 5;
    $field->columntype = "date";
    $field->typeofdata = 'D~O';
    
    $block = Vtiger_Block::getInstance('Recurring Invoice Information', $moduleInstance);
    $block->addField($field);
    
    //Remove unwanted Files
    global $root_directory;
    $filesPath = array(
            "layouts/v7/modules/Mobile/simple/resources/libs/md-icons/README.md",
            "layouts/v7/modules/Mobile/simple/resources/libs/md-icons/preview.html",
            "/layouts/v7/lib/jquery/Lightweight-jQuery-In-page-Filtering-Plugin-instaFilta/demo.html",
            "/layouts/v7/lib/vt-icons/demo.html",
            "/layouts/v7/lib/jquery/daterangepicker/index.html",
            "/layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/index.html",
            "/layouts/v7/lib/jquery/timepicker/index.html",
            "/libraries/bootstrap/js/tests",
            "/libraries/jquery/colorpicker/index.html",
            "/libraries/jquery/jquery-ui/third-party/jQuery-UI-Date-Range-Picker/index.html",
            "/libraries/jquery/timepicker/index.html",
    );
    foreach ($filesPath as $path){
            $fileName = "$root_directory"."$path";
            if (file_exists($fileName)) {
                    shell_exec("rm -rf $fileName");
            }
    }
    echo "unwanted files..cleared.<br>";
    
    //update conditions column of vtiger_calendar_default_activitytypes
    $db->pquery('ALTER TABLE vtiger_calendar_default_activitytypes DROP COLUMN conditions', array());
    $db->pquery('ALTER TABLE vtiger_calendar_default_activitytypes ADD COLUMN conditions VARCHAR(255) DEFAULT ""', array());
    
    echo 'Conditions column in vtiger_calendar_default_activitytypes updated';
}
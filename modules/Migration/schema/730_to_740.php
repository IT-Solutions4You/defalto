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
 
    $eventManager = new VTEventsManager($db);
    $className = 'Vtiger_RecordLabelUpdater_Handler';
    $eventManager->unregisterHandler($className);
    echo "Unregistered record label update handler.<br>";

    $moduleName = 'Users';
    $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
    $fieldName = 'userlabel';
    $blockModel = Vtiger_Block_Model::getInstance('LBL_MORE_INFORMATION', $moduleModel);
    if ($blockModel) {
        $fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
        if (!$fieldModel) {
            $fieldModel				= new Vtiger_Field();
            $fieldModel->name		= $fieldName;
            $fieldModel->label		= 'User Label';
            $fieldModel->table		= 'vtiger_users';
            $fieldModel->columntype = 'VARCHAR(255)';
            $fieldModel->typeofdata = 'V~O';
            $fieldModel->displaytype= 3;
            $blockModel->addField($fieldModel);
            echo "<br>Successfully added <b>$fieldName</b> field to <b>$moduleName</b><br>";
        }
    }
    $db->pquery("UPDATE vtiger_users SET $fieldName=TRIM(CONCAT(first_name, ' ' , last_name))", array());
    echo "<br>Successfully updated <b>$fieldName</b> value as concatenate of firstname and lastname for <b>$moduleName</b> module<br>";

    vimport('~modules/Users/CreateUserPrivilegeFile.php');
    $result = $db->pquery('SELECT id FROM vtiger_users', array());
    $count = $db->num_rows($result);
    while ($row = $db->fetch_array($result)) {
        $userId = $row['id'];
        createUserPrivilegesfile($userId);
        echo "<br>Successfully recreated <b>User's privileges</b> file for id:<b>$userId</b><br>";
    }
    echo "<br>Successfully completed concatenate of firstname and lastname as label in <b>$moduleName</b> module<br>";

}
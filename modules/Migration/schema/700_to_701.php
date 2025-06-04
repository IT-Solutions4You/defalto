<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

if(defined('VTIGER_UPGRADE')) {
	global $adb, $current_user;
	$db = PearDatabase::getInstance();
    $updateModulesList = [
        'Project',
        'Google',
    ];

    foreach ($updateModulesList as $moduleName) {
		Core_Install_Model::getInstance('module.postupdate', $moduleName)->installModule();
	}
}
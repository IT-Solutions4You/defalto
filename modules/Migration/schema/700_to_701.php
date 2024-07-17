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

	if (!Vtiger_Utils::CheckTable('vtiger_mailscanner')) {
		Vtiger_Utils::CreateTable('vtiger_mailscanner', 
				"(`scannerid` INT(11) NOT NULL AUTO_INCREMENT,
				`scannername` VARCHAR(30) DEFAULT NULL,
				`server` VARCHAR(100) DEFAULT NULL,
				`protocol` VARCHAR(10) DEFAULT NULL,
				`username` VARCHAR(255) DEFAULT NULL,
				`password` VARCHAR(255) DEFAULT NULL,
				`ssltype` VARCHAR(10) DEFAULT NULL,
				`sslmethod` VARCHAR(30) DEFAULT NULL,
				`connecturl` VARCHAR(255) DEFAULT NULL,
				`searchfor` VARCHAR(10) DEFAULT NULL,
				`markas` VARCHAR(10) DEFAULT NULL,
				`isvalid` INT(1) DEFAULT NULL,
				`scanfrom` VARCHAR(10) DEFAULT 'ALL',
				`time_zone` VARCHAR(10) DEFAULT NULL,
				PRIMARY KEY (`scannerid`)
			  ) ENGINE=InnoDB DEFAULT CHARSET=utf8", true);
	}

    $updateModulesList = [
        'Project',
        'Google',
    ];

    foreach ($updateModulesList as $moduleName) {
		Core_Install_Model::getInstance('module.postupdate', $moduleName)->installModule();
	}
}
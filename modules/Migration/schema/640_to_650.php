<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

if(defined('VTIGER_UPGRADE')) {

//Start add new currency - 'CFP Franc or Pacific Franc' 
global $adb;

Vtiger_Utils::AddColumn('vtiger_portalinfo', 'cryptmode', 'varchar(20)');

//Updating existing users password to thier md5 hash
$updateQuery = "UPDATE vtiger_portalinfo SET user_password=MD5(user_password),cryptmode='MD5' WHERE cryptmode is null";
$adb->pquery($updateQuery, array());

//Change column type of inventory line-item comment.
$adb->pquery("ALTER TABLE vtiger_inventoryproductrel MODIFY COLUMN comment TEXT", array());

}


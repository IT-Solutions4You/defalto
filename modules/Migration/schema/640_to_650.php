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
global $db;

//Updating existing users password to thier md5 hash

$selectQuery = 'SELECT 1 FROM vtiger_portalinfo';
$resultSet = $db->pquery($selectQuery, array());
if ($db->num_rows($resultSet) > 0) {
    $updateQuery = 'UPDATE vtiger_portalinfo SET user_password=MD5(user_password)';
    $db->pquery($updateQuery, array());
}
}
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
}
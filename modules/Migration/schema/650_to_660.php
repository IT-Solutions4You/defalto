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
global $adb;

// Migration for - #117 - Convert lead field mapping NULL values and redundant rows
$phoneFieldId = getFieldid(getTabid('Leads'), 'phone');
$db->pquery('UPDATE vtiger_convertleadmapping SET editable=? WHERE leadfid=?', array(1, $phoneFieldId));

}


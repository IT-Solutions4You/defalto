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

    // Remove unused column from user table
    $columns = $db->getColumnNames('vtiger_users');
    if (in_array('user_hash', $columns)) {
        $db->pquery('ALTER TABLE vtiger_users DROP COLUMN user_hash', []);
    }

    // Resizing column to hold wider string value.
    $db->pquery('ALTER TABLE vtiger_systems MODIFY server_password VARCHAR(255)', []);
}
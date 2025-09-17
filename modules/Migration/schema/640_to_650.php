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
//Start add new currency - 'CFP Franc or Pacific Franc'
    global $adb;

    Vtiger_Utils::AddColumn('vtiger_portalinfo', 'cryptmode', 'varchar(20)');
    $adb->pquery("ALTER TABLE vtiger_portalinfo MODIFY COLUMN user_password varchar(255)", []);

//Updating existing users password to thier md5 hash
    $portalinfo_hasmore = true;
    do {
        $result = $adb->pquery('SELECT id, user_password FROM vtiger_portalinfo WHERE cryptmode is null limit 1000', []);

        $portalinfo_hasmore = false; // assume we are done.
        while ($row = $adb->fetch_array($result)) {
            $portalinfo_hasmore = true; // we found at-least one so there could be more.

            $enc_password = Vtiger_Functions::generateEncryptedPassword(decode_html($row['user_password']));
            $adb->pquery('UPDATE vtiger_portalinfo SET user_password=?, cryptmode = ? WHERE id=?', [$enc_password, 'CRYPT', $row['id']]);
        }
    } while ($portalinfo_hasmore);

//Change column type of inventory line-item comment.
    $adb->pquery("ALTER TABLE vtiger_inventoryproductrel MODIFY COLUMN comment TEXT", []);

// Initlize mailer_queue tables.
    include_once 'vtlib/Vtiger/Mailer.php';
    $mailer = new Vtiger_Mailer();
    $mailer->__initializeQueue();

// Extend description data-type (eg. allow large emails to be stored)
    $adb->pquery("ALTER TABLE vtiger_crmentity MODIFY COLUMN description MEDIUMTEXT", []);
}
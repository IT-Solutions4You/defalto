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

//set settings links, fixes translation issue on migrations from 5.x
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Users&parent=Settings&view=List' where name='LBL_USERS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Roles&parent=Settings&view=Index' where name='LBL_ROLES'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Profiles&parent=Settings&view=List' where name='LBL_PROFILES'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Groups&parent=Settings&view=List' where name='USERGROUPLIST'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=SharingAccess&parent=Settings&view=Index' where name='LBL_SHARING_ACCESS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=FieldAccess&parent=Settings&view=Index' where name='LBL_FIELDS_ACCESS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=LoginHistory&parent=Settings&view=List' where name='LBL_LOGIN_HISTORY_DETAILS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=ModuleManager&parent=Settings&view=List' where name='VTLIB_LBL_MODULE_MANAGER'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=Picklist&view=Index' where name='LBL_PICKLIST_EDITOR'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=PickListDependency&view=List' where name='LBL_PICKLIST_DEPENDENCY_SETUP'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=MenuEditor&parent=Settings&view=Index' where name='LBL_MENU_EDITOR'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Settings&view=listnotificationschedulers&parenttab=Settings' where name='NOTIFICATIONSCHEDULERS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=Vtiger&view=CompanyDetails' where name='LBL_COMPANY_DETAILS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail' where name='LBL_MAIL_SERVER_SETTINGS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=Currency&view=List' where name='LBL_CURRENCY_SETTINGS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Settings&submodule=Server&view=ProxyConfig' where name='LBL_SYSTEM_INFO'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit' where name='LBL_ANNOUNCEMENT'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Settings&action=DefModuleView&parenttab=Settings' where name='LBL_DEFAULT_MODULE_VIEW'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit' where name='INVENTORYTERMSANDCONDITIONS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering' where name='LBL_CUSTOMIZE_MODENT_NUMBER'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?parent=Settings&module=MailConverter&view=List' where name='LBL_MAIL_SCANNER'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Workflows&parent=Settings&view=List' where name='LBL_LIST_WORKFLOWS'", []);
    $adb->pquery("Update vtiger_settings_field set linkto='index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail' where name='LBL_CONFIG_EDITOR'", []);

// Extend description data-type (eg. allow large emails to be stored)
    $adb->pquery("ALTER TABLE vtiger_crmentity MODIFY COLUMN description MEDIUMTEXT", []);
}
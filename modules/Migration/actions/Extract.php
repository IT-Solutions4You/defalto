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

class Migration_Extract_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function isLoginRequired(): bool
    {
        return false;
    }

    public function process(Vtiger_Request $request)
    {
        global $root_directory, $log;
        @session_start();
        $userName = $request->get('username');
        $password = $request->get('password');

        $user = CRMEntity::getInstance('Users');
        $user->column_fields['user_name'] = $userName;
        $userid = $user->retrieve_user_id($userName);
        $userRecordModel = Users_Privileges_Model::getInstanceById($userid, 'Users');

        if ($userRecordModel->isAdminUser()) {
            if ($user->doLogin($password)) {
                $zip = new ZipArchive();
                $fileName = 'vtiger8.zip';

                if ($zip->open($fileName)) {
                    if ($zip->extractTo($root_directory)) {
                        $zip->close();
                        $_SESSION['authenticated_user_id'] = $userid;
                        $_SESSION['app_unique_key'] = vglobal('application_unique_key');

                        /* Give time for PHP runtime to pickup new changes after zip
                         * for files that are include/require once previously */
                        sleep(5);

                        header('Location: index.php?module=Migration&view=Index&mode=step1');
                    } else {
                        $errorMessage = 'ERROR EXTRACTING MIGRATION ZIP FILE!';
                        header('Location: migrate/index.php?error=' . $errorMessage);
                    }
                } else {
                    $errorMessage = 'ERROR READING MIGRATION ZIP FILE!';
                    header('Location: migrate/index.php?error=' . $errorMessage);
                }
            } else {
                $errorMessage = 'INVALID CREDENTIALS';
                header('Location: migrate/index.php?error=' . $errorMessage);
            }
        } else {
            $errorMessage = 'PERMISSION DENIED! ONLY ADMIN USERS CAN ACCESS';
            header('Location: migrate/index.php?error=' . $errorMessage);
        }
    }
}
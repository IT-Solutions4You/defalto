<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Migration_Extract_Action extends Vtiger_Action_Controller
{

    public function loginRequired()
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
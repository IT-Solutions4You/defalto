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

chdir(__DIR__ . "/../../../");

require_once 'vendorCheck.php';
require_once 'vendor/autoload.php';
include_once "include/utils/VtlibUtils.php";
include_once "include/utils/CommonUtils.php";
include_once 'includes/runtime/BaseModel.php';
include_once 'includes/runtime/Viewer.php';
include_once "includes/http/Request.php";
include_once "include/Webservices/Custom/ChangePassword.php";
include_once "include/Webservices/Utils.php";
include_once "includes/runtime/EntryPoint.php";

class Users_ForgotPassword_Action
{
    public function changePassword($request)
    {
        $request = new Vtiger_Request($request);
        $viewer = Vtiger_Viewer::getInstance();
        $userName = $request->get('username');
        $newPassword = $request->get('password');
        $confirmPassword = $request->get('confirmPassword');
        $shortURLID = $request->get('shorturl_id');
        $secretHash = $request->get('secret_hash');
        $shortURLModel = Vtiger_ShortURL_Helper::getInstance($shortURLID);
        $secretToken = $shortURLModel->handler_data['secret_token'];

        $validateData = [
            'username'     => $userName,
            'secret_token' => $secretToken,
            'secret_hash'  => $secretHash
        ];

        $valid = $shortURLModel->compareEquals($validateData);
        if ($valid) {
            $userId = getUserId_Ol($userName);
            $user = Users::getActiveAdminUser();
            $wsUserId = vtws_getWebserviceEntityId('Users', $userId);
            try {
                vtws_changePassword($wsUserId, '', $newPassword, $confirmPassword, $user);
            } catch (Exception $e) {
                $viewer->assign('ERROR', true);
                $viewer->assign('MESSAGE', html_entity_decode($e->getMessage()));
            }
        } else {
            $viewer->assign('ERROR', true);
            $viewer->assign('MESSAGE', 'Error, please retry setting the password!!');
        }
        $shortURLModel->delete();
        $viewer->assign('USERNAME', $userName);
        $viewer->assign('PASSWORD', $newPassword);
        $viewer->view('FPLogin.tpl', 'Users');
    }

    public static function run($request)
    {
        $instance = new self();
        $instance->changePassword($request);
    }
}

Users_ForgotPassword_Action::run($_REQUEST);
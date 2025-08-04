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

class Users_ForgotPassword_Handler
{
    public function changePassword($data)
    {
        global $site_URL;
        $request = new Vtiger_Request($data);
        $viewer = Vtiger_Viewer::getInstance();
        $companyModel = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyName = $companyModel->get('organizationname');
        $organisationDetails = $companyModel->getLogo();
        $logoTitle = $organisationDetails->get('title');
        $logoName = $organisationDetails->get('imagepath');
        $moduleName = 'Users';
        $viewer->assign('LOGOURL', $site_URL . $logoName);
        $viewer->assign('TITLE', $logoTitle);
        $viewer->assign('COMPANYNAME', $companyName);
        $viewer->assign('USERNAME', $request->get('username'));
        $changePasswordTrackUrl = $site_URL . "modules/Users/actions/ForgotPassword.php";
        $viewer->assign('TRACKURL', $changePasswordTrackUrl);
        $viewer->view('ForgotPassword.tpl', $moduleName);
    }
}
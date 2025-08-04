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

class Users_Logout_Action extends Vtiger_Action_Controller
{
    function checkPermission(Vtiger_Request $request)
    {
        return true;
    }

    function process(Vtiger_Request $request)
    {
        //Redirect into the referer page
        $logoutURL = $this->getLogoutURL();
        session_regenerate_id(true);
        Vtiger_Session::destroy();

        //Track the logout History
        $moduleName = $request->getModule();
        $moduleModel = Users_Module_Model::getInstance($moduleName);
        $moduleModel->saveLogoutHistory();
        //End

        if (!empty($logoutURL)) {
            header('Location: ' . $logoutURL);
            exit();
        } else {
            header('Location: index.php');
        }
    }

    protected function getLogoutURL()
    {
        $logoutUrl = Vtiger_Session::get('LOGOUT_URL');
        if (isset($logoutUrl) && !empty($logoutUrl)) {
            return $logoutUrl;
        }

        return VtigerConfig::getOD('LOGIN_URL');
    }
}
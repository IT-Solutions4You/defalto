<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class CustomerPortal_ChangePassword extends CustomerPortal_API_Abstract
{

    function process(CustomerPortal_API_Request $request)
    {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();

        if ($current_user) {
            $current_customer = $this->getActiveCustomer();
            $username = $this->getActiveCustomer()->username;
            $password = $request->get('password');

            if (!$this->authenticatePortalUser($username, $password)) {
                throw new Exception("Wrong password.Please try again", 1412);
                exit;
            }

            $newPassword = $request->get('newPassword');
            $sql = "UPDATE vtiger_portalinfo SET user_password=? WHERE id=? AND user_name=?";
            $adb->pquery($sql, [Vtiger_Functions::generateEncryptedPassword($newPassword), $current_customer->id, $username]);
            $response->setResult('Password changed successfully');
        }

        return $response;
    }
}
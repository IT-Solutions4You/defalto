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

class CustomerPortal_UpdateLoginDetails extends CustomerPortal_API_Abstract
{
    function process(CustomerPortal_API_Request $request)
    {
        $response = new CustomerPortal_API_Response();
        $customer = $this->getActiveCustomer();
        $status = $request->get('status');
        global $adb;
        $currentTime = $adb->formatDate(date('YmdHis'), true);

        if ($status == 'Login') {
            $sql = 'UPDATE vtiger_portalinfo SET login_time=? WHERE id=?';
            $adb->pquery($sql, [$currentTime, $customer->id]);
        } elseif ($status == 'Logout') {
            $sql = 'UPDATE vtiger_portalinfo SET logout_time=?, last_login_time=login_time WHERE id=?';
            $adb->pquery($sql, [$currentTime, $customer->id]);
        }

        $response->setResult(true);

        return $response;
    }
}
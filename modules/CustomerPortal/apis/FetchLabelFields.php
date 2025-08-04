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

class CustomerPortal_FetchLabelFields extends CustomerPortal_API_Abstract
{
    function process(CustomerPortal_API_Request $request)
    {
        $current_user = $this->getActiveUser();
        $response = new CustomerPortal_API_Response();
        global $adb;

        if ($current_user) {
            $sql = "SELECT tabid FROM vtiger_customerportal_tabs WHERE visible=? ";
            $sqlResult = $adb->pquery($sql, [1]);
            $num_rows = $adb->num_rows($sqlResult);
            $result = [];

            for ($i = 0; $i < $num_rows; $i++) {
                $moduleId = $adb->query_result($sqlResult, $i, 'tabid');
                $module = Vtiger_Functions::getModuleName($moduleId);
                $describe = vtws_describe($module, $current_user);
                $labelFields = explode(',', $describe['labelFields']);
                $result[] = [$module => $labelFields];
            }
        }
        $response->setResult($result);

        return $response;
    }
}
<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class Google_Authenticate_View extends Vtiger_Index_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $moduleName = $request->getModule();

        $recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'index');

        if (!$recordPermission) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $oauth2Connector = new Google_Oauth2_Connector($moduleName);
        $oauth2Connector->authorize();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        /* Ignore check - as referer could be CRM or Google Accounts */
        return true;
    }
}
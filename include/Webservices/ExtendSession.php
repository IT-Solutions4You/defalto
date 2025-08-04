<?php
/************************************************************************************
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

/**
 * @return array
 * @throws WebServiceException
 */
function vtws_extendSession()
{
    global $adb, $API_VERSION, $application_unique_key;
    
    if ($_SESSION['authenticatedUserId'] || (isset($_SESSION["authenticated_user_id"]) && $_SESSION["app_unique_key"] == $application_unique_key)) {
        $userId = ($_SESSION["authenticated_user_id"]) ? : $_SESSION['authenticatedUserId'];
        unset($_SESSION['__CRM_Session_Expire_TS']);
        $sessionManager = new SessionManager();
        SessionManager::set("authenticatedUserId", $userId);
        $crmObject = VtigerWebserviceObject::fromName($adb, "Users");
        $userId = vtws_getId($crmObject->getEntityId(), $userId);
        $vtigerVersion = vtws_getVtigerVersion();

        return ["sessionName" => $sessionManager->getSessionId(), "userId" => $userId, "version" => $API_VERSION, "vtigerVersion" => $vtigerVersion];
    }

    throw new WebServiceException(WebServiceErrorCode::$AUTHFAILURE, "Authencation Failed");
}
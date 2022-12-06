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
 * Portions created by IT-Solutions4You s.r.o. are Copyright (C) IT-Solutions4You s.r.o.
 */

/**
 * @param $sessionId
 * @param $user
 *
 * @return string[]
 * @throws WebServiceException
 */
function vtws_logout($sessionId, $user)
{
    global $adb;
    $sql = "select type from vtiger_ws_operation where name=?";
    $result = $adb->pquery($sql, ["logout"]);
    $row = $adb->query_result_rowdata($result, 0);
    $requestType = $row['type'];
    if ($_SERVER['REQUEST_METHOD'] != $requestType) {
        throw new WebServiceException(WebServiceErrorCode::$OPERATIONNOTSUPPORTED, "Permission to perform the operation is denied");
    }
    $sessionManager = new SessionManager();
    $sid = $sessionManager->startSession($sessionId);

    if (!isset($sessionId) || !$sessionManager->isValid()) {
        return $sessionManager->getError();
    }

    SessionManager::destroy();

//	$sessionManager->setExpire(1);
    return ["message" => "successfull"];
}

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

function vtws_describe($elementType, $user)
{
    global $log, $adb, $app_strings;

    //setting $app_strings
    if (!$app_strings) {
        $currentLanguage = Vtiger_Language_Handler::getLanguage();
        $moduleLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage);
        $app_strings = $moduleLanguageStrings['languageStrings'];
    }

    $webserviceObject = VtigerWebserviceObject::fromName($adb, $elementType);
    $handlerPath = $webserviceObject->getHandlerPath();
    $handlerClass = $webserviceObject->getHandlerClass();

    require_once $handlerPath;

    $handler = new $handlerClass($webserviceObject, $user, $adb, $log);
    $meta = $handler->getMeta();

    $types = vtws_listtypes(null, $user);
    if (!in_array($elementType, $types['types'])) {
        throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to perform the operation is denied");
    }

    $entity = $handler->describe($elementType);
    VTWS_PreserveGlobal::flush();

    return $entity;
}
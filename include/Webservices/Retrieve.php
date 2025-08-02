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

function vtws_retrieve($id, $user)
{
    global $log, $adb;

    $webserviceObject = VtigerWebserviceObject::fromId($adb, $id);
    $handlerPath = $webserviceObject->getHandlerPath();
    $handlerClass = $webserviceObject->getHandlerClass();

    require_once $handlerPath;

    $handler = new $handlerClass($webserviceObject, $user, $adb, $log);
    $meta = $handler->getMeta();
    $entityName = $meta->getObjectEntityName($id);
    $types = vtws_listtypes(null, $user);
    if (!in_array($entityName, $types['types'])) {
        throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to perform the operation is denied");
    }
    if ($meta->hasReadAccess() !== true) {
        throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to write is denied");
    }

    if ($entityName !== $webserviceObject->getEntityName()) {
        throw new WebServiceException(WebServiceErrorCode::$INVALIDID, "Id specified is incorrect");
    }

    if (!$meta->hasPermission(EntityMeta::$RETRIEVE, $id)) {
        throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to read given object is denied");
    }

    $idComponents = vtws_getIdComponents($id);
    if (!$meta->exists($idComponents[1])) {
        throw new WebServiceException(WebServiceErrorCode::$RECORDNOTFOUND, "Record you are trying to access is not found");
    }

    $entity = $handler->retrieve($id);
    VTWS_PreserveGlobal::flush();

    return $entity;
}
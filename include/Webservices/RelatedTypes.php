<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
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

function vtws_relatedtypes($elementType, $user)
{
    global $adb, $log;

    $allowedTypes = vtws_listtypes(null, $user);

    $webserviceObject = VtigerWebserviceObject::fromName($adb, $elementType);
    $handlerPath = $webserviceObject->getHandlerPath();
    $handlerClass = $webserviceObject->getHandlerClass();

    require_once $handlerPath;
    $handler = new $handlerClass($webserviceObject, $user, $adb, $log);
    $meta = $handler->getMeta();
    $tabid = $meta->getTabId();

    $sql = "SELECT vtiger_relatedlists.label, vtiger_tab.name, vtiger_tab.isentitytype FROM vtiger_relatedlists 
            INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_relatedlists.related_tabid 
            WHERE vtiger_relatedlists.tabid=? AND vtiger_tab.presence = 0 AND vtiger_relatedlists.presence = 0";

    $params = [$tabid];
    $rs = $adb->pquery($sql, $params);

    $return = ['types' => [], 'information' => []];

    while ($row = $adb->fetch_array($rs)) {
        if (in_array($row['name'], $allowedTypes['types'])) {
            $return['types'][] = $row['name'];
            // There can be same module related under different label - so label is our key.
            $return['information'][$row['label']] = [
                'name'     => $row['name'],
                'label'    => $row['label'],
                'isEntity' => $row['isentitytype']
            ];
        }
    }

    return $return;
}
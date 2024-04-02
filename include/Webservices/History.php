<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

// vtws_getWebserviceEntityId - seem to be missing the optimization
// which could pose performance challenge while gathering the changes made
// this helper function targets to cache and optimize the transformed values.
function vtws_history_entityIdHelper($moduleName, $id)
{
    static $wsEntityIdCache = null;

    if ($wsEntityIdCache === null) {
        $wsEntityIdCache = ['users' => [], 'records' => []];
    }

    if (!isset($wsEntityIdCache[$moduleName][$id])) {
        // Determine moduleName based on $id
        if (empty($moduleName)) {
            $moduleName = getSalesEntityType($id);
        }

        $wsEntityIdCache[$moduleName][$id] = vtws_getWebserviceEntityId($moduleName, $id);
    }

    return $wsEntityIdCache[$moduleName][$id];
}
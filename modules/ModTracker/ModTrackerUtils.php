<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
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

require_once('include/utils/utils.php');
require_once 'vtlib/Vtiger/Module.php';
require_once dirname(__FILE__) . '/ModTracker.php';

class ModTrackerUtils
{
    static function modTrac_changeModuleVisibility($tabid, $status)
    {
        if ($status == 'module_disable') {
            ModTracker::disableTrackingForModule($tabid);
        } else {
            ModTracker::enableTrackingForModule($tabid);
        }
    }

    function modTrac_getModuleinfo()
    {
        global $adb;
        $query = $adb->pquery(
            "SELECT vtiger_modtracker_tabs.visible,vtiger_tab.name,vtiger_tab.tabid
								FROM vtiger_tab
								LEFT JOIN vtiger_modtracker_tabs ON vtiger_modtracker_tabs.tabid = vtiger_tab.tabid
								WHERE vtiger_tab.isentitytype = 1",
            []
        );
        $rows = $adb->num_rows($query);

        for ($i = 0; $i < $rows; $i++) {
            $infomodules[$i]['tabid'] = $adb->query_result($query, $i, 'tabid');
            $infomodules[$i]['visible'] = $adb->query_result($query, $i, 'visible');
            $infomodules[$i]['name'] = $adb->query_result($query, $i, 'name');
        }

        return $infomodules;
    }
}
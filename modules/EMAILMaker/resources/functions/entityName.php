<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!function_exists('pdfmakerGetEntityName')) {
    function pdfmakerGetEntityName($entityid)
    {
        global $adb;
        $result = $adb->pquery("SELECT setype FROM vtiger_crmentity WHERE crmid=?", [$entityid]);
        $row = $adb->fetchByAssoc($result);
        $return = getEntityName($row['setype'], [$entityid]);

        return $return[$entityid];
    }
}

<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!function_exists('pdfmakerGetEntityName')) {
    function pdfmakerGetEntityName($entityid)
    {
        global $adb;
        $result = $adb->pquery("SELECT setype FROM vtiger_crmentity WHERE crmid=?", array($entityid));
        $row = $adb->fetchByAssoc($result);
        $return = getEntityName($row['setype'], array($entityid));
        return $return[$entityid];
    }
}

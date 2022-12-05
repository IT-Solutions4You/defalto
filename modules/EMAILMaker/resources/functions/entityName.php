<?php

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

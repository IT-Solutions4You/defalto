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

class Assets_Module_Model extends Vtiger_Module_Model
{
    public function getQueryByModuleField($sourceModule, $field, $record, $listQuery)
    {
        if ($sourceModule == 'HelpDesk') {
            $condition = " vtiger_assets.assetsid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ";
            $db = PearDatabase::getInstance();
            $condition = $db->convert2Sql($condition, [$record, $record]);

            $pos = stripos($listQuery, 'where');
            if ($pos) {
                $split = preg_split('/where/i', $listQuery);
                $overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
            } else {
                $overRideQuery = $listQuery . ' WHERE ' . $condition;
            }

            return $overRideQuery;
        }
    }

    /*
     * Function to get supported utility actions for a module
     */
    public function getUtilityActionsNames()
    {
        return ['Import', 'Export', 'DuplicatesHandling'];
    }
}
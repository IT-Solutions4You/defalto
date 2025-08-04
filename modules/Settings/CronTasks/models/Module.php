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

class Settings_CronTasks_Module_Model extends Settings_Vtiger_Module_Model
{
    var $baseTable = 'vtiger_cron_task';
    var $baseIndex = 'id';
    var $listFields = ['sequence' => 'Sequence', 'name' => 'Cron Job', 'frequency' => 'Frequency(H:M)', 'status' => 'Status', 'laststart' => 'Last Start', 'lastend' => 'Last End'];
    var $nameFields = [''];
    var $name = 'CronTasks';

    /**
     * Function to get editable fields from this module
     * @return <Array> List of fieldNames
     */
    public function getEditableFieldsList()
    {
        return ['frequency', 'status'];
    }

    /**
     * Function to update sequence of several records
     *
     * @param <Array> $sequencesList
     */
    public function updateSequence($sequencesList)
    {
        $db = PearDatabase::getInstance();

        $updateQuery = "UPDATE vtiger_cron_task SET sequence = CASE";

        foreach ($sequencesList as $sequence => $recordId) {
            $updateQuery .= " WHEN id = $recordId THEN $sequence ";
        }
        $updateQuery .= " END";
        $db->pquery($updateQuery, []);
    }

    public function hasCreatePermissions()
    {
        return false;
    }

    public function isPagingSupported()
    {
        return false;
    }
}
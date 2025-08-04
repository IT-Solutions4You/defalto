<?php
/************************************************************************************
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

class Campaigns_Record_Model extends Vtiger_Record_Model
{
    /**
     * Function to get selected ids list of related module for send email
     *
     * @param <String> $relatedModuleName
     * @param <array>  $excludedIds
     *
     * @return <array> List of selected ids
     */
    public function getSelectedIdsList($relatedModuleName, $excludedIds = false)
    {
        $db = PearDatabase::getInstance();

        switch ($relatedModuleName) {
            case "Leads"        :
                $tableName = "vtiger_campaignleadrel";
                $fieldName = "leadid";
                break;
            case "Accounts"        :
                $tableName = "vtiger_campaignaccountrel";
                $fieldName = "accountid";
                break;
            case 'Contacts'        :
                $tableName = "vtiger_campaigncontrel";
                $fieldName = "contactid";
                break;
        }

        $query = "SELECT $fieldName FROM $tableName
					INNER JOIN vtiger_crmentity ON $tableName.$fieldName = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = ?
					WHERE campaignid = ?";
        $params = [0, $this->getId()];
        if ($excludedIds) {
            $query .= " AND $fieldName NOT IN (" . generateQuestionMarks($excludedIds) . ")";
            $params = array_merge($params, $excludedIds);
        }

        $result = $db->pquery($query, $params);
        $numOfRows = $db->num_rows($result);

        $selectedIdsList = [];
        for ($i = 0; $i < $numOfRows; $i++) {
            $selectedIdsList[] = $db->query_result($result, $i, $fieldName);
        }

        return $selectedIdsList;
    }
}
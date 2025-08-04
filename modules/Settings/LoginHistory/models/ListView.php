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

class Settings_LoginHistory_ListView_Model extends Settings_Vtiger_ListView_Model
{
    /**
     * Funtion to get the Login history basic query
     * @return type
     */
    public function getBasicListQuery()
    {
        $db = PearDatabase::getInstance();
        $module = $this->getModule();
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $query = "SELECT login_id, $userNameSql AS user_name, user_ip, logout_time, login_time, vtiger_loginhistory.status FROM $module->baseTable 
				INNER JOIN vtiger_users ON vtiger_users.user_name = $module->baseTable.user_name";

        $search_key = $this->get('search_key');
        $value = Vtiger_Functions::realEscapeString($this->get('search_value'));

        $params = [];
        if (!empty($search_key) && !empty($value)) {
            $query .= " WHERE $module->baseTable.$search_key = ?";
            $params[] = $value;
        }
        $query .= " ORDER BY login_time DESC";

        return $db->convert2Sql($query, $params);
    }

    public function getListViewLinks()
    {
        return [];
    }

    /**
     * Function which will get the list view count
     * @return - number of records
     */

    public function getListViewCount()
    {
        $db = PearDatabase::getInstance();

        $module = $this->getModule();
        $listQuery = "SELECT count(*) AS count FROM $module->baseTable INNER JOIN vtiger_users ON vtiger_users.user_name = $module->baseTable.user_name";

        $search_key = $this->get('search_key');
        $value = $this->get('search_value');
        $params = [];
        if (!empty($search_key) && !empty($value)) {
            $listQuery .= " WHERE $module->baseTable.$search_key = ?";
            $params[] = $value;
        }

        $listResult = $db->pquery($listQuery, $params);

        return $db->query_result($listResult, 0, 'count');
    }
}
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

class Vtiger_CRMEntity extends CRMEntity
{

    // Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
    var $sortby_fields = [];

    // Required Information for enabling Import feature
    var $required_fields = ['assigned_user_id' => 1];

    // Callback function list during Importing
    var $special_functions = ['set_import_assigned_user'];

    /**
     * Return query to use based on given modulename, fieldname
     * Useful to handle specific case handling for Popup
     */
    function getQueryByModuleField($module, $fieldname, $srcrecord)
    {
        // $srcrecord could be empty
    }

    /**
     * Get list view query.
     */
    function getListQuery($module, $where = '')
    {
        $query = "SELECT vtiger_crmentity.*, $this->table_name.*";

        // Select Custom Field Table Columns if present
        if (!empty($this->customFieldTable)) {
            $query .= ", " . $this->customFieldTable[0] . ".* ";
        }

        $query .= " FROM $this->table_name";

        $query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

        // Consider custom table join as well.
        if (!empty($this->customFieldTable)) {
            $query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
        }
        $query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id";
        $query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";

        $linkedModulesQuery = $this->db->pquery(
            "SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
            " INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
            " WHERE uitype='10' AND vtiger_fieldmodulerel.module=?",
            [$module]
        );
        $linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

        for ($i = 0; $i < $linkedFieldsCount; $i++) {
            $related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
            $fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
            $columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

            $other = CRMEntity::getInstance($related_module);
            vtlib_setup_modulevars($related_module, $other);

            $query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index =" .
                "$this->table_name.$columnname";
        }

        global $current_user;
        $query .= $this->getNonAdminAccessControlQuery($module, $current_user);
        $query .= "WHERE vtiger_crmentity.deleted = 0 " . $where;

        return $query;
    }

    /**
     * Apply security restriction (sharing privilege) query part for List view.
     */
    function getListViewSecurityParameter($module)
    {
        global $current_user;
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

        $sec_query = '';
        $tabid = getTabid($module);

        if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
            && $defaultOrgSharingPermission[$tabid] == 3) {
            $sec_query .= " AND (vtiger_crmentity.assigned_user_id in($current_user->id) OR vtiger_crmentity.assigned_user_id IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
					)
					OR vtiger_crmentity.assigned_user_id IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=" . $current_user->id . " AND tabid=" . $tabid . "
					)
					OR
						(";

            // Build the query based on the group association of current user.
            if (php7_count($current_user_groups) > 0) {
                $sec_query .= " vtiger_crmentity.assigned_user_id IN (" . implode(",", $current_user_groups) . ") OR ";
            }
            $sec_query .= " vtiger_crmentity.assigned_user_id IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=" . $current_user->id . " and tabid=" . $tabid . "
						)";
            $sec_query .= ")
				)";
        }

        return $sec_query;
    }

    /**
     * Create query to export the records.
     */
    function create_export_query($where)
    {
        global $current_user, $currentModule;

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery('ServiceContracts', "detail_view");

        $fields_list = getFieldsListFromQuery($sql);

        $query = "SELECT $fields_list, vtiger_users.user_name AS user_name
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

        if (!empty($this->customFieldTable)) {
            $query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
        }

        $query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";
        $query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id = vtiger_users.id and " .
            "vtiger_users.status='Active'";

        $linkedModulesQuery = $this->db->pquery(
            "SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
            " INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
            " WHERE uitype='10' AND vtiger_fieldmodulerel.module=?",
            [$thismodule]
        );
        $linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

        for ($i = 0; $i < $linkedFieldsCount; $i++) {
            $related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
            $fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
            $columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

            $other = CRMEntity::getInstance($related_module);
            vtlib_setup_modulevars($related_module, $other);

            $query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = " .
                "$this->table_name.$columnname";
        }

        $query .= $this->getNonAdminAccessControlQuery($thismodule, $current_user);
        $where_auto = " vtiger_crmentity.deleted=0";

        if ($where != '') {
            $query .= " WHERE ($where) AND $where_auto";
        } else {
            $query .= " WHERE $where_auto";
        }

        return $query;
    }

    /**
     * Function which will give the basic query to find duplicates
     */
    function getDuplicatesQuery($module, $table_cols, $field_values, $ui_type_arr, $select_cols = '')
    {
        $select_clause = "SELECT " . $this->table_name . "." . $this->table_index . " AS recordid, vtiger_users_last_import.deleted," . $table_cols;

        // Select Custom Field Table Columns if present
        if (isset($this->customFieldTable)) {
            $query .= ", " . $this->customFieldTable[0] . ".* ";
        }

        $from_clause = " FROM $this->table_name";

        $from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

        // Consider custom table join as well.
        if (isset($this->customFieldTable)) {
            $from_clause .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
        }
        $from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";

        $where_clause = "	WHERE vtiger_crmentity.deleted = 0";
        $where_clause .= $this->getListViewSecurityParameter($module);

        if (isset($select_cols) && trim($select_cols) != '') {
            $sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
                " INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
            // Consider custom table join as well.
            if (isset($this->customFieldTable)) {
                $sub_query .= " INNER JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
            }
            $sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
        } else {
            $sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
        }

        $query = $select_clause . $from_clause .
            " LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
            " INNER JOIN (" . $sub_query . ") AS temp ON " . get_on_clause($field_values, $ui_type_arr, $module) .
            $where_clause .
            " ORDER BY $table_cols," . $this->table_name . "." . $this->table_index . " ASC";

        return $query;
    }
}
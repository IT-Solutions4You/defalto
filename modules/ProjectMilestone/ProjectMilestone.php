<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectMilestone extends CRMEntity
{
    public string $moduleName = 'ProjectMilestone';
    public string $parentName = 'PROJECT';

    var $table_name = 'vtiger_projectmilestone';
    var $table_index = 'projectmilestoneid';

    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = ['vtiger_projectmilestonecf', 'projectmilestoneid'];

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    var $tab_name = ['vtiger_crmentity', 'vtiger_projectmilestone', 'vtiger_projectmilestonecf'];

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    var $tab_name_index = [
        'vtiger_crmentity'          => 'crmid',
        'vtiger_projectmilestone'   => 'projectmilestoneid',
        'vtiger_projectmilestonecf' => 'projectmilestoneid'
    ];

    /**
     * Mandatory for Listing (Related listview)
     */
    var $list_fields = [
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Project Milestone Name' => ['projectmilestone', 'projectmilestonename'],
        'Milestone Date'         => ['projectmilestone', 'projectmilestonedate'],
        'Type'                   => ['projectmilestone', 'projectmilestonetype'],
        //'Assigned To' => Array('crmentity','assigned_user_id')
    ];
    var $list_fields_name = [
        /* Format: Field Label => fieldname */
        'Project Milestone Name' => 'projectmilestonename',
        'Milestone Date'         => 'projectmilestonedate',
        'Type'                   => 'projectmilestonetype',
        //'Assigned To' => 'assigned_user_id'
    ];

    // Make the field link to detail view from list view (Fieldname)
    var $list_link_field = 'projectmilestonename';

    // For Popup listview and UI type support
    var $search_fields = [
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Project Milestone Name' => ['projectmilestone', 'projectmilestonename'],
        'Milestone Date'         => ['projectmilestone', 'projectmilestonedate'],
        'Type'                   => ['projectmilestone', 'projectmilestonetype'],
    ];
    var $search_fields_name = [
        /* Format: Field Label => fieldname */
        'Project Milestone Namee' => 'projectmilestonename',
        'Milestone Date'          => 'projectmilestonedate',
        'Type'                    => 'projectmilestonetype',
    ];

    // Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
    var $sortby_fields = [];

    // For Alphabetical search
    var $def_basicsearch_col = 'projectmilestonename';

    // Column value to use on detail view record text display
    var $def_detailview_recname = 'projectmilestonename';

    // Required Information for enabling Import feature
    var $required_fields = ['projectmilestonename' => 1];

    // Callback function list during Importing
    var $special_functions = ['set_import_assigned_user'];

    var $default_order_by = 'projectmilestonedate';
    var $default_sort_order = 'ASC';
    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    var $mandatory_fields = ['createdtime', 'modifiedtime', 'projectmilestonename', 'projectid', 'assigned_user_id'];

    /**
     * Return query to use based on given modulename, fieldname
     * Useful to handle specific case handling for Popup
     */
    function getQueryByModuleField($module, $fieldname, $srcrecord)
    {
        // $srcrecord could be empty
    }

    /**
     * Get list view query (send more WHERE clause condition if required)
     */
    function getListQuery($module, $where = '')
    {
        $query = "SELECT vtiger_crmentity.*, $this->table_name.*";

        // Keep track of tables joined to avoid duplicates
        $joinedTables = [];

        // Select Custom Field Table Columns if present
        if (!empty($this->customFieldTable)) {
            $query .= ", " . $this->customFieldTable[0] . ".* ";
        }

        $query .= " FROM $this->table_name";

        $query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

        $joinedTables[] = $this->table_name;
        $joinedTables[] = 'vtiger_crmentity';

        // Consider custom table join as well.
        if (!empty($this->customFieldTable)) {
            $query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
            $joinedTables[] = $this->customFieldTable[0];
        }
        $query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id";
        $query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";

        $joinedTables[] = 'vtiger_users';
        $joinedTables[] = 'vtiger_groups';

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

            if (!in_array($other->table_name, $joinedTables)) {
                $query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
                $joinedTables[] = $other->table_name;
            }
        }

        global $current_user;
        $query .= $this->getNonAdminAccessControlQuery($module, $current_user);
        $query .= "	WHERE vtiger_crmentity.deleted = 0 " . $usewhere;

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
                $sec_query .= " vtiger_groups.groupid IN (" . implode(",", $current_user_groups) . ") OR ";
            }
            $sec_query .= " vtiger_groups.groupid IN
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
        global $current_user;

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery('ProjectMilestone', "detail_view");

        $fields_list = getFieldsListFromQuery($sql);

        $query = "SELECT $fields_list, vtiger_users.user_name AS user_name
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

        if (!empty($this->customFieldTable)) {
            $query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
        }

        $query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";
        $query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id = vtiger_users.id and vtiger_users.status='Active'";

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

            $query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
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
     * Transform the value while exporting
     */
    function transform_export_value($key, $value)
    {
        return parent::transform_export_value($key, $value);
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
                $sub_query .= " LEFT JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
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

    /**
     * Invoked when special actions are performed on the module.
     *
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     *
     * @throws Exception
     */
    function vtlib_handler($moduleName, $eventType)
    {
        Core_Install_Model::getInstance($eventType, $moduleName)->install();
    }

    /**
     * Handle saving related module information.
     * NOTE: This function has been added to CRMEntity (base class).
     * You can override the behavior by re-defining it here.
     */
    // function save_related_module($module, $crmid, $with_module, $with_crmid) { }

    /**
     * Handle deleting related module information.
     * NOTE: This function has been added to CRMEntity (base class).
     * You can override the behavior by re-defining it here.
     */
    //function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

    /**
     * Handle getting related list information.
     * NOTE: This function has been added to CRMEntity (base class).
     * You can override the behavior by re-defining it here.
     */
    //function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

    /**
     * Handle getting dependents list information.
     * NOTE: This function has been added to CRMEntity (base class).
     * You can override the behavior by re-defining it here.
     */
    //function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

    /*
    * Function to get the secondary query part of a report
    * @param - $module primary module name
    * @param - $secmodule secondary module name
    * returns the query string formed on fetching the related data for report for secondary module
    */
    function generateReportsSecQuery($module, $secmodule, $queryPlanner)
    {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityProjectMilestone', ['vtiger_groupsProjectMilestone', 'vtiger_usersProjectMilestone', 'vtiger_lastModifiedByProjectMilestone']);

        if (!$queryPlanner->requireTable('vtiger_projectmilestone', $matrix)) {
            return '';
        }
        $matrix->setDependency('vtiger_projectmilestone', ['vtiger_crmentityProjectMilestone']);

        $query .= $this->getRelationQuery($module, $secmodule, "vtiger_projectmilestone", "projectmilestoneid", $queryPlanner);

        if ($queryPlanner->requireTable('vtiger_crmentityProjectMilestone', $matrix)) {
            $query .= " LEFT JOIN vtiger_crmentity AS vtiger_crmentityProjectMilestone ON vtiger_crmentityProjectMilestone.crmid=vtiger_projectmilestone.projectmilestoneid and vtiger_crmentityProjectMilestone.deleted=0";
        }
        if ($queryPlanner->requireTable('vtiger_projectmilestonecf')) {
            $query .= " LEFT JOIN vtiger_projectmilestonecf ON vtiger_projectmilestone.projectmilestoneid = vtiger_projectmilestonecf.projectmilestoneid";
        }
        if ($queryPlanner->requireTable('vtiger_groupsProjectMilestone')) {
            $query .= "	left join vtiger_groups AS vtiger_groupsProjectMilestone ON vtiger_groupsProjectMilestone.groupid = vtiger_crmentityProjectMilestone.assigned_user_id";
        }
        if ($queryPlanner->requireTable('vtiger_usersProjectMilestone')) {
            $query .= " LEFT JOIN vtiger_users AS vtiger_usersProjectMilestone ON vtiger_usersProjectMilestone.id = vtiger_crmentityProjectMilestone.assigned_user_id";
        }
        if ($queryPlanner->requireTable('vtiger_lastModifiedByProjectMilestone')) {
            $query .= " LEFT JOIN vtiger_users AS vtiger_lastModifiedByProjectMilestone ON vtiger_lastModifiedByProjectMilestone.id = vtiger_crmentityProjectMilestone.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyProjectMilestone")) {
            $query .= " LEFT JOIN vtiger_users AS vtiger_createdbyProjectMilestone ON vtiger_createdbyProjectMilestone.id = vtiger_crmentityProjectMilestone.creator_user_id ";
        }
        //if secondary modules custom reference field is selected
        $query .= parent::getReportsUiType10Query($secmodule, $queryPlanner);

        return $query;
    }
}
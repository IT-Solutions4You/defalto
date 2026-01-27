<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Potentials/Potentials.php,v 1.65 2005/04/28 08:08:27 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Potentials extends CRMEntity
{
    public string $moduleName = 'Potentials';
    public string $parentName = 'HOME';
    public $table_name = "vtiger_potential";
    public $table_index = 'potentialid';

    public $tab_name = ['vtiger_crmentity', 'vtiger_potential', 'vtiger_potentialscf'];
    public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_potential' => 'potentialid', 'vtiger_potentialscf' => 'potentialid'];
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_potentialscf', 'potentialid'];

    public $sortby_fields = ['potentialname', 'amount', 'closingdate', 'assigned_user_id', 'accountname'];

    // This is the list of vtiger_fields that are in the lists.
    public $list_fields = [
        'Potential'           => ['potential' => 'potentialname'],
        'Organization Name'   => ['potential' => 'related_to'],
        'Contact Name'        => ['potential' => 'contact_id'],
        'Sales Stage'         => ['potential' => 'sales_stage'],
        'Amount'              => ['potential' => 'amount'],
        'Expected Close Date' => ['potential' => 'closingdate'],
        'Assigned To'         => ['crmentity', 'assigned_user_id'],
    ];

    public $list_fields_name = [
        'Potential'           => 'potentialname',
        'Organization Name'   => 'related_to',
        'Contact Name'        => 'contact_id',
        'Sales Stage'         => 'sales_stage',
        'Amount'              => 'amount',
        'Expected Close Date' => 'closingdate',
        'Assigned To'         => 'assigned_user_id',
    ];

    public $list_link_field = 'potentialname';

    public $search_fields = [
        'Potential'           => ['potential' => 'potentialname'],
        'Related To'          => ['potential' => 'related_to'],
        'Expected Close Date' => ['potential' => 'closedate'],
    ];

    public $search_fields_name = [
        'Potential'           => 'potentialname',
        'Related To'          => 'related_to',
        'Expected Close Date' => 'closingdate',
    ];

    public $required_fields = [];

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime', 'potentialname'];

    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = 'potentialname';
    public $default_sort_order = 'ASC';

    // For Alphabetical search
    public $def_basicsearch_col = 'potentialname';

    public $related_module_table_index = [
        'Contacts' => ['table_name' => 'vtiger_contactdetails', 'table_index' => 'contactid', 'rel_index' => 'contactid'],
    ];

    public $LBL_POTENTIAL_MAPPING = 'LBL_OPPORTUNITY_MAPPING';

    /** Function to create list query
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Query.
     */
    function create_list_query($order_by, $where)
    {
        global $log, $current_user;
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
        $tab_id = getTabid("Potentials");
        $log->debug("Entering create_list_query(" . $order_by . "," . $where . ") method ...");
        // Determine if the vtiger_account name is present in the where clause.
        $account_required = preg_match("/accounts\.name/", $where);

        if ($account_required) {
            $query = "SELECT vtiger_potential.potentialid,  vtiger_potential.potentialname, vtiger_potential.dateclosed FROM vtiger_potential, vtiger_account ";
            $where_auto = "account.accountid = vtiger_potential.related_to AND vtiger_crmentity.deleted=0 ";
        } else {
            $query = 'SELECT vtiger_potential.potentialid, vtiger_potential.potentialname, vtiger_crmentity.creator_user_id, vtiger_potential.closingdate FROM vtiger_potential inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_potential.potentialid LEFT JOIN vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.assigned_user_id left join vtiger_users on vtiger_users.id = vtiger_crmentity.assigned_user_id ';
            $where_auto = ' AND vtiger_crmentity.deleted=0';
        }

        $query .= $this->getNonAdminAccessControlQuery('Potentials', $current_user);
        if ($where != "") {
            $query .= " where $where " . $where_auto;
        } else {
            $query .= " where " . $where_auto;
        }
        if ($order_by != "") {
            $query .= " ORDER BY $order_by";
        }

        $log->debug("Exiting create_list_query method ...");

        return $query;
    }

    /** Function to export the Opportunities records in CSV Format
     *
     * @param reference variable - order by is passed when the query is executed
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export Potentials Query.
     */
    function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("Potentials", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
				FROM vtiger_potential
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_potential.potentialid
				LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id=vtiger_users.id
				LEFT JOIN vtiger_account on vtiger_potential.related_to=vtiger_account.accountid
				LEFT JOIN vtiger_contactdetails on vtiger_potential.contact_id=vtiger_contactdetails.contactid
				LEFT JOIN vtiger_potentialscf on vtiger_potentialscf.potentialid=vtiger_potential.potentialid
                LEFT JOIN vtiger_groups
        	        ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_campaign
					ON vtiger_campaign.campaignid = vtiger_potential.campaignid";

        $query .= $this->getNonAdminAccessControlQuery('Potentials', $current_user);
        $where_auto = "  vtiger_crmentity.deleted = 0 ";

        if ($where != "") {
            $query .= "  WHERE ($where) AND " . $where_auto;
        } else {
            $query .= "  WHERE " . $where_auto;
        }

        $log->debug("Exiting create_export_query method ...");

        return $query;
    }

    /**
     * Move the related records of the specified list of id's to the given record.
     *
     * @param String This module name
     * @param Array List of Entity Id's from which related records need to be transfered
     * @param Integer Id of the the Record to which the related records are to be moved
     */
    function transferRelatedRecords($module, $transferEntityIds, $entityId)
    {
        global $adb, $log;
        $log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

        $rel_table_arr = [
            'Attachments' => 'vtiger_seattachmentsrel',
            'Quotes'      => 'vtiger_quotes',
            'SalesOrder'  => 'vtiger_salesorder',
        ];

        $tbl_field_arr = [
            'vtiger_seattachmentsrel' => 'attachmentsid',
            'vtiger_quotes'           => 'quoteid',
            'vtiger_salesorder'       => 'salesorderid',
        ];

        $entity_tbl_field_arr = [
            'vtiger_seattachmentsrel' => 'crmid',
            'vtiger_quotes'           => 'potential_id',
            'vtiger_salesorder'       => 'potential_id',
        ];

        foreach ($transferEntityIds as $transferId) {
            foreach ($rel_table_arr as $rel_module => $rel_table) {
                $id_field = $tbl_field_arr[$rel_table];
                $entity_id_field = $entity_tbl_field_arr[$rel_table];
                // IN clause to avoid duplicate entries
                $sel_result = $adb->pquery(
                    "select $id_field from $rel_table where $entity_id_field=? " .
                    " and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
                    [$transferId, $entityId]
                );
                $res_cnt = $adb->num_rows($sel_result);
                if ($res_cnt > 0) {
                    for ($i = 0; $i < $res_cnt; $i++) {
                        $id_field_value = $adb->query_result($sel_result, $i, $id_field);
                        $adb->pquery(
                            "update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?",
                            [$entityId, $transferId, $id_field_value]
                        );
                    }
                }
            }
        }
        parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
        $log->debug("Exiting transferRelatedRecords...");
    }

    /*
     * Function to get the secondary query part of a report
     * @param - $module primary module name
     * @param - $secmodule secondary module name
     * returns the query string formed on fetching the related data for report for secondary module
     */
    function generateReportsSecQuery($module, $secmodule, $queryPlanner)
    {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityPotentials', ['vtiger_groupsPotentials', 'vtiger_usersPotentials', 'vtiger_lastModifiedByPotentials']);

        if (!$queryPlanner->requireTable("vtiger_potential", $matrix)) {
            return '';
        }
        $matrix->setDependency(
            'vtiger_potential',
            ['vtiger_crmentityPotentials', 'vtiger_accountPotentials', 'vtiger_contactdetailsPotentials', 'vtiger_campaignPotentials', 'vtiger_potentialscf']
        );

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_potential", "potentialid", $queryPlanner);

        if ($queryPlanner->requireTable("vtiger_crmentityPotentials", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityPotentials on vtiger_crmentityPotentials.crmid=vtiger_potential.potentialid and vtiger_crmentityPotentials.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_accountPotentials")) {
            $query .= " left join vtiger_account as vtiger_accountPotentials on vtiger_potential.related_to = vtiger_accountPotentials.accountid";
        }
        if ($queryPlanner->requireTable("vtiger_contactdetailsPotentials")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsPotentials on vtiger_potential.contact_id = vtiger_contactdetailsPotentials.contactid";
        }
        if ($queryPlanner->requireTable("vtiger_potentialscf")) {
            $query .= " left join vtiger_potentialscf on vtiger_potentialscf.potentialid = vtiger_potential.potentialid";
        }
        if ($queryPlanner->requireTable("vtiger_groupsPotentials")) {
            $query .= " left join vtiger_groups vtiger_groupsPotentials on vtiger_groupsPotentials.groupid = vtiger_crmentityPotentials.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_usersPotentials")) {
            $query .= " left join vtiger_users as vtiger_usersPotentials on vtiger_usersPotentials.id = vtiger_crmentityPotentials.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_campaignPotentials")) {
            $query .= " left join vtiger_campaign as vtiger_campaignPotentials on vtiger_potential.campaignid = vtiger_campaignPotentials.campaignid";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByPotentials")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByPotentials on vtiger_lastModifiedByPotentials.id = vtiger_crmentityPotentials.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyPotentials")) {
            $query .= " left join vtiger_users as vtiger_createdbyPotentials on vtiger_createdbyPotentials.id = vtiger_crmentityPotentials.creator_user_id ";
        }

        //if secondary modules custom reference field is selected
        $query .= parent::getReportsUiType10Query($secmodule, $queryPlanner);

        return $query;
    }

    /*
     * Function to get the relation tables for related modules
     * @param - $secmodule secondary module name
     * returns the array with table names and fieldnames storing relations between module and this module
     */
    function setRelationTables($secmodule)
    {
        $rel_tables = [
            "Quotes"     => ["vtiger_quotes" => ["potential_id", "quoteid"], "vtiger_potential" => "potentialid"],
            "SalesOrder" => ["vtiger_salesorder" => ["potential_id", "salesorderid"], "vtiger_potential" => "potentialid"],
            "Accounts"   => ["vtiger_potential" => ["potentialid", "related_to"]],
            "Contacts"   => ["vtiger_potential" => ["potentialid", "contact_id"]],
        ];

        return $rel_tables[$secmodule];
    }

    /**
     * Invoked when special actions are to be performed on the module.
     *
     * @param String Module name
     * @param String Event Type
     */
    function vtlib_handler($moduleName, $eventType)
    {
        if ($moduleName == 'Potentials') {
            if ($eventType == 'module.disabled') {
                Settings_Vtiger_MenuItem_Model::deactivate($this->LBL_POTENTIAL_MAPPING);
            } elseif ($eventType == 'module.enabled') {
                Settings_Vtiger_MenuItem_Model::activate($this->LBL_POTENTIAL_MAPPING);
            }
        }
    }
}
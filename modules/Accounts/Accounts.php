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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Accounts/Accounts.php,v 1.53 2005/04/28 08:06:45 rank Exp $
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
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
class Accounts extends CRMEntity
{
    public string $moduleName = 'Accounts';
    public string $parentName = 'HOME';
    public $table_name = "vtiger_account";
    public $table_index = 'accountid';
    public $tab_name = ['vtiger_crmentity', 'vtiger_account', 'vtiger_accountbillads', 'vtiger_accountshipads', 'vtiger_accountscf'];
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'vtiger_account' => 'accountid',
        'vtiger_accountbillads' => 'accountaddressid',
        'vtiger_accountshipads' => 'accountaddressid',
        'vtiger_accountscf' => 'accountid'
    ];
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_accountscf', 'accountid'];
    public $entity_table = "vtiger_crmentity";

    public $sortby_fields = ['accountname', 'bill_city', 'website', 'phone', 'assigned_user_id'];

    //public $groupTable = Array('vtiger_accountgrouprelation','accountid');

    // This is the list of vtiger_fields that are in the lists.
    public $list_fields = [
        'Account Name' => ['vtiger_account' => 'accountname'],
        'Billing City' => ['vtiger_accountbillads' => 'bill_city'],
        'Website' => ['vtiger_account' => 'website'],
        'Phone' => ['vtiger_account' => 'phone'],
        'Assigned To' => ['vtiger_crmentity' => 'assigned_user_id']
    ];

    public $list_fields_name = [
        'Account Name' => 'accountname',
        'Billing City' => 'bill_city',
        'Website' => 'website',
        'Phone' => 'phone',
        'Assigned To' => 'assigned_user_id'
    ];
    public $list_link_field = 'accountname';

    public $search_fields = [
        'Account Name' => ['vtiger_account' => 'accountname'],
        'Billing City' => ['vtiger_accountbillads' => 'bill_city'],
        'Assigned To' => ['vtiger_crmentity' => 'assigned_user_id'],
    ];

    public $search_fields_name = [
        'Account Name' => 'accountname',
        'Billing City' => 'bill_city',
        'Assigned To' => 'assigned_user_id',
    ];
    // This is the list of vtiger_fields that are required
    public $required_fields = [];

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime', 'accountname'];

    // For Alphabetical search
    public $def_basicsearch_col = 'accountname';

    public $related_module_table_index = [
        'Contacts' => ['table_name' => 'vtiger_contactdetails', 'table_index' => 'contactid', 'rel_index' => 'account_id'],
        'Potentials' => ['table_name' => 'vtiger_potential', 'table_index' => 'potentialid', 'rel_index' => 'related_to'],
        'Quotes' => ['table_name' => 'vtiger_quotes', 'table_index' => 'quoteid', 'rel_index' => 'account_id'],
        'SalesOrder' => ['table_name' => 'vtiger_salesorder', 'table_index' => 'salesorderid', 'rel_index' => 'account_id'],
        'Invoice' => ['table_name' => 'vtiger_invoice', 'table_index' => 'invoiceid', 'rel_index' => 'account_id'],
        'HelpDesk' => ['table_name' => 'vtiger_troubletickets', 'table_index' => 'ticketid', 'rel_index' => 'parent_id'],
        'ServiceContracts' => ['table_name' => 'vtiger_servicecontracts', 'table_index' => 'servicecontractsid', 'rel_index' => 'account_id'],
        'Assets' => ['table_name' => 'vtiger_assets', 'table_index' => 'assetsid', 'rel_index' => 'account'],
        'Project' => ['table_name' => 'vtiger_project', 'table_index' => 'projectid', 'rel_index' => 'account_id'],
        'PurchaseOrder' => ['table_name' => 'vtiger_purchaseorder', 'table_index' => 'purchaseorderid', 'rel_index' => 'accountid'],
    ];

    public string $default_order_by = 'accountname';
    public string $default_sort_order = 'asc';

    /** Function to export the account records in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export Accounts Query.
     */
    function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("Accounts", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);

        $query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name
            FROM " . $this->entity_table . "
            INNER JOIN vtiger_account ON vtiger_account.accountid = vtiger_crmentity.crmid
            LEFT JOIN vtiger_accountbillads ON vtiger_accountbillads.accountaddressid = vtiger_account.accountid
            LEFT JOIN vtiger_accountshipads ON vtiger_accountshipads.accountaddressid = vtiger_account.accountid
            LEFT JOIN vtiger_accountscf ON vtiger_accountscf.accountid = vtiger_account.accountid
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
            LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id and vtiger_users.status = 'Active'
            LEFT JOIN vtiger_account vtiger_account2 ON vtiger_account2.accountid = vtiger_account.account_id";
        //vtiger_account2 is added to get the Member of account

        $query .= $this->getNonAdminAccessControlQuery('Accounts', $current_user);
        $where_auto = " vtiger_crmentity.deleted = 0 ";

        if ($where != "") {
            $query .= " WHERE ($where) AND " . $where_auto;
        } else {
            $query .= " WHERE " . $where_auto;
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
        $log->debug("Entering function transferRelatedRecords ($module, " . implode(',', $transferEntityIds) . ", $entityId)");

        $rel_table_arr = [
            'Contacts' => 'vtiger_contactdetails',
            'Potentials' => 'vtiger_potential',
            'Quotes' => 'vtiger_quotes',
            'SalesOrder' => 'vtiger_salesorder',
            'Invoice' => 'vtiger_invoice',
            'Attachments' => 'vtiger_seattachmentsrel',
            'HelpDesk' => 'vtiger_troubletickets',
            'ServiceContracts' => 'vtiger_servicecontracts',
            'Campaigns' => 'vtiger_campaignaccountrel',
            'Assets' => 'vtiger_assets',
            'Project' => 'vtiger_project'
        ];

        $tbl_field_arr = [
            'vtiger_contactdetails' => 'contactid',
            'vtiger_potential' => 'potentialid',
            'vtiger_quotes' => 'quoteid',
            'vtiger_salesorder' => 'salesorderid',
            'vtiger_invoice' => 'invoiceid',
            'vtiger_seattachmentsrel' => 'attachmentsid',
            'vtiger_troubletickets' => 'ticketid',
            'vtiger_servicecontracts' => 'servicecontractsid',
            'vtiger_campaignaccountrel' => 'campaignid',
            'vtiger_assets' => 'assetsid',
            'vtiger_project' => 'projectid'
        ];

        $entity_tbl_field_arr = [
            'vtiger_contactdetails' => 'account_id',
            'vtiger_potential' => 'related_to',
            'vtiger_quotes' => 'account_id',
            'vtiger_salesorder' => 'account_id',
            'vtiger_invoice' => 'account_id',
            'vtiger_seattachmentsrel' => 'crmid',
            'vtiger_troubletickets' => 'parent_id',
            'vtiger_servicecontracts' => 'account_id',
            'vtiger_campaignaccountrel' => 'accountid',
            'vtiger_assets' => 'account',
            'vtiger_project' => 'account_id',
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
     * Function to get the relation tables for related modules
     * @param - $secmodule secondary module name
     * returns the array with table names and fieldnames storing relations between module and this module
     */
    public function setRelationTables($secmodule)
    {
        $rel_tables = [
            'Contacts' => ['vtiger_contactdetails' => ['account_id', 'contactid'], 'vtiger_account' => 'accountid'],
            'Potentials' => ['vtiger_potential' => ['related_to', 'potentialid'], 'vtiger_account' => 'accountid'],
            'Quotes' => ['vtiger_quotes' => ['account_id', 'quoteid'], 'vtiger_account' => 'accountid'],
            'SalesOrder' => ['vtiger_salesorder' => ['account_id', 'salesorderid'], 'vtiger_account' => 'accountid'],
            'Invoice' => ['vtiger_invoice' => ['account_id', 'invoiceid'], 'vtiger_account' => 'accountid'],
            'HelpDesk' => ['vtiger_troubletickets' => ['parent_id', 'ticketid'], 'vtiger_account' => 'accountid'],
        ];

        return $rel_tables[$secmodule];
    }

    /**
     * Function to get Account hierarchy of the given Account
     *
     * @param integer $id - accountid
     *                    returns Account hierarchy in array format
     */
    function getAccountHierarchy($id)
    {
        global $log, $adb, $current_user;
        $log->debug("Entering getAccountHierarchy(" . $id . ") method ...");
        require('user_privileges/user_privileges_' . $current_user->id . '.php');

        $tabname = getParentTab();
        $listview_header = [];
        $listview_entries = [];

        foreach ($this->list_fields_name as $fieldname => $colname) {
            if (getFieldVisibilityPermission('Accounts', $current_user->id, $colname) == '0') {
                $listview_header[] = getTranslatedString($fieldname);
            }
        }

        $accounts_list = [];

        // Get the accounts hierarchy from the top most account in the hierarch of the current account, including the current account
        $encountered_accounts = [$id];
        $accounts_list = $this->__getParentAccounts($id, $accounts_list, $encountered_accounts);

        // Get the accounts hierarchy (list of child accounts) based on the current account
        $accounts_list = $this->__getChildAccounts($id, $accounts_list, $accounts_list[$id]['depth']);

        // Create array of all the accounts in the hierarchy
        foreach ($accounts_list as $account_id => $account_info) {
            $account_info_data = [];

            $hasRecordViewAccess = (is_admin($current_user)) || (isPermitted('Accounts', 'DetailView', $account_id) == 'yes');

            foreach ($this->list_fields_name as $fieldname => $colname) {
                // Permission to view account is restricted, avoid showing field values (except account name)
                if (!$hasRecordViewAccess && $colname != 'accountname') {
                    $account_info_data[] = '';
                } elseif (getFieldVisibilityPermission('Accounts', $current_user->id, $colname) == '0') {
                    $data = $account_info[$colname];
                    if ($colname == 'accountname') {
                        if ($account_id != $id) {
                            if ($hasRecordViewAccess) {
                                $data = '<a href="index.php?module=Accounts&action=DetailView&record=' . $account_id . '&parenttab=' . $tabname . '">' . $data . '</a>';
                            } else {
                                $data = '<i>' . $data . '</i>';
                            }
                        } else {
                            $data = '<b>' . $data . '</b>';
                        }
                        // - to show the hierarchy of the Accounts
                        $account_depth = str_repeat(" .. ", $account_info['depth'] * 2);
                        $data = $account_depth . $data;
                    } elseif ($colname == 'website') {
                        $data = '<a href="http://' . $data . '" target="_blank">' . $data . '</a>';
                    }
                    $account_info_data[] = $data;
                }
            }
            $listview_entries[$account_id] = $account_info_data;
        }

        $account_hierarchy = ['header' => $listview_header, 'entries' => $listview_entries];
        $log->debug("Exiting getAccountHierarchy method ...");

        return $account_hierarchy;
    }

    /**
     * Function to Recursively get all the upper accounts of a given Account
     *
     * @param integer $id - accountid
     * @param array $parent_accounts - Array of all the parent accounts
     *                                 returns All the parent accounts of the given accountid in array format
     *
     * @throws Exception
     */
    function __getParentAccounts($id, &$parent_accounts, &$encountered_accounts)
    {
        global $log, $adb;
        $log->debug("Entering __getParentAccounts(" . $id . "," . $parent_accounts . ") method ...");

        $query = 'SELECT vtiger_account.account_id FROM vtiger_account 
		    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
		    WHERE vtiger_crmentity.deleted = 0 and vtiger_account.accountid = ?';
        $params = [$id];
        $res = $adb->pquery($query, $params);
        $parentId = $adb->query_result($res, 0, 'account_id');

        if ($adb->num_rows($res) > 0 && !empty($accountId) && !in_array($accountId, $encountered_accounts)) {
            $encountered_accounts[] = $parentId;
            $this->__getParentAccounts($parentId, $parent_accounts, $encountered_accounts);
        }

        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $query = "SELECT vtiger_account.*, vtiger_accountbillads.*, CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name 
		    FROM vtiger_account
		    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid 
            INNER JOIN vtiger_accountbillads ON vtiger_account.accountid = vtiger_accountbillads.accountaddressid
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
            LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id
            WHERE vtiger_crmentity.deleted = 0 and vtiger_account.accountid = ?";
        $params = [$id];
        $res = $adb->pquery($query, $params);

        $parent_account_info = [];
        $depth = 0;
        $immediate_parentid = $adb->query_result($res, 0, 'account_id');
        if (isset($parent_accounts[$immediate_parentid])) {
            $depth = $parent_accounts[$immediate_parentid]['depth'] + 1;
        }
        $parent_account_info['depth'] = $depth;
        foreach ($this->list_fields_name as $fieldname => $columnname) {
            if ($columnname == 'assigned_user_id') {
                $parent_account_info[$columnname] = $adb->query_result($res, 0, 'user_name');
            } else {
                $parent_account_info[$columnname] = $adb->query_result($res, 0, $columnname);
            }
        }
        $parent_accounts[$id] = $parent_account_info;
        $log->debug("Exiting __getParentAccounts method ...");

        return $parent_accounts;
    }

    /**
     * Function to Recursively get all the child accounts of a given Account
     *
     * @param integer $id - accountid
     * @param array $child_accounts - Array of all the child accounts
     * @param integer $depth - Depth at which the particular account has to be placed in the hierarchy
     *                                returns All the child accounts of the given accountid in array format
     */
    function __getChildAccounts($id, &$child_accounts, $depth)
    {
        global $log, $adb;
        $log->debug("Entering __getChildAccounts(" . $id . "," . $child_accounts . "," . $depth . ") method ...");

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name' => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_account.*, vtiger_accountbillads.*, CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name 
		    FROM vtiger_account
		    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
		    INNER JOIN vtiger_accountbillads ON vtiger_account.accountid = vtiger_accountbillads.accountaddressid
		    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
		    LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id
		    WHERE vtiger_crmentity.deleted = 0 and vtiger_account.account_id = ?";
        $params = [$id];
        $res = $adb->pquery($query, $params);

        $num_rows = $adb->num_rows($res);

        if ($num_rows > 0) {
            $depth = $depth + 1;
            for ($i = 0; $i < $num_rows; $i++) {
                $child_acc_id = $adb->query_result($res, $i, 'accountid');
                if (array_key_exists($child_acc_id, $child_accounts)) {
                    continue;
                }
                $child_account_info = [];
                $child_account_info['depth'] = $depth;
                foreach ($this->list_fields_name as $fieldname => $columnname) {
                    if ($columnname == 'assigned_user_id') {
                        $child_account_info[$columnname] = $adb->query_result($res, $i, 'user_name');
                    } else {
                        $child_account_info[$columnname] = $adb->query_result($res, $i, $columnname);
                    }
                }
                $child_accounts[$child_acc_id] = $child_account_info;
                $this->__getChildAccounts($child_acc_id, $child_accounts, $depth);
            }
        }
        $log->debug("Exiting __getChildAccounts method ...");

        return $child_accounts;
    }

    // Function to unlink the dependent records of the given record by id
    function unlinkDependencies($module, $id)
    {
        Core_Relation_Model::saveEntityDependencies($id, 'vtiger_account', 'accountid', 'vtiger_potential', 'potentialid', 'related_to');
        Core_Relation_Model::saveEntityDependencies($id, 'vtiger_account', 'accountid', 'vtiger_quotes', 'quoteid', 'account_id');
        Core_Relation_Model::saveDependencies($id, 'account_id', 'vtiger_contactdetails', 'contactid');
        Core_Relation_Model::saveDependencies($id, 'parent_id', 'vtiger_troubletickets', 'ticketid');

        parent::unlinkDependencies($module, $id);
    }

    // Function to unlink an entity with given Id from another entity
    public function unlinkRelationship($id, $return_module, $return_id)
    {
        global $log;
        if (empty($return_module) || empty($return_id)) {
            return;
        }

        if ($return_module == 'Campaigns') {
            Campaigns_Relation_Model::deleteCampaignRelation($return_id, $return_module, $id, 'Accounts');
        }

        parent::unlinkRelationship($id, $return_module, $return_id);
    }

    public function save_related_module($module, $crmid, $with_module, $with_crmids, $otherParams = [])
    {
        $adb = $this->db;

        if (!is_array($with_crmids)) {
            $with_crmids = [$with_crmids];
        }

        foreach ($with_crmids as $with_crmid) {
            if ($with_module == 'Campaigns') {
                Campaigns_Relation_Model::saveCampaignRelation($with_crmid, $with_module, $crmid, 'Accounts');
            }

            parent::save_related_module($module, $crmid, $with_module, $with_crmid);
        }
    }

    function getListButtons($app_strings, $mod_strings = false)
    {
        $list_buttons = [];

        if (isPermitted('Accounts', 'Delete', '') == 'yes') {
            $list_buttons['del'] = $app_strings["LBL_MASS_DELETE"];
        }
        if (isPermitted('Accounts', 'EditView', '') == 'yes') {
            $list_buttons['mass_edit'] = $app_strings["LBL_MASS_EDIT"];
            $list_buttons['c_owner'] = $app_strings["LBL_CHANGE_OWNER"];
        }
        // mailer export
        if (isPermitted('Accounts', 'Export', '') == 'yes') {
            $list_buttons['mailer_exp'] = $mod_strings["LBL_MAILER_EXPORT"];
        }

        // end of mailer export
        return $list_buttons;
    }

    /* Function to get related contact ids for an account record*/
    function getRelatedContactsIds($id = null)
    {
        global $adb;
        if ($id == null) {
            $id = $this->id;
        }
        $entityIds = [];
        $query = 'SELECT contactid FROM vtiger_contactdetails
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				WHERE vtiger_contactdetails.account_id = ? AND vtiger_crmentity.deleted = 0';
        $accountContacts = $adb->pquery($query, [$id]);
        $numOfContacts = $adb->num_rows($accountContacts);
        if ($accountContacts && $numOfContacts > 0) {
            for ($i = 0; $i < $numOfContacts; ++$i) {
                array_push($entityIds, $adb->query_result($accountContacts, $i, 'contactid'));
            }
        }

        return $entityIds;
    }

    function getRelatedPotentialIds($id)
    {
        $relatedIds = [];
        $db = PearDatabase::getInstance();
        $query = "SELECT DISTINCT vtiger_crmentity.crmid FROM vtiger_potential INNER JOIN vtiger_crmentity ON 
					vtiger_crmentity.crmid = vtiger_potential.potentialid LEFT JOIN vtiger_account ON vtiger_account.accountid = 
					vtiger_potential.related_to WHERE vtiger_crmentity.deleted = 0 AND vtiger_potential.related_to = ?";
        $result = $db->pquery($query, [$id]);
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $relatedIds[] = $db->query_result($result, $i, 'crmid');
        }

        return $relatedIds;
    }

    function getRelatedTicketIds($id)
    {
        $relatedIds = [];
        $db = PearDatabase::getInstance();
        $query = "SELECT DISTINCT vtiger_crmentity.crmid FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON 
					vtiger_crmentity.crmid = vtiger_troubletickets.ticketid WHERE vtiger_crmentity.deleted = 0 AND 
					vtiger_troubletickets.parent_id = ?";
        $result = $db->pquery($query, [$id]);
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $relatedIds[] = $db->query_result($result, $i, 'crmid');
        }

        return $relatedIds;
    }
}
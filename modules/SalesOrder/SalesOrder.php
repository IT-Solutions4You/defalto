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
 * $Header$
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

class SalesOrder extends CRMEntity
{
    public string $moduleVersion = '1.1';
    public string $moduleName = 'SalesOrder';
    public string $parentName = 'SALES';

    public $table_name = "vtiger_salesorder";
    public $table_index = 'salesorderid';
    public $tab_name = [
        'vtiger_crmentity',
        'vtiger_salesorder',
        'vtiger_sobillads',
        'vtiger_soshipads',
        'vtiger_salesordercf',
        'vtiger_invoice_recurring_info',
    ];
    public $tab_name_index = [
        'vtiger_crmentity'              => 'crmid',
        'vtiger_salesorder'             => 'salesorderid',
        'vtiger_sobillads'              => 'sobilladdressid',
        'vtiger_soshipads'              => 'soshipaddressid',
        'vtiger_salesordercf'           => 'salesorderid',
        'vtiger_invoice_recurring_info' => 'salesorderid',
    ];
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_salesordercf', 'salesorderid'];
    public $entity_table = "vtiger_crmentity";

    public $billadr_table = "vtiger_sobillads";

    public $object_name = "SalesOrder";

    public $new_schema = true;

    public $update_product_array = [];

    public $sortby_fields = ['subject', 'assigned_user_id', 'accountname', 'lastname'];

    // This is used to retrieve related vtiger_fields from form posts.
    public $additional_column_fields = [
        'assigned_user_name',
        'assigned_user_id',
        'opportunity_id',
        'case_id',
        'contact_id',
        'task_id',
        'note_id',
        'meeting_id',
        'call_id',
        'email_id',
        'parent_name',
        'member_id'
    ];

    // This is the list of vtiger_fields that are in the lists.
    public $list_fields = [
        // Module Sequence Numbering
        //'Order No'=>Array('crmentity'=>'crmid'),
        'Order No'     => ['salesorder', 'salesorder_no'],
        // END
        'Subject'      => ['salesorder' => 'subject'],
        'Account Name' => ['account' => 'accountid'],
        'Quote Name'   => ['quotes' => 'quote_id'],
        'Total'        => ['salesorder' => 'grand_total'],
        'Assigned To'  => ['crmentity' => 'assigned_user_id']
    ];

    public $list_fields_name = [
        'Order No'     => 'salesorder_no',
        'Subject'      => 'subject',
        'Account Name' => 'account_id',
        'Quote Name'   => 'quote_id',
        'Total'        => 'grand_total',
        'Assigned To'  => 'assigned_user_id'
    ];
    public $list_link_field = 'subject';

    public $search_fields = [
        'Order No'     => ['salesorder' => 'salesorder_no'],
        'Subject'      => ['salesorder' => 'subject'],
        'Account Name' => ['account' => 'accountid'],
        'Quote Name'   => ['salesorder' => 'quoteid']
    ];

    public $search_fields_name = [
        'Order No'     => 'salesorder_no',
        'Subject'      => 'subject',
        'Account Name' => 'account_id',
        'Quote Name'   => 'quote_id'
    ];

    // This is the list of vtiger_fields that are required.
    public $required_fields = ["accountname" => 1];

    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = 'subject';
    public $default_sort_order = 'ASC';
    //public $groupTable = Array('vtiger_sogrouprelation','salesorderid');

    public $mandatory_fields = ['subject', 'createdtime', 'modifiedtime', 'assigned_user_id', 'quantity', 'listprice', 'productid'];

    // For Alphabetical search
    public $def_basicsearch_col = 'subject';

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $sourceModule = $request->get('sourceModule');
        $sourceRecord = (int)$request->get('sourceRecord');

        if ((empty($sourceModule) || empty($sourceRecord)) && !empty($this->column_fields['quote_id'])) {
            InventoryItem_CopyOnCreate_Model::run($this, $this->column_fields['quote_id']);
        } else {
            InventoryItem_CopyOnCreate_Model::run($this);
        }
    }

    /** Function to get the invoices associated with the Sales Order
     *  This function accepts the id as arguments and execute the MySQL query using the id
     *  and sends the query and the id as arguments to renderRelatedInvoices() method.
     */
    public function get_invoices($id)
    {
        global $log, $singlepane_view;
        $log->debug("Entering get_invoices(" . $id . ") method ...");
        require_once('modules/Invoice/Invoice.php');

        $focus = new Invoice();

        $button = '';
        if ($singlepane_view == 'true') {
            $returnset = '&return_module=SalesOrder&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=SalesOrder&return_action=CallRelatedList&return_id=' . $id;
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "select vtiger_crmentity.*, vtiger_invoice.*, vtiger_account.accountname,
			vtiger_salesorder.subject as salessubject, case when
			(vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname
			end as user_name from vtiger_invoice
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid
			left outer join vtiger_account on vtiger_account.accountid=vtiger_invoice.account_id
			inner join vtiger_salesorder on vtiger_salesorder.salesorderid=vtiger_invoice.salesorder_id
            LEFT JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
			left join vtiger_users on vtiger_users.id=vtiger_crmentity.assigned_user_id
			left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.assigned_user_id
			where vtiger_crmentity.deleted=0 and vtiger_salesorder.salesorderid=" . $id;

        $log->debug("Exiting get_invoices method ...");

        return GetRelatedList('SalesOrder', 'Invoice', $focus, $query, $button, $returnset);
    }

    /*
     * Function to get the secondary query part of a report
     * @param - $module primary module name
     * @param - $secmodule secondary module name
     * returns the query string formed on fetching the related data for report for secondary module
     */
    public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
    {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentitySalesOrder', ['vtiger_usersSalesOrder', 'vtiger_groupsSalesOrder', 'vtiger_lastModifiedBySalesOrder']);
        if (!$queryPlanner->requireTable('vtiger_salesorder', $matrix)) {
            return '';
        }
        $matrix->setDependency('vtiger_salesorder', [
            'vtiger_crmentitySalesOrder',
            "vtiger_currency_info$secmodule",
            'vtiger_salesordercf',
            'vtiger_potentialRelSalesOrder',
            'vtiger_sobillads',
            'vtiger_soshipads',
            'vtiger_contactdetailsSalesOrder',
            'vtiger_accountSalesOrder',
            'vtiger_invoice_recurring_info',
            'vtiger_quotesSalesOrder'
        ]);

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_salesorder", "salesorderid", $queryPlanner);
        if ($queryPlanner->requireTable("vtiger_crmentitySalesOrder", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentitySalesOrder on vtiger_crmentitySalesOrder.crmid=vtiger_salesorder.salesorderid and vtiger_crmentitySalesOrder.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_salesordercf")) {
            $query .= " left join vtiger_salesordercf on vtiger_salesorder.salesorderid = vtiger_salesordercf.salesorderid";
        }
        if ($queryPlanner->requireTable("vtiger_sobillads")) {
            $query .= " left join vtiger_sobillads on vtiger_salesorder.salesorderid=vtiger_sobillads.sobilladdressid";
        }
        if ($queryPlanner->requireTable("vtiger_soshipads")) {
            $query .= " left join vtiger_soshipads on vtiger_salesorder.salesorderid=vtiger_soshipads.soshipaddressid";
        }
        if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_salesorder.currency_id";
        }
        if ($queryPlanner->requireTable("vtiger_groupsSalesOrder")) {
            $query .= " left join vtiger_groups as vtiger_groupsSalesOrder on vtiger_groupsSalesOrder.groupid = vtiger_crmentitySalesOrder.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_usersSalesOrder")) {
            $query .= " left join vtiger_users as vtiger_usersSalesOrder on vtiger_usersSalesOrder.id = vtiger_crmentitySalesOrder.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_potentialRelSalesOrder")) {
            $query .= " left join vtiger_potential as vtiger_potentialRelSalesOrder on vtiger_potentialRelSalesOrder.potentialid = vtiger_salesorder.potential_id";
        }
        if ($queryPlanner->requireTable("vtiger_contactdetailsSalesOrder")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsSalesOrder on vtiger_salesorder.contact_id = vtiger_contactdetailsSalesOrder.contactid";
        }
        if ($queryPlanner->requireTable("vtiger_invoice_recurring_info")) {
            $query .= " left join vtiger_invoice_recurring_info on vtiger_salesorder.salesorderid = vtiger_invoice_recurring_info.salesorderid";
        }
        if ($queryPlanner->requireTable("vtiger_quotesSalesOrder")) {
            $query .= " left join vtiger_quotes as vtiger_quotesSalesOrder on vtiger_salesorder.quote_id = vtiger_quotesSalesOrder.quoteid";
        }
        if ($queryPlanner->requireTable("vtiger_accountSalesOrder")) {
            $query .= " left join vtiger_account as vtiger_accountSalesOrder on vtiger_accountSalesOrder.accountid = vtiger_salesorder.account_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedBySalesOrder")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedBySalesOrder on vtiger_lastModifiedBySalesOrder.id = vtiger_crmentitySalesOrder.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbySalesOrder")) {
            $query .= " left join vtiger_users as vtiger_createdbySalesOrder on vtiger_createdbySalesOrder.id = vtiger_crmentitySalesOrder.creator_user_id ";
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
    public function setRelationTables($secmodule)
    {
        $rel_tables = [
            "Invoice"   => ["vtiger_invoice" => ["salesorder_id", "invoiceid"], "vtiger_salesorder" => "salesorderid"],
            "Documents" => ["vtiger_senotesrel" => ["crmid", "notesid"], "vtiger_salesorder" => "salesorderid"],
        ];

        return $rel_tables[$secmodule];
    }

    // Function to unlink an entity with given Id from another entity
    public function unlinkRelationship($id, $return_module, $return_id)
    {
        global $log;
        if (empty($return_module) || empty($return_id)) {
            return;
        }

        if ($return_module == 'Accounts') {
            $this->trash('SalesOrder', $id);
        } elseif ($return_module == 'Quotes') {
            $relation_query = 'UPDATE vtiger_salesorder SET quote_id=? WHERE salesorderid=?';
            $this->db->pquery($relation_query, [null, $id]);
        } elseif ($return_module == 'Potentials') {
            $relation_query = 'UPDATE vtiger_salesorder SET potential_id=? WHERE salesorderid=?';
            $this->db->pquery($relation_query, [null, $id]);
        } elseif ($return_module == 'Contacts') {
            $relation_query = 'UPDATE vtiger_salesorder SET contact_id=? WHERE salesorderid=?';
            $this->db->pquery($relation_query, [null, $id]);
        } elseif ($return_module == 'Documents') {
            $sql = 'DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } else {
            parent::unlinkRelationship($id, $return_module, $return_id);
        }
    }

    public function getJoinClause($tableName)
    {
        if ($tableName == 'vtiger_invoice_recurring_info') {
            return 'LEFT JOIN';
        }

        return parent::getJoinClause($tableName);
    }

    /*Function to create records in current module.
    **This function called while importing records to this module*/
    public function createRecords($obj)
    {
        $createRecords = createRecords($obj);

        return $createRecords;
    }

    /*Function returns the record information which means whether the record is imported or not
    **This function called while importing records to this module*/
    public function importRecord($obj, $inventoryFieldData, $lineItemDetails)
    {
        $entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);

        return $entityInfo;
    }

    /*Function to return the status count of imported records in current module.
    **This function called while importing records to this module*/
    public function getImportStatusCount($obj)
    {
        $statusCount = getImportStatusCount($obj);

        return $statusCount;
    }

    public function undoLastImport($obj, $user)
    {
        $undoLastImport = undoLastImport($obj, $user);
    }

    /** Function to export the lead records in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export SalesOrder Query.
     */
    public function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("SalesOrder", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);
        $fields_list .= getInventoryFieldsForExport($this->table_name);
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_salesorder ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_salesordercf ON vtiger_salesordercf.salesorderid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_sobillads ON vtiger_sobillads.sobilladdressid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_soshipads ON vtiger_soshipads.soshipaddressid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_salesorder.contact_id
				LEFT JOIN vtiger_invoice_recurring_info ON vtiger_invoice_recurring_info.salesorderid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_salesorder.potential_id
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_salesorder.account_id
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_salesorder.currency_id
				LEFT JOIN vtiger_quotes ON vtiger_quotes.quoteid = vtiger_salesorder.quote_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id";

        $query .= $this->getNonAdminAccessControlQuery('SalesOrder', $current_user);
        $where_auto = " vtiger_crmentity.deleted=0";

        if ($where != "") {
            $query .= " where ($where) AND " . $where_auto;
        } else {
            $query .= " where " . $where_auto;
        }

        $log->debug("Exiting create_export_query method ...");

        return $query;
    }

    /**
     * Function which will give the basic query to find duplicates
     *
     * @param <String>  $module
     * @param <String>  $tableColumns
     * @param <String>  $selectedColumns
     * @param <Boolean> $ignoreEmpty
     * @param <Array>   $requiredTables
     *
     * @return string
     */
    // Note : remove getDuplicatesQuery API once vtiger5 code is removed
    public function getQueryForDuplicates($module, $tableColumns, $selectedColumns = '', $ignoreEmpty = false, $requiredTables = [])
    {
        if (is_array($tableColumns)) {
            $tableColumnsString = implode(',', $tableColumns);
        }
        $selectClause = "SELECT " . $this->table_name . "." . $this->table_index . " AS recordid," . $tableColumnsString;

        // Select Custom Field Table Columns if present
        if (isset($this->customFieldTable)) {
            $query .= ", " . $this->customFieldTable[0] . ".* ";
        }

        $fromClause = " FROM $this->table_name";

        $fromClause .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

        if ($this->tab_name) {
            foreach ($this->tab_name as $tableName) {
                if ($tableName != 'vtiger_crmentity' && $tableName != $this->table_name && in_array($tableName, $requiredTables)) {
                    if ($tableName == 'vtiger_invoice_recurring_info') {
                        $fromClause .= " LEFT JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
                            " = $this->table_name.$this->table_index";
                    } elseif ($this->tab_name_index[$tableName]) {
                        $fromClause .= " INNER JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
                            " = $this->table_name.$this->table_index";
                    }
                }
            }
        }
        $fromClause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";

        $whereClause = " WHERE vtiger_crmentity.deleted = 0";
        $whereClause .= $this->getListViewSecurityParameter($module);

        if ($ignoreEmpty) {
            foreach ($tableColumns as $tableColumn) {
                $whereClause .= " AND ($tableColumn IS NOT NULL AND $tableColumn != '') ";
            }
        }

        if (isset($selectedColumns) && trim($selectedColumns) != '') {
            $sub_query = "SELECT $selectedColumns FROM $this->table_name AS t " .
                " INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
            // Consider custom table join as well.
            if (isset($this->customFieldTable)) {
                $sub_query .= " LEFT JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
            }
            $sub_query .= " WHERE crm.deleted=0 GROUP BY $selectedColumns HAVING COUNT(*)>1";
        } else {
            $sub_query = "SELECT $tableColumnsString $fromClause $whereClause GROUP BY $tableColumnsString HAVING COUNT(*)>1";
        }

        $i = 1;
        foreach ($tableColumns as $tableColumn) {
            $tableInfo = explode('.', $tableColumn);
            $duplicateCheckClause .= " ifnull($tableColumn,'null') = ifnull(temp.$tableInfo[1],'null')";
            if (php7_count($tableColumns) != $i++) {
                $duplicateCheckClause .= " AND ";
            }
        }

        $query = $selectClause . $fromClause .
            " LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
            " INNER JOIN (" . $sub_query . ") AS temp ON " . $duplicateCheckClause .
            $whereClause .
            " ORDER BY $tableColumnsString," . $this->table_name . "." . $this->table_index . " ASC";

        return $query;
    }

    /**
     * Function to get importable mandatory fields
     * By default some fields like Quantity, List Price is not mandaroty for Invertory modules but
     * import fails if those fields are not mapped during import.
     */
    public function getMandatoryImportableFields()
    {
        return getInventoryImportableMandatoryFeilds($this->moduleName);
    }
}
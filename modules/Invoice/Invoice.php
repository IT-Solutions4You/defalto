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
 * Description: Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class Invoice extends CRMEntity
{
    public string $parentName = 'Inventory';
    public $log;
    public $db;

    public $table_name = "vtiger_invoice";
    public $table_index = 'invoiceid';
    public $tab_name = ['vtiger_crmentity', 'vtiger_invoice', 'vtiger_invoicebillads', 'vtiger_invoiceshipads', 'vtiger_invoicecf'];
    public $tab_name_index = [
        'vtiger_crmentity'           => 'crmid',
        'vtiger_invoice'             => 'invoiceid',
        'vtiger_invoicebillads'      => 'invoicebilladdressid',
        'vtiger_invoiceshipads'      => 'invoiceshipaddressid',
        'vtiger_invoicecf'           => 'invoiceid',
    ];
    public $customFieldTable = ['vtiger_invoicecf', 'invoiceid'];

    public $column_fields = [];

    public $update_product_array = [];

    public $sortby_fields = ['subject', 'invoice_no', 'invoicestatus', 'smownerid', 'accountname', 'lastname'];

    // This is used to retrieve related vtiger_fields from form posts.
    public $additional_column_fields = [
        'assigned_user_name',
        'smownerid',
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
        //'Invoice No'=>Array('crmentity'=>'crmid'),
        'Invoice No'  => ['invoice' => 'invoice_no'],
        'Subject'     => ['invoice' => 'subject'],
        'Sales Order' => ['invoice' => 'salesorderid'],
        'Status'      => ['invoice' => 'invoicestatus'],
        'Total'       => ['invoice' => 'total'],
        'Assigned To' => ['crmentity' => 'smownerid']
    ];

    public $list_fields_name = [
        'Invoice No'  => 'invoice_no',
        'Subject'     => 'subject',
        'Sales Order' => 'salesorder_id',
        'Status'      => 'invoicestatus',
        'Total'       => 'price_total',
        'Assigned To' => 'assigned_user_id'
    ];
    public $list_link_field = 'subject';

    public $search_fields = [
        //'Invoice No'=>Array('crmentity'=>'crmid'),
        'Invoice No'   => ['invoice' => 'invoice_no'],
        'Subject'      => ['purchaseorder' => 'subject'],
        'Account Name' => ['contactdetails' => 'account_id'],
        'Created Date' => ['crmentity' => 'createdtime'],
        'Assigned To'  => ['crmentity' => 'smownerid'],
    ];

    public $search_fields_name = [
        'Invoice No'   => 'invoice_no',
        'Subject'      => 'subject',
        'Account Name' => 'account_id',
        'Created Time' => 'createdtime',
        'Assigned To'  => 'assigned_user_id'
    ];

    // This is the list of vtiger_fields that are required.
    public $required_fields = ["accountname" => 1];

    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = 'crmid';
    public $default_sort_order = 'ASC';

    //public $groupTable = Array('vtiger_invoicegrouprelation','invoiceid');

    public $mandatory_fields = ['subject', 'createdtime', 'modifiedtime', 'assigned_user_id', 'quantity', 'listprice', 'productid'];
    public $_salesorderid;
    public $_recurring_mode;

    // For Alphabetical search
    public $def_basicsearch_col = 'subject';

    public $entity_table = "vtiger_crmentity";

    // For workflows update field tasks is deleted all the lineitems.
    public $isLineItemUpdate = true;

    /**    Constructor which will set the column_fields in this object
     */
    public function __construct()
    {
        $this->log = Logger::getLogger('Invoice');
        $this->log->debug("Entering Invoice() method ...");
        $this->db = PearDatabase::getInstance();
        $this->column_fields = getColumnFields('Invoice');
        $this->log->debug("Exiting Invoice method ...");
    }


    /** Function to handle the module specific save operations
     */
    public function save_module($module)
    {
        global $updateInventoryProductRel_deduct_stock;
        $updateInventoryProductRel_deduct_stock = true;

        /* $_REQUEST['REQUEST_FROM_WS'] is set from webservices script.
         * Depending on $_REQUEST['totalProductCount'] value inserting line items into DB.
         * This should be done by webservices, not be normal save of Inventory record.
         * So unsetting the value $_REQUEST['totalProductCount'] through check point
         */
        if (isset($_REQUEST['REQUEST_FROM_WS']) && $_REQUEST['REQUEST_FROM_WS']) {
            unset($_REQUEST['totalProductCount']);
        }
        //in ajax save we should not call this function, because this will delete all the existing product values
        if (isset($this->_recurring_mode) && $this->_recurring_mode == 'recurringinvoice_from_so' && isset($this->_salesorderid) && $this->_salesorderid != '') {
            // We are getting called from the RecurringInvoice cron service!
            $this->createRecurringInvoiceFromSO();
        } elseif (isset($_REQUEST) && $_REQUEST['action'] == 'InvoiceAjax' || $_REQUEST['action'] == 'MassEditSave' || $_REQUEST['action'] == 'FROM_WS') {
            $updateInventoryProductRel_deduct_stock = false;
        }
        // Update the currency id and the conversion rate for the invoice
        $update_query = "update vtiger_invoice set currency_id=?, conversion_rate=? where invoiceid=?";

        $update_params = [$this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id];
        $this->db->pquery($update_query, $update_params);
    }

    /**
     * Customizing the restore procedure.
     */
    public function restore($module, $id)
    {
        global $updateInventoryProductRel_deduct_stock;
        $status = getInvoiceStatus($id);
        if ($status != 'Cancel') {
            $updateInventoryProductRel_deduct_stock = true;
        }
        parent::restore($module, $id);
    }

    /**
     * Customizing the Delete procedure.
     */
    public function trash($module, $recordId)
    {
        $status = getInvoiceStatus($recordId);
        if ($status != 'Cancel') {
            addProductsToStock($recordId);
        }
        parent::trash($module, $recordId);
    }

    /**    function used to get the name of the current object
     * @return string $this->name - name of the current object
     */
    public function get_summary_text()
    {
        global $log;
        $log->debug("Entering get_summary_text() method ...");
        $log->debug("Exiting get_summary_text method ...");

        return $this->name;
    }

    // Function to get column name - Overriding function of base class
    public function get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype = '')
    {
        if ($columname == 'salesorderid') {
            if ($fldvalue == '') {
                return null;
            }
        }

        return parent::get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype);
    }

    /*
     * Function to get the secondary query part of a report
     * @param - $module primary module name
     * @param - $secmodule secondary module name
     * returns the query string formed on fetching the related data for report for secondary module
     */
    public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
    {
        // Define the dependency matrix ahead
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityInvoice', ['vtiger_usersInvoice', 'vtiger_groupsInvoice', 'vtiger_lastModifiedByInvoice']);

        if (!$queryPlanner->requireTable('vtiger_invoice', $matrix)) {
            return '';
        }

        $matrix->setDependency('vtiger_invoice', [
            'vtiger_crmentityInvoice',
            "vtiger_currency_info$secmodule",
            'vtiger_invoicecf',
            'vtiger_salesorderInvoice',
            'vtiger_invoicebillads',
            'vtiger_invoiceshipads',
            'vtiger_contactdetailsInvoice',
            'vtiger_accountInvoice'
        ]);

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_invoice", "invoiceid", $queryPlanner);

        if ($queryPlanner->requireTable('vtiger_crmentityInvoice', $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityInvoice on vtiger_crmentityInvoice.crmid=vtiger_invoice.invoiceid and vtiger_crmentityInvoice.deleted=0";
        }
        if ($queryPlanner->requireTable('vtiger_invoicecf')) {
            $query .= " left join vtiger_invoicecf on vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid";
        }
        if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_invoice.currency_id";
        }
        if ($queryPlanner->requireTable('vtiger_salesorderInvoice')) {
            $query .= " left join vtiger_salesorder as vtiger_salesorderInvoice on vtiger_salesorderInvoice.salesorderid=vtiger_invoice.salesorderid";
        }
        if ($queryPlanner->requireTable('vtiger_invoicebillads')) {
            $query .= " left join vtiger_invoicebillads on vtiger_invoice.invoiceid=vtiger_invoicebillads.invoicebilladdressid";
        }
        if ($queryPlanner->requireTable('vtiger_invoiceshipads')) {
            $query .= " left join vtiger_invoiceshipads on vtiger_invoice.invoiceid=vtiger_invoiceshipads.invoiceshipaddressid";
        }
        if ($queryPlanner->requireTable('vtiger_groupsInvoice')) {
            $query .= " left join vtiger_groups as vtiger_groupsInvoice on vtiger_groupsInvoice.groupid = vtiger_crmentityInvoice.smownerid";
        }
        if ($queryPlanner->requireTable('vtiger_usersInvoice')) {
            $query .= " left join vtiger_users as vtiger_usersInvoice on vtiger_usersInvoice.id = vtiger_crmentityInvoice.smownerid";
        }
        if ($queryPlanner->requireTable('vtiger_contactdetailsInvoice')) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsInvoice on vtiger_invoice.contactid = vtiger_contactdetailsInvoice.contactid";
        }
        if ($queryPlanner->requireTable('vtiger_accountInvoice')) {
            $query .= " left join vtiger_account as vtiger_accountInvoice on vtiger_accountInvoice.accountid = vtiger_invoice.accountid";
        }
        if ($queryPlanner->requireTable('vtiger_lastModifiedByInvoice')) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByInvoice on vtiger_lastModifiedByInvoice.id = vtiger_crmentityInvoice.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyInvoice")) {
            $query .= " left join vtiger_users as vtiger_createdbyInvoice on vtiger_createdbyInvoice.id = vtiger_crmentityInvoice.smcreatorid ";
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
            "Documents" => ["vtiger_senotesrel" => ["crmid", "notesid"], "vtiger_invoice" => "invoiceid"],
            "Accounts"  => ["vtiger_invoice" => ["invoiceid", "accountid"]],
            "Contacts"  => ["vtiger_invoice" => ["invoiceid", "contactid"]],
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

        if ($return_module == 'Accounts' || $return_module == 'Contacts') {
            $this->trash('Invoice', $id);
        } elseif ($return_module == 'SalesOrder') {
            $relation_query = 'UPDATE vtiger_invoice set salesorderid=? where invoiceid=?';
            $this->db->pquery($relation_query, [null, $id]);
        } elseif ($return_module == 'Documents') {
            $sql = 'DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } else {
            parent::unlinkRelationship($id, $return_module, $return_id);
        }
    }

    /*
     * Function to get the relations of salesorder to invoice for recurring invoice procedure
     * @param - $salesorder_id Salesorder ID
     */
    public function createRecurringInvoiceFromSO()
    {
        global $adb;
        $salesorder_id = $this->_salesorderid;
        $query1 = "SELECT * FROM vtiger_inventoryproductrel WHERE id=?";
        $res = $adb->pquery($query1, [$salesorder_id]);
        $no_of_products = $adb->num_rows($res);
        $fieldsList = $adb->getFieldsArray($res);
        $update_stock = [];
        for ($j = 0; $j < $no_of_products; $j++) {
            $row = $adb->query_result_rowdata($res, $j);
            $col_value = [];
            for ($k = 0; $k < php7_count($fieldsList); $k++) {
                if ($fieldsList[$k] != 'lineitem_id') {
                    $col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
                }
            }
            if (php7_count($col_value) > 0) {
                $col_value['id'] = $this->id;
                $columns = array_keys($col_value);
                $values = array_values($col_value);
                $query2 = "INSERT INTO vtiger_inventoryproductrel(" . implode(",", $columns) . ") VALUES (" . generateQuestionMarks($values) . ")";
                $adb->pquery($query2, [$values]);
                $qty = $col_value['quantity'];
                $update_stock[$col_value['sequence_no']] = $qty;
            }
        }

        $query1 = "SELECT * FROM vtiger_inventorysubproductrel WHERE id=?";
        $res = $adb->pquery($query1, [$salesorder_id]);
        $no_of_products = $adb->num_rows($res);
        $fieldsList = $adb->getFieldsArray($res);
        for ($j = 0; $j < $no_of_products; $j++) {
            $row = $adb->query_result_rowdata($res, $j);
            $col_value = [];
            for ($k = 0; $k < php7_count($fieldsList); $k++) {
                $col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
            }
            if (php7_count($col_value) > 0) {
                $col_value['id'] = $this->id;
                $columns = array_keys($col_value);
                $values = array_values($col_value);
                $query2 = "INSERT INTO vtiger_inventorysubproductrel(" . implode(",", $columns) . ") VALUES (" . generateQuestionMarks($values) . ")";
                $adb->pquery($query2, [$values]);
            }
        }

        //Adding charge values
        $adb->pquery('DELETE FROM vtiger_inventorychargesrel WHERE recordid = ?', [$this->id]);
        $adb->pquery('INSERT INTO vtiger_inventorychargesrel SELECT ?, charges FROM vtiger_inventorychargesrel WHERE recordid = ?', [$this->id, $salesorder_id]);

        //Update the netprice (subtotal), taxtype, discount, S&H charge, adjustment and total for the Invoice
        $updatequery = " UPDATE vtiger_invoice SET ";
        $updateparams = [];
        // Remaining column values to be updated -> column name to field name mapping
        $invoice_column_field = [
            'adjustment'       => 'adjustment',
            'subtotal'         => 'subtotal',
            'total'            => 'total',
            'taxtype'          => 'hdnTaxType',
            'discount_amount'  => 'discount_amount',
            'region_id'        => 'region_id',
            'balance'          => 'total'
        ];
        $updatecols = [];
        foreach ($invoice_column_field as $col => $field) {
            $updatecols[] = "$col=?";
            $updateparams[] = $this->column_fields[$field];
        }
        if (php7_count($updatecols) > 0) {
            $updatequery .= implode(",", $updatecols);

            $updatequery .= " WHERE invoiceid=?";
            array_push($updateparams, $this->id);

            $adb->pquery($updatequery, $updateparams);
        }
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
     * Returns Export Invoice Query.
     */
    public function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("Invoice", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);
        $fields_list .= getInventoryFieldsForExport($this->table_name);
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_invoice ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_salesorder ON vtiger_salesorder.salesorderid = vtiger_invoice.salesorderid
				LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_invoice.contactid
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_invoice.accountid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_invoice.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

        $query .= $this->getNonAdminAccessControlQuery('Invoice', $current_user);
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
     * Function to get importable mandatory fields
     * By default some fields like Quantity, List Price is not mandaroty for Invertory modules but
     * import fails if those fields are not mapped during import.
     */
    public function getMandatoryImportableFields()
    {
        return getInventoryImportableMandatoryFeilds($this->moduleName);
    }
}
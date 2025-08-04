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

class Invoice extends CRMEntity
{
    public string $parentName = 'Inventory';
    var $log;
    var $db;

    var $table_name = "vtiger_invoice";
    var $table_index = 'invoiceid';
    var $tab_name = ['vtiger_crmentity', 'vtiger_invoice', 'vtiger_invoicebillads', 'vtiger_invoiceshipads', 'vtiger_invoicecf', 'vtiger_inventoryproductrel'];
    var $tab_name_index = [
        'vtiger_crmentity'           => 'crmid',
        'vtiger_invoice'             => 'invoiceid',
        'vtiger_invoicebillads'      => 'invoicebilladdressid',
        'vtiger_invoiceshipads'      => 'invoiceshipaddressid',
        'vtiger_invoicecf'           => 'invoiceid',
        'vtiger_inventoryproductrel' => 'id'
    ];
    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = ['vtiger_invoicecf', 'invoiceid'];

    var $column_fields = [];

    var $update_product_array = [];

    var $sortby_fields = ['subject', 'invoice_no', 'invoicestatus', 'assigned_user_id', 'accountname', 'lastname'];

    // This is used to retrieve related vtiger_fields from form posts.
    var $additional_column_fields = [
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
    var $list_fields = [
        //'Invoice No'=>Array('crmentity'=>'crmid'),
        'Invoice No'  => ['invoice' => 'invoice_no'],
        'Subject'     => ['invoice' => 'subject'],
        'Sales Order' => ['invoice' => 'salesorder_id'],
        'Status'      => ['invoice' => 'invoicestatus'],
        'Total'       => ['invoice' => 'grand_total'],
        'Assigned To' => ['crmentity' => 'assigned_user_id']
    ];

    var $list_fields_name = [
        'Invoice No'  => 'invoice_no',
        'Subject'     => 'subject',
        'Sales Order' => 'salesorder_id',
        'Status'      => 'invoicestatus',
        'Total'       => 'grand_total',
        'Assigned To' => 'assigned_user_id'
    ];
    var $list_link_field = 'subject';

    var $search_fields = [
        //'Invoice No'=>Array('crmentity'=>'crmid'),
        'Invoice No'   => ['invoice' => 'invoice_no'],
        'Subject'      => ['purchaseorder' => 'subject'],
        'Account Name' => ['contactdetails' => 'account_id'],
        'Created Date' => ['crmentity' => 'createdtime'],
        'Assigned To'  => ['crmentity' => 'assigned_user_id'],
    ];

    var $search_fields_name = [
        'Invoice No'   => 'invoice_no',
        'Subject'      => 'subject',
        'Account Name' => 'account_id',
        'Created Time' => 'createdtime',
        'Assigned To'  => 'assigned_user_id'
    ];

    // This is the list of vtiger_fields that are required.
    var $required_fields = ["accountname" => 1];

    //Added these variables which are used as default order by and sortorder in ListView
    var $default_order_by = 'crmid';
    var $default_sort_order = 'ASC';

    //var $groupTable = Array('vtiger_invoicegrouprelation','invoiceid');

    var $mandatory_fields = ['subject', 'createdtime', 'modifiedtime', 'assigned_user_id', 'quantity', 'listprice', 'productid'];
    var $_salesorderid;
    var $_recurring_mode;

    // For Alphabetical search
    var $def_basicsearch_col = 'subject';

    var $entity_table = "vtiger_crmentity";

    // For workflows update field tasks is deleted all the lineitems.
    var $isLineItemUpdate = true;

    /**    Constructor which will set the column_fields in this object
     */
    function __construct()
    {
        $this->log = Logger::getLogger('Invoice');
        $this->log->debug("Entering Invoice() method ...");
        $this->db = PearDatabase::getInstance();
        $this->column_fields = getColumnFields('Invoice');
        $this->log->debug("Exiting Invoice method ...");
    }

    /** Function to handle the module specific save operations
     */

    function save_module($module)
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
        } elseif (isset($_REQUEST)) {
            if ($_REQUEST['action'] != 'InvoiceAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
                && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
                && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {
                //Based on the total Number of rows we will save the product relationship with this entity
                saveInventoryProductDetails($this, 'Invoice');
            } elseif ($_REQUEST['action'] == 'InvoiceAjax' || $_REQUEST['action'] == 'MassEditSave' || $_REQUEST['action'] == 'FROM_WS') {
                $updateInventoryProductRel_deduct_stock = false;
            }
        }
        // Update the currency id and the conversion rate for the invoice
        $update_query = "update vtiger_invoice set currency_id=?, conversion_rate=? where invoiceid=?";

        $update_params = [$this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id];
        $this->db->pquery($update_query, $update_params);
    }

    /**
     * Customizing the restore procedure.
     */
    function restore($module, $id)
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
    function trash($module, $recordId)
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
    function get_summary_text()
    {
        global $log;
        $log->debug("Entering get_summary_text() method ...");
        $log->debug("Exiting get_summary_text method ...");

        return $this->name;
    }

    // Function to get column name - Overriding function of base class
    function get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype = '')
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
    function generateReportsSecQuery($module, $secmodule, $queryPlanner)
    {
        // Define the dependency matrix ahead
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityInvoice', ['vtiger_usersInvoice', 'vtiger_groupsInvoice', 'vtiger_lastModifiedByInvoice']);
        $matrix->setDependency('vtiger_inventoryproductrelInvoice', ['vtiger_productsInvoice', 'vtiger_serviceInvoice']);

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
            'vtiger_inventoryproductrelInvoice',
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
            $query .= " left join vtiger_salesorder as vtiger_salesorderInvoice on vtiger_salesorderInvoice.salesorderid=vtiger_invoice.salesorder_id";
        }
        if ($queryPlanner->requireTable('vtiger_invoicebillads')) {
            $query .= " left join vtiger_invoicebillads on vtiger_invoice.invoiceid=vtiger_invoicebillads.invoicebilladdressid";
        }
        if ($queryPlanner->requireTable('vtiger_invoiceshipads')) {
            $query .= " left join vtiger_invoiceshipads on vtiger_invoice.invoiceid=vtiger_invoiceshipads.invoiceshipaddressid";
        }
        if ($queryPlanner->requireTable('vtiger_inventoryproductrelInvoice', $matrix)) {
        }
        if ($queryPlanner->requireTable('vtiger_productsInvoice')) {
            $query .= " left join vtiger_products as vtiger_productsInvoice on vtiger_productsInvoice.productid = vtiger_inventoryproductreltmpInvoice.productid";
        }
        if ($queryPlanner->requireTable('vtiger_serviceInvoice')) {
            $query .= " left join vtiger_service as vtiger_serviceInvoice on vtiger_serviceInvoice.serviceid = vtiger_inventoryproductreltmpInvoice.productid";
        }
        if ($queryPlanner->requireTable('vtiger_groupsInvoice')) {
            $query .= " left join vtiger_groups as vtiger_groupsInvoice on vtiger_groupsInvoice.groupid = vtiger_crmentityInvoice.assigned_user_id";
        }
        if ($queryPlanner->requireTable('vtiger_usersInvoice')) {
            $query .= " left join vtiger_users as vtiger_usersInvoice on vtiger_usersInvoice.id = vtiger_crmentityInvoice.assigned_user_id";
        }
        if ($queryPlanner->requireTable('vtiger_contactdetailsInvoice')) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsInvoice on vtiger_invoice.contact_id = vtiger_contactdetailsInvoice.contactid";
        }
        if ($queryPlanner->requireTable('vtiger_accountInvoice')) {
            $query .= " left join vtiger_account as vtiger_accountInvoice on vtiger_accountInvoice.accountid = vtiger_invoice.account_id";
        }
        if ($queryPlanner->requireTable('vtiger_lastModifiedByInvoice')) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByInvoice on vtiger_lastModifiedByInvoice.id = vtiger_crmentityInvoice.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyInvoice")) {
            $query .= " left join vtiger_users as vtiger_createdbyInvoice on vtiger_createdbyInvoice.id = vtiger_crmentityInvoice.creator_user_id ";
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
            "Documents" => ["vtiger_senotesrel" => ["crmid", "notesid"], "vtiger_invoice" => "invoiceid"],
            "Accounts"  => ["vtiger_invoice" => ["invoiceid", "account_id"]],
            "Contacts"  => ["vtiger_invoice" => ["invoiceid", "contact_id"]],
        ];

        return $rel_tables[$secmodule];
    }

    // Function to unlink an entity with given Id from another entity
    function unlinkRelationship($id, $return_module, $return_id)
    {
        global $log;
        if (empty($return_module) || empty($return_id)) {
            return;
        }

        if ($return_module == 'Accounts' || $return_module == 'Contacts') {
            $this->trash('Invoice', $id);
        } elseif ($return_module == 'SalesOrder') {
            $relation_query = 'UPDATE vtiger_invoice set salesorder_id=? where invoiceid=?';
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
    function createRecurringInvoiceFromSO()
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
            'grand_total'      => 'grand_total',
            'taxtype'          => 'taxtype',
            'discount_percent' => 'hdnDiscountPercent',
            'discount_amount'  => 'hdnDiscountAmount',
            's_h_amount'       => 's_h_amount',
            'region_id'        => 'region_id',
            's_h_percent'      => 's_h_percent',
            'balance'          => 'grand_total'
        ];
        $updatecols = [];
        foreach ($invoice_column_field as $col => $field) {
            $updatecols[] = "$col=?";
            $updateparams[] = $this->column_fields[$col] ?: $this->column_fields[$field];
        }
        if (php7_count($updatecols) > 0) {
            $updatequery .= implode(",", $updatecols);

            $updatequery .= " WHERE invoiceid=?";
            array_push($updateparams, $this->id);

            $adb->pquery($updatequery, $updateparams);
        }
    }

    function insertIntoEntityTable($table_name, $module, $fileid = '')
    {
        //Ignore relation table insertions while saving of the record
        if ($table_name == 'vtiger_inventoryproductrel') {
            return;
        }
        parent::insertIntoEntityTable($table_name, $module, $fileid);
    }

    /*Function to create records in current module.
    **This function called while importing records to this module*/
    function createRecords($obj)
    {
        $createRecords = createRecords($obj);

        return $createRecords;
    }

    /*Function returns the record information which means whether the record is imported or not
    **This function called while importing records to this module*/
    function importRecord($obj, $inventoryFieldData, $lineItemDetails)
    {
        $entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);

        return $entityInfo;
    }

    /*Function to return the status count of imported records in current module.
    **This function called while importing records to this module*/
    function getImportStatusCount($obj)
    {
        $statusCount = getImportStatusCount($obj);

        return $statusCount;
    }

    function undoLastImport($obj, $user)
    {
        $undoLastImport = undoLastImport($obj, $user);
    }

    /** Function to export the lead records in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export Invoice Query.
     */
    function create_export_query($where)
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
				LEFT JOIN vtiger_salesorder ON vtiger_salesorder.salesorderid = vtiger_invoice.salesorder_id
				LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_invoice.contact_id
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_invoice.account_id
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_invoice.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id";

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
    function getMandatoryImportableFields()
    {
        return getInventoryImportableMandatoryFeilds($this->moduleName);
    }
}
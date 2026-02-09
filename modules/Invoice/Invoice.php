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
    public string $moduleVersion = '1.2';
    public string $moduleName = 'Invoice';
    public string $parentName = 'SALES';

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

    public $update_product_array = [];

    public $sortby_fields = ['subject', 'invoice_no', 'invoicestatus', 'assigned_user_id', 'accountname', 'lastname'];

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
        //'Invoice No'=>Array('crmentity'=>'crmid'),
        'Invoice No'  => ['invoice' => 'invoice_no'],
        'Subject'     => ['invoice' => 'subject'],
        'Sales Order' => ['invoice' => 'salesorder_id'],
        'Status'      => ['invoice' => 'invoicestatus'],
        'Total'       => ['invoice' => 'grand_total'],
        'Assigned To' => ['crmentity' => 'assigned_user_id']
    ];

    public $list_fields_name = [
        'Invoice No'  => 'invoice_no',
        'Subject'     => 'subject',
        'Sales Order' => 'salesorder_id',
        'Status'      => 'invoicestatus',
        'Total'       => 'grand_total',
        'Assigned To' => 'assigned_user_id'
    ];
    public $list_link_field = 'subject';

    public $search_fields = [
        //'Invoice No'=>Array('crmentity'=>'crmid'),
        'Invoice No'   => ['invoice' => 'invoice_no'],
        'Subject'      => ['purchaseorder' => 'subject'],
        'Account Name' => ['contactdetails' => 'account_id'],
        'Created Date' => ['crmentity' => 'createdtime'],
        'Assigned To'  => ['crmentity' => 'assigned_user_id'],
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

    public $mandatory_fields = ['subject', 'createdtime', 'modifiedtime', 'assigned_user_id', 'quantity', 'listprice', 'productid'];
    public $_salesorderid;

    // For Alphabetical search
    public $def_basicsearch_col = 'subject';

    public $entity_table = "vtiger_crmentity";

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $sourceModule = $request->get('sourceModule');
        $sourceRecord = (int)$request->get('sourceRecord');

        if (!empty($this->_salesorderid)) {
            InventoryItem_CopyOnCreate_Model::run($this, $this->_salesorderid);
        } elseif ((empty($sourceModule) || empty($sourceRecord)) && !empty($this->column_fields['salesorder_id'])) {
            InventoryItem_CopyOnCreate_Model::run($this, $this->column_fields['salesorder_id']);
        } elseif (is_numeric($request->get('duplicateFrom'))) {
            InventoryItem_CopyOnCreate_Model::run($this, $request->get('duplicateFrom'));
        } else {
            InventoryItem_CopyOnCreate_Model::run($this);
        }
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
     * Function to get the relation tables for related modules
     * @param - $secmodule secondary module name
     * returns the array with table names and fieldnames storing relations between module and this module
     */
    public function setRelationTables($secmodule)
    {
        $rel_tables = [
            'Accounts' => ['vtiger_invoice' => ['invoiceid', 'account_id']],
            'Contacts' => ['vtiger_invoice' => ['invoiceid', 'contact_id']],
        ];

        return $rel_tables[$secmodule];
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
				LEFT JOIN vtiger_salesorder ON vtiger_salesorder.salesorderid = vtiger_invoice.salesorder_id
				LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
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
    public function getMandatoryImportableFields()
    {
        return getInventoryImportableMandatoryFeilds($this->moduleName);
    }
}
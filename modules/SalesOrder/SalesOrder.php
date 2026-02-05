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
    public array $tab_name_left_join = [
        'vtiger_invoice_recurring_info'
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
        } elseif (is_numeric($request->get('duplicateFrom'))) {
            InventoryItem_CopyOnCreate_Model::run($this, $request->get('duplicateFrom'));
        } else {
            InventoryItem_CopyOnCreate_Model::run($this);
        }
    }

    /*
     * Function to get the relation tables for related modules
     * @param - $secmodule secondary module name
     * returns the array with table names and fieldnames storing relations between module and this module
     */
    public function setRelationTables($secmodule)
    {
        $rel_tables = [
            'Invoice' => ['vtiger_invoice' => ['salesorder_id', 'invoiceid'], 'vtiger_salesorder' => 'salesorderid'],
        ];

        return $rel_tables[$secmodule];
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
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
class Quotes extends CRMEntity
{
    public string $moduleVersion = '1.1';
    public string $moduleName = 'Quotes';
    public string $parentName = 'SALES';

    var $table_name = "vtiger_quotes";
    var $table_index = 'quoteid';
    var $tab_name = ['vtiger_crmentity', 'vtiger_quotes', 'vtiger_quotesbillads', 'vtiger_quotesshipads', 'vtiger_quotescf'];
    var $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'vtiger_quotes' => 'quoteid',
        'vtiger_quotesbillads' => 'quotebilladdressid',
        'vtiger_quotesshipads' => 'quoteshipaddressid',
        'vtiger_quotescf' => 'quoteid'
    ];
    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = ['vtiger_quotescf', 'quoteid'];
    var $entity_table = "vtiger_crmentity";

    var $billadr_table = "vtiger_quotesbillads";

    var $object_name = "Quote";

    var $new_schema = true;

    var $sortby_fields = ['subject', 'crmid', 'assigned_user_id', 'accountname', 'lastname'];

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
        //'Quote No'=>Array('crmentity'=>'crmid'),
        // Module Sequence Numbering
        'Quote No' => ['quotes' => 'quote_no'],
        // END
        'Subject' => ['quotes' => 'subject'],
        'Quote Stage' => ['quotes' => 'quotestage'],
        'Potential Name' => ['quotes' => 'potential_id'],
        'Account Name' => ['account' => 'account_id'],
        'Total' => ['quotes' => 'total'],
        'Assigned To' => ['crmentity' => 'assigned_user_id']
    ];

    var $list_fields_name = [
        'Quote No' => 'quote_no',
        'Subject' => 'subject',
        'Quote Stage' => 'quotestage',
        'Potential Name' => 'potential_id',
        'Account Name' => 'account_id',
        'Total' => 'grand_total',
        'Assigned To' => 'assigned_user_id'
    ];
    var $list_link_field = 'subject';

    var $search_fields = [
        'Quote No' => ['quotes' => 'quote_no'],
        'Subject' => ['quotes' => 'subject'],
        'Account Name' => ['quotes' => 'account_id'],
        'Quote Stage' => ['quotes' => 'quotestage'],
    ];

    var $search_fields_name = [
        'Quote No' => 'quote_no',
        'Subject' => 'subject',
        'Account Name' => 'account_id',
        'Quote Stage' => 'quotestage',
    ];

    // This is the list of vtiger_fields that are required.
    var $required_fields = ["accountname" => 1];

    var $mandatory_fields = ['subject', 'createdtime', 'modifiedtime', 'assigned_user_id', 'quantity', 'listprice', 'productid'];

    // For Alphabetical search
    var $def_basicsearch_col = 'subject';

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        if (is_numeric($request->get('duplicateFrom'))) {
            InventoryItem_CopyOnCreate_Model::run($this, $request->get('duplicateFrom'));
        } else {
            InventoryItem_CopyOnCreate_Model::run($this);
        }
    }

    // Function to get column name - Overriding function of base class
    function get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype = '')
    {
        if ($columname == 'potentialid' || $columname == 'contactid') {
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
            'SalesOrder' => ['vtiger_salesorder' => ['quote_id', 'salesorderid'], 'vtiger_quotes' => 'quoteid'],
            'Accounts' => ['vtiger_quotes' => ['quoteid', 'account_id']],
            'Contacts' => ['vtiger_quotes' => ['quoteid', 'contact_id']],
            'Potentials' => ['vtiger_quotes' => ['quoteid', 'potential_id']],
        ];

        return $rel_tables[$secmodule];
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
     * Returns Export Quotes Query.
     */
    function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("Quotes", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_quotes ON vtiger_quotes.quoteid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_quotescf ON vtiger_quotescf.quoteid = vtiger_quotes.quoteid
				LEFT JOIN vtiger_quotesbillads ON vtiger_quotesbillads.quotebilladdressid = vtiger_quotes.quoteid
				LEFT JOIN vtiger_quotesshipads ON vtiger_quotesshipads.quoteshipaddressid = vtiger_quotes.quoteid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_quotes.contact_id
				LEFT JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_quotes.potential_id
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_quotes.account_id
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_quotes.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id";

        $query .= $this->getNonAdminAccessControlQuery('Quotes', $current_user);
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
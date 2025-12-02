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
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PurchaseOrder extends CRMEntity
{
    public string $moduleName = 'PurchaseOrder';
    public string $parentName = 'INVENTORY';

    public $table_name = 'vtiger_purchaseorder';
    public $table_index = 'purchaseorderid';
    public $tab_name = ['vtiger_crmentity', 'vtiger_purchaseorder', 'vtiger_pobillads', 'vtiger_poshipads', 'vtiger_purchaseordercf'];
    public $tab_name_index = [
        'vtiger_crmentity'       => 'crmid',
        'vtiger_purchaseorder'   => 'purchaseorderid',
        'vtiger_pobillads'       => 'pobilladdressid',
        'vtiger_poshipads'       => 'poshipaddressid',
        'vtiger_purchaseordercf' => 'purchaseorderid'
    ];
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_purchaseordercf', 'purchaseorderid'];
    public $entity_table = 'vtiger_crmentity';

    public $billadr_table = 'vtiger_pobillads';

    public $sortby_fields = ['subject', 'tracking_no', 'assigned_user_id', 'lastname'];

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
        //  Module Sequence Numbering
        //'Order No'=>Array('crmentity'=>'crmid'),
        'Order No'        => ['purchaseorder' => 'purchaseorder_no'],
        // END
        'Subject'         => ['purchaseorder' => 'subject'],
        'Vendor Name'     => ['purchaseorder' => 'vendor_id'],
        'Tracking Number' => ['purchaseorder' => 'tracking_no'],
        'Total'           => ['purchaseorder' => 'grand_total'],
        'Assigned To'     => ['crmentity' => 'assigned_user_id']
    ];

    public $list_fields_name = [
        'Order No'        => 'purchaseorder_no',
        'Subject'         => 'subject',
        'Vendor Name'     => 'vendor_id',
        'Tracking Number' => 'tracking_no',
        'Total'           => 'grand_total',
        'Assigned To'     => 'assigned_user_id'
    ];
    public $list_link_field = 'subject';

    public $search_fields = [
        'Order No' => ['purchaseorder' => 'purchaseorder_no'],
        'Subject'  => ['purchaseorder' => 'subject'],
    ];

    public $search_fields_name = [
        'Order No' => 'purchaseorder_no',
        'Subject'  => 'subject',
    ];
    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['subject', 'vendor_id', 'createdtime', 'modifiedtime', 'assigned_user_id', 'quantity', 'listprice', 'productid'];

    // This is the list of vtiger_fields that are required.
    public $required_fields = ['accountname' => 1];

    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = 'subject';
    public $default_sort_order = 'ASC';

    // For Alphabetical search
    public $def_basicsearch_col = 'subject';


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

    /*
     * Function to get the secondary query part of a report
     * @param - $module primary module name
     * @param - $secmodule secondary module name
     * returns the query string formed on fetching the related data for report for secondary module
     */
    public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
    {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityPurchaseOrder', ['vtiger_usersPurchaseOrder', 'vtiger_groupsPurchaseOrder', 'vtiger_lastModifiedByPurchaseOrder']);

        if (!$queryPlanner->requireTable('vtiger_purchaseorder', $matrix)) {
            return '';
        }

        $matrix->setDependency('vtiger_purchaseorder', [
            'vtiger_crmentityPurchaseOrder',
            'vtiger_currency_info' . $secmodule,
            'vtiger_purchaseordercf',
            'vtiger_vendorRelPurchaseOrder',
            'vtiger_pobillads',
            'vtiger_poshipads',
            'vtiger_contactdetailsPurchaseOrder'
        ]);

        $query = $this->getRelationQuery($module, $secmodule, 'vtiger_purchaseorder', 'purchaseorderid', $queryPlanner);

        if ($queryPlanner->requireTable('vtiger_crmentityPurchaseOrder', $matrix)) {
            $query .= ' left join vtiger_crmentity as vtiger_crmentityPurchaseOrder on vtiger_crmentityPurchaseOrder.crmid=vtiger_purchaseorder.purchaseorderid and vtiger_crmentityPurchaseOrder.deleted=0';
        }

        if ($queryPlanner->requireTable('vtiger_purchaseordercf')) {
            $query .= ' left join vtiger_purchaseordercf on vtiger_purchaseorder.purchaseorderid = vtiger_purchaseordercf.purchaseorderid';
        }

        if ($queryPlanner->requireTable('vtiger_pobillads')) {
            $query .= ' left join vtiger_pobillads on vtiger_purchaseorder.purchaseorderid=vtiger_pobillads.pobilladdressid';
        }

        if ($queryPlanner->requireTable('vtiger_poshipads')) {
            $query .= ' left join vtiger_poshipads on vtiger_purchaseorder.purchaseorderid=vtiger_poshipads.poshipaddressid';
        }

        if ($queryPlanner->requireTable('vtiger_currency_info' . $secmodule)) {
            $query .= ' left join vtiger_currency_info as vtiger_currency_info' . $secmodule . ' on vtiger_currency_info' . $secmodule . '.id = vtiger_purchaseorder.currency_id';
        }

        if ($queryPlanner->requireTable('vtiger_usersPurchaseOrder')) {
            $query .= ' left join vtiger_users as vtiger_usersPurchaseOrder on vtiger_usersPurchaseOrder.id = vtiger_crmentityPurchaseOrder.assigned_user_id';
        }

        if ($queryPlanner->requireTable('vtiger_groupsPurchaseOrder')) {
            $query .= ' left join vtiger_groups as vtiger_groupsPurchaseOrder on vtiger_groupsPurchaseOrder.groupid = vtiger_crmentityPurchaseOrder.assigned_user_id';
        }

        if ($queryPlanner->requireTable('vtiger_vendorRelPurchaseOrder')) {
            $query .= ' left join vtiger_vendor as vtiger_vendorRelPurchaseOrder on vtiger_vendorRelPurchaseOrder.vendorid = vtiger_purchaseorder.vendor_id';
        }

        if ($queryPlanner->requireTable('vtiger_contactdetailsPurchaseOrder')) {
            $query .= ' left join vtiger_contactdetails as vtiger_contactdetailsPurchaseOrder on vtiger_contactdetailsPurchaseOrder.contactid = vtiger_purchaseorder.contact_id';
        }

        if ($queryPlanner->requireTable('vtiger_lastModifiedByPurchaseOrder')) {
            $query .= ' left join vtiger_users as vtiger_lastModifiedByPurchaseOrder on vtiger_lastModifiedByPurchaseOrder.id = vtiger_crmentityPurchaseOrder.modifiedby ';
        }

        if ($queryPlanner->requireTable('vtiger_createdbyPurchaseOrder')) {
            $query .= ' left join vtiger_users as vtiger_createdbyPurchaseOrder on vtiger_createdbyPurchaseOrder.id = vtiger_crmentityPurchaseOrder.creator_user_id ';
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
            'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_purchaseorder' => 'purchaseorderid'],
            'Contacts'  => ['vtiger_purchaseorder' => ['purchaseorderid', 'contact_id']],
        ];

        return $rel_tables[$secmodule];
    }

    // Function to unlink an entity with given Id from another entity
    public function unlinkRelationship($id, $return_module, $return_id)
    {
        if (empty($return_module) || empty($return_id)) {
            return;
        }

        switch ($return_module) {
            case 'Vendors':
                $sql_req = 'UPDATE vtiger_purchaseorder SET vendor_id = ? WHERE purchaseorderid = ?';
                $this->db->pquery($sql_req, [$id]);
                break;
            case 'Contacts':
                $sql_req = 'UPDATE vtiger_purchaseorder SET contact_id=? WHERE purchaseorderid = ?';
                $this->db->pquery($sql_req, [null, $id]);
                break;
            case 'Documents':
                $sql = 'DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?';
                $this->db->pquery($sql, [$id, $return_id]);
                break;
            case 'Accounts':
                $sql = 'UPDATE vtiger_purchaseorder SET account_id=? WHERE purchaseorderid=?';
                $this->db->pquery($sql, [null, $id]);
                break;
            default:
                parent::unlinkRelationship($id, $return_module, $return_id);
        }
    }

    /*Function to create records in current module.
    **This function called while importing records to this module*/
    public function createRecords($obj)
    {
        return createRecords($obj);
    }

    /*Function returns the record information which means whether the record is imported or not
    **This function called while importing records to this module*/
    public function importRecord($obj, $inventoryFieldData, $lineItemDetails)
    {
        return importRecord($obj, $inventoryFieldData, $lineItemDetails);
    }

    /*Function to return the status count of imported records in current module.
    **This function called while importing records to this module*/
    public function getImportStatusCount($obj)
    {
        return getImportStatusCount($obj);
    }

    public function undoLastImport($obj, $user)
    {
        $undoLastImport = undoLastImport($obj, $user);
    }

    /** Function to export the lead records in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export PurchaseOrder Query.
     */
    public function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug('Entering create_export_query(' . $where . ') method ...');

        include('include/utils/ExportUtils.php');

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery('PurchaseOrder', 'detail_view');
        $fields_list = getFieldsListFromQuery($sql);
        $fields_list .= getInventoryFieldsForExport($this->table_name);
        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $query = 'SELECT ' . $fields_list . ' FROM ' . $this->entity_table . '
				INNER JOIN vtiger_purchaseorder ON vtiger_purchaseorder.purchaseorderid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_purchaseordercf ON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid
				LEFT JOIN vtiger_pobillads ON vtiger_pobillads.pobilladdressid = vtiger_purchaseorder.purchaseorderid
				LEFT JOIN vtiger_poshipads ON vtiger_poshipads.poshipaddressid = vtiger_purchaseorder.purchaseorderid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_purchaseorder.contact_id
				LEFT JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_purchaseorder.vendor_id
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_purchaseorder.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id';

        $query .= $this->getNonAdminAccessControlQuery('PurchaseOrder', $current_user);
        $where_auto = ' vtiger_crmentity.deleted=0';

        if ($where != '') {
            $query .= ' where (' . $where . ') AND ' . $where_auto;
        } else {
            $query .= ' where ' . $where_auto;
        }

        $log->debug('Exiting create_export_query method ...');

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
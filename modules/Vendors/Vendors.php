<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
class Vendors extends CRMEntity
{
    public string $moduleName = 'Vendors';
    public string $parentName = 'INVENTORY';
    var $table_name = "vtiger_vendor";
    var $table_index = 'vendorid';
    var $tab_name = ['vtiger_crmentity', 'vtiger_vendor', 'vtiger_vendorcf'];
    var $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_vendor' => 'vendorid', 'vtiger_vendorcf' => 'vendorid'];
    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = ['vtiger_vendorcf', 'vendorid'];

    //Pavani: Assign value to entity_table
    var $entity_table = "vtiger_crmentity";
    var $sortby_fields = ['vendorname', 'category'];

    // This is the list of vtiger_fields that are in the lists.
    var $list_fields = [
        'Vendor Name' => ['vendor' => 'vendorname'],
        'Phone' => ['vendor' => 'phone'],
        'Email' => ['vendor' => 'email'],
        'Category' => ['vendor' => 'category']
    ];
    var $list_fields_name = [
        'Vendor Name' => 'vendorname',
        'Phone' => 'phone',
        'Email' => 'email',
        'Category' => 'category'
    ];
    var $list_link_field = 'vendorname';

    //Specifying required fields for vendors
    var $required_fields = [];

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    var $mandatory_fields = ['createdtime', 'modifiedtime', 'vendorname', 'assigned_user_id'];

    // For Alphabetical search
    var $def_basicsearch_col = 'vendorname';

    /** Function to export the vendors in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export Vendors Query.
     */
    function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("Vendors", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);

        $query = "SELECT $fields_list FROM " . $this->entity_table . "
                                INNER JOIN vtiger_vendor
                                        ON vtiger_crmentity.crmid = vtiger_vendor.vendorid
                                LEFT JOIN vtiger_vendorcf
                                        ON vtiger_vendorcf.vendorid=vtiger_vendor.vendorid
                                LEFT JOIN vtiger_seattachmentsrel
                                        ON vtiger_vendor.vendorid=vtiger_seattachmentsrel.crmid
                                LEFT JOIN vtiger_attachments
                                ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                                LEFT JOIN vtiger_users
                                        ON vtiger_crmentity.assigned_user_id = vtiger_users.id and vtiger_users.status='Active'
                                ";
        $where_auto = " vtiger_crmentity.deleted = 0 ";

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
            "Products" => "vtiger_products",
            "PurchaseOrder" => "vtiger_purchaseorder",
        ];

        $tbl_field_arr = [
            "vtiger_products" => "productid",
            "vtiger_purchaseorder" => "purchaseorderid"
        ];

        $entity_tbl_field_arr = [
            "vtiger_products" => "vendor_id",
            "vtiger_purchaseorder" => "vendor_id"
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
            'Products' => ['vtiger_products' => ['vendor_id', 'productid'], 'vtiger_vendor' => 'vendorid'],
            'PurchaseOrder' => ['vtiger_purchaseorder' => ['vendor_id', 'purchaseorderid'], 'vtiger_vendor' => 'vendorid'],
        ];

        return $rel_tables[$secmodule];
    }

    // Function to unlink all the dependent entities of the given Entity by Id
    public function unlinkDependencies($module, $id)
    {
        Core_Relation_Model::saveEntityDependencies($id, 'vtiger_vendor', 'vendorid', 'vtiger_purchaseorder', 'purchaseorderid', 'vendor_id');
        Core_Relation_Model::saveDependencies($id, 'vendor_id', 'vtiger_products', 'productid');

        parent::unlinkDependencies($module, $id);
    }
}
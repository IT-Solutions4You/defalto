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

class Services extends CRMEntity
{
    use Core_UnitPrice_Trait;

    public string $moduleName = 'Services';
    public string $parentName = 'INVENTORY';
    var $db, $log; // Used in class functions of CRMEntity

    var $table_name = 'vtiger_service';
    var $table_index = 'serviceid';
    var $column_fields = [];

    /** Indicator if this is a custom module or standard module */
    var $IsCustomModule = true;

    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = ['vtiger_servicecf', 'serviceid'];

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    var $tab_name = ['vtiger_crmentity', 'vtiger_service', 'vtiger_servicecf'];

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    var $tab_name_index = [
        'vtiger_crmentity'     => 'crmid',
        'vtiger_service'       => 'serviceid',
        'vtiger_servicecf'     => 'serviceid',
        'vtiger_producttaxrel' => 'productid'
    ];

    /**
     * Mandatory for Listing (Related listview)
     */
    var $list_fields = [
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Service No'      => ['service' => 'service_no'],
        'Service Name'    => ['service' => 'servicename'],
        'Commission Rate' => ['service' => 'commissionrate'],
        'No of Units'     => ['service' => 'qty_per_unit'],
        'Price'           => ['service' => 'unit_price']
    ];
    var $list_fields_name = [
        /* Format: Field Label => fieldname */
        'Service No'      => 'service_no',
        'Service Name'    => 'servicename',
        'Commission Rate' => 'commissionrate',
        'No of Units'     => 'qty_per_unit',
        'Price'           => 'unit_price'
    ];

    // Make the field link to detail view
    var $list_link_field = 'servicename';

    // For Popup listview and UI type support
    var $search_fields = [
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Service No'   => ['service' => 'service_no'],
        'Service Name' => ['service' => 'servicename'],
        'Price'        => ['service' => 'unit_price']
    ];
    var $search_fields_name = [
        /* Format: Field Label => fieldname */
        'Service No'   => 'service_no',
        'Service Name' => 'servicename',
        'Price'        => 'unit_price'
    ];

    // For Popup window record selection
    var $popup_fields = ['servicename', 'service_usageunit', 'unit_price'];

    // Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
    var $sortby_fields = [];

    // For Alphabetical search
    var $def_basicsearch_col = 'servicename';

    // Column value to use on detail view record text display
    var $def_detailview_recname = 'servicename';

    // Required Information for enabling Import feature
    var $required_fields = ['servicename' => 1];

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    var $mandatory_fields = ['servicename', 'assigned_user_id'];

    var $default_order_by = 'servicename';
    var $default_sort_order = 'ASC';

    var $unit_price;

    /**    Constructor which will set the column_fields in this object
     */
    function __construct()
    {
        global $log;
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = PearDatabase::getInstance();
        $this->log = $log;
    }

    function save_module($module)
    {
        //Inserting into service_taxrel table
        if ($_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'ProcessDuplicates' && !$this->isWorkFlowFieldUpdate) {
            $request = new Vtiger_Request($_REQUEST, $_REQUEST);
            $request->set('taxes_data', $request->get('taxclass'));

            Core_TaxRecord_Model::getInstance((int)$this->id)->saveFromRequest($request);

            if ($_REQUEST['action'] != 'MassEditSave') {
                $this->insertPriceInformation('vtiger_productcurrencyrel', 'Services');
            }
        }

        if ($_REQUEST['action'] == 'SaveAjax' && isset($_REQUEST['base_currency']) && isset($_REQUEST['unit_price'])) {
            $this->insertPriceInformation('vtiger_productcurrencyrel', 'Services');
        }
        // Update unit price value in vtiger_productcurrencyrel
        $this->updateUnitPrice();
    }

    /**    function to save the service tax information in vtiger_servicetaxrel table
     *
     * @param string $tablename - vtiger_tablename to save the service tax relationship (servicetaxrel)
     * @param string $module    - current module name
     *                          $return void
     */
    function insertTaxInformation($tablename, $module)
    {
        global $adb, $log;
        $log->debug("Entering into insertTaxInformation($tablename, $module) method ...");
        $tax_details = getAllTaxes();

        $tax_per = '';
        //Save the Product - tax relationship if corresponding tax check box is enabled
        //Delete the existing tax if any
        if ($this->mode == 'edit' && $_REQUEST['action'] != 'MassEditSave') {
            for ($i = 0; $i < php7_count($tax_details); $i++) {
                $taxid = getTaxId($tax_details[$i]['taxname']);
                $sql = "DELETE FROM vtiger_producttaxrel WHERE productid=? AND taxid=?";
                $adb->pquery($sql, [$this->id, $taxid]);
            }
        }
        for ($i = 0; $i < php7_count($tax_details); $i++) {
            $tax_name = $tax_details[$i]['taxname'];
            $tax_checkname = $tax_details[$i]['taxname'] . "_check";
            if ($_REQUEST[$tax_checkname] == 'on' || $_REQUEST[$tax_checkname] == 1) {
                $taxid = getTaxId($tax_name);
                $tax_per = $_REQUEST[$tax_name];

                $taxRegions = $_REQUEST[$tax_name . '_regions'];
                if ($taxRegions) {
                    $tax_per = $_REQUEST[$tax_name . '_defaultPercentage'];
                } else {
                    $taxRegions = [];
                }

                if ($tax_per == '') {
                    $log->debug("Tax selected but value not given so default value will be saved.");
                    $tax_per = getTaxPercentage($tax_name);
                }

                $log->debug("Going to save the Product - $tax_name tax relationship");

                if ($_REQUEST['action'] === 'MassEditSave') {
                    $adb->pquery('DELETE FROM vtiger_producttaxrel WHERE productid=? AND taxid=?', [$this->id, $taxid]);
                }

                $query = "INSERT INTO vtiger_producttaxrel VALUES(?,?,?,?)";
                $adb->pquery($query, [$this->id, $taxid, $tax_per, Zend_Json::encode($taxRegions)]);
            }
        }

        $log->debug("Exiting from insertTaxInformation($tablename, $module) method ...");
    }

    /**    function to save the service price information in vtiger_servicecurrencyrel table
     *
     * @param string $tablename - vtiger_tablename to save the service currency relationship (servicecurrencyrel)
     * @param string $module    - current module name
     *                          $return void
     */


    function updateUnitPrice()
    {
        $prod_res = $this->db->pquery("select unit_price, currency_id from vtiger_service where serviceid=?", [$this->id]);
        $prod_unit_price = $this->db->query_result($prod_res, 0, 'unit_price');
        $prod_base_currency = $this->db->query_result($prod_res, 0, 'currency_id');

        $query = "update vtiger_productcurrencyrel set actual_price=? where productid=? and currencyid=?";
        $params = [$prod_unit_price, $this->id, $prod_base_currency];
        $this->db->pquery($query, $params);
    }

    /**
     * Return query to use based on given modulename, fieldname
     * Useful to handle specific case handling for Popup
     */
    function getQueryByModuleField($module, $fieldname, $srcrecord)
    {
        // $srcrecord could be empty
    }

    /**
     * Get list view query.
     */
    function getListQuery($module, $where = '')
    {
        $query = "SELECT vtiger_crmentity.*, $this->table_name.*";

        // Select Custom Field Table Columns if present
        if (!empty($this->customFieldTable)) {
            $query .= ", " . $this->customFieldTable[0] . ".* ";
        }

        $query .= " FROM $this->table_name";

        $query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

        // Consider custom table join as well.
        if (!empty($this->customFieldTable)) {
            $query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
        }
        $query .= " LEFT JOIN vtiger_groups
						ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
					LEFT JOIN vtiger_users
						ON vtiger_users.id = vtiger_crmentity.assigned_user_id ";
        global $current_user;
        $query .= $this->getNonAdminAccessControlQuery($module, $current_user);
        $query .= "WHERE vtiger_crmentity.deleted = 0 " . $where;

        return $query;
    }

    /**
     * Apply security restriction (sharing privilege) query part for List view.
     */
    function getListViewSecurityParameter($module)
    {
        global $current_user;
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

        $sec_query = '';
        $tabid = getTabid($module);

        if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
            && $defaultOrgSharingPermission[$tabid] == 3) {
            $sec_query .= " AND (vtiger_crmentity.assigned_user_id in($current_user->id) OR vtiger_crmentity.assigned_user_id IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
					)
					OR vtiger_crmentity.assigned_user_id IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=" . $current_user->id . " AND tabid=" . $tabid . "
					)
					OR
						(";

            // Build the query based on the group association of current user.
            if (php7_count($current_user_groups) > 0) {
                $sec_query .= " vtiger_groups.groupid IN (" . implode(",", $current_user_groups) . ") OR ";
            }
            $sec_query .= " vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=" . $current_user->id . " and tabid=" . $tabid . "
						)";
            $sec_query .= ")
				)";
        }

        return $sec_query;
    }

    /**
     * Create query to export the records.
     */
    function create_export_query($where)
    {
        global $current_user;

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery('Services', "detail_view");

        $fields_list = getFieldsListFromQuery($sql);

        $query = "SELECT $fields_list
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

        if (!empty($this->customFieldTable)) {
            $query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
        }

        $query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";
        $query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id = vtiger_users.id AND vtiger_users.status='Active'";
        $query .= $this->getNonAdminAccessControlQuery('Services', $current_user);
        $where_auto = " vtiger_crmentity.deleted=0";

        if ($where != '') {
            $query .= " WHERE ($where) AND $where_auto";
        } else {
            $query .= " WHERE $where_auto";
        }

        return $query;
    }

    /**
     * Transform the value while exporting
     */
    function transform_export_value($key, $value)
    {
        return parent::transform_export_value($key, $value);
    }

    /**
     * Function which will give the basic query to find duplicates
     */
    function getDuplicatesQuery($module, $table_cols, $field_values, $ui_type_arr, $select_cols = '')
    {
        $select_clause = "SELECT " . $this->table_name . "." . $this->table_index . " AS recordid, vtiger_users_last_import.deleted," . $table_cols;

        // Select Custom Field Table Columns if present
        if (isset($this->customFieldTable)) {
            $query .= ", " . $this->customFieldTable[0] . ".* ";
        }

        $from_clause = " FROM $this->table_name";

        $from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

        // Consider custom table join as well.
        if (isset($this->customFieldTable)) {
            $from_clause .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
                " = $this->table_name.$this->table_index";
        }
        $from_clause .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
							LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id";
        $where_clause = "	WHERE vtiger_crmentity.deleted = 0";
        $where_clause .= $this->getListViewSecurityParameter($module);

        if (isset($select_cols) && trim($select_cols) != '') {
            $sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
                " INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
            // Consider custom table join as well.
            if (isset($this->customFieldTable)) {
                $sub_query .= " INNER JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
            }
            $sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
        } else {
            $sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
        }

        $query = $select_clause . $from_clause .
            " LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
            " INNER JOIN (" . $sub_query . ") AS temp ON " . get_on_clause($field_values, $ui_type_arr, $module) .
            $where_clause .
            " ORDER BY $table_cols," . $this->table_name . "." . $this->table_index . " ASC";

        return $query;
    }
    /**
     * Handle saving related module information.
     * NOTE: This function has been added to CRMEntity (base class).
     * You can override the behavior by re-defining it here.
     */
    // function save_related_module($module, $crmid, $with_module, $with_crmid) { }

    /**
     * Handle deleting related module information.
     * NOTE: This function has been added to CRMEntity (base class).
     * You can override the behavior by re-defining it here.
     */
    //function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

    /**
     * Handle getting related list information.
     * NOTE: This function has been added to CRMEntity (base class).
     * You can override the behavior by re-defining it here.
     */
    //function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

    /**    function used to get the list of quotes which are related to the service
     *
     * @param int $id - service id
     *
     * @return array - array which will be returned from the function GetRelatedList
     */
    function get_quotes($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule, $current_user;
        $log->debug("Entering get_quotes(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        if ($actions) {
            $actions = sanitizeRelatedListsActions($actions);

            if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString(
                        $related_module
                    ) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString(
                        'LBL_SELECT'
                    ) . " " . getTranslatedString($related_module) . "'>&nbsp;";
            }

            if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_crmentity.*,
			vtiger_quotes.*,
			vtiger_potential.potentialname,
			vtiger_account.accountname,
			df_inventoryitem.productid,
			case when (vtiger_users.user_name not like '') then $userNameSql
				else vtiger_groups.groupname end as user_name
			FROM vtiger_quotes
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_quotes.quoteid
			INNER JOIN df_inventoryitem
				ON df_inventoryitem.parentid = vtiger_quotes.quoteid
			INNER JOIN vtiger_crmentity AS crmentityInventoryItem
				ON crmentityInventoryItem.crmid = df_inventoryitem.inventoryitemid AND crmentityInventoryItem.deleted = 0
			LEFT OUTER JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_quotes.account_id
			LEFT OUTER JOIN vtiger_potential
				ON vtiger_potential.potentialid = vtiger_quotes.potential_id
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
			LEFT JOIN vtiger_quotescf
				ON vtiger_quotescf.quoteid = vtiger_quotes.quoteid
			LEFT JOIN vtiger_quotesbillads
				ON vtiger_quotesbillads.quotebilladdressid = vtiger_quotes.quoteid
			LEFT JOIN vtiger_quotesshipads
				ON vtiger_quotesshipads.quoteshipaddressid = vtiger_quotes.quoteid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.assigned_user_id
			WHERE vtiger_crmentity.deleted = 0
			AND df_inventoryitem.productid = " . $id;

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_quotes method ...");

        return $return_value;
    }

    /**    function used to get the list of purchase orders which are related to the service
     *
     * @param int $id - service id
     *
     * @return array - array which will be returned from the function GetRelatedList
     */
    function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule, $current_user;
        $log->debug("Entering get_purchase_orders(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        if ($actions) {
            $actions = sanitizeRelatedListsActions($actions);

            if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString(
                        $related_module
                    ) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString(
                        'LBL_SELECT'
                    ) . " " . getTranslatedString($related_module) . "'>&nbsp;";
            }

            if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_crmentity.*,
			vtiger_purchaseorder.*,
			vtiger_service.servicename,
			df_inventoryitem.productid,
			case when (vtiger_users.user_name not like '') then $userNameSql
				else vtiger_groups.groupname end as user_name
			FROM vtiger_purchaseorder
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_purchaseorder.purchaseorderid
            INNER JOIN df_inventoryitem
                ON df_inventoryitem.parentid = vtiger_purchaseorder.purchaseorderid
            INNER JOIN vtiger_crmentity AS crmentityInventoryItem
                ON crmentityInventoryItem.crmid = df_inventoryitem.inventoryitemid AND crmentityInventoryItem.deleted = 0
			INNER JOIN vtiger_service
				ON vtiger_service.serviceid = df_inventoryitem.productid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
			LEFT JOIN vtiger_purchaseordercf
				ON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid
			LEFT JOIN vtiger_pobillads
				ON vtiger_pobillads.pobilladdressid = vtiger_purchaseorder.purchaseorderid
			LEFT JOIN vtiger_poshipads
				ON vtiger_poshipads.poshipaddressid = vtiger_purchaseorder.purchaseorderid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.assigned_user_id
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_service.serviceid = " . $id;

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_purchase_orders method ...");

        return $return_value;
    }

    /**    function used to get the list of sales orders which are related to the service
     *
     * @param int $id - service id
     *
     * @return array - array which will be returned from the function GetRelatedList
     */
    function get_salesorder($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule, $current_user;
        $log->debug("Entering get_salesorder(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        if ($actions) {
            $actions = sanitizeRelatedListsActions($actions);

            if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString(
                        $related_module
                    ) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString(
                        'LBL_SELECT'
                    ) . " " . getTranslatedString($related_module) . "'>&nbsp;";
            }

            if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' => 'vtiger_users.first_name',
            'last_name'  =>
                'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_crmentity.*,
			vtiger_salesorder.*,
			vtiger_service.servicename AS servicename,
			vtiger_account.accountname,
			case when (vtiger_users.user_name not like '') then $userNameSql
				else vtiger_groups.groupname end as user_name
			FROM vtiger_salesorder
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_salesorder.salesorderid
            INNER JOIN df_inventoryitem
                ON df_inventoryitem.parentid = vtiger_salesorder.salesorderid
            INNER JOIN vtiger_crmentity AS crmentityInventoryItem
                ON crmentityInventoryItem.crmid = df_inventoryitem.inventoryitemid AND crmentityInventoryItem.deleted = 0
			INNER JOIN vtiger_service
				ON vtiger_service.serviceid = df_inventoryitem.productid
			LEFT OUTER JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_salesorder.account_id
			LEFT JOIN vtiger_invoice_recurring_info
				ON vtiger_invoice_recurring_info.salesorderid = vtiger_salesorder.salesorderid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
			LEFT JOIN vtiger_salesordercf
				ON vtiger_salesordercf.salesorderid = vtiger_salesorder.salesorderid
			LEFT JOIN vtiger_sobillads
				ON vtiger_sobillads.sobilladdressid = vtiger_salesorder.salesorderid
			LEFT JOIN vtiger_soshipads
				ON vtiger_soshipads.soshipaddressid = vtiger_salesorder.salesorderid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.assigned_user_id
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_service.serviceid = " . $id;

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_salesorder method ...");

        return $return_value;
    }

    /**    function used to get the list of invoices which are related to the service
     *
     * @param int $id - service id
     *
     * @return array - array which will be returned from the function GetRelatedList
     */
    function get_invoices($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule, $current_user;
        $log->debug("Entering get_invoices(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        if ($actions) {
            $actions = sanitizeRelatedListsActions($actions);

            if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString(
                        $related_module
                    ) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString(
                        'LBL_SELECT'
                    ) . " " . getTranslatedString($related_module) . "'>&nbsp;";
            }

            if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_crmentity.*,
			vtiger_invoice.*,
			df_inventoryitem.quantity,
			vtiger_account.accountname,
			case when (vtiger_users.user_name not like '') then $userNameSql
				else vtiger_groups.groupname end as user_name
			FROM vtiger_invoice
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
			LEFT OUTER JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_invoice.account_id
            INNER JOIN df_inventoryitem
                ON df_inventoryitem.parentid = vtiger_invoice.invoiceid
            INNER JOIN vtiger_crmentity AS crmentityInventoryItem
                ON crmentityInventoryItem.crmid = df_inventoryitem.inventoryitemid AND crmentityInventoryItem.deleted = 0
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
			LEFT JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_invoicebillads
				ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_invoiceshipads
				ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_users
				ON  vtiger_users.id = vtiger_crmentity.assigned_user_id
			WHERE vtiger_crmentity.deleted = 0
			AND df_inventoryitem.productid = " . $id;

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_invoices method ...");

        return $return_value;
    }

    /**    function used to get the list of pricebooks which are related to the service
     *
     * @param int $id - service id
     *
     * @return array - array which will be returned from the function GetRelatedList
     */
    function get_service_pricebooks($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $currentModule, $log, $singlepane_view, $mod_strings;
        $log->debug("Entering get_service_pricebooks(" . $id . ") method ...");

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        checkFileAccessForInclusion("modules/$related_module/$related_module.php");
        require_once("modules/$related_module/$related_module.php");
        $focus = new $related_module();
        $singular_modname = vtlib_toSingular($related_module);

        if ($singlepane_view == 'true') {
            $returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
        } else {
            $returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";
        }

        $button = '';
        if ($actions) {
            $actions = sanitizeRelatedListsActions($actions);

            if (in_array('SELECT', $actions) && isPermitted($related_module, 1, '') == 'yes' && isPermitted($currentModule, 'EditView', $id) == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_ADD_TO') . " " . getTranslatedString($related_module) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"AddServiceToPriceBooks\";this.form.module.value=\"$currentModule\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_TO') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $query = "SELECT vtiger_crmentity.crmid,
			vtiger_pricebook.*,
			vtiger_pricebookproductrel.productid as prodid
			FROM vtiger_pricebook
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_pricebook.pricebookid
			INNER JOIN vtiger_pricebookproductrel
				ON vtiger_pricebookproductrel.pricebookid = vtiger_pricebook.pricebookid
			INNER JOIN vtiger_pricebookcf
				ON vtiger_pricebookcf.pricebookid = vtiger_pricebook.pricebookid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_pricebookproductrel.productid = " . $id;
        $log->debug("Exiting get_product_pricebooks method ...");

        $return_value = GetRelatedList($currentModule, $related_module, $focus, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_service_pricebooks method ...");

        return $return_value;
    }

    /**    Function to display the Services which are related to the PriceBook
     *
     * @param string $query     - query to get the list of products which are related to the current PriceBook
     * @param object $focus     - PriceBook object which contains all the information of the current PriceBook
     * @param string $returnset - return_module, return_action and return_id which are sequenced with & to pass to the URL which is optional
     *                          return array $return_data which will be formed like array('header'=>$header,'entries'=>$entries_list) where as $header contains all the header columns and $entries_list will contain all the Service entries
     */
    function getPriceBookRelatedServices($query, $focus, $returnset = '')
    {
        global $log;
        $log->debug("Entering getPriceBookRelatedServices(" . $query . "," . get_class($focus) . "," . $returnset . ") method ...");

        global $adb;
        global $app_strings;
        global $current_language, $current_user;
        $current_module_strings = return_module_language($current_language, 'Services');
        $no_of_decimal_places = getCurrencyDecimalPlaces();
        global $list_max_entries_per_page;
        global $urlPrefix;

        global $theme;
        $pricebook_id = $_REQUEST['record'];
        $theme_path = "themes/" . $theme . "/";
        $image_path = $theme_path . "images/";

        $computeCount = $_REQUEST['withCount'];
        if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true ||
            ((boolean)$computeCount) == true) {
            $noofrows = $adb->query_result($adb->query(Vtiger_Functions::mkCountQuery($query)), 0, 'count');
        } else {
            $noofrows = null;
        }
        $module = 'PriceBooks';
        $relatedmodule = 'Services';
        if (!$_SESSION['rlvs'][$module][$relatedmodule]) {
            $modObj = new ListViewSession();
            $modObj->sortby = $focus->default_order_by;
            $modObj->sorder = $focus->default_sort_order;
            $_SESSION['rlvs'][$module][$relatedmodule] = get_object_vars($modObj);
        }
        if (isset($_REQUEST['relmodule']) && $_REQUEST['relmodule'] != '' && $_REQUEST['relmodule'] == $relatedmodule) {
            $relmodule = vtlib_purify($_REQUEST['relmodule']);
            if ($_SESSION['rlvs'][$module][$relmodule]) {
                setSessionVar($_SESSION['rlvs'][$module][$relmodule], $noofrows, $list_max_entries_per_page, $module, $relmodule);
            }
        }
        global $relationId;
        $start = RelatedListViewSession::getRequestCurrentPage($relationId, $query);
        $navigation_array = VT_getSimpleNavigationValues(
            $start,
            $list_max_entries_per_page,
            $noofrows
        );

        $limit_start_rec = ($start - 1) * $list_max_entries_per_page;

        if ($adb->dbType == "pgsql") {
            $list_result = $adb->pquery(
                $query .
                " OFFSET $limit_start_rec LIMIT $list_max_entries_per_page",
                []
            );
        } else {
            $list_result = $adb->pquery(
                $query .
                " LIMIT $limit_start_rec, $list_max_entries_per_page",
                []
            );
        }

        $header = [];
        $header[] = $current_module_strings['LBL_LIST_SERVICE_NAME'];
        if (getFieldVisibilityPermission('Services', $current_user->id, 'unit_price') == '0') {
            $header[] = $current_module_strings['LBL_SERVICE_UNIT_PRICE'];
        }
        $header[] = $current_module_strings['LBL_PB_LIST_PRICE'];
        if (isPermitted("PriceBooks", "EditView", "") == 'yes' || isPermitted("PriceBooks", "Delete", "") == 'yes') {
            $header[] = $app_strings['LBL_ACTION'];
        }

        $currency_id = $focus->column_fields['currency_id'];
        $numRows = $adb->num_rows($list_result);
        for ($i = 0; $i < $numRows; $i++) {
            $entity_id = $adb->query_result($list_result, $i, "crmid");
            $unit_price = $adb->query_result($list_result, $i, "unit_price");
            if ($currency_id != null) {
                $prod_prices = getPricesForProducts($currency_id, [$entity_id], 'Services');
                $unit_price = $prod_prices[$entity_id];
            }
            $listprice = $adb->query_result($list_result, $i, "listprice");
            $field_name = $entity_id . "_listprice";

            $entries = [];
            $entries[] = textlength_check($adb->query_result($list_result, $i, "servicename"));
            if (getFieldVisibilityPermission('Services', $current_user->id, 'unit_price') == '0') {
                $entries[] = CurrencyField::convertToUserFormat($unit_price, null, true);
            }

            $entries[] = CurrencyField::convertToUserFormat($listprice, null, true);
            $action = "";
            if (isPermitted("PriceBooks", "EditView", "") == 'yes' && isPermitted('Services', 'EditView', $entity_id) == 'yes') {
                $action .= '<img style="cursor:pointer;" src="themes/images/editfield.gif" border="0" onClick="fnvshobj(this,\'editlistprice\'),editProductListPrice(\'' . $entity_id . '\',\'' . $pricebook_id . '\',\'' . number_format(
                        $listprice,
                        $no_of_decimal_places,
                        '.',
                        ''
                    ) . '\')" alt="' . $app_strings["LBL_EDIT_BUTTON"] . '" title="' . $app_strings["LBL_EDIT_BUTTON"] . '"/>';
            } else {
                $action .= '<img src="' . vtiger_imageurl('blank.gif', $theme) . '" border="0" />';
            }
            if (isPermitted("PriceBooks", "Delete", "") == 'yes' && isPermitted('Services', 'Delete', $entity_id) == 'yes') {
                if ($action != "") {
                    $action .= '&nbsp;|&nbsp;';
                }
                $action .= '<img src="themes/images/delete.gif" onclick="if(confirm(\'' . $app_strings['ARE_YOU_SURE'] . '\')) deletePriceBookProductRel(' . $entity_id . ',' . $pricebook_id . ');" alt="' . $app_strings["LBL_DELETE"] . '" title="' . $app_strings["LBL_DELETE"] . '" style="cursor:pointer;" border="0">';
            }
            if ($action != "") {
                $entries[] = $action;
            }
            $entries_list[] = $entries;
        }
        $navigationOutput[] = getRecordRangeMessage($list_result, $limit_start_rec, $noofrows);
        $navigationOutput[] = getRelatedTableHeaderNavigation($navigation_array, '', $module, $relatedmodule, $focus->id);
        $return_data = ['header' => $header, 'entries' => $entries_list, 'navigation' => $navigationOutput];

        $log->debug("Exiting getPriceBookRelatedServices method ...");

        return $return_data;
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
            "Quotes"        => "df_inventoryitem",
            "PurchaseOrder" => "df_inventoryitem",
            "SalesOrder"    => "df_inventoryitem",
            "Invoice"       => "df_inventoryitem",
            "PriceBooks"    => "vtiger_pricebookproductrel",
            "Documents"     => "vtiger_senotesrel"
        ];

        $tbl_field_arr = ["df_inventoryitem"=>"inventoryitemid", "vtiger_pricebookproductrel" => "pricebookid", "vtiger_senotesrel" => "notesid"];

        $entity_tbl_field_arr = ["df_inventoryitem" => "productid", "vtiger_pricebookproductrel" => "productid", "vtiger_senotesrel" => "crmid"];

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
        global $current_user;
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityServices', ['vtiger_usersServices', 'vtiger_groupsServices', 'vtiger_lastModifiedByServices']);
        if (!$queryPlanner->requireTable("vtiger_service", $matrix)) {
            return '';
        }
        $matrix->setDependency('vtiger_service', ['actual_unit_price', 'vtiger_currency_info', 'vtiger_productcurrencyrel', 'vtiger_servicecf', 'vtiger_crmentityServices']);

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_service", "serviceid", $queryPlanner);
        if ($queryPlanner->requireTable("innerService")) {
            $query .= " LEFT JOIN (
			SELECT vtiger_service.serviceid,
			(CASE WHEN (vtiger_service.currency_id = " . $current_user->currency_id . " ) THEN vtiger_service.unit_price
			WHEN (vtiger_productcurrencyrel.actual_price IS NOT NULL) THEN vtiger_productcurrencyrel.actual_price
			ELSE (vtiger_service.unit_price / vtiger_currency_info.conversion_rate) * " . $current_user->conv_rate . " END
			) AS actual_unit_price FROM vtiger_service
			LEFT JOIN vtiger_currency_info ON vtiger_service.currency_id = vtiger_currency_info.id
			LEFT JOIN vtiger_productcurrencyrel ON vtiger_service.serviceid = vtiger_productcurrencyrel.productid
			AND vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . ")
			AS innerService ON innerService.serviceid = vtiger_service.serviceid";
        }
        if ($queryPlanner->requireTable("vtiger_crmentityServices", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityServices on vtiger_crmentityServices.crmid=vtiger_service.serviceid and vtiger_crmentityServices.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_servicecf")) {
            $query .= " left join vtiger_servicecf on vtiger_service.serviceid = vtiger_servicecf.serviceid";
        }
        if ($queryPlanner->requireTable("vtiger_usersServices")) {
            $query .= " left join vtiger_users as vtiger_usersServices on vtiger_usersServices.id = vtiger_crmentityServices.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_groupsServices")) {
            $query .= " left join vtiger_groups as vtiger_groupsServices on vtiger_groupsServices.groupid = vtiger_crmentityServices.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByServices")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByServices on vtiger_lastModifiedByServices.id = vtiger_crmentityServices.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyServices")) {
            $query .= " left join vtiger_users as vtiger_createdbyServices on vtiger_createdbyServices.id = vtiger_crmentityServices.creator_user_id ";
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
            "Quotes"        => ["df_inventoryitem" => ["productid", "inventoryitemid"], "vtiger_service" => "serviceid"],
            "PurchaseOrder" => ["df_inventoryitem" => ["productid", "inventoryitemid"], "vtiger_service" => "serviceid"],
            "SalesOrder"    => ["df_inventoryitem" => ["productid", "inventoryitemid"], "vtiger_service" => "serviceid"],
            "Invoice"       => ["df_inventoryitem" => ["productid", "inventoryitemid"], "vtiger_service" => "serviceid"],
            "PriceBooks"    => ["vtiger_pricebookproductrel" => ["productid", "pricebookid"], "vtiger_service" => "serviceid"],
            "Documents"     => ["vtiger_senotesrel" => ["crmid", "notesid"], "vtiger_service" => "serviceid"],
        ];

        return $rel_tables[$secmodule];
    }

    // Function to unlink all the dependent entities of the given Entity by Id
    function unlinkDependencies($module, $id)
    {
        global $log;
        $this->db->pquery('DELETE from vtiger_seproductsrel WHERE productid=? or crmid=?', [$id, $id]);

        parent::unlinkDependencies($module, $id);
    }

    /**
     * Invoked when special actions are performed on the module.
     *
     * @param String Module name
     * @param String Event Type
     *
     * @throws Exception
     */
    function vtlib_handler($moduleName, $eventType)
    {
        if ($eventType == 'module.postupdate') {
            //adds sharing accsess
            $ServicesModule = Vtiger_Module::getInstance('Services');
            Vtiger_Access::setDefaultSharing($ServicesModule);
        }

        Core_Install_Model::getInstance($eventType, $moduleName)->install();
    }

    /** Function to unlink an entity with given Id from another entity */
    function unlinkRelationship($id, $return_module, $return_id)
    {
        global $log, $currentModule;
        $log->fatal('id:--' . $id);
        $log->fatal('return_module:--' . $return_module);
        $log->fatal('return_id:---' . $return_id);
        if ($return_module == 'Accounts') {
            $focus = CRMEntity::getInstance($return_module);
            $entityIds = $focus->getRelatedContactsIds($return_id);
            array_push($entityIds, $return_id);
            $entityIds = implode(',', $entityIds);
            $return_modules = "'Accounts','Contacts'";
        } elseif ($return_module == 'Documents') {
            $sql = 'DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } else {
            $entityIds = $return_id;
            $return_modules = "'" . $return_module . "'";
        }

        if ($return_module != 'Documents') {
            $query = 'DELETE FROM vtiger_crmentityrel WHERE (relcrmid=' . $id . ' AND module IN (' . $return_modules . ') AND crmid IN (' . $entityIds . ')) OR (crmid=' . $id . ' AND relmodule IN (' . $return_modules . ') AND relcrmid IN (' . $entityIds . '))';
            $this->db->pquery($query, []);
        }
    }

    /**
     * Function to get Product's related Products
     *
     * @param integer $id - productid
     *                    returns related Products record in array format
     */
    function get_services($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule, $current_user;
        $log->debug("Entering get_products(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        if ($actions && $this->ismember_check() === 0) {
            $actions = sanitizeRelatedListsActions($actions);

            if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString(
                        $related_module
                    ) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString(
                        'LBL_SELECT'
                    ) . " " . getTranslatedString($related_module) . "'>&nbsp;";
            }

            if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
                $button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
                    "<input title='" . getTranslatedString('LBL_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $query = "SELECT vtiger_service.serviceid, vtiger_service.servicename,
			vtiger_service.service_no, vtiger_service.commissionrate,
			vtiger_service.service_usageunit, vtiger_service.unit_price,
			vtiger_crmentity.crmid, vtiger_crmentity.assigned_user_id
			FROM vtiger_service
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid
			INNER JOIN vtiger_servicecf
				ON vtiger_service.serviceid = vtiger_servicecf.serviceid
			LEFT JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_service.serviceid AND vtiger_crmentityrel.module='Services'
			LEFT JOIN vtiger_users
				ON vtiger_users.id=vtiger_crmentity.assigned_user_id
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
			WHERE vtiger_crmentity.deleted = 0 AND vtiger_crmentityrel.crmid = $id ";

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_products method ...");

        return $return_value;
    }
}
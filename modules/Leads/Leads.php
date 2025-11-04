<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Leads extends CRMEntity
{
    public string $parentName = 'HOME';

    var $table_name = "vtiger_leaddetails";
    var $table_index = 'leadid';

    var $tab_name = ['vtiger_crmentity', 'vtiger_leaddetails', 'vtiger_leadsubdetails', 'vtiger_leadaddress', 'vtiger_leadscf'];
    var $tab_name_index = [
        'vtiger_crmentity'      => 'crmid',
        'vtiger_leaddetails'    => 'leadid',
        'vtiger_leadsubdetails' => 'leadsubscriptionid',
        'vtiger_leadaddress'    => 'leadaddressid',
        'vtiger_leadscf'        => 'leadid'
    ];

    var $entity_table = "vtiger_crmentity";

    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = ['vtiger_leadscf', 'leadid'];

    //construct this from database;
    var $column_fields = [];
    var $sortby_fields = ['lastname', 'firstname', 'email', 'phone', 'company', 'assigned_user_id', 'website'];

    // This is used to retrieve related vtiger_fields from form posts.
    var $additional_column_fields = ['creator_user_id', 'assigned_user_id', 'contactid', 'potentialid', 'crmid'];

    // This is the list of vtiger_fields that are in the lists.
    var $list_fields = [
        'First Name'  => ['leaddetails' => 'firstname'],
        'Last Name'   => ['leaddetails' => 'lastname'],
        'Company'     => ['leaddetails' => 'company'],
        'Phone'       => ['leadaddress' => 'phone'],
        'Website'     => ['leadsubdetails' => 'website'],
        'Email'       => ['leaddetails' => 'email'],
        'Assigned To' => ['crmentity' => 'assigned_user_id']
    ];
    var $list_fields_name = [
        'First Name'  => 'firstname',
        'Last Name'   => 'lastname',
        'Company'     => 'company',
        'Phone'       => 'phone',
        'Website'     => 'website',
        'Email'       => 'email',
        'Assigned To' => 'assigned_user_id'
    ];
    var $list_link_field = 'lastname';

    var $search_fields = [
        'Name'    => ['leaddetails' => 'lastname'],
        'Company' => ['leaddetails' => 'company']
    ];
    var $search_fields_name = [
        'Name'    => 'lastname',
        'Company' => 'company'
    ];

    var $required_fields = [];

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    var $mandatory_fields = ['assigned_user_id', 'lastname', 'createdtime', 'modifiedtime'];

    //Added these variables which are used as default order by and sortorder in ListView
    var $default_order_by = 'lastname';
    var $default_sort_order = 'ASC';

    // For Alphabetical search
    var $def_basicsearch_col = 'lastname';

    var $LBL_LEAD_MAPPING = 'LBL_LEAD_MAPPING';

    /** Function to export the lead records in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export Leads Query.
     */
    function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("Leads", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);
        $table = $this->entity_table;
        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
			FROM $table
			INNER JOIN vtiger_leaddetails ON vtiger_crmentity.crmid=vtiger_leaddetails.leadid
            LEFT JOIN vtiger_leadsubdetails ON vtiger_leaddetails.leadid = vtiger_leadsubdetails.leadsubscriptionid
            LEFT JOIN vtiger_leadaddress ON vtiger_leaddetails.leadid=vtiger_leadaddress.leadaddressid
            LEFT JOIN vtiger_leadscf ON vtiger_leadscf.leadid=vtiger_leaddetails.leadid
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
            LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id = vtiger_users.id and vtiger_users.status='Active'";

        $query .= $this->getNonAdminAccessControlQuery('Leads', $current_user);
        $where_auto = " vtiger_crmentity.deleted=0 AND vtiger_leaddetails.converted =0";

        if ($where != "") {
            $query .= " where ($where) AND " . $where_auto;
        } else {
            $query .= " where " . $where_auto;
        }

        $log->debug("Exiting create_export_query method ...");

        return $query;
    }

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
        if ($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id', 'readwrite') == '0') {
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

        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $query = "SELECT vtiger_crmentity.*, vtiger_quotes.*, vtiger_leaddetails.leadid,
            case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_quotes.quoteid
            LEFT JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_quotes.contactid
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
            LEFT JOIN vtiger_quotescf ON vtiger_quotescf.quoteid = vtiger_quotes.quoteid
            LEFT JOIN vtiger_quotesbillads ON vtiger_quotesbillads.quotebilladdressid = vtiger_quotes.quoteid
            LEFT JOIN vtiger_quotesshipads ON vtiger_quotesshipads.quoteshipaddressid = vtiger_quotes.quoteid
            LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.assigned_user_id
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.leadid = $id";

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_quotes method ...");

        return $return_value;
    }

    /** Returns a list of the associated Campaigns
     *
     * @param $id -- campaign id :: Type Integer
     *
     * @returns list of campaigns in array format
     */
    function get_campaigns($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule, $current_user;
        $log->debug("Entering get_campaigns(" . $id . ") method ...");
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

        $button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

        if ($actions) {
            $actions = sanitizeRelatedListsActions($actions);

            if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
                $button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString(
                        $related_module
                    ) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString(
                        'LBL_SELECT'
                    ) . " " . getTranslatedString($related_module) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name ,
				vtiger_campaign.campaignid, vtiger_campaign.campaignname, vtiger_campaign.campaigntype, vtiger_campaign.campaignstatus,
				vtiger_campaign.expectedrevenue, vtiger_campaign.closingdate, vtiger_crmentity.crmid, vtiger_crmentity.assigned_user_id,
				vtiger_crmentity.modifiedtime from vtiger_campaign
				inner join vtiger_campaignleadrel on vtiger_campaignleadrel.campaignid=vtiger_campaign.campaignid
				inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaign.campaignid
				inner join vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
				left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.assigned_user_id
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.assigned_user_id
				where vtiger_campaignleadrel.leadid=" . $id . " and vtiger_crmentity.deleted=0";

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_campaigns method ...");

        return $return_value;
    }

    /**
     * Function to get lead related Products
     *
     * @param integer $id - leadid
     *                    returns related Products record in array format
     */
    function get_products($id, $cur_tab_id, $rel_tab_id, $actions = false)
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

        $query = "SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.commissionrate, vtiger_products.qty_per_unit, vtiger_products.unit_price,
            vtiger_crmentity.crmid, vtiger_crmentity.assigned_user_id
            FROM vtiger_products
            INNER JOIN vtiger_seproductsrel ON vtiger_products.productid = vtiger_seproductsrel.productid  and vtiger_seproductsrel.setype = 'Leads'
            INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
            INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_seproductsrel.crmid
            LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.assigned_user_id
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
			WHERE vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.leadid = $id";

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_products method ...");

        return $return_value;
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
            'Documents'   => 'vtiger_senotesrel',
            'Attachments' => 'vtiger_seattachmentsrel',
            'Products'    => 'vtiger_seproductsrel',
            'Campaigns'   => 'vtiger_campaignleadrel'
        ];

        $tbl_field_arr = [
            'vtiger_senotesrel'       => 'notesid',
            'vtiger_seattachmentsrel' => 'attachmentsid',
            'vtiger_seproductsrel'    => 'productid',
            'vtiger_campaignleadrel'  => 'campaignid'
        ];

        $entity_tbl_field_arr = [
            'vtiger_senotesrel'       => 'crmid',
            'vtiger_seattachmentsrel' => 'crmid',
            'vtiger_seproductsrel'    => 'crmid',
            'vtiger_campaignleadrel'  => 'leadid'
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
     * Function to get the secondary query part of a report
     * @param - $module primary module name
     * @param - $secmodule secondary module name
     * returns the query string formed on fetching the related data for report for secondary module
     */
    function generateReportsSecQuery($module, $secmodule, $queryPlanner)
    {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityLeads', ['vtiger_groupsLeads', 'vtiger_usersLeads', 'vtiger_lastModifiedByLeads']);

        // TODO Support query planner
        if (!$queryPlanner->requireTable("vtiger_leaddetails", $matrix)) {
            return '';
        }

        $matrix->setDependency('vtiger_leaddetails', ['vtiger_crmentityLeads', 'vtiger_leadaddress', 'vtiger_leadsubdetails', 'vtiger_leadscf', 'vtiger_email_trackLeads']);

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_leaddetails", "leadid", $queryPlanner);
        if ($queryPlanner->requireTable("vtiger_crmentityLeads", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityLeads on vtiger_crmentityLeads.crmid = vtiger_leaddetails.leadid and vtiger_crmentityLeads.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_leadaddress")) {
            $query .= " left join vtiger_leadaddress on vtiger_leaddetails.leadid = vtiger_leadaddress.leadaddressid";
        }
        if ($queryPlanner->requireTable("vtiger_leadsubdetails")) {
            $query .= " left join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid";
        }
        if ($queryPlanner->requireTable("vtiger_leadscf")) {
            $query .= " left join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leaddetails.leadid";
        }
        if ($queryPlanner->requireTable("vtiger_email_trackLeads")) {
            $query .= " LEFT JOIN vtiger_email_track AS vtiger_email_trackLeads ON vtiger_email_trackLeads.crmid = vtiger_leaddetails.leadid";
        }
        if ($queryPlanner->requireTable("vtiger_groupsLeads")) {
            $query .= " left join vtiger_groups as vtiger_groupsLeads on vtiger_groupsLeads.groupid = vtiger_crmentityLeads.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_usersLeads")) {
            $query .= " left join vtiger_users as vtiger_usersLeads on vtiger_usersLeads.id = vtiger_crmentityLeads.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByLeads")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByLeads on vtiger_lastModifiedByLeads.id = vtiger_crmentityLeads.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyLeads")) {
            $query .= " left join vtiger_users as vtiger_createdbyLeads on vtiger_createdbyLeads.id = vtiger_crmentityLeads.creator_user_id ";
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
            "Products"  => ["vtiger_seproductsrel" => ["crmid", "productid"], "vtiger_leaddetails" => "leadid"],
            "Campaigns" => ["vtiger_campaignleadrel" => ["leadid", "campaignid"], "vtiger_leaddetails" => "leadid"],
            "Documents" => ["vtiger_senotesrel" => ["crmid", "notesid"], "vtiger_leaddetails" => "leadid"],
            "Services"  => ["vtiger_crmentityrel" => ["crmid", "relcrmid"], "vtiger_leaddetails" => "leadid"],
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

        if ($return_module == 'Campaigns') {
            $sql = 'DELETE FROM vtiger_campaignleadrel WHERE leadid=? AND campaignid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } elseif ($return_module == 'Products') {
            $sql = 'DELETE FROM vtiger_seproductsrel WHERE crmid=? AND productid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } elseif ($return_module == 'Documents') {
            $sql = 'DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } else {
            parent::unlinkRelationship($id, $return_module, $return_id);
        }
    }

    function getListButtons($app_strings)
    {
        $list_buttons = [];

        if (isPermitted('Leads', 'Delete', '') == 'yes') {
            $list_buttons['del'] = $app_strings["LBL_MASS_DELETE"];
        }
        if (isPermitted('Leads', 'EditView', '') == 'yes') {
            $list_buttons['mass_edit'] = $app_strings["LBL_MASS_EDIT"];
            $list_buttons['c_owner'] = $app_strings["LBL_CHANGE_OWNER"];
        }

        // end of mailer export
        return $list_buttons;
    }

    public function save_related_module($module, $crmid, $with_module, $with_crmids, $otherParams = [])
    {
        $adb = PearDatabase::getInstance();

        if (!is_array($with_crmids)) {
            $with_crmids = [$with_crmids];
        }

        foreach ($with_crmids as $with_crmid) {
            if ($with_module == 'Products') {
                $adb->pquery('INSERT INTO vtiger_seproductsrel VALUES(?,?,?,?)', [$crmid, $with_crmid, $module, 1]);
                $this->setTrackLinkedInfo($crmid, $with_crmid);
            } elseif ($with_module == 'Campaigns') {
                $adb->pquery('insert into  vtiger_campaignleadrel values(?,?,1)', [$with_crmid, $crmid]);
                $this->setTrackLinkedInfo($crmid, $with_crmid);
            } else {
                parent::save_related_module($module, $crmid, $with_module, $with_crmid);
            }
        }
    }

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
                    if ($this->tab_name_index[$tableName]) {
                        $fromClause .= " INNER JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
                            " = $this->table_name.$this->table_index";
                    }
                }
            }
        }

        $whereClause = " WHERE vtiger_crmentity.deleted = 0 AND vtiger_leaddetails.converted=0 ";
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
     * Invoked when special actions are to be performed on the module.
     *
     * @param String Module name
     * @param String Event Type
     */
    function vtlib_handler($moduleName, $eventType)
    {
        if ($moduleName == 'Leads') {
            if ($eventType == 'module.disabled') {
                Settings_Vtiger_MenuItem_Model::deactivate($this->LBL_LEAD_MAPPING);
            } elseif ($eventType == 'module.enabled') {
                Settings_Vtiger_MenuItem_Model::activate($this->LBL_LEAD_MAPPING);
            }
        }
    }
}
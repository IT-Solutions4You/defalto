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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Contacts/Contacts.php,v 1.70 2005/04/27 11:21:49 rank Exp $
 * Description:  TODO: To be written.
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

// Contact is used to store customer information.
class Contacts extends CRMEntity
{
    public string $moduleName = 'Contacts';
    public string $parentName = 'HOME';

    public $table_name = "vtiger_contactdetails";
    public $table_index = 'contactid';
    public $tab_name = [
        'vtiger_crmentity',
        'vtiger_contactdetails',
        'vtiger_contactaddress',
        'vtiger_contactsubdetails',
        'vtiger_contactscf',
        'vtiger_customerdetails',
        'vtiger_portalinfo'
    ];
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'vtiger_contactdetails' => 'contactid',
        'vtiger_contactaddress' => 'contactaddressid',
        'vtiger_contactsubdetails' => 'contactsubscriptionid',
        'vtiger_contactscf' => 'contactid',
        'vtiger_customerdetails' => 'customerid',
        'vtiger_portalinfo' => 'id',
        'vtiger_campaignrelstatus' => 'campaignrelstatusid',
    ];

    public array $tab_name_left_join = [
        'vtiger_campaignrelstatus'
    ];

    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_contactscf', 'contactid'];

    public $sortby_fields = ['lastname', 'firstname', 'title', 'email', 'phone', 'assigned_user_id', 'accountname'];

    public $list_link_field = 'lastname';

    // This is the list of vtiger_fields that are in the lists.
    public $list_fields = [
        'First Name' => ['contactdetails' => 'firstname'],
        'Last Name' => ['contactdetails' => 'lastname'],
        'Title' => ['contactdetails' => 'title'],
        'Account Name' => ['account' => 'accountid'],
        'Email' => ['contactdetails' => 'email'],
        'Office Phone' => ['contactdetails' => 'phone'],
        'Assigned To' => ['crmentity' => 'assigned_user_id'],
    ];

    public $range_fields = [
        'first_name',
        'last_name',
        'primary_address_city',
        'account_name',
        'account_id',
        'id',
        'email1',
        'salutation',
        'title',
        'phone_mobile',
        'reports_to_name',
        'primary_address_street',
        'primary_address_city',
        'primary_address_state',
        'primary_address_postalcode',
        'primary_address_country_id',
        'alt_address_city',
        'alt_address_street',
        'alt_address_city',
        'alt_address_state',
        'alt_address_postalcode',
        'alt_address_country_id',
        'office_phone',
        'home_phone',
        'other_phone',
        'fax',
        'department',
        'birthdate',
    ];

    public $list_fields_name = [
        'First Name' => 'firstname',
        'Last Name' => 'lastname',
        'Title' => 'title',
        'Account Name' => 'account_id',
        'Email' => 'email',
        'Office Phone' => 'phone',
        'Assigned To' => 'assigned_user_id',
    ];

    public $search_fields = [
        'First Name' => ['contactdetails' => 'firstname'],
        'Last Name' => ['contactdetails' => 'lastname'],
        'Title' => ['contactdetails' => 'title'],
        'Account Name' => ['contactdetails' => 'account_id'],
        'Assigned To' => ['crmentity' => 'assigned_user_id'],
    ];

    public $search_fields_name = [
        'First Name' => 'firstname',
        'Last Name' => 'lastname',
        'Title' => 'title',
        'Account Name' => 'account_id',
        'Assigned To' => 'assigned_user_id',
    ];

    // This is the list of vtiger_fields that are required
    public $required_fields = ["lastname" => 1];

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['assigned_user_id', 'lastname', 'createdtime', 'modifiedtime'];

    // For Alphabetical search
    public $def_basicsearch_col = 'lastname';

    public $related_module_table_index = [
        'Potentials' => ['table_name' => 'vtiger_potential', 'table_index' => 'potentialid', 'rel_index' => 'contact_id'],
        'Quotes' => ['table_name' => 'vtiger_quotes', 'table_index' => 'quoteid', 'rel_index' => 'contact_id'],
        'SalesOrder' => ['table_name' => 'vtiger_salesorder', 'table_index' => 'salesorderid', 'rel_index' => 'contact_id'],
        'PurchaseOrder' => ['table_name' => 'vtiger_purchaseorder', 'table_index' => 'purchaseorderid', 'rel_index' => 'contact_id'],
        'Invoice' => ['table_name' => 'vtiger_invoice', 'table_index' => 'invoiceid', 'rel_index' => 'contact_id'],
        'HelpDesk' => ['table_name' => 'vtiger_troubletickets', 'table_index' => 'ticketid', 'rel_index' => 'contact_id'],
        'ServiceContracts' => ['table_name' => 'vtiger_servicecontracts', 'table_index' => 'servicecontractsid', 'rel_index' => 'contact_id'],
        'Assets' => ['table_name' => 'vtiger_assets', 'table_index' => 'assetsid', 'rel_index' => 'contact'],
        'Project' => ['table_name' => 'vtiger_project', 'table_index' => 'projectid', 'rel_index' => 'contact_id'],
    ];

    /** Function to get the number of Contacts assigned to a particular User.
     *
     * @param varchar $user name - Assigned to User
     *                      Returns the count of contacts assigned to user.
     */
    function getCount($user_name)
    {
        global $log;
        $log->debug("Entering getCount(" . $user_name . ") method ...");
        $query = "select count(*) from vtiger_contactdetails 
            inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid 
            inner join vtiger_users on vtiger_users.id=vtiger_crmentity.assigned_user_id 
            where user_name=? and vtiger_crmentity.deleted=0";
        $result = $this->db->pquery($query, [$user_name], true, "Error retrieving contacts count");
        $rows_found = $this->db->getRowCount($result);
        $row = $this->db->fetchByAssoc($result, 0);

        $log->debug("Exiting getCount method ...");

        return $row["count(*)"];
    }

    /** Function to process list query for Plugin with Security Parameters for a given query
     *
     * @param $query
     *  Returns the results of query in array format
     */
    function plugin_process_list_query($query)
    {
        global $log, $adb, $current_user;
        $log->debug("Entering process_list_query1(" . $query . ") method ...");
        $permitted_field_lists = [];
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
            $sql1 = "select columnname from vtiger_field where tabid=4 and block <> 75 and vtiger_field.presence in (0,2)";
            $params1 = [];
        } else {
            $profileList = getCurrentUserProfileList();
            $sql1 = "select columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=4 and vtiger_field.block <> 6 and vtiger_field.block <> 75 and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_field.presence in (0,2)";
            $params1 = [];
            if (php7_count($profileList) > 0) {
                $sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
                array_push($params1, $profileList);
            }
        }
        $result1 = $this->db->pquery($sql1, $params1);
        for ($i = 0; $i < $adb->num_rows($result1); $i++) {
            $permitted_field_lists[] = $adb->query_result($result1, $i, 'columnname');
        }

        $result =& $this->db->pquery($query, [], true, "Error retrieving $this->object_name list: ");
        $list = [];
        $rows_found = $this->db->getRowCount($result);
        if ($rows_found != 0) {
            for ($index = 0, $row = $this->db->fetchByAssoc($result, $index); $row && $index < $rows_found; $index++, $row = $this->db->fetchByAssoc($result, $index)) {
                $contact = [];

                $contact["lastname"] = in_array("lastname", $permitted_field_lists) ? $row["lastname"] : "";
                $contact["firstname"] = in_array("firstname", $permitted_field_lists) ? $row["firstname"] : "";
                $contact["email"] = in_array("email", $permitted_field_lists) ? $row["email"] : "";

                if (in_array("accountid", $permitted_field_lists)) {
                    $contact["accountname"] = $row["accountname"];
                    $contact["account_id"] = $row["accountid"];
                } else {
                    $contact["accountname"] = "";
                    $contact["account_id"] = "";
                }
                $contact["contactid"] = $row["contactid"];
                $list[] = $contact;
            }
        }

        $response = [];
        $response['list'] = $list;
        $response['row_count'] = $rows_found;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;
        $log->debug("Exiting process_list_query1 method ...");

        return $response;
    }

    /** Function to export the contact records in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export Contacts Query.
     */
    function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("Contacts", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);

        $query = "SELECT vtiger_contactdetails.salutation as 'Salutation',$fields_list,case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name
            FROM vtiger_contactdetails
            inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
            LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id=vtiger_users.id and vtiger_users.status='Active'
            LEFT JOIN vtiger_account on vtiger_contactdetails.account_id=vtiger_account.accountid
            left join vtiger_contactaddress on vtiger_contactaddress.contactaddressid=vtiger_contactdetails.contactid
            left join vtiger_contactsubdetails on vtiger_contactsubdetails.contactsubscriptionid=vtiger_contactdetails.contactid
            left join vtiger_contactscf on vtiger_contactscf.contactid=vtiger_contactdetails.contactid
			left join vtiger_customerdetails on vtiger_customerdetails.customerid=vtiger_contactdetails.contactid
	        LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id ";
        $query .= getNonAdminAccessControlQuery('Contacts', $current_user);
        $where_auto = " vtiger_crmentity.deleted = 0 ";

        if ($where != "") {
            $query .= "  WHERE ($where) AND " . $where_auto;
        } else {
            $query .= "  WHERE " . $where_auto;
        }

        $log->info("Export Query Constructed Successfully");
        $log->debug("Exiting create_export_query method ...");

        return $query;
    }

    /** Function to get the Columnnames of the Contacts
     * Used By vtigerCRM Word Plugin
     * Returns the Merge Fields for Word Plugin
     */
    function getColumnNames()
    {
        global $log, $current_user;
        $log->debug("Entering getColumnNames() method ...");
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
            $sql1 = "select fieldlabel from vtiger_field where tabid=4 and block <> 75 and vtiger_field.presence in (0,2)";
            $params1 = [];
        } else {
            $profileList = getCurrentUserProfileList();
            $sql1 = "select vtiger_field.fieldid,fieldlabel from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=4 and vtiger_field.block <> 75 and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_field.presence in (0,2)";
            $params1 = [];
            if (php7_count($profileList) > 0) {
                $sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by fieldid";
                array_push($params1, $profileList);
            }
        }
        $result = $this->db->pquery($sql1, $params1);
        $numRows = $this->db->num_rows($result);
        for ($i = 0; $i < $numRows; $i++) {
            $custom_fields[$i] = $this->db->query_result($result, $i, "fieldlabel");
            $custom_fields[$i] = preg_replace("/\s+/", "", $custom_fields[$i]);
            $custom_fields[$i] = strtoupper($custom_fields[$i]);
        }
        $mergeflds = $custom_fields;
        $log->debug("Exiting getColumnNames method ...");

        return $mergeflds;
    }
//End

    /** Function to get the Contacts assigned to a user with a valid email address.
     *
     * @param varchar $username - User Name
     * @param varchar $emailaddress - Email Addr for each contact.
     *                              Used By vtigerCRM Outlook Plugin
     *                              Returns the Query
     */
    function get_searchbyemailid($username, $emailaddress)
    {
        global $log;
        global $current_user;
        require_once("modules/Users/Users.php");
        $seed_user = new Users();
        $user_id = $seed_user->retrieve_user_id($username);
        $current_user = $seed_user;
        $current_user->retrieve_entity_info($user_id, 'Users');
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
        $log->debug("Entering get_searchbyemailid(" . $username . "," . $emailaddress . ") method ...");
        $query = "select vtiger_contactdetails.lastname,vtiger_contactdetails.firstname, vtiger_contactdetails.contactid, vtiger_contactdetails.salutationtype,
            vtiger_contactdetails.email,vtiger_contactdetails.title, vtiger_contactdetails.mobile,vtiger_account.accountname, vtiger_account.accountid as accountid 
            from vtiger_contactdetails
            inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
            inner join vtiger_users on vtiger_users.id=vtiger_crmentity.assigned_user_id
            left join vtiger_account on vtiger_account.accountid=vtiger_contactdetails.accountid
            left join vtiger_contactaddress on vtiger_contactaddress.contactaddressid=vtiger_contactdetails.contactid
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id";
        $query .= getNonAdminAccessControlQuery('Contacts', $current_user);
        $query .= "where vtiger_crmentity.deleted=0";
        if (trim($emailaddress) != '') {
            $query .= " and ((vtiger_contactdetails.email like '" . formatForSqlLike($emailaddress) .
                "') or vtiger_contactdetails.lastname REGEXP REPLACE('" . $emailaddress .
                "',' ','|') or vtiger_contactdetails.firstname REGEXP REPLACE('" . $emailaddress .
                "',' ','|'))  and vtiger_contactdetails.email != ''";
        } else {
            $query .= " and (vtiger_contactdetails.email like '" . formatForSqlLike($emailaddress) .
                "' and vtiger_contactdetails.email != '')";
        }

        $log->debug("Exiting get_searchbyemailid method ...");

        return $this->plugin_process_list_query($query);
    }

    /** Function to get the Contacts associated with the particular User Name.
     *
     * @param varchar $user_name - User Name
     *                           Returns query
     */

    function get_contactsforol($user_name)
    {
        global $log, $adb, $current_user;
        require_once("modules/Users/Users.php");
        $seed_user = new Users();
        $user_id = $seed_user->retrieve_user_id($user_name);
        $current_user = $seed_user;
        $current_user->retrieve_entity_info($user_id, 'Users');
        require('user_privileges/user_privileges_' . $current_user->id . '.php');
        require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

        if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
            $sql1 = "select tablename,columnname from vtiger_field where tabid=4 and vtiger_field.presence in (0,2)";
            $params1 = [];
        } else {
            $profileList = getCurrentUserProfileList();
            $sql1 = "select tablename,columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=4 and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_field.presence in (0,2)";
            $params1 = [];
            if (php7_count($profileList) > 0) {
                $sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
                array_push($params1, $profileList);
            }
        }
        $result1 = $adb->pquery($sql1, $params1);
        for ($i = 0; $i < $adb->num_rows($result1); $i++) {
            $permitted_lists[] = $adb->query_result($result1, $i, 'tablename');
            $permitted_lists[] = $adb->query_result($result1, $i, 'columnname');
            if ($adb->query_result($result1, $i, 'columnname') == "accountid") {
                $permitted_lists[] = 'vtiger_account';
                $permitted_lists[] = 'accountname';
            }
        }
        $permitted_lists = array_chunk($permitted_lists, 2);
        $column_table_lists = [];
        for ($i = 0; $i < php7_count($permitted_lists); $i++) {
            $column_table_lists[] = implode(".", $permitted_lists[$i]);
        }

        $log->debug("Entering get_contactsforol(" . $user_name . ") method ...");
        $columns = implode(',', $column_table_lists);
        $query = "SELECT vtiger_contactdetails.contactid as id, $columns from vtiger_contactdetails
            inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
            inner join vtiger_users on vtiger_users.id=vtiger_crmentity.assigned_user_id
            left join vtiger_customerdetails on vtiger_customerdetails.customerid=vtiger_contactdetails.contactid
            left join vtiger_account on vtiger_account.accountid=vtiger_contactdetails.account_id
            left join vtiger_contactaddress on vtiger_contactaddress.contactaddressid=vtiger_contactdetails.contactid
            left join vtiger_contactsubdetails on vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid
            left join vtiger_campaigncontrel on vtiger_contactdetails.contactid = vtiger_campaigncontrel.contactid
            left join vtiger_campaignrelstatus on vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaigncontrel.campaignrelstatusid
            LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
            where vtiger_crmentity.deleted=0 and vtiger_users.user_name='$user_name'";
        $log->debug("Exiting get_contactsforol method ...");

        return $query;
    }

    /**
     *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
     *
     * @param int $id - entity id to which the vtiger_files to be uploaded
     * @param string $module - the current module name
     */
    function insertIntoAttachment($id, $module)
    {
        global $log, $adb, $upload_badext;
        $log->debug("Entering into insertIntoAttachment($id,$module) method.");

        $file_saved = false;
        //This is to added to store the existing attachment id of the contact where we should delete this when we give new image
        $old_result = $adb->pquery(
            "select vtiger_crmentity.crmid from vtiger_seattachmentsrel inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_seattachmentsrel.attachmentsid where  vtiger_seattachmentsrel.crmid=?",
            [$id]
        );
        $old_attachmentid = $adb->query_result($old_result, 0, 'crmid');

        foreach ($_FILES as $fileindex => $files) {
            if ($files['name'] != '' && $files['size'] > 0) {
                $files['original_name'] = vtlib_purify($_REQUEST[$fileindex . '_hidden']);
                $file_saved = $this->uploadAndSaveFile($id, $module, $files);
            }
        }

        $imageNameSql = 'SELECT name FROM vtiger_seattachmentsrel INNER JOIN vtiger_attachments ON
								vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid LEFT JOIN vtiger_contactdetails ON
								vtiger_contactdetails.contactid = vtiger_seattachmentsrel.crmid WHERE vtiger_seattachmentsrel.crmid = ?';
        $imageNameResult = $adb->pquery($imageNameSql, [$id]);
        $imageName = decode_html($adb->query_result($imageNameResult, 0, "name"));

        //Inserting image information of record into base table
        $adb->pquery('UPDATE vtiger_contactdetails SET imagename = ? WHERE contactid = ?', [$imageName, $id]);

        //This is to handle the delete image for contacts
        if ($module == 'Contacts' && $file_saved) {
            if ($old_attachmentid != '') {
                $setype = getSalesEntityType($old_attachmentid);

                if ($setype == 'Contacts Image') {
                    $del_res1 = $adb->pquery("delete from vtiger_attachments where attachmentsid=?", [$old_attachmentid]);
                    $del_res2 = $adb->pquery("delete from vtiger_seattachmentsrel where attachmentsid=?", [$old_attachmentid]);
                }
            }
        }

        $log->debug("Exiting from insertIntoAttachment($id,$module) method.");
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
            "Potentials" => "vtiger_potential",
            "HelpDesk" => "vtiger_troubletickets",
            "Quotes" => "vtiger_quotes",
            "PurchaseOrder" => "vtiger_purchaseorder",
            "SalesOrder" => "vtiger_salesorder",
            "Attachments" => "vtiger_seattachmentsrel",
            'Invoice' => 'vtiger_invoice',
            'ServiceContracts' => 'vtiger_servicecontracts',
            'Project' => 'vtiger_project',
            'Assets' => 'vtiger_assets',
        ];

        $tbl_field_arr = [
            "vtiger_potential" => "potentialid",
            "vtiger_troubletickets" => "ticketid",
            "vtiger_quotes" => "quoteid",
            "vtiger_purchaseorder" => "purchaseorderid",
            "vtiger_salesorder" => "salesorderid",
            "vtiger_seattachmentsrel" => "attachmentsid",
            'vtiger_invoice' => 'invoiceid',
            'vtiger_servicecontracts' => 'servicecontractsid',
            'vtiger_project' => 'projectid',
            'vtiger_assets' => 'assetsid',
        ];

        $entity_tbl_field_arr = [
            "vtiger_potential" => "contact_id",
            "vtiger_troubletickets" => "contact_id",
            "vtiger_quotes" => "contact_id",
            "vtiger_purchaseorder" => "contact_id",
            "vtiger_salesorder" => "contact_id",
            "vtiger_seattachmentsrel" => "crmid",
            'vtiger_invoice' => 'contact_id',
            'vtiger_servicecontracts' => 'contact_id',
            'vtiger_project' => 'contact_id',
            'vtiger_assets' => 'contact',
        ];

        foreach ($transferEntityIds as $transferId) {
            foreach ($rel_table_arr as $rel_module => $rel_table) {
                $relModuleModel = Vtiger_Module::getInstance($rel_module);
                if ($relModuleModel) {
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
            $adb->pquery("UPDATE vtiger_potential SET related_to = ? WHERE related_to = ?", [$entityId, $transferId]);
        }
        parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
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
            'HelpDesk' => ['vtiger_troubletickets' => ['contact_id', 'ticketid'], 'vtiger_contactdetails' => 'contactid'],
            'Quotes' => ['vtiger_quotes' => ['contact_id', 'quoteid'], 'vtiger_contactdetails' => 'contactid'],
            'PurchaseOrder' => ['vtiger_purchaseorder' => ['contact_id', 'purchaseorderid'], 'vtiger_contactdetails' => 'contactid'],
            'SalesOrder' => ['vtiger_salesorder' => ['contact_id', 'salesorderid'], 'vtiger_contactdetails' => 'contactid'],
            'Accounts' => ['vtiger_contactdetails' => ['contactid', 'account_id']],
            'Invoice' => ['vtiger_invoice' => ['contact_id', 'invoiceid'], 'vtiger_contactdetails' => 'contactid'],
        ];

        return $rel_tables[$secmodule];
    }

    // Function to unlink all the dependent entities of the given Entity by Id
    public function unlinkDependencies($module, $id)
    {
        Core_Relation_Model::saveDependencies($id, 'contact_id', 'vtiger_potential', 'potentialid');
        Core_Relation_Model::saveDependencies($id, 'contact_id', 'vtiger_troubletickets', 'ticketid');
        Core_Relation_Model::saveDependencies($id, 'contact_id', 'vtiger_purchaseorder', 'purchaseorderid');
        Core_Relation_Model::saveDependencies($id, 'contact_id', 'vtiger_salesorder', 'salesorderid');
        Core_Relation_Model::saveDependencies($id, 'contact_id', 'vtiger_quotes', 'quoteid');

        //remove the portal info the contact
        $this->db->pquery('DELETE FROM vtiger_portalinfo WHERE id = ?', [$id]);
        $this->db->pquery('UPDATE vtiger_customerdetails SET portal=0,support_start_date=NULL,support_end_date=NULl WHERE customerid=?', [$id]);

        parent::unlinkDependencies($module, $id);
    }

    // Function to unlink an entity with given Id from another entity
    public function unlinkRelationship($id, $return_module, $return_id)
    {
        if (empty($return_module) || empty($return_id)) {
            return;
        }

        if ($return_module == 'Campaigns') {
            Campaigns_Relation_Model::deleteCampaignRelation($return_id, $return_module, $id, 'Contacts');
        }

        parent::unlinkRelationship($id, $return_module, $return_id);
    }

    //added to get mail info for portal user
    //type argument included when when addin customizable tempalte for sending portal login details
    public static function getPortalEmailContents($entityData, $password, $type = '')
    {
        require_once 'config.inc.php';
        global $PORTAL_URL, $HELPDESK_SUPPORT_EMAIL_ID;

        $moduleName = $entityData->getModuleName();
        $portalURL = vtranslate('Please ', $moduleName) . '<a href="' . $PORTAL_URL . '" style="font-family:Arial, Helvetica, sans-serif;font-size:13px;">' . vtranslate(
                'click here',
                $moduleName
            ) . '</a>';

        $language = Vtiger_Language_Handler::getLanguage();
        $params = ['templatename' => 'Customer Login Details', 'category' => 'system'];
        $templateId = EMAILMaker_Record_Model::getTemplateId($params);
        $recordId = vtws_getIdComponents($entityData->getId())[1];
        $contentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateId, $language, $moduleName, $recordId, $recordId, $moduleName);
        $contentModel->getContent();

        $body = decode_html($contentModel->getBody());
        $contents = $body;
        $contents = str_replace('$contact_name$', $entityData->get('firstname') . " " . $entityData->get('lastname'), $contents);
        $contents = str_replace('$login_name$', $entityData->get('email'), $contents);
        $contents = str_replace('$password$', $password, $contents);
        $contents = str_replace('$URL$', $portalURL, $contents);
        $contents = str_replace('$support_team$', getTranslatedString('Support Team', $moduleName), $contents);
        $contents = str_replace('$logo$', '<img src="cid:logo" />', $contents);

        if ('LoginDetails' === $type) {
            $temp = $contents;
            $value["subject"] = decode_html($contentModel->getSubject());
            $value["body"] = $temp;

            return $value;
        }

        return $contents;
    }

    public function save_related_module($module, $crmid, $with_module, $with_crmids, $otherParams = [])
    {
        if (!is_array($with_crmids)) {
            $with_crmids = [$with_crmids];
        }

        foreach ($with_crmids as $with_crmid) {
            if ($with_module == 'Campaigns') {
                Campaigns_Relation_Model::saveCampaignRelation($with_crmid, $with_module, $crmid, 'Contacts');
            }

            parent::save_related_module($module, $crmid, $with_module, $with_crmid);
        }
    }

    function getListButtons($app_strings, $mod_strings = false)
    {
        $list_buttons = [];

        if (isPermitted('Contacts', 'Delete', '') == 'yes') {
            $list_buttons['del'] = $app_strings["LBL_MASS_DELETE"];
        }
        if (isPermitted('Contacts', 'EditView', '') == 'yes') {
            $list_buttons['mass_edit'] = $app_strings["LBL_MASS_EDIT"];
            $list_buttons['c_owner'] = $app_strings["LBL_CHANGE_OWNER"];
        }

        return $list_buttons;
    }
}
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

class HelpDesk extends CRMEntity
{
    public string $moduleVersion = '1.2';
    public string $moduleName = 'HelpDesk';
    public string $parentName = 'SUPPORT';
    public $table_name = 'vtiger_troubletickets';
    public $table_index = 'ticketid';
    public $tab_name = ['vtiger_crmentity', 'vtiger_troubletickets', 'vtiger_ticketcf'];
    public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_troubletickets' => 'ticketid', 'vtiger_ticketcf' => 'ticketid', 'vtiger_ticketcomments' => 'ticketid'];
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_ticketcf', 'ticketid'];

    //Pavani: Assign value to entity_table
    public $entity_table = 'vtiger_crmentity';

    public $sortby_fields = ['ticket_title', 'ticketstatus', 'ticketpriorities', 'crmid', 'firstname', 'assigned_user_id'];

    public $list_fields = [
        //Module Sequence Numbering
        //'Ticket ID'=>Array('crmentity'=>'crmid'),
        'Ticket No'    => ['troubletickets' => 'ticket_no'],
        // END
        'Subject'      => ['troubletickets' => 'ticket_title'],
        'Related to'   => ['troubletickets' => 'parent_id'],
        'Contact Name' => ['troubletickets' => 'contact_id'],
        'Status'       => ['troubletickets' => 'ticketstatus'],
        'Priority'     => ['troubletickets' => 'ticketpriorities'],
        'Assigned To'  => ['crmentity', 'assigned_user_id'],
    ];

    public $list_fields_name = [
        'Ticket No'    => 'ticket_no',
        'Subject'      => 'ticket_title',
        'Related to'   => 'parent_id',
        'Contact Name' => 'contact_id',
        'Status'       => 'ticketstatus',
        'Priority'     => 'ticketpriorities',
        'Assigned To'  => 'assigned_user_id',
    ];

    public $list_link_field = 'ticket_title';

    public $range_fields = [
        'ticketid',
        'ticket_title',
        'firstname',
        'lastname',
        'parent_id',
        'productid',
        'productname',
        'ticketpriorities',
        'ticketseverities',
        'ticketstatus',
        'ticketcategories',
        'description',
        'solution',
        'modifiedtime',
        'createdtime',
    ];
    public $search_fields = [
        //'Ticket ID' => Array('vtiger_crmentity'=>'crmid'),
        'Ticket No' => ['vtiger_troubletickets' => 'ticket_no'],
        'Title'     => ['vtiger_troubletickets' => 'ticket_title'],
    ];
    public $search_fields_name = [
        'Ticket No' => 'ticket_no',
        'Title'     => 'ticket_title',
    ];
    //Specify Required fields
    public $required_fields = [];

    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime', 'ticket_title', 'ticketpriorities', 'ticketstatus'];

    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = 'crmid';
    public $default_sort_order = 'DESC';

    // For Alphabetical search
    public $def_basicsearch_col = 'ticket_title';

    /**
     * @inheritDoc
     */
    public function save_module(string $module)
    {
        //Inserting into Ticket Comment Table
        $this->insertIntoTicketCommentTable("vtiger_ticketcomments", $module);

        //Inserting into vtiger_attachments
        $this->insertIntoAttachment($this->id, $module);

        //service contract update
        $return_action = $_REQUEST['return_action'] ?? '';
        $for_module = $_REQUEST['return_module'] ?? '';
        $for_crmid = $_REQUEST['return_id'] ?? '';

        if ($return_action && $for_module && $for_crmid) {
            if ($for_module == 'ServiceContracts') {
                $on_focus = CRMEntity::getInstance($for_module);
                $on_focus->save_related_module($for_module, $for_crmid, $module, $this->id);
            }
        }
    }

    public function save_related_module($module, $crmid, $with_module, $with_crmid, $otherParams = [])
    {
        parent::save_related_module($module, $crmid, $with_module, $with_crmid);

        if ($with_module == 'ServiceContracts') {
            $serviceContract = CRMEntity::getInstance("ServiceContracts");
            $serviceContract->updateHelpDeskRelatedTo($with_crmid, $crmid);
            $serviceContract->updateServiceContractState($with_crmid);
        }
    }

    /** Function to insert values in vtiger_ticketcomments  for the specified tablename and  module
     *
     * @param $table_name -- table name:: Type varchar
     * @param $module     -- module:: Type varchar
     */
    function insertIntoTicketCommentTable($table_name, $module)
    {
        global $log;
        $log->info("in insertIntoTicketCommentTable  " . $table_name . "    module is  " . $module);
        global $adb;
        global $current_user;

        $current_time = $adb->formatDate(date('Y-m-d H:i:s'), true);
        if ($this->column_fields['from_portal'] != 1) {
            $ownertype = 'user';
            $ownerId = $current_user->id;
        } else {
            $ownertype = 'customer';
            $ownerId = $this->column_fields['parent_id'];
        }

        $comment = $this->column_fields['comments'];
        if ($comment != '') {
            $sql = "insert into vtiger_ticketcomments values(?,?,?,?,?,?)";
            $params = ['', $this->id, from_html($comment), $ownerId, $ownertype, $current_time];
            $adb->pquery($sql, $params);
        }
    }

    /**
     *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
     *
     * @param int    $id     - entity id to which the vtiger_files to be uploaded
     * @param string $module - the current module name
     */
    function insertIntoAttachment($id, $module)
    {
        global $log, $adb;
        $log->debug("Entering into insertIntoAttachment($id,$module) method.");

        $file_saved = false;

        if (php7_count($_FILES)) {
            foreach ($_FILES as $fileindex => $files) {
                if ($files['name'] != '' && $files['size'] > 0) {
                    $files['original_name'] = vtlib_purify($_REQUEST[$fileindex . '_hidden']);
                    $file_saved = $this->uploadAndSaveFile($id, $module, $files);
                }
            }
        }

        $log->debug("Exiting from insertIntoAttachment($id,$module) method.");
    }

    /**    Function to process the list query and return the result with number of rows
     *
     * @param string $query - query
     *
     * @return array  $response - array(    list           => array(
     * $i => array(key => val)
     * ),
     * row_count      => '',
     * next_offset    => '',
     * previous_offset    =>''
     * )
     * where $i=0,1,..n & key = ticketid, title, firstname, ..etc(range_fields) & val = value of the key from db retrieved row
     **/
    function process_list_query($query, $row_offset, $limit = -1, $max_per_page = -1)
    {
        global $log;
        $log->debug("Entering process_list_query(" . $query . ") method ...");

        $result =& $this->db->pquery($query, [], true, "Error retrieving $this->object_name list: ");
        $list = [];
        $rows_found = $this->db->getRowCount($result);
        if ($rows_found != 0) {
            $ticket = [];
            for ($index = 0, $row = $this->db->fetchByAssoc($result, $index); $row && $index < $rows_found; $index++, $row = $this->db->fetchByAssoc($result, $index)) {
                foreach ($this->range_fields as $columnName) {
                    if (isset($row[$columnName])) {
                        $ticket[$columnName] = $row[$columnName];
                    } else {
                        $ticket[$columnName] = "";
                    }
                }
                $list[] = $ticket;
            }
        }

        $response = [];
        $response['list'] = $list;
        $response['row_count'] = $rows_found;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;

        $log->debug("Exiting process_list_query method ...");

        return $response;
    }

    /**     Function to get the Customer Name who has made comment to the ticket from the customer portal
     *
     * @param int $id - Ticket id
     *
     * @return string $customername - The contact name
     **/
    function getCustomerName($id)
    {
        global $log;
        $log->debug("Entering getCustomerName(" . $id . ") method ...");
        global $adb;
        $sql = "select * from vtiger_portalinfo inner join vtiger_troubletickets on vtiger_troubletickets.contact_id = vtiger_portalinfo.id where vtiger_troubletickets.ticketid=?";
        $result = $adb->pquery($sql, [$id]);
        $customername = $adb->query_result($result, 0, 'user_name');
        $log->debug("Exiting getCustomerName method ...");

        return $customername;
    }
    // Function to create, export query for helpdesk module

    /** Function to export the ticket records in CSV Format
     *
     * @param reference variable - where condition is passed when the query is executed
     * Returns Export Tickets Query.
     */
    function create_export_query($where)
    {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        //To get the Permitted fields query and the permitted fields list
        $sql = getPermittedFieldsQuery("HelpDesk", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);
        //Ticket changes--5198
        $fields_list = str_replace(",vtiger_ticketcomments.comments as 'Add Comment'", ' ', $fields_list);

        $userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name',], 'Users');
        $query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
                   FROM " . $this->entity_table . "
				    INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid =vtiger_crmentity.crmid
				    LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_troubletickets.parent_id
				    LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_troubletickets.contact_id
				    LEFT JOIN vtiger_ticketcf ON vtiger_ticketcf.ticketid=vtiger_troubletickets.ticketid
				    LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.assigned_user_id
				    LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.assigned_user_id and vtiger_users.status='Active'
				    LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_troubletickets.product_id";
        //end
        $query .= getNonAdminAccessControlQuery('HelpDesk', $current_user);
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

        $rel_table_arr = ['Attachments' => 'vtiger_seattachmentsrel', 'Documents' => 'vtiger_senotesrel'];

        $tbl_field_arr = ['vtiger_seattachmentsrel' => 'attachmentsid', 'vtiger_senotesrel' => 'notesid'];

        $entity_tbl_field_arr = ['vtiger_seattachmentsrel' => "crmid", 'vtiger_senotesrel' => 'crmid'];

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
        $matrix->setDependency("vtiger_crmentityHelpDesk", ["vtiger_groupsHelpDesk", "vtiger_usersHelpDesk", "vtiger_lastModifiedByHelpDesk"]);
        $matrix->setDependency("vtiger_crmentityRelHelpDesk", ["vtiger_accountRelHelpDesk", "vtiger_contactdetailsRelHelpDesk"]);

        if (!$queryPlanner->requireTable('vtiger_troubletickets', $matrix)) {
            return '';
        }

        $matrix->setDependency("vtiger_troubletickets", ["vtiger_crmentityHelpDesk", "vtiger_ticketcf", "vtiger_crmentityRelHelpDesk", "vtiger_productsRel"]);

        // TODO Support query planner
        $query = $this->getRelationQuery($module, $secmodule, "vtiger_troubletickets", "ticketid", $queryPlanner);

        if ($queryPlanner->requireTable("vtiger_crmentityHelpDesk", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityHelpDesk on vtiger_crmentityHelpDesk.crmid=vtiger_troubletickets.ticketid and vtiger_crmentityHelpDesk.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_ticketcf")) {
            $query .= " left join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid";
        }
        if ($queryPlanner->requireTable("vtiger_crmentityRelHelpDesk", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityRelHelpDesk on vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id";
        }
        if ($queryPlanner->requireTable("vtiger_accountRelHelpDesk")) {
            $query .= " left join vtiger_account as vtiger_accountRelHelpDesk on vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid";
        }
        if ($queryPlanner->requireTable("vtiger_contactdetailsRelHelpDesk")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk on vtiger_contactdetailsRelHelpDesk.contactid= vtiger_troubletickets.contact_id";
        }
        if ($queryPlanner->requireTable("vtiger_productsRel")) {
            $query .= " left join vtiger_products as vtiger_productsRel on vtiger_productsRel.productid = vtiger_troubletickets.product_id";
        }
        if ($queryPlanner->requireTable("vtiger_groupsHelpDesk")) {
            $query .= " left join vtiger_groups as vtiger_groupsHelpDesk on vtiger_groupsHelpDesk.groupid = vtiger_crmentityHelpDesk.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_usersHelpDesk")) {
            $query .= " left join vtiger_users as vtiger_usersHelpDesk on vtiger_usersHelpDesk.id = vtiger_crmentityHelpDesk.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByHelpDesk")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByHelpDesk on vtiger_lastModifiedByHelpDesk.id = vtiger_crmentityHelpDesk.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyHelpDesk")) {
            $query .= " left join vtiger_users as vtiger_createdbyHelpDesk on vtiger_createdbyHelpDesk.id = vtiger_crmentityHelpDesk.creator_user_id ";
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
            "Documents" => ["vtiger_senotesrel" => ["crmid", "notesid"], "vtiger_troubletickets" => "ticketid"],
            "Products"  => ["vtiger_troubletickets" => ["ticketid", "product_id"]],
            "Services"  => ["vtiger_crmentityrel" => ["crmid", "relcrmid"], "vtiger_troubletickets" => "ticketid"],
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

        if ($return_module == 'Accounts') {
            $sql = 'UPDATE vtiger_troubletickets SET parent_id=? WHERE ticketid=?';
            $this->db->pquery($sql, [null, $id]);
            $se_sql = 'DELETE FROM vtiger_seticketsrel WHERE ticketid=?';
            $this->db->pquery($se_sql, [$id]);
        } elseif ($return_module == 'Contacts') {
            $sql = 'UPDATE vtiger_troubletickets SET contact_id=? WHERE ticketid=?';
            $this->db->pquery($sql, [null, $id]);
            $se_sql = 'DELETE FROM vtiger_seticketsrel WHERE ticketid=?';
            $this->db->pquery($se_sql, [$id]);
        } elseif ($return_module == 'Products') {
            $sql = 'UPDATE vtiger_troubletickets SET product_id=? WHERE ticketid=?';
            $this->db->pquery($sql, [null, $id]);
        } elseif ($return_module == 'Documents') {
            $sql = 'DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } else {
            parent::unlinkRelationship($id, $return_module, $return_id);
        }
    }

    public static function getTicketEmailContents($entityData, $toOwner = false)
    {
        global $HELPDESK_SUPPORT_NAME;
        $adb = PearDatabase::getInstance();
        $moduleName = $entityData->getModuleName();
        $wsId = $entityData->getId();

        if (strpos($wsId, 'x')) {
            $parts = explode('x', $wsId);
            $entityId = $parts[1];
        } else {
            $entityId = $wsId;
        }

        $isNew = $entityData->isNew();

        if (!$isNew) {
            $reply = getTranslatedString("replied", $moduleName);
            $temp = getTranslatedString("Re", $moduleName);
        } else {
            $reply = getTranslatedString("created", $moduleName);
            $temp = " ";
        }

        $wsParentId = $entityData->get('contact_id');
        $parentIdParts = explode('x', $wsParentId);

        // If this function is being triggered as part of Eventing API
        // Then the reference field ID will not matching the webservice format.
        // Regardless of the entry we need just the ID
        $parentId = array_pop($parentIdParts);

        $desc = getTranslatedString('Ticket ID', $moduleName) . ' : ' . $entityId . '<br>'
            . getTranslatedString('Ticket Title', $moduleName) . ' : ' . $temp . ' '
            . $entityData->get('ticket_title');
        $name = (!$toOwner) ? Vtiger_Functions::getCRMRecordLabel($parentId) : '';
        $desc .= "<br><br>" . getTranslatedString('Hi', $moduleName) . " " . $name . ",<br><br>"
            . getTranslatedString('LBL_PORTAL_BODY_MAILINFO', $moduleName) . " " . $reply . " " . getTranslatedString('LBL_DETAIL', $moduleName) . "<br>";
        $desc .= "<br>" . getTranslatedString('Ticket No', $moduleName) . " : " . $entityData->get('ticket_no');
        $desc .= "<br>" . getTranslatedString('Status', $moduleName) . " : " . $entityData->get('ticketstatus');
        $desc .= "<br>" . getTranslatedString('Category', $moduleName) . " : " . $entityData->get('ticketcategories');
        $desc .= "<br>" . getTranslatedString('Severity', $moduleName) . " : " . $entityData->get('ticketseverities');
        $desc .= "<br>" . getTranslatedString('Priority', $moduleName) . " : " . $entityData->get('ticketpriorities');
        $desc .= "<br><br>" . getTranslatedString('Description', $moduleName) . " : <br>" . $entityData->get('description');
        $desc .= "<br><br>" . getTranslatedString('Solution', $moduleName) . " : <br>" . $entityData->get('solution');
        $desc .= getTicketComments($entityId);

        $sql = "SELECT * FROM vtiger_ticketcf WHERE ticketid = ?";
        $result = $adb->pquery($sql, [$entityId]);
        $cffields = $adb->getFieldsArray($result);
        foreach ($cffields as $cfOneField) {
            if ($cfOneField != 'ticketid' && $cfOneField != 'from_portal') {
                $cfData = $adb->query_result($result, 0, $cfOneField);
                $sql = "SELECT fieldlabel FROM vtiger_field WHERE columnname = ? and vtiger_field.presence in (0,2)";
                $cfLabel = $adb->query_result($adb->pquery($sql, [$cfOneField]), 0, 'fieldlabel');
                $desc .= '<br>' . $cfLabel . ' : ' . $cfData;
            }
        }
        $desc .= '<br><br>' . getTranslatedString("LBL_REGARDS", $moduleName) . ',<br>' . $HELPDESK_SUPPORT_NAME;

        return $desc;
    }

    public static function getPortalTicketEmailContents($entityData)
    {
        require_once 'config.inc.php';
        global $PORTAL_URL, $HELPDESK_SUPPORT_NAME;

        $moduleName = $entityData->getModuleName();
        $wsId = $entityData->getId();

        if (strpos($wsId, 'x')) {
            $parts = explode('x', $wsId);
            $entityId = $parts[1];
        } else {
            $entityId = $wsId;
        }
        $wsParentId = $entityData->get('contact_id');
        $parentIdParts = explode('x', $wsParentId);

        // If this function is being triggered as part of Eventing API
        // Then the reference field ID will not matching the webservice format.
        // Regardless of the entry we need just the ID
        $parentId = array_pop($parentIdParts);

        $portalUrl = "<a href='" . $PORTAL_URL . "/index.php?module=HelpDesk&action=index&ticketid=" . $entityId . "&fun=detail'>"
            . getTranslatedString('LBL_TICKET_DETAILS', $moduleName) . "</a>";
        $contents = getTranslatedString('Dear', $moduleName) . ' ';
        $contents .= ($parentId) ? Vtiger_Functions::getCRMRecordLabel($parentId) : '';
        $contents .= ",<br>";
        $contents .= getTranslatedString('reply', $moduleName) . ' <b>' . $entityData->get('ticket_title')
            . '</b> ' . getTranslatedString('customer_portal', $moduleName);
        $contents .= getTranslatedString("link", $moduleName) . '<br>';
        $contents .= $portalUrl;
        $contents .= '<br><br>' . getTranslatedString("Thanks", $moduleName) . '<br>' . $HELPDESK_SUPPORT_NAME;

        return $contents;
    }

    function clearSingletonSaveFields()
    {
        $this->column_fields['comments'] = '';
    }
}
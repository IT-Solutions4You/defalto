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
 * Portions created by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 */
class Campaigns extends CRMEntity
{
    public string $moduleName = 'Campaigns';
    public string $parentName = 'MARKETING';
    public $table_name = "vtiger_campaign";
    public $table_index = 'campaignid';

    public $tab_name = ['vtiger_crmentity', 'vtiger_campaign', 'vtiger_campaignscf'];
    public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_campaign' => 'campaignid', 'vtiger_campaignscf' => 'campaignid'];
    /**
     * Mandatory table for supporting custom fields.
     */
    public $customFieldTable = ['vtiger_campaignscf', 'campaignid'];

    public $sortby_fields = [
        'campaignname',
        'assigned_user_id',
        'campaigntype',
        'productname',
        'expectedrevenue',
        'closingdate',
        'campaignstatus',
        'expectedresponse',
        'targetaudience',
        'expectedcost'
    ];

    public $list_fields = [
        'Campaign Name' => ['campaign' => 'campaignname'],
        'Campaign Type' => ['campaign' => 'campaigntype'],
        'Campaign Status' => ['campaign' => 'campaignstatus'],
        'Expected Revenue' => ['campaign' => 'expectedrevenue'],
        'Expected Close Date' => ['campaign' => 'closingdate'],
        'Assigned To' => ['crmentity' => 'assigned_user_id'],
    ];

    public $list_fields_name = [
        'Campaign Name' => 'campaignname',
        'Campaign Type' => 'campaigntype',
        'Campaign Status' => 'campaignstatus',
        'Expected Revenue' => 'expectedrevenue',
        'Expected Close Date' => 'closingdate',
        'Assigned To' => 'assigned_user_id',
    ];

    public $list_link_field = 'campaignname';
    //Added these variables which are used as default order by and sortorder in ListView
    public $default_order_by = 'crmid';
    public $default_sort_order = 'DESC';

    //public $groupTable = Array('vtiger_campaigngrouprelation','campaignid');

    public $search_fields = [
        'Campaign Name' => ['vtiger_campaign' => 'campaignname'],
        'Campaign Type' => ['vtiger_campaign' => 'campaigntype'],
    ];

    public $search_fields_name = [
        'Campaign Name' => 'campaignname',
        'Campaign Type' => 'campaigntype',
    ];
    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    public $mandatory_fields = ['campaignname', 'createdtime', 'modifiedtime', 'assigned_user_id'];

    // For Alphabetical search
    public $def_basicsearch_col = 'campaignname';

    public $campaignrelstatus;

    /*
     * Function populate the status columns' HTML
     * @param - $related_list return value from GetRelatedList
     * @param - $status_column index of the status column in the list.
     * returns true on success
     */
    function add_status_popup($related_list, $status_column = 7, $related_module = null)
    {
        global $adb;

        if (!isset($this->campaignrelstatus)) {
            $result = $adb->pquery('SELECT * FROM vtiger_campaignrelstatus;', []);
            while ($row = $adb->fetchByAssoc($result)) {
                $this->campaignrelstatus[$row['campaignrelstatus']] = $row;
            }
        }
        foreach ($related_list['entries'] as $key => &$entry) {
            $popupitemshtml = '';
            foreach ($this->campaignrelstatus as $campaingrelstatus) {
                $camprelstatus = getTranslatedString($campaingrelstatus["campaignrelstatus"], 'Campaigns');
                $popupitemshtml .= "<a onmouseover=\"javascript: showBlock('campaignstatus_popup_$key')\" href=\"javascript:updateCampaignRelationStatus('$related_module', '" . $this->id . "', '$key', '$campaingrelstatus[campaignrelstatusid]', '" . addslashes(
                        $camprelstatus
                    ) . "');\">$camprelstatus</a><br />";
            }
            $popuphtml = '<div onmouseover="javascript:clearTimeout(statusPopupTimer);" onmouseout="javascript:closeStatusPopup(\'campaignstatus_popup_' . $key . '\');" style="margin-top: -14px; width: 200px;" id="campaignstatus_popup_' . $key . '" class="calAction"><div style="background-color: #FFFFFF; padding: 8px;">' . $popupitemshtml . '</div></div>';

            $entry[$status_column] = "<a href=\"javascript: showBlock('campaignstatus_popup_$key');\">[+]</a> <span id='campaignstatus_$key'>" . $entry[$status_column] . "</span>" . $popuphtml;
        }

        return $related_list;
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
        $matrix->setDependency('vtiger_crmentityCampaigns', ['vtiger_groupsCampaigns', 'vtiger_usersCampaignss', 'vtiger_lastModifiedByCampaigns', 'vtiger_campaignscf']);

        if (!$queryPlanner->requireTable("vtiger_campaign", $matrix)) {
            return '';
        }

        $matrix->setDependency('vtiger_campaign', ['vtiger_crmentityCampaigns', 'vtiger_productsCampaigns']);

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_campaign", "campaignid", $queryPlanner);

        if ($queryPlanner->requireTable("vtiger_crmentityCampaigns", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityCampaigns on vtiger_crmentityCampaigns.crmid=vtiger_campaign.campaignid and vtiger_crmentityCampaigns.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_productsCampaigns")) {
            $query .= " 	left join vtiger_products as vtiger_productsCampaigns on vtiger_campaign.product_id = vtiger_productsCampaigns.productid";
        }
        if ($queryPlanner->requireTable("vtiger_campaignscf")) {
            $query .= " 	left join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_crmentityCampaigns.crmid";
        }
        if ($queryPlanner->requireTable("vtiger_groupsCampaigns")) {
            $query .= " left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentityCampaigns.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_usersCampaigns")) {
            $query .= " left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentityCampaigns.assigned_user_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByCampaigns")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByCampaigns on vtiger_lastModifiedByCampaigns.id = vtiger_crmentityCampaigns.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyCampaigns")) {
            $query .= " left join vtiger_users as vtiger_createdbyCampaigns on vtiger_createdbyCampaigns.id = vtiger_crmentityCampaigns.creator_user_id ";
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
            "Potentials" => ["vtiger_potential" => ["campaignid", "potentialid"], "vtiger_campaign" => "campaignid"],
            "Products" => ["vtiger_campaign" => ["campaignid", "product_id"]],
        ];

        return $rel_tables[$secmodule];
    }

    public function unlinkRelationship($id, $return_module, $return_id)
    {
        if (empty($return_module) || empty($return_id)) {
            return;
        }

        if ($this->moduleName == 'Campaigns') {
            Campaigns_Relation_Model::deleteCampaignRelation($id, $this->moduleName, $return_id, $return_module);
        }

        parent::unlinkRelationship($id, $return_module, $return_id);
    }

    public function save_related_module($module, $crmid, $with_module, $with_crmids, $otherParams = [])
    {
        if (!is_array($with_crmids)) {
            $with_crmids = [$with_crmids];
        }

        foreach ($with_crmids as $with_crmid) {
            if ($module == 'Campaigns') {
                Campaigns_Relation_Model::saveCampaignRelation($crmid, $module, $with_crmid, $with_module);
            }

            parent::save_related_module($module, $crmid, $with_module, $with_crmid);
        }
    }
}
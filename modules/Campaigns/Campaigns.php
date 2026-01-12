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
        'Campaign Name'       => ['campaign' => 'campaignname'],
        'Campaign Type'       => ['campaign' => 'campaigntype'],
        'Campaign Status'     => ['campaign' => 'campaignstatus'],
        'Expected Revenue'    => ['campaign' => 'expectedrevenue'],
        'Expected Close Date' => ['campaign' => 'closingdate'],
        'Assigned To'         => ['crmentity' => 'assigned_user_id'],
    ];

    public $list_fields_name = [
        'Campaign Name'       => 'campaignname',
        'Campaign Type'       => 'campaigntype',
        'Campaign Status'     => 'campaignstatus',
        'Expected Revenue'    => 'expectedrevenue',
        'Expected Close Date' => 'closingdate',
        'Assigned To'         => 'assigned_user_id',
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

    /**
     * Function to get Campaign related Accouts
     *
     * @param integer $id - campaignid
     *                    returns related Accounts record in array format
     */
    function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule;
        $log->debug("Entering get_accounts(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();

        $is_CampaignStatusAllowed = false;
        global $current_user;
        if (getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus') == '0') {
            $other->list_fields['Status'] = ['vtiger_campaignrelstatus' => 'campaignrelstatus'];
            $other->list_fields_name['Status'] = 'campaignrelstatus';
            $other->sortby_fields[] = 'campaignrelstatus';
            $is_CampaignStatusAllowed = (getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus', 'readwrite') == '0') ? true : false;
        }

        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        // Send mail button for selected Accounts
        $button .= "<input title='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' class='crmbutton small edit' value='" . getTranslatedString(
                'LBL_SEND_MAIL_BUTTON'
            ) . "' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
        $button .= '&nbsp;&nbsp;&nbsp;&nbsp';
        /* To get Accounts CustomView -START */
        require_once('modules/CustomView/CustomView.php');
        $ahtml = "<select id='" . $related_module . "_cv_list' class='small'><option value='None'>-- " . getTranslatedString('Select One') . " --</option>";
        $oCustomView = new CustomView($related_module);
        $viewid = $oCustomView->getViewId($related_module);
        $customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
        $ahtml .= $customviewcombo_html;
        $ahtml .= "</select>";
        /* To get Accounts CustomView -END */

        $button .= $ahtml . "<input title='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' class='crmbutton small edit' value='" . getTranslatedString(
                'LBL_LOAD_LIST',
                $this_module
            ) . "' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
        $button .= '&nbsp;&nbsp;&nbsp;&nbsp';

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
                $button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
                    "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_account.*,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.*, vtiger_crmentity.modifiedtime, vtiger_campaignrelstatus.*, vtiger_accountbillads.*
				FROM vtiger_account
				INNER JOIN vtiger_campaignaccountrel ON vtiger_campaignaccountrel.accountid = vtiger_account.accountid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
				INNER JOIN vtiger_accountshipads ON vtiger_accountshipads.accountaddressid = vtiger_account.accountid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id=vtiger_users.id
				LEFT JOIN vtiger_accountbillads ON vtiger_accountbillads.accountaddressid = vtiger_account.accountid
				LEFT JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignaccountrel.campaignrelstatusid
				WHERE vtiger_campaignaccountrel.campaignid = " . $id . " AND vtiger_crmentity.deleted=0";

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        } elseif ($is_CampaignStatusAllowed && isset($return_value['header']) && is_array($return_value['header'])) {
            $statusPos = php7_count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
            $return_value = $this->add_status_popup($return_value, $statusPos, 'Accounts');
        }

        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_accounts method ...");

        return $return_value;
    }

    /**
     * Function to get Campaign related Contacts
     *
     * @param integer $id - campaignid
     *                    returns related Contacts record in array format
     */
    function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule;
        $log->debug("Entering get_contacts(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();

        $is_CampaignStatusAllowed = false;
        global $current_user;
        if (getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus') == '0') {
            $other->list_fields['Status'] = ['vtiger_campaignrelstatus' => 'campaignrelstatus'];
            $other->list_fields_name['Status'] = 'campaignrelstatus';
            $other->sortby_fields[] = 'campaignrelstatus';
            $is_CampaignStatusAllowed = (getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus', 'readwrite') == '0') ? true : false;
        }

        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        // Send mail button for selected Leads
        $button .= "<input title='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' class='crmbutton small edit' value='" . getTranslatedString(
                'LBL_SEND_MAIL_BUTTON'
            ) . "' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
        $button .= '&nbsp;&nbsp;&nbsp;&nbsp';

        /* To get Leads CustomView -START */
        require_once('modules/CustomView/CustomView.php');
        $lhtml = "<select id='" . $related_module . "_cv_list' class='small'><option value='None'>-- " . getTranslatedString('Select One') . " --</option>";
        $oCustomView = new CustomView($related_module);
        $viewid = $oCustomView->getViewId($related_module);
        $customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
        $lhtml .= $customviewcombo_html;
        $lhtml .= "</select>";
        /* To get Leads CustomView -END */

        $button .= $lhtml . "<input title='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' class='crmbutton small edit' value='" . getTranslatedString(
                'LBL_LOAD_LIST',
                $this_module
            ) . "' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
        $button .= '&nbsp;&nbsp;&nbsp;&nbsp';

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
                $button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
                    "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_contactdetails.account_id, vtiger_account.accountname,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name ,
				vtiger_contactdetails.contactid, vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.title,
				vtiger_contactdetails.department, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid,
				vtiger_crmentity.assigned_user_id, vtiger_crmentity.modifiedtime, vtiger_campaignrelstatus.*
				FROM vtiger_contactdetails
				INNER JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid
				INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
				INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
				INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id=vtiger_users.id
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.account_id
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaigncontrel.campaignrelstatusid
				WHERE vtiger_campaigncontrel.campaignid = " . $id . " AND vtiger_crmentity.deleted=0";

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        } elseif ($is_CampaignStatusAllowed) {
            $statusPos = isset($return_value['header']) ? php7_count(
                    $return_value['header']
                ) - 2 : ''; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
            $return_value = $this->add_status_popup($return_value, $statusPos, 'Contacts');
        }

        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_contacts method ...");

        return $return_value;
    }

    /**
     * Function to get Campaign related Leads
     *
     * @param integer $id - campaignid
     *                    returns related Leads record in array format
     */
    function get_leads($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule;
        $log->debug("Entering get_leads(" . $id . ") method ...");
        $this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
        require_once("modules/$related_module/$related_module.php");
        $other = new $related_module();

        $is_CampaignStatusAllowed = false;
        global $current_user;
        if (getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus') == '0') {
            $other->list_fields['Status'] = ['vtiger_campaignrelstatus' => 'campaignrelstatus'];
            $other->list_fields_name['Status'] = 'campaignrelstatus';
            $other->sortby_fields[] = 'campaignrelstatus';
            $is_CampaignStatusAllowed = (getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus', 'readwrite') == '0') ? true : false;
        }

        vtlib_setup_modulevars($related_module, $other);
        $singular_modname = vtlib_toSingular($related_module);

        $parenttab = getParentTab();

        if ($singlepane_view == 'true') {
            $returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
        } else {
            $returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;
        }

        $button = '';

        // Send mail button for selected Leads
        $button .= "<input title='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' class='crmbutton small edit' value='" . getTranslatedString(
                'LBL_SEND_MAIL_BUTTON'
            ) . "' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
        $button .= '&nbsp;&nbsp;&nbsp;&nbsp';

        /* To get Leads CustomView -START */
        require_once('modules/CustomView/CustomView.php');
        $lhtml = "<select id='" . $related_module . "_cv_list' class='small'><option value='None'>-- " . getTranslatedString('Select One') . " --</option>";
        $oCustomView = new CustomView($related_module);
        $viewid = $oCustomView->getViewId($related_module);
        $customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
        $lhtml .= $customviewcombo_html;
        $lhtml .= "</select>";
        /* To get Leads CustomView -END */

        $button .= $lhtml . "<input title='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' class='crmbutton small edit' value='" . getTranslatedString(
                'LBL_LOAD_LIST',
                $this_module
            ) . "' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
        $button .= '&nbsp;&nbsp;&nbsp;&nbsp';

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
                $button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
                    "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
                    " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
                    " value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
            }
        }

        $userNameSql = getSqlForNameInDisplayFormat([
            'first_name' =>
                'vtiger_users.first_name',
            'last_name'  => 'vtiger_users.last_name'
        ], 'Users');
        $query = "SELECT vtiger_leaddetails.*, vtiger_crmentity.crmid,vtiger_leadaddress.phone,vtiger_leadsubdetails.website,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.assigned_user_id, vtiger_campaignrelstatus.*
				FROM vtiger_leaddetails
				INNER JOIN vtiger_campaignleadrel ON vtiger_campaignleadrel.leadid=vtiger_leaddetails.leadid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
				INNER JOIN vtiger_leadsubdetails  ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
				INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leadsubdetails.leadsubscriptionid
				INNER JOIN vtiger_leadscf ON vtiger_leaddetails.leadid = vtiger_leadscf.leadid
				LEFT JOIN vtiger_users ON vtiger_crmentity.assigned_user_id = vtiger_users.id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.assigned_user_id
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignleadrel.campaignrelstatusid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_leaddetails.converted=0 AND vtiger_campaignleadrel.campaignid = " . $id;

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        } elseif ($is_CampaignStatusAllowed) {
            $statusPos = isset($return_value['header']) ? php7_count(
                    $return_value['header']
                ) - 2 : ''; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
            $return_value = $this->add_status_popup($return_value, $statusPos, 'Leads');
        }

        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_leads method ...");

        return $return_value;
    }

    /**
     * Function to get Campaign related Potentials
     *
     * @param integer $id - campaignid
     *                    returns related potentials record in array format
     */
    function get_opportunities($id, $cur_tab_id, $rel_tab_id, $actions = false)
    {
        global $log, $singlepane_view, $currentModule, $current_user;
        $log->debug("Entering get_opportunities(" . $id . ") method ...");
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

        if ($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'campaignid', 'readwrite') == '0') {
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
        $query = "SELECT CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
					vtiger_potential.related_to, vtiger_potential.contact_id, vtiger_account.accountname, vtiger_potential.potentialid, vtiger_potential.potentialname,
					vtiger_potential.opportunity_type, vtiger_potential.sales_stage, vtiger_potential.amount, vtiger_potential.closingdate,
					vtiger_crmentity.crmid, vtiger_crmentity.assigned_user_id FROM vtiger_campaign
					INNER JOIN vtiger_potential ON vtiger_campaign.campaignid = vtiger_potential.campaignid
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid
					INNER JOIN vtiger_potentialscf ON vtiger_potential.potentialid = vtiger_potentialscf.potentialid
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.assigned_user_id
					LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.assigned_user_id
					LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_potential.related_to
					LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_potential.contact_id
					WHERE vtiger_campaign.campaignid = " . $id . " AND vtiger_crmentity.deleted=0";

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

        if ($return_value == null) {
            $return_value = [];
        }
        $return_value['CUSTOM_BUTTON'] = $button;

        $log->debug("Exiting get_opportunities method ...");

        return $return_value;
    }

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
            "Contacts"   => ["vtiger_campaigncontrel" => ["campaignid", "contactid"], "vtiger_campaign" => "campaignid"],
            "Leads"      => ["vtiger_campaignleadrel" => ["campaignid", "leadid"], "vtiger_campaign" => "campaignid"],
            "Accounts"   => ["vtiger_campaignaccountrel" => ["campaignid", "accountid"], "vtiger_campaign" => "campaignid"],
            "Potentials" => ["vtiger_potential" => ["campaignid", "potentialid"], "vtiger_campaign" => "campaignid"],
            "Products"   => ["vtiger_campaign" => ["campaignid", "product_id"]],
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

        if ($return_module == 'Leads') {
            $sql = 'DELETE FROM vtiger_campaignleadrel WHERE campaignid=? AND leadid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } elseif ($return_module == 'Contacts') {
            $sql = 'DELETE FROM vtiger_campaigncontrel WHERE campaignid=? AND contactid=?';
            $this->db->pquery($sql, [$id, $return_id]);
        } elseif ($return_module == 'Accounts') {
            $sql = 'DELETE FROM vtiger_campaignaccountrel WHERE campaignid=? AND accountid=?';
            $this->db->pquery($sql, [$id, $return_id]);
            $sql = 'DELETE FROM vtiger_campaigncontrel WHERE campaignid=? AND contactid IN (SELECT contactid FROM vtiger_contactdetails WHERE account_id=?)';
            $this->db->pquery($sql, [$id, $return_id]);
        } else {
            parent::unlinkRelationship($id, $return_module, $return_id);
        }
    }

    public function save_related_module($module, $crmid, $with_module, $with_crmids, $otherParams = [])
    {
        $adb = PearDatabase::getInstance();

        if (!is_array($with_crmids)) {
            $with_crmids = [$with_crmids];
        }

        foreach ($with_crmids as $with_crmid) {
            if ($with_module == 'Leads') {
                $checkResult = $adb->pquery(
                    'SELECT 1 FROM vtiger_campaignleadrel WHERE campaignid = ? AND leadid = ?',
                    [$crmid, $with_crmid]
                );

                if ($checkResult && $adb->num_rows($checkResult)) {
                    continue;
                }

                $adb->pquery('INSERT INTO vtiger_campaignleadrel VALUES(?,?,1)', [$crmid, $with_crmid]);
                $this->setTrackLinkedInfo($crmid, $with_crmid);
            } elseif ($with_module == 'Contacts') {
                $checkResult = $adb->pquery(
                    'SELECT 1 FROM vtiger_campaigncontrel WHERE campaignid = ? AND contactid = ?',
                    [$crmid, $with_crmid]
                );

                if ($checkResult && $adb->num_rows($checkResult)) {
                    continue;
                }

                $adb->pquery('INSERT INTO vtiger_campaigncontrel VALUES(?,?,1)', [$crmid, $with_crmid]);
                $this->setTrackLinkedInfo($crmid, $with_crmid);
            } elseif ($with_module == 'Accounts') {
                $checkResult = $adb->pquery(
                    'SELECT 1 FROM vtiger_campaignaccountrel WHERE campaignid = ? AND accountid = ?',
                    [$crmid, $with_crmid]
                );

                if ($checkResult && $adb->num_rows($checkResult)) {
                    continue;
                }

                $adb->pquery('INSERT INTO vtiger_campaignaccountrel VALUES(?,?,1)', [$crmid, $with_crmid]);
                $this->setTrackLinkedInfo($crmid, $with_crmid);
            } else {
                parent::save_related_module($module, $crmid, $with_module, $with_crmid);
            }
        }
    }
}
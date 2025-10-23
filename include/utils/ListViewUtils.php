<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

/*********************************************************************************
 * $Header: /cvsroot/vtigercrm/vtiger_crm/include/utils/ListViewUtils.php,v 1.32 2006/02/03 06:53:08 mangai Exp $
 * Description:  Includes generic helper functions used throughout the application.
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

require_once('include/database/PearDatabase.php');
require_once('include/ComboUtil.php'); //new
require_once('include/utils/CommonUtils.php'); //new
require_once('include/utils/UserInfoUtil.php');
require_once('include/Zend/Json.php');

/** Function to get the list query for a module
 *
 * @param $module -- module name:: Type string
 * @param $where  -- where:: Type string
 * @returns $query -- query:: Type query
 */
function getListQuery($module, $where = '')
{
    global $log;

    $log->debug("Entering getListQuery(" . $module . "," . $where . ") method ...");

    $focus = CRMEntity::getInstance($module);
    $query = $focus->getListQuery($module, $where);

    if ($module != 'Users') {
        $query = listQueryNonAdminChange($query, $module);
    }

    $log->debug("Exiting getListQuery method ...");

    return $query;
}

/* * This function stores the variables in session sent in list view url string.
 * Param $lv_array - list view session array
 * Param $noofrows - no of rows
 * Param $max_ent - maximum entires
 * Param $module - module name
 * Param $related - related module
 * Return type void.
 */

function setSessionVar($lv_array, $noofrows, $max_ent, $module = '', $related = '')
{
    global $currentModule;

    $start = '';
    if ($noofrows >= 1) {
        $lv_array['start'] = 1;
        $start = 1;
    } elseif ($related != '' && $noofrows == 0) {
        $lv_array['start'] = 1;
        $start = 1;
    } else {
        $lv_array['start'] = 0;
        $start = 0;
    }

    if (isset($_REQUEST['start']) && $_REQUEST['start'] != '') {
        $lv_array['start'] = ListViewSession::getRequestStartPage();
        $start = ListViewSession::getRequestStartPage();
    } elseif ($_SESSION['rlvs'][$module][$related]['start'] != '') {
        if ($related != '') {
            $lv_array['start'] = $_SESSION['rlvs'][$module][$related]['start'];
            $start = $_SESSION['rlvs'][$module][$related]['start'];
        }
    }
    if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '') {
        $lv_array['viewname'] = vtlib_purify($_REQUEST['viewname']);
    }

    if ($related == '') {
        $_SESSION['lvs'][$_REQUEST['module']] = $lv_array;
    } else {
        $_SESSION['rlvs'][$module][$related] = $lv_array;
    }

    if ($start < ceil($noofrows / $max_ent) && $start != '') {
        $start = ceil($noofrows / $max_ent);
        if ($related == '') {
            $_SESSION['lvs'][$currentModule]['start'] = $start;
        }
    }
}

/* * Function to get the table headers for related listview
 * Param $navigation_arrray - navigation values in array
 * Param $url_qry - url string
 * Param $module - module name
 * Param $action- action file name
 * Param $viewid - view id
 * Returns an string value
 */

function getRelatedTableHeaderNavigation($navigation_array, $url_qry, $module, $related_module, $recordid)
{
    global $log, $app_strings, $adb;
    $log->debug("Entering getTableHeaderNavigation(" . $navigation_array . "," . $url_qry . "," . $module . "," . $action_val . "," . $viewid . ") method ...");
    global $theme;
    $relatedTabId = getTabid($related_module);
    $tabid = getTabid($module);

    $relatedListResult = $adb->pquery(
        'SELECT * FROM vtiger_relatedlists WHERE tabid=? AND
		related_tabid=?',
        [$tabid, $relatedTabId]
    );
    if (empty($relatedListResult)) {
        return;
    }
    $relatedListRow = $adb->fetch_row($relatedListResult);
    $header = $relatedListRow['label'];
    $actions = $relatedListRow['actions'];
    $functionName = $relatedListRow['name'];

    $urldata = "module=$module&action={$module}Ajax&file=DetailViewAjax&record={$recordid}&" .
        "ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$relatedListRow['relation_id']}" .
        "&actions={$actions}&{$url_qry}";

    $formattedHeader = str_replace(' ', '', $header);
    $target = 'tbl_' . $module . '_' . $formattedHeader;
    $imagesuffix = $module . '_' . $formattedHeader;

    $output = '<td align="right" style="padding="5px;">';
    if (($navigation_array['prev']) != 0) {
        $output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=1\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl(
                'start.gif',
                $theme
            ) . '" border="0" align="absmiddle"></a>&nbsp;';
        $output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['prev'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . vtiger_imageurl(
                'previous.gif',
                $theme
            ) . '" border="0" align="absmiddle"></a>&nbsp;';
    } else {
        $output .= '<img src="' . vtiger_imageurl('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
        $output .= '<img src="' . vtiger_imageurl('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
    }

    $jsHandler = "return VT_disableFormSubmit(event);";
    $output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
		style='width: 3em;margin-right: 0.7em;' onchange=\"loadRelatedListBlock('{$urldata}&start='+this.value+'','{$target}','{$imagesuffix}');\"
		onkeypress=\"$jsHandler\">";
    $output .= "<span name='listViewCountContainerName' class='small' style='white-space: nowrap;'>";
    $computeCount = $_REQUEST['withCount'];
    if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true
        || ((boolean)$computeCount) == true) {
        $output .= $app_strings['LBL_LIST_OF'] . ' ' . $navigation_array['verylast'];
    } else {
        $output .= "<img src='" . vtiger_imageurl('windowRefresh.gif', $theme) . "' alt='" . $app_strings['LBL_HOME_COUNT'] . "'
			onclick=\"loadRelatedListBlock('{$urldata}&withCount=true&start={$navigation_array['current']}','{$target}','{$imagesuffix}');\"
			align='absmiddle' name='" . $module . "_listViewCountRefreshIcon'/>
			<img name='" . $module . "_listViewCountContainerBusy' src='" . vtiger_imageurl('vtbusy.gif', $theme) . "' style='display: none;'
			align='absmiddle' alt='" . $app_strings['LBL_LOADING'] . "'>";
    }
    $output .= '</span>';

    if (($navigation_array['next']) != 0) {
        $output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['next'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . vtiger_imageurl(
                'next.gif',
                $theme
            ) . '" border="0" align="absmiddle"></a>&nbsp;';
        $output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['verylast'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . vtiger_imageurl(
                'end.gif',
                $theme
            ) . '" border="0" align="absmiddle"></a>&nbsp;';
    } else {
        $output .= '<img src="' . vtiger_imageurl('next_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
        $output .= '<img src="' . vtiger_imageurl('end_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
    }
    $output .= '</td>';
    $log->debug("Exiting getTableHeaderNavigation method ...");
    if ($navigation_array['first'] == '') {
        return;
    } else {
        return $output;
    }
}

/* Function to get the Entity Id of a given Entity Name */

function getEntityId($module, $entityName)
{
    global $log, $adb;
    $log->info("in getEntityId " . $entityName);

    $query = "select fieldname,tablename,entityidfield from vtiger_entityname where modulename = ?";
    $result = $adb->pquery($query, [$module]);
    $fieldsname = $adb->query_result($result, 0, 'fieldname');
    $tablename = $adb->query_result($result, 0, 'tablename');
    $entityidfield = $adb->query_result($result, 0, 'entityidfield');
    if (!(strpos($fieldsname, ',') === false)) {
        $fieldlists = explode(',', $fieldsname);
        $fieldsname = "trim(concat(";
        $fieldsname = $fieldsname . implode(",' ',", $fieldlists);
        $fieldsname = $fieldsname . "))";
        $entityName = trim($entityName);
    }

    if ($entityName != '') {
        $sql = "select $entityidfield from $tablename INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $tablename.$entityidfield " .
            " WHERE vtiger_crmentity.deleted = 0 and $fieldsname=?";
        $result = $adb->pquery($sql, [$entityName]);
        if ($adb->num_rows($result) > 0) {
            $entityId = $adb->query_result($result, 0, $entityidfield);
        }
    }
    if (!empty($entityId)) {
        return $entityId;
    } else {
        return 0;
    }
}

function decode_emptyspace_html($str)
{
    $str = str_replace("&nbsp;", "*#chr*#", $str); // (*#chr*#) used as jargan to replace it back with &nbsp;
    $str = str_replace("\xc2", "*#chr*#", $str); // Ã (for special chrtr)
    $str = decode_html($str);

    return str_replace("*#chr*#", "&nbsp;", $str);
}

function decode_html($str)
{
    global $default_charset;
    // Direct Popup action or Ajax Popup action should be treated the same.
    if ((isset($_REQUEST['action']) && $_REQUEST['action'] === 'Popup') || (isset($_REQUEST['file']) && $_REQUEST['file'] === 'Popup')) {
        return html_entity_decode($str);
    }

    if (is_string($str)) {
        return html_entity_decode($str, ENT_QUOTES, $default_charset);
    }

    return $str;
}

//function added to check the text length in the listview.
function textlength_check($field_val)
{
    global $listview_max_textlength, $default_charset;
    if ($listview_max_textlength && $listview_max_textlength > 0) {
        $temp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", (string)$field_val);
        if (function_exists('mb_strlen')) {
            if (mb_strlen(decode_html($temp_val)) > $listview_max_textlength) {
                $temp_val = mb_substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", decode_html($field_val)), 0, $listview_max_textlength, $default_charset) . '...';
            }
        } elseif (strlen(html_entity_decode($field_val)) > $listview_max_textlength) {
            $temp_val = substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength) . '...';
        }
    } else {
        $temp_val = $field_val;
    }

    return $temp_val;
}

/**
 * this function accepts a modulename and a fieldname and returns the first related module for it
 * it expects the uitype of the field to be 10
 *
 * @param string $module    - the modulename
 * @param string $fieldname - the field name
 *
 * @return string $data - the first related module
 */
function getFirstModule($module, $fieldname)
{
    global $adb;
    $sql = "select fieldid, uitype from vtiger_field where tabid=? and fieldname=?";
    $result = $adb->pquery($sql, [getTabid($module), $fieldname]);

    if ($adb->num_rows($result) > 0) {
        $uitype = $adb->query_result($result, 0, "uitype");

        if ($uitype == 10) {
            $fieldid = $adb->query_result($result, 0, "fieldid");
            $sql = "select * from vtiger_fieldmodulerel where fieldid=?";
            $result = $adb->pquery($sql, [$fieldid]);
            $count = $adb->num_rows($result);

            if ($count > 0) {
                $data = $adb->query_result($result, 0, "relmodule");
            }
        }
    }

    return $data;
}

function VT_getSimpleNavigationValues($start, $size, $total)
{
    $prev = $start - 1;
    if ($prev < 0) {
        $prev = 0;
    }
    if ($total === null) {
        return [
            'start'    => $start,
            'first'    => $start,
            'current'  => $start,
            'end'      => $start,
            'end_val'  => $size,
            'allflag'  => 'All',
            'prev'     => $prev,
            'next'     => $start + 1,
            'verylast' => 'last'
        ];
    }
    if (empty($total)) {
        $lastPage = 1;
    } else {
        $lastPage = ceil($total / $size);
    }

    $next = $start + 1;
    if ($next > $lastPage) {
        $next = 0;
    }

    return [
        'start'    => $start,
        'first'    => $start,
        'current'  => $start,
        'end'      => $start,
        'end_val'  => $size,
        'allflag'  => 'All',
        'prev'     => $prev,
        'next'     => $next,
        'verylast' => $lastPage
    ];
}

function getRecordRangeMessage($listResult, $limitStartRecord, $totalRows = '')
{
    global $adb, $app_strings;
    $numRows = $adb->num_rows($listResult);
    $recordListRangeMsg = '';
    if ($numRows > 0) {
        $recordListRangeMsg = $app_strings['LBL_SHOWING'] . ' ' . $app_strings['LBL_RECORDS'] .
            ' ' . ($limitStartRecord + 1) . ' - ' . ($limitStartRecord + $numRows);
        if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
            $recordListRangeMsg .= ' ' . $app_strings['LBL_LIST_OF'] . " $totalRows";
        }
    }

    return $recordListRangeMsg;
}

function listQueryNonAdminChange($query, $module, $scope = '')
{
    $instance = CRMEntity::getInstance($module);

    return $instance->listQueryNonAdminChange($query, $scope);
}
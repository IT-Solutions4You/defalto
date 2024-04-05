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
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
require_once('include/database/PearDatabase.php');
require_once('include/ComboUtil.php'); //new
require_once('include/utils/ListViewUtils.php');
require_once('include/utils/EditViewUtils.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/InventoryUtils.php');
require_once('include/FormValidationUtil.php');
require_once('include/events/SqlResultIterator.inc');
require_once('include/fields/DateTimeField.php');
require_once('include/fields/CurrencyField.php');
require_once('data/CRMEntity.php');
require_once 'vtlib/Vtiger/Language.php';
require_once("include/ListView/ListViewSession.php");

require_once 'vtlib/Vtiger/Functions.php';
require_once 'vtlib/Vtiger/Deprecated.php';

require_once 'includes/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'vtlib/Vtiger/AccessControl.php';
require_once 'includes/runtime/Configs.php';
// Constants to be defined here

// For Migration status.
define("MIG_CHARSET_PHP_UTF8_DB_UTF8", 1);
define("MIG_CHARSET_PHP_NONUTF8_DB_NONUTF8", 2);
define("MIG_CHARSET_PHP_NONUTF8_DB_UTF8", 3);
define("MIG_CHARSET_PHP_UTF8_DB_NONUTF8", 4);

// For Customview status.
define("CV_STATUS_DEFAULT", 0);
define("CV_STATUS_PRIVATE", 1);
define("CV_STATUS_PENDING", 2);
define("CV_STATUS_PUBLIC", 3);

// For Restoration.
define("RB_RECORD_DELETED", 'delete');
define("RB_RECORD_INSERTED", 'insert');
define("RB_RECORD_UPDATED", 'update');

//used in module file
function get_user_array($add_blank=true, $status="Active", $assigned_user="",$private="",$module=false)
{
	global $log;
	$log->debug("Entering get_user_array(".$add_blank.",". $status.",".$assigned_user.",".$private.") method ...");
	global $current_user;
	if(isset($current_user) && $current_user->id != '')
	{
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
	}
	static $user_array = null;
	if(!$module){
        $module=$_REQUEST['module'];
    }


	if($user_array == null)
	{
		require_once('include/database/PearDatabase.php');
		$db = PearDatabase::getInstance();
		$temp_result = Array();
		// Including deleted vtiger_users for now.
		if (empty($status)) {
				$query = "SELECT id, user_name, userlabel from vtiger_users";
				$params = array();
		}
		else {
				if($private == 'private')
				{
					$log->debug("Sharing is Private. Only the current user should be listed");
					$query = "select id as id,user_name as user_name,first_name,last_name,userlabel from vtiger_users where id=? and status='Active' union select vtiger_user2role.userid as id,vtiger_users.user_name as user_name ,
							  vtiger_users.first_name as first_name ,vtiger_users.last_name as last_name, vtiger_users.userlabel AS userlabel 
							  from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ? and status='Active' union
							  select shareduserid as id,vtiger_users.user_name as user_name ,
							  vtiger_users.first_name as first_name ,vtiger_users.last_name as last_name,vtiger_users.userlabel AS userlabel from vtiger_tmp_write_user_sharing_per inner join vtiger_users on vtiger_users.id=vtiger_tmp_write_user_sharing_per.shareduserid where status='Active' and vtiger_tmp_write_user_sharing_per.userid=? and vtiger_tmp_write_user_sharing_per.tabid=? and (user_name != 'admin' OR is_owner=1)";
					$params = array($current_user->id, $current_user_parent_role_seq."::%", $current_user->id, getTabid($module));
				}
				else
				{
					$log->debug("Sharing is Public. All vtiger_users should be listed");
					$query = "SELECT id, user_name,first_name,last_name,userlabel from vtiger_users WHERE status=? and (user_name != 'admin' OR is_owner=1)";
					$params = array($status);
				}
		}
		if (!empty($assigned_user)) {
			 $query .= " OR id=?";
			 array_push($params, $assigned_user);
		}

		$query .= " order by user_name ASC";

		$result = $db->pquery($query, $params, true, "Error filling in user array: ");

		if ($add_blank==true){
			// Add in a blank row
			$temp_result[''] = '';
		}

		// Get the id and the name.
		while($row = $db->fetchByAssoc($result))
		{
			$temp_result[$row['id']] = $row['userlabel'];
		}

		$user_array = &$temp_result;
	}

	$log->debug("Exiting get_user_array method ...");
	return $user_array;
}

function get_group_array($add_blank=true, $status="Active", $assigned_user="",$private="",$module = false)
{
	global $log;
	$log->debug("Entering get_user_array(".$add_blank.",". $status.",".$assigned_user.",".$private.") method ...");
	global $current_user;
	if(isset($current_user) && $current_user->id != '')
	{
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
	}
	static $group_array = null;
	if(!$module){
        $module=$_REQUEST['module'];
    }

	if($group_array == null)
	{
		require_once('include/database/PearDatabase.php');
		$db = PearDatabase::getInstance();
		$temp_result = Array();
		// Including deleted vtiger_users for now.
		$log->debug("Sharing is Public. All vtiger_users should be listed");
		$query = "SELECT groupid, groupname from vtiger_groups";
		$params = array();

		if($private == 'private'){

			$query .= " WHERE groupid=?";
			$params = array( $current_user->id);

			if(!empty($current_user_groups) && (php7_count($current_user_groups) != 0)) {
				$query .= " OR vtiger_groups.groupid in (".generateQuestionMarks($current_user_groups).")";
				array_push($params, $current_user_groups);
			}
			$log->debug("Sharing is Private. Only the current user should be listed");
			$query .= " union select vtiger_group2role.groupid as groupid,vtiger_groups.groupname as groupname from vtiger_group2role inner join vtiger_groups on vtiger_groups.groupid=vtiger_group2role.groupid inner join vtiger_role on vtiger_role.roleid=vtiger_group2role.roleid where vtiger_role.parentrole like ?";
			array_push($params, $current_user_parent_role_seq."::%");

			if(!empty($current_user_groups) && (php7_count($current_user_groups) != 0)) {
				$query .= " union select vtiger_groups.groupid as groupid,vtiger_groups.groupname as groupname from vtiger_groups inner join vtiger_group2rs on vtiger_groups.groupid=vtiger_group2rs.groupid where vtiger_group2rs.roleandsubid in (".generateQuestionMarks($parent_roles).")";
				array_push($params, $parent_roles);
			}

			$query .= " union select sharedgroupid as groupid,vtiger_groups.groupname as groupname from vtiger_tmp_write_group_sharing_per inner join vtiger_groups on vtiger_groups.groupid=vtiger_tmp_write_group_sharing_per.sharedgroupid where vtiger_tmp_write_group_sharing_per.userid=?";
			array_push($params, $current_user->id);

			$query .= " and vtiger_tmp_write_group_sharing_per.tabid=?";
			array_push($params,  getTabid($module));
		}
		$query .= " order by groupname ASC";

		$result = $db->pquery($query, $params, true, "Error filling in user array: ");

		if ($add_blank==true){
			// Add in a blank row
			$temp_result[''] = '';
		}

		// Get the id and the name.
		while($row = $db->fetchByAssoc($result))
		{
			$temp_result[$row['groupid']] = $row['groupname'];
		}

		$group_array = &$temp_result;
	}

	$log->debug("Exiting get_user_array method ...");
	return $group_array;
}

/** This function retrieves an application language file and returns the array of strings included in the $app_list_strings var.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * If you are using the current language, do not call this function unless you are loading it for the first time */

function return_app_list_strings_language($language) {
	return Vtiger_Deprecated::return_app_list_strings_language($language);
}

/** This function retrieves an application language file and returns the array of strings included.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * If you are using the current language, do not call this function unless you are loading it for the first time */
function return_application_language($language) {
	return Vtiger_Deprecated::return_app_list_strings_language($language);
	}

/** This function retrieves a module's language file and returns the array of strings included.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * If you are in the current module, do not call this function unless you are loading it for the first time */
function return_module_language($language, $module) {
	return Vtiger_Deprecated::getModuleTranslationStrings($language, $module);
}

/*This function returns the mod_strings for the current language and the specified module
*/

function return_specified_module_language($language, $module) {
	return Vtiger_Deprecated::return_app_list_strings_language($language, $module);
}

/**
 * Function to decide whether to_html should convert values or not for a request
 * @global type $doconvert
 * @global type $inUTF8
 * @global type $default_charset
 */
function decide_to_html() {
	global $doconvert, $inUTF8, $default_charset;
 	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : ''; 
 		     
    $inUTF8 = (strtoupper($default_charset) == 'UTF-8'); 

    $doconvert = true; 
	if ($action == 'ExportData') {
        $doconvert = false; 
    } 
}
decide_to_html();

/** Function to convert the given string to html
  * @param $string -- string:: Type string
  * @param $encode -- boolean:: Type boolean
    * @returns $string -- string:: Type string
       */
function to_html($string, $encode=true) {
	// For optimization - default_charset can be either upper / lower case.
    global $doconvert,$inUTF8,$default_charset,$htmlCache;

    if(is_string($string)) {
		// In vtiger5 ajax request are treated specially and the data is encoded
		if ($doconvert == true) {
            if(isset($htmlCache[$string])){
                $string = $htmlCache[$string];
            }else{
			if($inUTF8)
				$string = htmlentities($string, ENT_QUOTES, $default_charset);
			else
				$string = preg_replace(array('/</', '/>/', '/"/'), array('&lt;', '&gt;', '&quot;'), $string);
                $htmlCache[$string] = $string;
            }
		}
	}
	return $string;
}

/** Function to get the tablabel for a given id
  * @param $tabid -- tab id:: Type integer
  * @returns $string -- string:: Type string
*/

function getTabname($tabid)
{
	global $log;
	$log->debug("Entering getTabname(".$tabid.") method ...");
        $log->info("tab id is ".$tabid);
        global $adb;

	static $cache = array();

	if (!isset($cache[$tabid])) {
		$sql = "select tablabel from vtiger_tab where tabid=?";
		$result = $adb->pquery($sql, array($tabid));
		$tabname=  $adb->query_result($result,0,"tablabel");
		$cache[$tabid] = $tabname;
	}

	$log->debug("Exiting getTabname method ...");
	return $cache[$tabid];

}

/** Function to get the tab module name for a given id
  * @param $tabid -- tab id:: Type integer
    * @returns $string -- string:: Type string
      *
       */

function getTabModuleName($tabid)
{
	return Vtiger_Functions::getModuleName($tabid);
}

/** Function to get column fields for a given module
  * @param $module -- module:: Type string
    * @returns $column_fld -- column field :: Type array
      *
       */

function getColumnFields($module)
{
	global $log;
	$log->debug("Entering getColumnFields(".$module.") method ...");
	$log->debug("in getColumnFields ".$module);

	// Lookup in cache for information
	$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);

	if($cachedModuleFields === false) {
		global $adb;
		$tabid = getTabid($module);

		// To overcome invalid module names.
		if (empty($tabid)) {
			return array();
		}

    	// Let us pick up all the fields first so that we can cache information
		$sql = "SELECT tabid, fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence
		FROM vtiger_field WHERE tabid in (" . generateQuestionMarks($tabid) . ")";

        $result = $adb->pquery($sql, array($tabid));
        $noofrows = $adb->num_rows($result);

        if($noofrows) {
        	while($resultrow = $adb->fetch_array($result)) {
        		// Update information to cache for re-use
        		VTCacheUtils::updateFieldInfo(
        			$resultrow['tabid'], $resultrow['fieldname'], $resultrow['fieldid'],
        			$resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'],
        			$resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
        		);
        	}
        }

        // For consistency get information from cache
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
	}

	$column_fld = new TrackableObject();
	if($cachedModuleFields) {
		foreach($cachedModuleFields as $fieldinfo) {
			$column_fld[$fieldinfo['fieldname']] = '';
		}
	}

	$log->debug("Exiting getColumnFields method ...");
	return $column_fld;
}

/**
 * Get first email address for given user id.
 *
 * @param int $userId
 *
 * @return string
 */

function getUserEmail($userId): string
{
    global $log;
    $log->debug("Entering getUserEmail(" . $userId . ") method ...");

    $usersRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
    $log->debug("Exiting getUserEmail method ...");

    if ($usersRecordModel) {
        return $usersRecordModel->getEmailAddress();
    }

    return '';
}

/**
 * Get first email address for given username.
 *
 * @param string $userName
 *
 * @return string
 */
function getUserEmailByName(string $userName): string
{
    $usersRecordModel = Users_Record_Model::getInstanceByName($userName);

    if ($usersRecordModel) {
        return $usersRecordModel->getEmailAddress();
    }

    return '';
}

/**
 * Function to get the group users Emails
 *
 * @param $groupId
 *
 * @return array
 * @throws Exception
 */
function getDefaultAssigneeEmailIds($groupId): array
{
    if (empty($groupId)) {
        return [];
    }

    require_once 'include/utils/GetGroupUsers.php';
    $userGroups = new GetGroupUsers();
    $userGroups->getAllUsersInGroup($groupId);

    //Clearing static cache for subgroups
    GetGroupUsers::$groupIdsList = [];

    if (php7_count($userGroups->group_users) == 0) {
        return [];
    }

    $emails = [];

    foreach ($userGroups->group_users as $userId) {
        $email = getUserEmail($userId);

        if ($email) {
            $emails[] = $email;
        }
    }

    return $emails;
}

/** Function to get a userid for outlook
  * @param $username -- username :: Type string
    * @returns $user_id -- user id :: Type integer
       */

//outlook security
function getUserId_Ol($username)
{
	global $log;
	$log->debug("Entering getUserId_Ol(".$username.") method ...");
	$log->info("in getUserId_Ol ".$username);
	$cache = Vtiger_Cache::getInstance();
	if($cache->getUserId($username) || $cache->getUserId($username) === 0){
		return $cache->getUserId($username);
	} else {
	global $adb;
	$sql = "select id from vtiger_users where user_name=?";
	$result = $adb->pquery($sql, array($username));
	$num_rows = $adb->num_rows($result);
	if($num_rows > 0)
	{
		$user_id = $adb->query_result($result,0,"id");
    	}
	else
	{
		$user_id = 0;
	}
	$log->debug("Exiting getUserId_Ol method ...");
		$cache->setUserId($username,$user_id);
	return $user_id;
	}
}


/** Function to get a action id for a given action name
  * @param $action -- action name :: Type string
    * @returns $actionid -- action id :: Type integer
       */

//outlook security

function getActionid($action)
{
	global $log;
	$log->debug("Entering getActionid(".$action.") method ...");
	global $adb;
	$log->info("get Actionid ".$action);
	$actionid = '';
	if(file_exists('tabdata.php') && (filesize('tabdata.php') != 0))
	{
		include('tabdata.php');
		$actionid= isset($action_id_array[$action])? $action_id_array[$action] : 0;
	}
	else
	{
		$query="select * from vtiger_actionmapping where actionname=?";
        	$result =$adb->pquery($query, array($action));
        	$actionid=$adb->query_result($result,0,'actionid');

	}
	$log->info("action id selected is ".$actionid );
	$log->debug("Exiting getActionid method ...");
	return $actionid;
}

/** Function to get a action for a given action id
  * @param $action id -- action id :: Type integer
    * @returns $actionname-- action name :: Type string
       */


function getActionname($actionid)
{
	global $log;
	$log->debug("Entering getActionname(".$actionid.") method ...");
	global $adb;

	$actionname='';

	if (file_exists('tabdata.php') && (filesize('tabdata.php') != 0))
	{
		include('tabdata.php');
		$actionname= $action_name_array[$actionid];
	}
	else
	{

		$query="select * from vtiger_actionmapping where actionid=? and securitycheck=0";
		$result =$adb->pquery($query, array($actionid));
		$actionname=$adb->query_result($result,0,"actionname");
	}
	$log->debug("Exiting getActionname method ...");
	return $actionname;
}

/** Function to get a user id or group id for a given entity
  * @param $record -- entity id :: Type integer
    * @returns $ownerArr -- owner id :: Type array
       */

function getRecordOwnerId($record)
{
	global $log;
	$log->debug("Entering getRecordOwnerId(".$record.") method ...");
	global $adb;
	$ownerArr=Array();

	// Look at cache first for information
	$ownerId = VTCacheUtils::lookupRecordOwner($record);

	if($ownerId === false) {
		$query="select smownerid from vtiger_crmentity where crmid = ?";
		$result=$adb->pquery($query, array($record));
		if($adb->num_rows($result) > 0)
		{
			$ownerId=$adb->query_result($result,0,'smownerid');
			// Update cache for re-use
			VTCacheUtils::updateRecordOwner($record, $ownerId);
		}
	}

	if($ownerId)
	{
		// Look at cache first for information
		$count = VTCacheUtils::lookupOwnerType($ownerId);

		if($count === false) {
			$sql_result = $adb->pquery('SELECT 1 FROM vtiger_users WHERE id = ?', array($ownerId));
			$count = $adb->query_result($sql_result, 0, 1);
			// Update cache for re-use
			VTCacheUtils::updateOwnerType($ownerId, $count);
		}
		if($count > 0)
			$ownerArr['Users'] = $ownerId;
		else
			$ownerArr['Groups'] = $ownerId;
	}
	$log->debug("Exiting getRecordOwnerId method ...");
	return $ownerArr;

}

/** Function to insert value to profile2field table
  * @param $profileid -- profileid :: Type integer
       */


function insertProfile2field($profileid)
{
	global $log;
	$log->debug("Entering insertProfile2field(".$profileid.") method ...");
        $log->info("in insertProfile2field ".$profileid);

	global $adb;
	$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
	$fld_result = $adb->pquery("select * from vtiger_field where generatedtype=1 and displaytype in (1,2,3) and vtiger_field.presence in (0,2) and tabid != 29", array());
    $num_rows = $adb->num_rows($fld_result);
    for($i=0; $i<$num_rows; $i++) {
         $tab_id = $adb->query_result($fld_result,$i,'tabid');
         $field_id = $adb->query_result($fld_result,$i,'fieldid');
		 $params = array($profileid, $tab_id, $field_id, 0, 0);
         $adb->pquery("insert into vtiger_profile2field values (?,?,?,?,?)", $params);
	}
	$log->debug("Exiting insertProfile2field method ...");
}

/** Function to update product quantity
  * @param $product_id -- product id :: Type integer
  * @param $upd_qty -- quantity :: Type integer
  */

function updateProductQty($product_id, $upd_qty)
{
	global $log;
	$log->debug("Entering updateProductQty(".$product_id.",". $upd_qty.") method ...");
	global $adb;
	$query= "update vtiger_products set qtyinstock=? where productid=?";
    $adb->pquery($query, array($upd_qty, $product_id));
	$log->debug("Exiting updateProductQty method ...");

}

/** This Function adds the specified product quantity to the Product Quantity in Stock in the Warehouse
  * The following is the input parameter for the function:
  *  $productId --> ProductId, Type:Integer
  *  $qty --> Quantity to be added, Type:Integer
  */
function addToProductStock($productId,$qty)
{
	global $log;
	$log->debug("Entering addToProductStock(".$productId.",".$qty.") method ...");
	global $adb;
	$qtyInStck=getProductQtyInStock($productId);
	$updQty=$qtyInStck + $qty;
	$sql = "UPDATE vtiger_products set qtyinstock=? where productid=?";
	$adb->pquery($sql, array($updQty, $productId));
	$log->debug("Exiting addToProductStock method ...");

    }

/**	This Function adds the specified product quantity to the Product Quantity in Demand in the Warehouse
  *	@param int $productId - ProductId
  *	@param int $qty - Quantity to be added
  */
function addToProductDemand($productId,$qty)
{
	global $log;
	$log->debug("Entering addToProductDemand(".$productId.",".$qty.") method ...");
		global $adb;
	$qtyInStck=getProductQtyInDemand($productId);
	$updQty=$qtyInStck + $qty;
	$sql = "UPDATE vtiger_products set qtyindemand=? where productid=?";
	$adb->pquery($sql, array($updQty, $productId));
	$log->debug("Exiting addToProductDemand method ...");

		}

/**	This Function subtract the specified product quantity to the Product Quantity in Demand in the Warehouse
  *	@param int $productId - ProductId
  *	@param int $qty - Quantity to be subtract
  */
function deductFromProductDemand($productId,$qty)
{
	global $log;
	$log->debug("Entering deductFromProductDemand(".$productId.",".$qty.") method ...");
	global $adb;
	$qtyInStck=getProductQtyInDemand($productId);
	$updQty=$qtyInStck - $qty;
	$sql = "UPDATE vtiger_products set qtyindemand=? where productid=?";
	$adb->pquery($sql, array($updQty, $productId));
	$log->debug("Exiting deductFromProductDemand method ...");

    }


/** This Function returns the current product quantity in stock.
  * The following is the input parameter for the function:
  *  $product_id --> ProductId, Type:Integer
  */
function getProductQtyInStock($product_id)
{
	global $log;
	$log->debug("Entering getProductQtyInStock(".$product_id.") method ...");
	global $adb;
        $query1 = "select qtyinstock from vtiger_products where productid=?";
        $result=$adb->pquery($query1, array($product_id));
        $qtyinstck= $adb->query_result($result,0,"qtyinstock");
	$log->debug("Exiting getProductQtyInStock method ...");
        return $qtyinstck;


}

/**	This Function returns the current product quantity in demand.
  *	@param int $product_id - ProductId
  *	@return int $qtyInDemand - Quantity in Demand of a product
  */
function getProductQtyInDemand($product_id)
{
	global $log;
	$log->debug("Entering getProductQtyInDemand(".$product_id.") method ...");
	global $adb;
        $query1 = "select qtyindemand from vtiger_products where productid=?";
        $result = $adb->pquery($query1, array($product_id));
        $qtyInDemand = $adb->query_result($result,0,"qtyindemand");
	$log->debug("Exiting getProductQtyInDemand method ...");
        return $qtyInDemand;
}

/**     Function to get the vtiger_table name from 'field' vtiger_table for the input vtiger_field based on the module
 *      @param  : string $module - current module value
 *      @param  : string $fieldname - vtiger_fieldname to which we want the vtiger_tablename
 *      @return : string $tablename - vtiger_tablename in which $fieldname is a column, which is retrieved from 'field' vtiger_table per $module basis
  */
function getTableNameForField($module,$fieldname)
{
	global $log;
	$log->debug("Entering getTableNameForField(".$module.",".$fieldname.") method ...");
	global $adb;
	$tabid = getTabid($module);
	//Asha
	$sql = "select tablename from vtiger_field where tabid in (". generateQuestionMarks($tabid) .") and vtiger_field.presence in (0,2) and columnname like ?";
	$res = $adb->pquery($sql, array($tabid, '%'.$fieldname.'%'));

	$tablename = '';
	if($adb->num_rows($res) > 0)
{
		$tablename = $adb->query_result($res,0,'tablename');
	}

	$log->debug("Exiting getTableNameForField method ...");
	return $tablename;
}

/** Function to get parent record owner
  * @param $tabid -- tabid :: Type integer
  * @param $parModId -- parent module id :: Type integer
  * @param $record_id -- record id :: Type integer
  * @returns $parentRecOwner -- parentRecOwner:: Type integer
  */

function getParentRecordOwner($tabid,$parModId,$record_id)
 {
	global $log;
	$log->debug("Entering getParentRecordOwner(".$tabid.",".$parModId.",".$record_id.") method ...");
	$parentRecOwner=Array();
	$parentTabName=getTabname($parModId);
	$relTabName=getTabname($tabid);
	$fn_name="get".$relTabName."Related".$parentTabName;
	$ent_id=$fn_name($record_id);
	if($ent_id != '')
        {
		$parentRecOwner=getRecordOwnerId($ent_id);
        }
	$log->debug("Exiting getParentRecordOwner method ...");
	return $parentRecOwner;
        }


// Return Question mark
function _questionify($v){
	return "?";
}

/**
* Function to generate question marks for a given list of items
  */
function generateQuestionMarks($items_list) {
	// array_map will call the function specified in the first parameter for every element of the list in second parameter
	if (is_array($items_list)) {
		return implode(",", array_map("_questionify", $items_list));
	} else {
		return implode(",", array_map("_questionify", explode(",", $items_list)));
}
}

/**
* Function to find the UI type of a field based on the uitype id
  */
function is_uitype($uitype, $reqtype) {
	$ui_type_arr = array(
		'_date_' => array(5, 6, 23, 70),
		'_picklist_' => array(15, 16, 52, 53, 54, 55, 59, 62, 63, 66, 68, 76, 77, 78, 80, 98, 101, 115, 357),
		'_users_list_' => array(52),
	);

	if ($ui_type_arr[$reqtype] != null) {
		if (in_array($uitype, $ui_type_arr[$reqtype])) {
			return true;
	}
	}
	return false;
	}
/**
 * Function to escape quotes
 * @param $value - String in which single quotes have to be replaced.
 * @return Input string with single quotes escaped.
  */
function escape_single_quotes($value) {
	if (isset($value)) $value = str_replace("'", "\'", $value);
	return $value;
}

/**
 * Function to format the input value for SQL like clause.
 * @param $str - Input string value to be formatted.
 * @param $flag - By default set to 0 (Will look for cases %string%).
 *                If set to 1 - Will look for cases %string.
 *                If set to 2 - Will look for cases string%.
 * @return String formatted as per the SQL like clause requirement
  */
function formatForSqlLike($str, $flag=0,$is_field=false) {
	global $adb;
	if (isset($str)) {
		if($is_field==false){
			$str = str_replace('%', '\%', $str);
			$str = str_replace('_', '\_', $str);
			if ($flag == 0) {
                // If value what to search is null then we should not add % which will fail
                if(empty($str))
                    $str = ''.$str.'';
                else
                    $str = '%'. $str .'%';
			} elseif ($flag == 1) {
				$str = '%'. $str;
			} elseif ($flag == 2) {
				$str = $str .'%';
			}
		} else {
			if ($flag == 0) {
				$str = 'concat("%",'. $str .',"%")';
			} elseif ($flag == 1) {
				$str = 'concat("%",'. $str .')';
			} elseif ($flag == 2) {
				$str = 'concat('. $str .',"%")';
			}
		}
	}
	return $adb->sql_escape_string($str);
}

function get_config_status() {
	global $default_charset;
	if(strtolower($default_charset) == 'utf-8')
		$config_status=1;
	else
		$config_status=0;
	return $config_status;
        }

/** Function to get on clause criteria for duplicate check queries */
function get_on_clause($field_list,$uitype_arr,$module)
{
	$field_array = explode(",",$field_list);
	$ret_str = '';
	$i=1;
	foreach($field_array as $fld)
	{
		$sub_arr = explode(".",$fld);
		$tbl_name = $sub_arr[0];
		$col_name = $sub_arr[1];
		$fld_name = $sub_arr[2];

		$ret_str .= " ifnull($tbl_name.$col_name,'null') = ifnull(temp.$col_name,'null')";

		if (php7_count($field_array) != $i) $ret_str .= " and ";
		$i++;
	}
	return $ret_str;
}

// Update all the data refering to currency $old_cur to $new_cur
function transferCurrency($old_cur, $new_cur) {

	// Transfer User currency to new currency
	transferUserCurrency($old_cur, $new_cur);

	// Transfer Product Currency to new currency
	transferProductCurrency($old_cur, $new_cur);

	// Transfer PriceBook Currency to new currency
	transferPriceBookCurrency($old_cur, $new_cur);
    
    // Transfer Services Currency to new currency
    transferServicesCurrency($old_cur, $new_cur);
}

// Function to transfer the users with currency $old_cur to $new_cur as currency
function transferUserCurrency($old_cur, $new_cur) {
	global $log, $adb, $current_user;
	$log->debug("Entering function transferUserCurrency...");

	$sql = 'UPDATE vtiger_users SET currency_id=? WHERE currency_id=?';
	$adb->pquery($sql, array($new_cur, $old_cur));

	$currentUserId = $current_user->id;
	$current_user->retrieve_entity_info($currentUserId, 'Users');
	unset(Users_Record_Model::$currentUserModels[$currentUserId]);

	require_once('modules/Users/CreateUserPrivilegeFile.php'); 
	createUserPrivilegesfile($currentUserId);

	$log->debug("Exiting function transferUserCurrency...");
}

// Function to transfer the products with currency $old_cur to $new_cur as currency
function transferProductCurrency($old_cur, $new_cur) {
	global $log, $adb;
	$log->debug("Entering function updateProductCurrency...");
	$prod_res = $adb->pquery("select productid from vtiger_products where currency_id = ?", array($old_cur));
	$numRows = $adb->num_rows($prod_res);
	$prod_ids = array();
	for($i=0;$i<$numRows;$i++) {
		$prod_ids[] = $adb->query_result($prod_res,$i,'productid');
	}
	if(!empty($prod_ids) && (php7_count($prod_ids) > 0)) {
		$prod_price_list = getPricesForProducts($new_cur,$prod_ids);

		for($i=0;$i<php7_count($prod_ids);$i++) {
			$product_id = $prod_ids[$i];
			$unit_price = $prod_price_list[$product_id];
			$query = "update vtiger_products set currency_id=?, unit_price=? where productid=?";
			$params = array($new_cur, $unit_price, $product_id);
			$adb->pquery($query, $params);
		}
}
	$log->debug("Exiting function updateProductCurrency...");
}

// Function to transfer the pricebooks with currency $old_cur to $new_cur as currency
// and to update the associated products with list price in $new_cur currency
function transferPriceBookCurrency($old_cur, $new_cur) {
	global $log, $adb;
	$log->debug("Entering function updatePriceBookCurrency...");
	$pb_res = $adb->pquery("select pricebookid from vtiger_pricebook where currency_id = ?", array($old_cur));
	$numRows = $adb->num_rows($pb_res);
	$pb_ids = array();
	for($i=0;$i<$numRows;$i++) {
		$pb_ids[] = $adb->query_result($pb_res,$i,'pricebookid');
}

	if(!empty($pb_ids) && (php7_count($pb_ids) > 0)) {
		require_once('modules/PriceBooks/PriceBooks.php');

		for($i=0;$i<php7_count($pb_ids);$i++) {
			$pb_id = $pb_ids[$i];
			$focus = new PriceBooks();
			$focus->id = $pb_id;
			$focus->mode = 'edit';
			$focus->retrieve_entity_info($pb_id, "PriceBooks");
			$focus->column_fields['currency_id'] = $new_cur;
			$focus->save("PriceBooks");
}
}

	$log->debug("Exiting function updatePriceBookCurrency...");
}

//To transfer all services after deleting currency to transfered currency
function transferServicesCurrency($old_cur, $new_cur) {
    global $log, $adb;
    $log->debug("Entering function updateServicesCurrency...");
	$ser_res = $adb->pquery('SELECT serviceid FROM vtiger_service WHERE currency_id = ?', array($old_cur));
    $numRows = $adb->num_rows($ser_res);
    $ser_ids = array();
    for ($i = 0; $i < $numRows; $i++) {
        $ser_ids[] = $adb->query_result($ser_res, $i, 'serviceid');
    }
    if (!empty($ser_ids) && (php7_count($ser_ids) > 0)) {
        $ser_price_list = getPricesForProducts($new_cur, $ser_ids, 'Services');
        for ($i = 0; $i < php7_count($ser_ids); $i++) {
            $service_id = $ser_ids[$i];
            $unit_price = $ser_price_list[$service_id];
			$query = 'UPDATE vtiger_service SET currency_id=?, unit_price=? WHERE serviceid=?';
            $params = array($new_cur, $unit_price, $service_id);
            $adb->pquery($query, $params);
        }
    }
    $log->debug("Exiting function updateServicesCurrency...");
}

//functions for asterisk integration end

/* Function to get the related tables data
 * @param - $module - Primary module name
 * @param - $secmodule - Secondary module name
 * return Array $rel_array tables and fields to be compared are sent
 * */
function getRelationTables($module,$secmodule){
	global $adb;
	$primary_obj = CRMEntity::getInstance($module);
	$secondary_obj = CRMEntity::getInstance($secmodule);

	$ui10_query = $adb->pquery("SELECT vtiger_field.tabid AS tabid,vtiger_field.tablename AS tablename, vtiger_field.columnname AS columnname FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE (vtiger_fieldmodulerel.module=? AND vtiger_fieldmodulerel.relmodule=?) OR (vtiger_fieldmodulerel.module=? AND vtiger_fieldmodulerel.relmodule=?)",array($module,$secmodule,$secmodule,$module));
		if($adb->num_rows($ui10_query)>0){
			$ui10_tablename = $adb->query_result($ui10_query,0,'tablename');
			$ui10_columnname = $adb->query_result($ui10_query,0,'columnname');
			$ui10_tabid = $adb->query_result($ui10_query,0,'tabid');

			if($primary_obj->table_name == $ui10_tablename){
				$reltables = array($ui10_tablename=>array("".$primary_obj->table_index."","$ui10_columnname"));
			} else if($secondary_obj->table_name == $ui10_tablename){
				$reltables = array($ui10_tablename=>array("$ui10_columnname","".$secondary_obj->table_index.""),"".$primary_obj->table_name."" => "".$primary_obj->table_index."");
			} else {
				if(isset($secondary_obj->tab_name_index[$ui10_tablename])){
					$rel_field = $secondary_obj->tab_name_index[$ui10_tablename];
					$reltables = array($ui10_tablename=>array("$ui10_columnname","$rel_field"),"".$primary_obj->table_name."" => "".$primary_obj->table_index."");
				} else {
					$rel_field = $primary_obj->tab_name_index[$ui10_tablename];
					$reltables = array($ui10_tablename=>array("$rel_field","$ui10_columnname"),"".$primary_obj->table_name."" => "".$primary_obj->table_index."");
				}
			}
	}else {
		if(method_exists($primary_obj,'setRelationTables')){
			$reltables = $primary_obj->setRelationTables($secmodule);
		} else {
			$reltables = '';
		}
	}
	if(is_array($reltables) && !empty($reltables)){
		$rel_array = $reltables;
	} else {
		$rel_array = array("vtiger_crmentityrel"=>array("crmid","relcrmid"),"".$primary_obj->table_name."" => "".$primary_obj->table_index."");
	}
	return $rel_array;
}

/**
 * This function returns no value but handles the delete functionality of each entity.
 * Input Parameter are $module - module name, $return_module - return module name, $focus - module object, $record - entity id, $return_id - return entity id.
  */
function DeleteEntity($module,$return_module,$focus,$record,$return_id) {
	global $log;
	$log->debug("Entering DeleteEntity method ($module, $return_module, $record, $return_id)");

	if ($module != $return_module && !empty($return_module) && !empty($return_id)) {
		$focus->unlinkRelationship($record, $return_module, $return_id);
		$focus->trackUnLinkedInfo($return_module, $return_id, $module, $record);
	} else {
		$focus->trash($module, $record);
	}
	$log->debug("Exiting DeleteEntity method ...");
}

/**
 * Function to related two records of different entity types
  */
function relateEntities($focus, $sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds)
{
    $db = PearDatabase::getInstance();
    $em = new VTEventsManager($db);
    $data = [
        'sourceModule' => $sourceModule,
        'sourceRecordId' => $sourceRecordId,
        'destinationModule' => $destinationModule,
        'destinationRecordIds' => $destinationRecordIds,
    ];
    $em->triggerEvent("vtiger.entity.beforerelate", $data);

    if (!is_array($destinationRecordIds)) {
        $destinationRecordIds = [$destinationRecordIds];
    }

    foreach ($destinationRecordIds as $destinationRecordId) {
        $focus->save_related_module($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId);
        $focus->trackLinkedInfo($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId);
    }

    $em->triggerEvent("vtiger.entity.afterrelate", $data);
}

/**
 * Track install/update vtlib module in current run.
 */
$_installOrUpdateVtlibModule = array();

/* Function to install Vtlib Compliant modules
 * @param - $packagename - Name of the module
 * @param - $packagepath - Complete path to the zip file of the Module
  */
function installVtlibModule($packagename, $packagepath, $customized=false) {
	global $log, $Vtiger_Utils_Log, $_installOrUpdateVtlibModule;
	if(!file_exists($packagepath)) return;

	if (isset($_installOrUpdateVtlibModule[$packagename.$packagepath])) return;
	$_installOrUpdateVtlibModule[$packagename.$packagepath] = 'install';

	require_once('vtlib/Vtiger/Package.php');
	require_once('vtlib/Vtiger/Module.php');
	$Vtiger_Utils_Log = defined('INSTALLATION_MODE_DEBUG')? INSTALLATION_MODE_DEBUG : true;
	$package = new Vtiger_Package();

	if($package->isLanguageType($packagepath)) {
		$package = new Vtiger_Language();
		$package->import($packagepath, true);
		return;
}
	$module = $package->getModuleNameFromZip($packagepath);

	// Customization
	if($package->isLanguageType()) {
		require_once('vtlib/Vtiger/Language.php');
		$languagePack = new Vtiger_Language();
		@$languagePack->import($packagepath, true);
		return;
}
	// END

	$module_exists = false;
	$module_dir_exists = false;
	if($module == null) {
		$log->fatal("$packagename Module zipfile is not valid!");
	} else if(Vtiger_Module::getInstance($module)) {
		$log->fatal("$module already exists!");
		$module_exists = true;
	}
	if($module_exists == false) {
		$log->debug("$module - Installation starts here");
		$package->import($packagepath, true);
		$moduleInstance = Vtiger_Module::getInstance($module);
		if (empty($moduleInstance)) {
			$log->fatal("$module module installation failed!");
		}
	}
}

/* Function to update Vtlib Compliant modules
 * @param - $module - Name of the module
 * @param - $packagepath - Complete path to the zip file of the Module
 */
function updateVtlibModule($module, $packagepath) {
	global $log, $_installOrUpdateVtlibModule;
	if(!file_exists($packagepath)) return;

	if (isset($_installOrUpdateVtlibModule[$module.$packagepath])) return;
	$_installOrUpdateVtlibModule[$module.$packagepath] = 'update';

	require_once('vtlib/Vtiger/Package.php');
	require_once('vtlib/Vtiger/Module.php');
	$Vtiger_Utils_Log = defined('INSTALLATION_MODE_DEBUG')? INSTALLATION_MODE_DEBUG : true;
	$package = new Vtiger_Package();

	if($package->isLanguageType($packagepath)) {
		require_once('vtlib/Vtiger/Language.php');
		$languagePack = new Vtiger_Language();
		$languagePack->update(null, $packagepath, true);
		return;
	}

	if($module == null) {
		$log->fatal("Module name is invalid");
	} else {
		$moduleInstance = Vtiger_Module::getInstance($module);
		if($moduleInstance || $package->isModuleBundle($packagepath)) {
			$log->debug("$module - Module instance found - Update starts here");
			$package->update($moduleInstance, $packagepath);
		} else {
			$log->fatal("$module doesn't exists!");
		}
	}
}

/**
 * this function checks if a given column exists in a given table or not
 * @param string $columnName - the columnname
 * @param string $tableName - the tablename
 * @return boolean $status - true if column exists; false otherwise
 */
function columnExists($columnName, $tableName){
	global $adb;
	$columnNames = array();
	$columnNames = $adb->getColumnNames($tableName);

	if(in_array($columnName, $columnNames)){
		return true;
	}else{
		return false;
	}
}

/**
 * Function to check if a given record exists (not deleted)
 * @param integer $recordId - record id
 */
function isRecordExists($recordId) {
	global $adb;
	$query = "SELECT crmid FROM vtiger_crmentity where crmid=? AND deleted=0";
	$result = $adb->pquery($query, array($recordId));
	if ($adb->num_rows($result)) {
		return true;
	}
	return false;
}

/** Function to set date values compatible to database (YY_MM_DD)
  * @param $value -- value :: Type string
  * @returns $insert_date -- insert_date :: Type string
  */
function getValidDBInsertDateValue($value) {
	global $log;
	$log->debug("Entering getValidDBInsertDateValue(".$value.") method ...");
	$value = trim($value);
	$delim = array('/','.');
	foreach ($delim as $delimiter){
		$x = strpos($value, $delimiter);
		if($x === false) continue;
		else{
			$value=str_replace($delimiter, '-', $value);
			break;
		}
	}
	global $current_user;
	$formate=$current_user->date_format;
	[$d,$m,$y] = explode('-',$value);
	if(strlen($d) == 4 || $formate == 'mm-dd-yyyy'){
		[$y,$m,$d]=explode('-',$value);
	}
	if(strlen($y) == 1) $y = '0'.$y;
	if(strlen($m) == 1) $m = '0'.$m;
	if(strlen($d) == 1) $d = '0'.$d;
	$value = implode('-', array($y,$m,$d));

	if(strlen($y)<4){
		$insert_date = DateTimeField::convertToDBFormat($value);
	} else {
		$insert_date = $value;
	}

	if (preg_match("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2}$/", $insert_date) == 0) {
		return '';
	}

	$log->debug("Exiting getValidDBInsertDateValue method ...");
	return $insert_date;
		}

function getValidDBInsertDateTimeValue($value) {
	$value = trim($value);
	$valueList = explode(' ',$value);
    //checking array count = 3 if datatime format is 12hr.
	if(is_array($valueList) && (php7_count($valueList) == 2 || php7_count($valueList) == 3)) {
		$dbDateValue = getValidDBInsertDateValue($valueList[0]);
		$dbTimeValue = $valueList[1];
		if(!empty($dbTimeValue) && strpos($dbTimeValue, ':') === false) {
			$dbTimeValue = $dbTimeValue.':';
		}
		$timeValueLength = strlen($dbTimeValue);
		if(!empty($dbTimeValue) &&  strrpos($dbTimeValue, ':') == ($timeValueLength-1)) {
			$dbTimeValue = $dbTimeValue.'00';
		}
		try {
			$dateTime = new DateTimeField($dbDateValue.' '.$dbTimeValue);
			return $dateTime->getDBInsertDateTimeValue();
		} catch (Exception $ex) {
			return '';
		}
	} elseif(is_array($valueList) && php7_count($valueList) == 1) {
		return getValidDBInsertDateValue($value);
	}
}

/** Function to sanitize the upload file name when the file name is detected to have bad extensions
 * @param String -- $fileName - File name to be sanitized
 * @return String - Sanitized file name
 */
function sanitizeUploadFileName($fileName, $badFileExtensions)
{
    if (!$badFileExtensions) {
        $badFileExtensions = vglobal('upload_badext');
    }

    $fileName = preg_replace('/[\s#%&?]+/', '_', $fileName); //replace space,#,%,&,? with _ in filename
    $fileName = rtrim($fileName, '\\/<>?*:"<>|');

    $fileNameParts = explode('.', $fileName);
    $countOfFileNameParts = php7_count($fileNameParts);
    $badExtensionFound = false;

    for ($i = 0; $i < $countOfFileNameParts; $i++) {
        $partOfFileName = $fileNameParts[$i];

        if (in_array(strtolower($partOfFileName), $badFileExtensions)) {
            $badExtensionFound = true;
            $fileNameParts[$i] = $partOfFileName . 'file';
        }
    }

    $newFileName = implode('.', $fileNameParts);

    if ($badExtensionFound) {
        $newFileName .= '.txt';
    }

    return ltrim(basename(' ' . $newFileName));
}

/** Function to return block name
 * @param Integer -- $blockid
 * @return String - Block Name
 */
function getBlockName($blockid) {
	global $adb;

	$blockname = VTCacheUtils::lookupBlockLabelWithId($blockid);

	if(!empty($blockid) && $blockname === false){
		$block_res = $adb->pquery('SELECT blocklabel FROM vtiger_blocks WHERE blockid = ?',array($blockid));
		if($adb->num_rows($block_res)){
			$blockname = $adb->query_result($block_res,0,'blocklabel');
		} else {
			$blockname = '';
		}
		VTCacheUtils::updateBlockLabelWithId($blockname, $blockid);
	}
	return $blockname;
}

function validateAlphaNumericInput($string){
    preg_match('/^[\w _\-]+$/', $string, $matches);
    if(php7_count($matches) == 0) {
        return false;
	}
    return true;
}

function validateServerName($string){
    preg_match('/^[\w\-\.\\/:]+$/', $string, $matches);
    if(php7_count($matches) == 0) {
        return false;
		}
    return true;
	}

/**
* Function to get the approximate difference between two date time values as string
*/
function dateDiffAsString($d1, $d2) {
	global $currentModule;

	$dateDiff = dateDiff($d1, $d2);

	$years = $dateDiff['years'];
	$months = $dateDiff['months'];
	$days = $dateDiff['days'];
	$hours = $dateDiff['hours'];
	$minutes = $dateDiff['minutes'];
	$seconds = $dateDiff['seconds'];

	if($years > 0) {
		$diffString = "$years ".getTranslatedString('LBL_YEARS',$currentModule);
	} elseif($months > 0) {
		$diffString = "$months ".getTranslatedString('LBL_MONTHS',$currentModule);
	} elseif($days > 0) {
		$diffString = "$days ".getTranslatedString('LBL_DAYS',$currentModule);
	} elseif($hours > 0) {
		$diffString = "$hours ".getTranslatedString('LBL_HOURS',$currentModule);
	} elseif($minutes > 0) {
		$diffString = "$minutes ".getTranslatedString('LBL_MINUTES',$currentModule);
				} else {
		$diffString = "$seconds ".getTranslatedString('LBL_SECONDS',$currentModule);
				}
	return $diffString;
			}

function getMinimumCronFrequency() {
	global $MINIMUM_CRON_FREQUENCY;

	if(!empty($MINIMUM_CRON_FREQUENCY)) {
		return $MINIMUM_CRON_FREQUENCY;
		}
	return 15;
}

//Function returns Email related Modules
function getEmailRelatedModules()
{
    return (new EMAILMaker_Module_Model())->getEmailRelatedModules();
}

//Get the User selected NumberOfCurrencyDecimals
function getCurrencyDecimalPlaces($user = null) {
    global $current_user;

	$currency_decimal_places = 2;
    if (!empty($user)) {
        $currency_decimal_places = $user->no_of_currency_decimals;
    } else if ($current_user) {
        $currency_decimal_places = $current_user->no_of_currency_decimals;
    }
    return (int)$currency_decimal_places;
}

function getInventoryModules() {
	$inventoryModules = array('Invoice','Quotes','PurchaseOrder','SalesOrder');
	return $inventoryModules;
}

function isLeadConverted($leadId) {
	$adb = PearDatabase::getInstance();

	$query = 'SELECT converted FROM vtiger_leaddetails WHERE converted = 1 AND leadid=?';
	$params = array($leadId);

	$result = $adb->pquery($query, $params);

	if($result && $adb->num_rows($result) > 0) {
		return true;
	}
	return false;
}

/** Function to get the difference between 2 datetime strings or millisecond values */
function dateDiff($d1, $d2){
	$d1 = (is_string($d1) ? strtotime($d1) : $d1);
	$d2 = (is_string($d2) ? strtotime($d2) : $d2);

	$diffSecs = abs($d1 - $d2);
	$baseYear = min(date("Y", $d1), date("Y", $d2));
	$diff = mktime(0, 0, $diffSecs, 1, 1, $baseYear);
	return array(
		"years" => date("Y", $diff) - $baseYear,
		"months_total" => (date("Y", $diff) - $baseYear) * 12 + date("n", $diff) - 1,
		"months" => date("n", $diff) - 1,
		"days_total" => floor($diffSecs / (3600 * 24)),
		"days" => date("j", $diff) - 1,
		"hours_total" => floor($diffSecs / 3600),
		"hours" => date("G", $diff),
		"minutes_total" => floor($diffSecs / 60),
		"minutes" => (int) date("i", $diff),
		"seconds_total" => $diffSecs,
		"seconds" => (int) date("s", $diff)
	);
}

/**
 * Function to get combinations of string from Array
 * @param <Array> $array
 * @param <String> $tempString
 * @return <Array>
 */
function getCombinations($array, $tempString = '') {
	for ($i=0; $i<php7_count($array); $i++) {
		$splicedArray = $array;
		$element = array_splice($splicedArray, $i, 1);// removes and returns the i'th element
		if (php7_count($splicedArray) > 0) {
			 if(!is_array($result)) {
				 $result = array();
			 }
			 $result = array_merge($result, getCombinations($splicedArray, $tempString. ' |##| ' .$element[0]));
		} else {
			return array($tempString. ' |##| ' . $element[0]);
		}
	}
	return $result;
}

function getCompanyDetails() {
	global $adb;

	$sql="select * from vtiger_organizationdetails";
	$result = $adb->pquery($sql, array());

	$companyDetails = array();
	$companyDetails['companyname'] = $adb->query_result($result,0,'organizationname');
	$companyDetails['website'] = $adb->query_result($result,0,'website');
	$companyDetails['address'] = $adb->query_result($result,0,'address');
	$companyDetails['city'] = $adb->query_result($result,0,'city');
	$companyDetails['state'] = $adb->query_result($result,0,'state');
	$companyDetails['country'] = $adb->query_result($result,0,'country');
	$companyDetails['phone'] = $adb->query_result($result,0,'phone');
	$companyDetails['fax'] = $adb->query_result($result,0,'fax');
	$companyDetails['logoname'] = $adb->query_result($result,0,'logoname');

	return $companyDetails;
}

/** call back function to change the array values in to lower case */
function lower_array(&$string){
	$string = strtolower(trim($string));
}

if (!function_exists('set_magic_quotes_runtime')) { function set_magic_quotes_runtime($flag) {} }

/** 
 * Function to escape backslash (\ to \\) in a string
 * @param string $value String to be escaped
 * @return string escaped string
 */
function escapeSlashes($value) {
    return str_replace('\\', '\\\\', $value);
}

/**
 * Function to get a group id for a given entity
 * @param $record -- entity id :: Type integer
 * @returns group id <int>
 */
function getRecordGroupId($record) {
	global $adb;
	// Look at cache first for information
	$groupId = VTCacheUtils::lookupRecordGroup($record);

	if ($groupId === false) {
		$query = "SELECT smgroupid FROM vtiger_crmentity WHERE crmid = ?";
		$result = $adb->pquery($query, array($record));
		if ($adb->num_rows($result) > 0) {
			$groupId = $adb->query_result($result, 0, 'smgroupid');
			// Update cache forupdateRecordGroup re-use
			VTCacheUtils::updateRecordGroup($record, $groupId);
		}
	}

	return $groupId;
}

/**
 * Function to delete record from $_SESSION[$moduleName.'_DetailView_Navigation'.$cvId]
 */
function deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName) {
	$recordNavigationInfo = Zend_Json::decode($_SESSION[$moduleName . '_DetailView_Navigation' . $cvId]);
	if (!empty($recordNavigationInfo) && (php7_count($recordNavigationInfo) != 0)) {
		foreach ($recordNavigationInfo as $key => $recordIdList) {
			$recordIdList = array_diff($recordIdList, array($recordId));
			$recordNavigationInfo[$key] = $recordIdList;
		}
		$_SESSION[$moduleName . '_DetailView_Navigation' . $cvId] = Zend_Json::encode($recordNavigationInfo);
	}
}

function sendMailToUserOnDuplicationPrevention($moduleName, $fieldData, $mailBody, $userModel = '') {
	if (!$userModel) {
		$userId = $_SESSION['authenticated_user_id'];
		if ($userId) {
			$userModel = Users_Record_Model::getInstanceFromPreferenceFile($userId);
		} else {
			$userModel = Users_Record_Model::getCurrentUserModel();
		}
	}

    $userName = $userModel->getName();

	$mailer = ITS4YouEmails_Mailer_Model::getCleanInstance();
    $mailer->retrieveSMTPVtiger();
	$mailer->isHTML(true);
    $mailer->setFrom($mailer::getFromEmailAddress(), $userName);
    $mailer->addReplyTo($mailer::getReplyToEmail(), $userName);
	$mailer->Subject = vtranslate('LBL_VTIGER_NOTIFICATION');
	$body = $mailBody;
    $body .= '<br>';
	$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
	$fieldModels = $moduleModel->getFields();

    foreach ($fieldModels as $fieldName => $fieldModel) {
        if ($fieldModel->isUniqueField() && $fieldModel->isViewable()) {
            $fieldValue = $fieldData[$fieldName];

            switch ($fieldModel->getFieldDataType()) {
                case 'reference'        :
                    [$refModuleId, $refRecordId] = vtws_getIdComponents($fieldValue);
                    $fieldValue = Vtiger_Functions::getCRMRecordLabel($refRecordId);
                    break;
                case 'date'                :
                case 'datetime'            :
                case 'currency'            :
                case 'currencyList'        :
                case 'documentsFolder'    :
                case 'multipicklist'    :
                    if ($fieldValue) {
                        $fieldValue = $fieldModel->getDisplayValue($fieldValue);
                    }
                    break;
            }

            $fieldLabel = $fieldModel->get('label');
            $body .= '<br>' . vtranslate($fieldLabel, $moduleName) . " : $fieldValue<br>";
        }
    }

    $body .= '<br>';

    if ($userModel->isAdminUser()) {
        $siteURL = vglobal('site_URL');
        $url = "$siteURL/index.php?parent=Settings&module=LayoutEditor&sourceModule=$moduleName&mode=showDuplicationHandling";
        $here = '<a href="' . $url . '" target="_blank">' . vtranslate('LBL_CLICK_HERE', $moduleName) . '</a>';
        $body .= vtranslate('LBL_DUPLICATION_FAILURE_FOR_ADMIN', $moduleName, $here);
    } else {
        $body .= vtranslate('LBL_DUPLICATION_FAILURE_FOR_NON_ADMIN', $moduleName);
    }

    $mailer->Body = $body;
	$mailer->addAddress($userModel->get('email1'), $userName);
	$mailer->send();
}

function getDuplicatesPreventionMessage($moduleName, $duplicateRecordsList) {
	$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
	$fieldModels = $moduleModel->getFields();

	$recordId = reset($duplicateRecordsList);
	$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
	$recordData = $recordModel->getData();

	$uniqueFields = array();
	foreach ($fieldModels as $fieldName => $fieldModel) {
		$fieldDataType = $fieldModel->getFieldDataType();
		$fieldValue = $recordData[$fieldName];

		if ($fieldDataType === 'reference' && $fieldValue == 0) {
			$fieldValue = '';
		}

		if ($fieldModel->isUniqueField() && $fieldModel->isViewable() && $fieldValue !== '' && $fieldValue !== NULL) {
			$uniqueFields[] = $fieldModel;
		}
	}

	$fieldsString = '';
	$uniqueFieldsCount = php7_count($uniqueFields);
	for($i=0; $i<$uniqueFieldsCount; $i++) {
		$fieldModel = $uniqueFields[$i];
		$fieldLabel = $fieldModel->get('label');
		$fieldsString .= vtranslate($fieldLabel, $moduleName);

		if ($uniqueFieldsCount != 1 && $i == ($uniqueFieldsCount-2)) {
			$fieldsString .= ' '.vtranslate('LBL_AND', $moduleName).' ';
		} else if ($i != ($uniqueFieldsCount-1)) {
			$fieldsString .= ', ';
		}
	}
	$fieldsString = rtrim($fieldsString, ',');

	$singleModuleName = vtranslate('SINGLE_'.$moduleName, $moduleName);
	$translatedModuleName = $singleModuleName;
	$duplicateRecordsCount = php7_count($duplicateRecordsList);
	if ($duplicateRecordsCount > 1) {
		$translatedModuleName = vtranslate($moduleName, $moduleName);
	}
	$message = vtranslate('LBL_DUPLICATES_FOUND_MESSAGE', $moduleName, $singleModuleName, $translatedModuleName, $fieldsString).' ';

	$currentUserModel = Users_Record_Model::getCurrentUserModel();
	if ($currentUserModel->isAdminUser()) {
		$url = "index.php?parent=Settings&module=LayoutEditor&sourceModule=$moduleName&mode=showDuplicationHandling";
		$here = '<a href="'.$url.'" target="_blank" style="color:#15c !important">'.vtranslate('LBL_CLICK_HERE', $moduleName).'</a>';
		$message .= vtranslate('LBL_DUPLICATION_FAILURE_FOR_ADMIN', $moduleName, $here);
	} else {
		$message .= vtranslate('LBL_DUPLICATION_FAILURE_FOR_NON_ADMIN', $moduleName);
	}

	$message .= '<br><br>';
    $message .= vtranslate('LBL_DUPLICATE_RECORD_LISTS', $moduleName, $singleModuleName).'<br>';
	for ($i=0; $i<$duplicateRecordsCount && $i<5; $i++) {
		$dupliRecordId = $duplicateRecordsList[$i];
		$dupliRecordModel = new Vtiger_Record_Model();
		$dupliRecordModel->setId($dupliRecordId)->setModuleFromInstance($moduleModel);
		$message .= '<a href="'.$dupliRecordModel->getDetailViewUrl().'" target="_blank" style="color:#15c !important">'.Vtiger_Functions::getCRMRecordLabel($dupliRecordId).'</a><br>';
	}

	if ($duplicateRecordsCount === 6) {
		$searchParams = array();
		foreach ($uniqueFields as $fieldModel) {
			$fieldName = $fieldModel->getName();
			$fieldValue = $recordData[$fieldName];
			$fieldDataType = $fieldModel->getFieldDataType();
			switch($fieldDataType) {
				case 'reference'		:	$fieldValue = Vtiger_Functions::getCRMRecordLabel($fieldValue);
											break;
				case 'date'				:
				case 'datetime'			:
				case 'currency'			:
				case 'currencyList'		:
				case 'documentsFolder'	:
				case 'multipicklist'	:	if ($fieldValue) {
												$fieldValue = $fieldModel->getDisplayValue($fieldValue);
											}
											break;
			}

			$comparator = 'e';
			if (in_array($fieldDataType, array('date', 'datetime'))) {
				$comparator = 'bw';
				$fieldValue = "$fieldValue,$fieldValue";
			}
			$searchParams[] = array($fieldName, $comparator, $fieldValue);
		}

		$listViewUrl = $moduleModel->getListViewUrl().'&search_params='.json_encode(array($searchParams));
		$message .= "<a href='$listViewUrl' target='_blank' style='color:#15c !important'>+".  strtolower(vtranslate('LBL_MORE', $moduleName)).'</a>';
	}

	return $message;
}

function show()
{
	$input_args = func_get_args();
	if (!empty($input_args)) {
		foreach ($input_args as $input) {
			if (is_array($input)) {
				echo '<table border="1">';
				echo '<tr><th>Key</th><th>Value</th></tr>';
				foreach ($input as $key => $value) {
					echo "<tr><td>$key</td><td>";
					show($value);
					echo "</td></tr>";
				}
				echo "</table>";
			} elseif (is_resource($input) || is_object($input)) {
				echo "<pre>";
				print_r($input);
				echo "</pre>";
			} elseif (is_bool($input)) {
				if ($input) {
					echo "<i>true</i>";
				} else {
					echo "<i>false</i>";
				}
				echo "<br />";
			} else {
				echo $input . "<br />";
			}
		}
	}
}

/**
 * @param $input
 *
 * @return array
 */
function sanitizeRelatedListsActions($input): array
{
	if (is_array($input)) {
		return $input;
	}

	if (is_string($input)) {
		return explode(',', strtoupper($input));
	}

	return [];
}

/**
 * Function to get the field information from module name and field label
 *
 * @param $module
 * @param $label
 *
 * @return mixed|null
 */
function getFieldByReportLabel($module, $label)
{
    $cacheLabel = VTCacheUtils::getReportFieldByLabel($module, $label);

    if ($cacheLabel) {
        return $cacheLabel;
    }

    // this is required so the internal cache is populated or reused.
    getColumnFields($module);
    //lookup all the accessible fields
    $cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
    $label = decode_html($label);

    if (empty($cachedModuleFields)) {
        return null;
    }

    foreach ($cachedModuleFields as $fieldInfo) {
        $fieldLabel = str_replace(' ', '_', $fieldInfo['fieldlabel']);

        $fieldLabel = decode_html($fieldLabel);

        if ($label == $fieldLabel) {
            VTCacheUtils::setReportFieldByLabel($module, $label, $fieldInfo);

            return $fieldInfo;
        }
    }

    return null;
}

/**
 * @param $uitype
 *
 * @return bool
 */
function isReferenceUIType($uitype): bool
{
    static $options = [
        '101',
        '116',
        '117',
        '26',
        '357',
        '50',
        '51',
        '52',
        '53',
        '57',
        '58',
        '59',
        '66',
        '68',
        '73',
        '75',
        '76',
        '77',
        '78',
        '80',
        '81'
    ];

    if (in_array($uitype, $options)) {
        return true;
    }

    return false;
}
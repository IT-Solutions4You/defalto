<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

/*
 * Check for image existence in themes orelse
 * use the common one.
 */
// Let us create cache to improve performance
if(!isset($__cache_vtiger_imagepath)) {
	$__cache_vtiger_imagepath = Array();
}
function vtiger_imageurl($imagename, $themename) {
	global $__cache_vtiger_imagepath;
	if($__cache_vtiger_imagepath[$imagename]) {
		$imagepath = $__cache_vtiger_imagepath[$imagename];
	} else {
		$imagepath = false;
		// Check in theme specific folder
		if(file_exists("themes/$themename/images/$imagename")) {
			$imagepath =  "themes/$themename/images/$imagename";
		} else if(file_exists("themes/images/$imagename")) {
			// Search in common image folder
			$imagepath = "themes/images/$imagename";
		} else {
			// Not found anywhere? Return whatever is sent
			$imagepath = $imagename;
		}
		$__cache_vtiger_imagepath[$imagename] = $imagepath;
	}
	return $imagepath;
}

/**
 * Get module name by id.
 */
function vtlib_getModuleNameById($tabid) {
	global $adb;
	$sqlresult = $adb->pquery("SELECT name FROM vtiger_tab WHERE tabid = ?",array($tabid));
	if($adb->num_rows($sqlresult)) return $adb->query_result($sqlresult, 0, 'name');
	return null;
}

/**
 * Cache the module active information for performance
 */
$__cache_module_activeinfo = Array();

/**
 * Check if module is set active (or enabled)
 */
function vtlib_isModuleActive($module) {
	global $adb, $__cache_module_activeinfo;

	if(in_array($module, vtlib_moduleAlwaysActive())){
		return true;
	}

	if(!isset($__cache_module_activeinfo[$module])) {
		include 'tabdata.php';
        $tabId = vtlib_array($tab_info_array)[$module];
        $presence = vtlib_array($tab_seq_array)[$tabId];
		$__cache_module_activeinfo[$module] = $presence;
	} else {
		$presence = $__cache_module_activeinfo[$module];
	}

	$active = false;
	//Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/7991
	if($presence === 0 || $presence==='0') $active = true; 

	return $active;
}

/**
 * Recreate user privileges files.
 */
function vtlib_RecreateUserPrivilegeFiles() {
	global $adb;
	$userres = $adb->pquery('SELECT id FROM vtiger_users WHERE deleted = 0', array());
	if($userres && $adb->num_rows($userres)) {
		while($userrow = $adb->fetch_array($userres)) {
			createUserPrivilegesfile($userrow['id']);
		}
	}
}

/**
 * Get list module names which are always active (cannot be disabled)
 */
function vtlib_moduleAlwaysActive() {
	$modules = Array (
		'Administration', 'CustomView', 'Settings', 'Users', 'Migration',
		'Utilities', 'uploads', 'Import', 'System', 'com_vtiger_workflow', 'PickList'
	);
	return $modules;
}

/**
 * Setup mandatory (requried) module variable values in the module class.
 */
function vtlib_setup_modulevars($module, $focus)
{
    $checkfor = ['table_name', 'table_index', 'related_tables', 'popup_fields', 'IsCustomModule'];
    foreach ($checkfor as $check) {
        if (!isset($focus->$check)) {
            $focus->$check = __vtlib_get_modulevar_value($module, $check);
        }
    }
}

function __vtlib_get_modulevar_value($module, $varname) {
	$mod_var_mapping =
		Array(
			'Accounts' =>
			Array(
				'IsCustomModule'=>false,
				'table_name'  => 'vtiger_account',
				'table_index' => 'accountid',
				// related_tables variable should define the association (relation) between dependent tables
				// FORMAT: related_tablename => Array ( related_tablename_column[, base_tablename, base_tablename_column] )
				// Here base_tablename_column should establish relation with related_tablename_column
				// NOTE: If base_tablename and base_tablename_column are not specified, it will default to modules (table_name, related_tablename_column)
				'related_tables' => Array(
					'vtiger_accountbillads' => Array ('accountaddressid', 'vtiger_account', 'accountid'),
					'vtiger_accountshipads' => Array ('accountaddressid', 'vtiger_account', 'accountid'),
					'vtiger_accountscf' => Array ('accountid', 'vtiger_account', 'accountid'),
				),
				'popup_fields' => Array('accountname'), // TODO: Add this initialization to all the standard module
			),
			'Contacts' =>
			Array(
				'IsCustomModule'=>false,
				'table_name'  => 'vtiger_contactdetails',
				'table_index' => 'contactid',
				'related_tables'=> Array( 
					'vtiger_account' => Array ('accountid' ),
					//REVIEW: Added these tables for displaying the data into relatedlist (based on configurable fields)
					'vtiger_contactaddress' => Array('contactaddressid', 'vtiger_contactdetails', 'contactid'),
					'vtiger_contactsubdetails' => Array('contactsubscriptionid', 'vtiger_contactdetails', 'contactid'),
					'vtiger_customerdetails' => Array('customerid', 'vtiger_contactdetails', 'contactid'),
					'vtiger_contactscf' => Array('contactid', 'vtiger_contactdetails', 'contactid')
					),
				'popup_fields' => Array ('lastname'),
			),
			'Leads' =>
			Array(
				'IsCustomModule'=>false,
				'table_name'  => 'vtiger_leaddetails',
				'table_index' => 'leadid',
				'related_tables' => Array (
					'vtiger_leadsubdetails' => Array ( 'leadsubscriptionid', 'vtiger_leaddetails', 'leadid' ),
					'vtiger_leadaddress'    => Array ( 'leadaddressid', 'vtiger_leaddetails', 'leadid' ),
					'vtiger_leadscf'    => Array ( 'leadid', 'vtiger_leaddetails', 'leadid' ),
				),
				'popup_fields'=> Array ('lastname'),
			),
			'Campaigns' =>
			Array(
				'IsCustomModule'=>false,
				'table_name'  => 'vtiger_campaign',
				'table_index' => 'campaignid',
				'popup_fields' => Array ('campaignname'),
			),
			'Potentials' =>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_potential',
				'table_index'=> 'potentialid',
				// NOTE: UIType 10 is being used instead of direct relationship from 5.1.0
				//'related_tables' => Array ('vtiger_account' => Array('accountid')),
				'popup_fields'=> Array('potentialname'),
				'related_tables' => Array (
					'vtiger_potentialscf'    => Array ( 'potentialid', 'vtiger_potential', 'potentialid' ),
				),
			),
			'Quotes' =>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_quotes',
				'table_index'=> 'quoteid',
				'related_tables' => Array (
					'vtiger_quotescf' => array('quoteid', 'vtiger_quotes', 'quoteid'),
					'vtiger_account' => Array('accountid')
				),
				'popup_fields'=>Array('subject'),
			),
			'SalesOrder'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_salesorder',
				'table_index'=> 'salesorderid',
				'related_tables'=> Array (
					'vtiger_salesordercf' => array('salesorderid', 'vtiger_salesorder', 'salesorderid'),
					'vtiger_account' => Array('accountid')
				),
				'popup_fields'=>Array('subject'),
			),
			'PurchaseOrder'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_purchaseorder',
				'table_index'=> 'purchaseorderid',
				'related_tables'=> Array (
					'vtiger_purchaseordercf' => Array('purchaseorderid','vtiger_purchaseorder','purchaseorderid'),
					'vtiger_poshipads' => Array('poshipaddressid','vtiger_purchaseorder','purchaseorderid'),
					'vtiger_pobillads' => Array('pobilladdressid','vtiger_purchaseorder','purchaseorderid'),
				),
				'popup_fields'=>Array('subject'),
			),
			'Invoice'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_invoice',
				'table_index'=> 'invoiceid',
				'popup_fields'=> Array('subject'),
				'related_tables'=> Array( 
					'vtiger_invoicecf' => Array('invoiceid', 'vtiger_invoice', 'invoiceid'),
					'vtiger_invoiceshipads' => Array('invoiceshipaddressid','vtiger_invoice','invoiceid'),
					'vtiger_invoicebillads' => Array('invoicebilladdressid','vtiger_invoice','invoiceid'),
					),
			),
			'HelpDesk'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_troubletickets',
				'table_index'=> 'ticketid',
				'related_tables'=> Array ('vtiger_ticketcf' => Array('ticketid')),
				'popup_fields'=> Array('ticket_title')
			),
			'Faq'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_faq',
				'table_index'=> 'id',
				'related_tables'=> Array ('vtiger_faqcf' => Array('faqid', 'vtiger_faq', 'id'))
			),
			'Documents'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_notes',
				'table_index'=> 'notesid',
				'related_tables' => Array(
					'vtiger_notescf' => Array('notesid', 'vtiger_notes', 'notesid')
				),
			),
			'Products'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_products',
				'table_index'=> 'productid',
				'related_tables' => Array(
					'vtiger_productcf' => Array('productid')
				),
				'popup_fields'=> Array('productname'),
			),
			'PriceBooks'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_pricebook',
				'table_index'=> 'pricebookid',
			),
			'Vendors'=>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_vendor',
				'table_index'=> 'vendorid',
				'popup_fields'=>Array('vendorname'),
				'related_tables'=> Array( 
					'vtiger_vendorcf' => Array('vendorid', 'vtiger_vendor', 'vendorid')
					),
			),
			'Project' => 
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_project',
				'table_index'=> 'projectid',
				'related_tables'=> Array( 
					'vtiger_projectcf' => Array('projectid', 'vtiger_project', 'projectid')
					),
			),
			'ProjectMilestone' =>
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_projectmilestone',
				'table_index'=> 'projectmilestoneid',
				'related_tables'=> Array( 
					'vtiger_projectmilestonecf' => Array('projectmilestoneid', 'vtiger_projectmilestone', 'projectmilestoneid')
					),
			),
			'ProjectTask' => 
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_projecttask',
				'table_index'=> 'projecttaskid',
				'related_tables'=> Array( 
					'vtiger_projecttaskcf' => Array('projecttaskid', 'vtiger_projecttask', 'projecttaskid')
					),
			),
			'Services' => 
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_service',
				'table_index'=> 'serviceid',
				'related_tables'=> Array( 
					'vtiger_servicecf' => Array('serviceid')
					),
			),
			'ServiceContracts' => 
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_servicecontracts',
				'table_index'=> 'servicecontractsid',
				'related_tables'=> Array( 
					'vtiger_servicecontractscf' => Array('servicecontractsid')
					),
			),
			'Assets' => 
			Array(
				'IsCustomModule'=>false,
				'table_name' => 'vtiger_assets',
				'table_index'=> 'assetsid',
				'related_tables'=> Array( 
					'vtiger_assetscf' => Array('assetsid')
					),
			)
	);
	if(array_key_exists($module,$mod_var_mapping) && array_key_exists($varname, $mod_var_mapping[$module])) {
		return $mod_var_mapping[$module][$varname];
	} else {
		if ($varname != 'related_tables' || !$module) {
			return '';
		}
		$focus = CRMEntity::getInstance($module);
		$customFieldTable = isset($focus->customFieldTable) ? $focus->customFieldTable: null;
		if (!empty($customFieldTable)) {
			$returnValue = array();
			$returnValue['related_tables'][$customFieldTable[0]] = array($customFieldTable[1], $focus->table_name, $focus->table_index);

			return $returnValue['related_tables'];
		}
	}
}

/**
 * Convert given text input to singular.
 */
function vtlib_tosingular($text) {
	$lastpos = strripos($text, 's');
	if($lastpos == strlen($text)-1)
		return substr($text, 0, -1);
	return $text;
}

/**
 * Get picklist values that is accessible by all roles.
 */
function vtlib_getPicklistValues_AccessibleToAll($field_columnname) {
	global $adb;

	$columnname =  $adb->sql_escape_string($field_columnname);
	$tablename = "vtiger_$columnname";

	// Gather all the roles (except H1 which is organization role)
	$roleres = $adb->pquery("SELECT roleid FROM vtiger_role WHERE roleid != 'H1'", array());
	$roleresCount= $adb->num_rows($roleres);
	$allroles = Array();
	if($roleresCount) {
		for($index = 0; $index < $roleresCount; ++$index)
			$allroles[] = $adb->query_result($roleres, $index, 'roleid');
	}
	sort($allroles);

	// Get all the picklist values associated to roles (except H1 - organization role).
	$picklistres = $adb->pquery(
		"SELECT $columnname as pickvalue, roleid FROM $tablename
		INNER JOIN vtiger_role2picklist ON $tablename.picklist_valueid=vtiger_role2picklist.picklistvalueid
		WHERE roleid != 'H1'", array());

	$picklistresCount = $adb->num_rows($picklistres);

	$picklistval_roles = Array();
	if($picklistresCount) {
		for($index = 0; $index < $picklistresCount; ++$index) {
			$picklistval = $adb->query_result($picklistres, $index, 'pickvalue');
			$pickvalroleid=$adb->query_result($picklistres, $index, 'roleid');
			$picklistval_roles[$picklistval][] = $pickvalroleid;
		}
	}
	// Collect picklist value which is associated to all the roles.
	$allrolevalues = Array();
	foreach($picklistval_roles as $picklistval => $pickvalroles) {
		sort($pickvalroles);
		$diff = array_diff($pickvalroles,$allroles);
		if(empty($diff)) $allrolevalues[] = $picklistval;
	}

	return $allrolevalues;
}

/**
 * Get all picklist values for a non-standard picklist type.
 */
function vtlib_getPicklistValues($field_columnname) {
	global $adb;
	$picklistvalues = Vtiger_Cache::get('PicklistValues', $field_columnname);
	if (!$picklistvalues) {
		$columnname =  $adb->sql_escape_string($field_columnname);
		$tablename = "vtiger_$columnname";

		$picklistres = $adb->pquery("SELECT $columnname as pickvalue FROM $tablename", array());

		$picklistresCount = $adb->num_rows($picklistres);

		$picklistvalues = Array();
		if($picklistresCount) {
			for($index = 0; $index < $picklistresCount; ++$index) {
				$picklistvalues[] = $adb->query_result($picklistres, $index, 'pickvalue');
			}
		}
	}
	return $picklistvalues;
}

/**
 * Check if give path is writeable.
 */
function vtlib_isWriteable($path) {
	if(is_dir($path)) {
		return vtlib_isDirWriteable($path);
	} else {
		return is_writable($path);
	}
}

/**
 * Check if given directory is writeable.
 * NOTE: The check is made by trying to create a random file in the directory.
 */
function vtlib_isDirWriteable($dirpath) {
	if(is_dir($dirpath)) {
		do {
			$tmpfile = 'vtiger' . time() . '-' . rand(1,1000) . '.tmp';
			// Continue the loop unless we find a name that does not exists already.
			$usefilename = "$dirpath/$tmpfile";
			if(!file_exists($usefilename)) break;
		} while(true);
		$fh = @fopen($usefilename,'a');
		if($fh) {
			fclose($fh);
			unlink($usefilename);
			return true;
		}
	}
	return false;
}

/** HTML Purifier global instance */
$__htmlpurifier_instance = false;
/**
 * Purify (Cleanup) malicious snippets of code from the input
 *
 * @param string|array $value
 * @param Boolean $ignore Skip cleaning of the input
 * @return String
 */
function vtlib_purify($input, $ignore = false) {
    global $__htmlpurifier_instance, $root_directory, $default_charset;

    static $purified_cache = array();
    $value = $input;

	$encryptInput = null;
    if (!is_array($input)) {
        $encryptInput = hash('sha256',$input);
        if (array_key_exists($encryptInput, $purified_cache)) {
            $value = $purified_cache[$encryptInput];
            //to escape cleaning up again
            $ignore = true;
        }
    }
    $use_charset = $default_charset;
    $use_root_directory = $root_directory;


    if (!$ignore) {
        // Initialize the instance if it has not yet done
        if ($__htmlpurifier_instance == false) {
            if (empty($use_charset))
                $use_charset = 'UTF-8';
            if (empty($use_root_directory))
                $use_root_directory = dirname(__FILE__) . '/../..';

            $allowedSchemes = array(
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                'data' => true
            );

			require_once __DIR__ . '/../../vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';

            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', $use_charset);
            $config->set('Cache.SerializerPath', "$use_root_directory/test/vtlib");
            $config->set('CSS.AllowTricky', true);
            $config->set('URI.AllowedSchemes', $allowedSchemes);
            $config->set('Attr.EnableID', true);

            $__htmlpurifier_instance = new HTMLPurifier($config);
        }
        if ($__htmlpurifier_instance) {
            // Composite type
            if (is_array($input)) {
                $value = array();
                foreach ($input as $k => $v) {
                    $value[$k] = vtlib_purify($v, $ignore);
                }
            } else { // Simple type
                $value = $__htmlpurifier_instance->purify($input);
                $value = purifyHtmlEventAttributes($value, true);
            }
        }
        if ($encryptInput != null) {
			$purified_cache[$encryptInput] = $value;
    	}
	}

	if (is_array($value)) {
        $value = str_replace_json('&amp;', '&', $value);
    } else {
		$value = str_replace('&amp;', '&', $value);
	}

    return $value;
}

/**
 * Function to replace values in multidimensional array (str_replace will support only one level of array)
 *
 * @param $search
 * @param $replace
 * @param $subject
 *
 * @return mixed
 */
function str_replace_json($search, $replace, $subject)
{
    return json_decode(str_replace($search, $replace, json_encode($subject)), true);
}

/**
 * To purify malicious html event attributes
 * @param <String> $value
 * @return <String>
 */
function purifyHtmlEventAttributes($value,$replaceAll = false){
    $tmp_markers = $office365ImageMarkers = [];
    $value = Vtiger_Functions::strip_base64_data($value, true, $tmp_markers);
    $value = Vtiger_Functions::stripInlineOffice365Image($value, true, $office365ImageMarkers);
    $tmp_markers = array_merge($tmp_markers, $office365ImageMarkers);

    // remove malicious html attributes with its value.
    if ($replaceAll) {
        $value = preg_replace('/\b(alert|on\w+)\s*\([^)]*\)|\s*(?: on\w+)=(".*?"|\'.*?\'|[^\'">\s]+)\s*/', '', $value);
        //remove script tag with contents
        $value = purifyScript($value);
        //purify javascript alert from the tag contents
        $value = purifyJavascriptAlert($value);

    } else {
        $htmlEventAttributes = 'onerror|onblur|onchange|oncontextmenu|onfocus|oninput|oninvalid|onresize|onauxclick|oncancel|oncanplay|oncanplaythrough|' .
            'onreset|onsearch|onselect|onsubmit|onkeydown|onkeypress|onkeyup|onclose|oncuechange|ondurationchange|onemptied|onended|' .
            'onclick|ondblclick|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragexit|onformdata|onloadeddata|onloadedmetadata|' .
            'ondragstart|ondrop|onmousedown|onmousemove|onmouseout|onmouseover|onmouseenter|onmouseleave|onpause|onplay|onplaying|' .
            'onmouseup|onmousewheel|onscroll|onwheel|oncopy|oncut|onpaste|onload|onprogress|onratechange|onsecuritypolicyviolation|' .
            'onselectionchange|onabort|onselectstart|onstart|onfinish|onloadstart|onshow|onreadystatechange|onseeked|onslotchange|' .
            'onseeking|onstalled|onsubmit|onsuspend|ontimeupdate|ontoggle|onvolumechange|onwaiting|onwebkitanimationend|onstorage|' .
            'onwebkitanimationiteration|onwebkitanimationstart|onwebkittransitionend|onafterprint|onbeforeprint|onbeforeunload|' .
            'onhashchange|onlanguagechange|onmessage|onmessageerror|onoffline|ononline|onpagehide|onpageshow|onpopstate|onunload|' .
            'onrejectionhandled|onunhandledrejection|onloadend|onpointerenter|ongotpointercapture|onlostpointercapture|onpointerdown|' .
            'onpointermove|onpointerup|onpointercancel|onpointerover|onpointerout|onpointerleave|onactivate|onafterscriptexecute|' .
            'onanimationcancel|onanimationend|onanimationiteration|onanimationstart|onbeforeactivate|onbeforedeactivate|onbeforescriptexecute|' .
            'onbegin|onbounce|ondeactivate|onend|onfocusin|onfocusout|onrepeat|ontransitioncancel|ontransitionend|ontransitionrun|' .
            'ontransitionstart|onbeforecopy|onbeforecut|onbeforepaste|onfullscreenchange|onmozfullscreenchange|onpointerrawupdate|' .
            'ontouchend|ontouchmove|ontouchstart';

        if (preg_match('/\s*(' . $htmlEventAttributes . ')\s*=/i', $value)) {
            $value = str_replace('=', '&equals;', $value);
        }
    }

    //Replace any strip-markers
	if ($tmp_markers){
		$keys = array();
		$values = array();
		foreach ($tmp_markers as $k => $v){
			$keys[] = $k;
			$values[] = $v;
		}
		$value = str_replace($keys, $values, $value);
	}
	
    return $value;
}

//function to remove script tag and its contents
function purifyScript($value){
    $scriptRegex = '/(&.*?lt;|<)script[\w\W]*?(>|&.*?gt;)[\w\W]*?(&.*?lt;|<)\/script(>|&.*?gt;|\s)/i';
    $value = preg_replace($scriptRegex,'',$value);
    return $value;
}



//function to purify html tag having 'javascript:' string by removing the tag contents.
function purifyJavascriptAlert($value){
    $restrictJavascriptInTags = array('a','iframe','object','embed','animate','set','base','button','input','form');
    
    foreach($restrictJavascriptInTags as $tag){
        
        if(!empty($value)){
            $originalValue = $value;
        }
        
        // skip javascript: contents check if tag is not available,as javascript: regex will cause performace issue if the contents will be large 
        if (preg_match_all('/(&.*?lt;|<)'.$tag.'[^>]*?(>|&.*?gt;)/i', $value,$matches)) {
            $javaScriptRegex = '/(&.*?lt;|<).?'.$tag.' [^>]*(j[\s]?a[\s]?v[\s]?a[\s]?s[\s]?c[\s]?r[\s]?i[\s]?p[\s]?t[\s]*[=&%#:])[^>]*?(>|&.*?gt;)/i';
            foreach($matches[0] as $matchedValue){
                //strict check addded - if &tab;/&newLine added in the above tags we are replacing it to spaces.
                $purifyContent = preg_replace('/&NewLine;|&amp;NewLine;|&Tab;|&amp;Tab;|\t/i',' ',$matchedValue);
                $purifyContent = preg_replace($javaScriptRegex,"<$tag>",$purifyContent);
                $value = str_replace($matchedValue, $purifyContent, $value);
                
                /*
                * if the content length will more. In that case, preg_replace will fail and return Null due to PREG_BACKTRACK_LIMIT_ERROR error
                * so skipping the validation and reseting the value - TODO
                */
               if (preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) {
                   $value = $originalValue;
                   return $value;
               }
            }        
        }
    }
    
    return $value;
}

/**
 * Function to return the valid SQl input.
 * @param <String> $string
 * @param <Boolean> $skipEmpty Skip the check if string is empty.
 * @return <String> $string/false
 */
function vtlib_purifyForSql($string, $skipEmpty=true) {
	$pattern = "/^[_a-zA-Z0-9.:\-]+$/";
	if ((empty($string) && $skipEmpty) || preg_match($pattern, $string)) {
		return $string;
	}
	return false;
}

function vtlib_mime_content_type($filename) {
	return Vtiger_Functions::mime_content_type($filename);
}

/**
 * PHP Strict helpers.
 */
require_once 'vtlib/Vtiger/Utils/GuardedArray.php';
function vtlib_array($data = null)
{
    return new Vtiger_GuardedArray($data);
}

function php7_compat_ereg($pattern, $str, $ignore_case=false) {
	$regex = '/'. preg_replace('/\//', '\\/', $pattern) .'/' . ($ignore_case ? 'i': '');
	return preg_match($regex, $str);
}

if (!function_exists('ereg')) { function ereg($pattern, $str) { return php7_compat_ereg($pattern, $str); } }
if (!function_exists('eregi')) { function eregi($pattern, $str) { return php7_compat_ereg($pattern, $str, true); } }

/**
 * Returns count of elements in $value. Safe for null (0) and uncountable variable types (1).
 *
 * @param $value
 *
 * @return int
 */
function php7_count($value): int
{
    if (is_null($value)) {
        return 0;
    }

    if (!is_countable($value)) {
        return 1;
    }

    return count($value);
}

/**
 * Remove content within quotes (single/double/unbalanced)
 * Helpful to keep away quote-injection xss attacks in the templates.
 */
function vtlib_strip_quoted($input)
{
    if (is_null($input)) {
        return null;
    }

    $output = $input;
    /*
     * Discard anything in "double-quoted until 'you' find next double quote"
     * or discard anything in 'single quoted until "you" find next single quote'
     */
    $qchar = '"';
    $idx = strpos($input, $qchar);

    if ($idx === false) { // no double-quote, find single-quote
        $qchar = "'";
        $idx = strpos($input, $qchar);
    }

    if ($idx !== false) {
        $output = substr($input, 0, $idx);
        $idx = strpos($input, $qchar, $idx + 1);

        if ($idx === false) {
            // unbalanced - eat all.
            $idx = strlen($input) - 1;
        }

        $input = substr($input, $idx + 1);
        $output .= vtlib_strip_quoted($input);
    }

    return $output;
}

function php7_trim($str) {
    // PHP 8.x marks as deprecated
    return $str == null ? $str : trim($str);
}

function php7_htmlentities($str) {
    // PHP 8.x marks as deprecated
    return $str == null ? $str : htmlentities($str);
}
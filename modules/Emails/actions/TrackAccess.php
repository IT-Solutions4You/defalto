<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false);

//Opensource fix for tracking email access count
chdir(__DIR__ . '/../../../');

require_once 'vendorCheck.php';
require_once 'vendor/autoload.php';
require_once 'include/utils/utils.php';

vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport ('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

class Emails_TrackAccess_Action extends Vtiger_Action_Controller {

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		return $permissions;
	}
	
	public function checkPermission(Vtiger_Request $request) {
		return parent::checkPermission($request);
	}
	
	public function process(Vtiger_Request $request) {
		if (vglobal('application_unique_key') !== $request->get('applicationKey')) {
			exit;
		}
		if((strpos($_SERVER['HTTP_REFERER'], vglobal('site_URL')) !== false)) {
			exit;
		}

		global $current_user;
		$current_user = Users::getActiveAdminUser();
		
		if($request->get('method') == 'click') {
			$this->clickHandler($request);
		}else{
			$parentId = $request->get('parentId');
			$recordId = $request->get('record');

			if ($parentId && $recordId) {
				$recordModel = Emails_Record_Model::getInstanceById($recordId);
				$recordModel->updateTrackDetails($parentId);
				Vtiger_ShortURL_Helper::sendTrackerImage();
			}
		}
	}
	
	public function clickHandler(Vtiger_Request $request) {
		$parentId = $request->get('parentId');
		$recordId = $request->get('record');

		if ($parentId && $recordId) {
			$recordModel = Emails_Record_Model::getInstanceById($recordId);
			$recordModel->trackClicks($parentId);
		}
		
		$redirectUrl = $request->get('redirectUrl');
		if(!empty($redirectUrl)) {
			return Vtiger_Functions::redirectUrl(rawurldecode($redirectUrl));
		}
	}
}

$track = new Emails_TrackAccess_Action();
$track->process(new Vtiger_Request($_REQUEST));

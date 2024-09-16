<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Home_Module_Model extends Vtiger_Module_Model {

    protected string $fontIcon = 'fa fa-dashboard';
	/**
	 * Function returns the default view for the Home module
	 * @return <String>
	 */
	public function getDefaultViewName() {
		return 'DashBoard';
	}

	/**
	 * Function returns latest comments across CRM
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel, $user, $dateFilter='') {
		$db = PearDatabase::getInstance();

		$sql = 'SELECT vtiger_modcomments.*,vtiger_crmentity.setype AS setype,vtiger_crmentity.createdtime AS createdtime, vtiger_crmentity.smownerid AS smownerid,
				crmentity2.crmid AS parentId, crmentity2.setype AS parentModule FROM vtiger_modcomments
				INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid
				AND vtiger_crmentity.deleted = 0
				INNER JOIN vtiger_crmentity crmentity2 ON vtiger_modcomments.related_to = crmentity2.crmid
				AND crmentity2.deleted = 0 
				INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid';

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$params = array();

		if($user === 'all') {
			if(!$currentUser->isAdminUser()){
				$accessibleUsers = array_keys($currentUser->getAccessibleUsers());
				$nonAdminAccessQuery = Users_Privileges_Model::getNonAdminAccessControlQuery('ModComments');
				$sql .= $nonAdminAccessQuery;
				$sql .= ' AND userid IN('.  generateQuestionMarks($accessibleUsers).')';
				$params = array_merge($params,$accessibleUsers);
			}
		}else{
			$sql .= ' AND userid = ?';
			$params[] = $user;
		}
		//handling date filter for history widget in home page
		if(!empty($dateFilter)) {
			$sql .= ' AND vtiger_modtracker_basic.changedon BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}

		$sql .= ' ORDER BY vtiger_crmentity.crmid DESC LIMIT ?, ?';
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit();
		$result = $db->pquery($sql,$params);
		
		$noOfRows = $db->num_rows($result);
		//setting up the count of records before checking permissions in history
		$pagingModel->set('historycount', $noOfRows);
		$comments = array();
		for($i=0; $i<$noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['related_to'])){
				$commentModel = Vtiger_Record_Model::getCleanInstance('ModComments');
				$commentModel->setData($row);
                $commentModel->set('commentcontent', $commentModel->getParsedContent());
				$comments[] = $commentModel;
			}
		}

		return $comments;
	}

	/**
	 * Function returns comments and recent activities across CRM
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type='', $userId='', $dateFilter='') {
		if(!$userId)	$userId	= 'all';
		if(!$type)		$type	= 'all';
		//TODO: need to handle security
		$comments = array();
		if($type == 'all' || $type == 'comments') {
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments'); 
			if($modCommentsModel->isPermitted('DetailView')){
				$comments = $this->getComments($pagingModel, $userId, $dateFilter);
			}
			if($type == 'comments') {
				return $comments;
			}
		}
		$db = PearDatabase::getInstance();
		$params = array();
		$sql = 'SELECT vtiger_modtracker_basic.*
				FROM vtiger_modtracker_basic
				INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
				AND module NOT IN ("ModComments","Users") ';

		$currentUser = Users_Record_Model::getCurrentUserModel();
		if($userId === 'all') {
			if(!$currentUser->isAdminUser()) {
				$accessibleUsers = array_keys($currentUser->getAccessibleUsers());
				$sql .= ' AND whodid IN ('.  generateQuestionMarks($accessibleUsers).')';
				$params = array_merge($params, $accessibleUsers);
			}
		}else{
			$sql .= ' AND whodid = ?';
			$params[] = $userId;
		}
		//handling date filter for history widget in home page
		if(!empty($dateFilter)) {
			$sql .= ' AND vtiger_modtracker_basic.changedon BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$sql .= ' ORDER BY vtiger_modtracker_basic.id DESC LIMIT ?, ?';
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit();
                
		//As getComments api is used to get comment infomation,no need of getting
		//comment information again,so avoiding from modtracker
		$result = $db->pquery($sql,$params);
                
		$activites = array();
		$noOfRows = $db->num_rows($result);
		//set the records count before checking permissions and unsetting it
		//If updates count more than comments count, this count should consider
		if($pagingModel->get('historycount') < $noOfRows) {
			$pagingModel->set('historycount', $noOfRows);
		}
		for($i=0; $i<$noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$moduleName = $row['module'];
			$recordId = $row['crmid'];

			if(Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId) && isRecordExists($recordId)){
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($recordId, $moduleName);
				$activites[] = $modTrackerRecorModel;
			}
		}

		$history = array_merge($activites, $comments);
		
		$dateTime = array();
		foreach($history as $model) {
			if(get_class($model) == 'ModComments_Record_Model') {
				$time = $model->get('createdtime');
			} else {
				$time = $model->get('changedon');
			}
			$dateTime[] = $time;
		}

		if(!empty($history)) {
			array_multisort($dateTime,SORT_DESC,SORT_STRING,$history);
			return $history;
		}
		return false;
	}

    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames() {
        return array();
    }
}

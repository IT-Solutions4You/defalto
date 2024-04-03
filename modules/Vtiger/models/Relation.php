<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Relation_Model extends Vtiger_Base_Model{

	protected $parentModule = false;
	protected $relatedModule = false;

	protected $relationType = false;

	//one to many
	const RELATION_DIRECT = 1;

	//Many to many and many to one
	const RELATION_INDIRECT = 2;

	/**
	 * Function returns the relation id
	 * @return <Integer>
	 */
	public function getId(){
		return $this->get('relation_id');
	}

	/**
	 * Function sets the relation's parent module model
	 * @param <Vtiger_Module_Model> $moduleModel
	 * @return Vtiger_Relation_Model
	 */
	public function setParentModuleModel($moduleModel){
		$this->parentModule = $moduleModel;
		return $this;
	}

	/**
	 * Function that returns the relation's parent module model
	 * @return <Vtiger_Module_Model>
	 */
	public function getParentModuleModel(){
		if(empty($this->parentModule)){
			$this->parentModule = Vtiger_Module_Model::getInstance($this->get('tabid'));
		}
		return $this->parentModule;
	}

	public function getRelationModuleModel(){
		if(empty($this->relatedModule)){
			$this->relatedModule = Vtiger_Module_Model::getInstance($this->get('related_tabid'));
		}
		return $this->relatedModule;
	}

	public function getParentModuleName() {
		return $this->getParentModuleModel()->getName();
	}

	public function getRelationModuleName() {
		$relationModuleName = $this->get('relatedModuleName');
		if(!empty($relationModuleName)) {
			return $relationModuleName;
		}
		return $this->getRelationModuleModel()->getName();
	}

	public function getListUrl($parentRecordModel) {
		return 'module='.$this->getParentModuleModel()->get('name').'&relatedModule='.$this->get('modulename').
				'&view=Detail&record='.$parentRecordModel->getId().'&mode=showRelatedList&relationId='.$this->get('relation_id');
	}

	public function setRelationModuleModel($relationModel){
		$this->relatedModule = $relationModel;
		return $this;
	}

	public function isActionSupported($actionName){
		$actionName = strtolower($actionName);
		$actions = $this->getActions();
		foreach($actions as $action) {
			if(strcmp(strtolower($action), $actionName)== 0){
				return true;
			}
		}
		return false;
	}

	public function isSelectActionSupported() {
		return $this->isActionSupported('select');
	}

	public function isAddActionSupported() {
		return $this->isActionSupported('add');
	}

	public function getActions(){
		$actionString = $this->get('actions');

		$label = $this->get('label');

		return explode(',', $actionString);
	}

	public function getQuery($parentRecord, $actions=false){
		$parentModuleModel = $this->getParentModuleModel();
		$relatedModuleModel = $this->getRelationModuleModel();
		$parentModuleName = $parentModuleModel->getName();
		$relatedModuleName = $relatedModuleModel->getName();
		$functionName = $this->get('name');
		if ($relatedModuleName == "ModComments") {
			$focus = CRMEntity::getInstance($relatedModuleName);
			$query = $focus->$functionName($parentRecord->getId());
		} else {
			$query = $parentModuleModel->getRelationQuery($parentRecord->getId(), $functionName, $relatedModuleModel, $this->getId());
		}

		return $query;
	}

	public function addRelation($sourcerecordId, $destinationRecordId) {
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		relateEntities($sourceModuleFocus, $sourceModuleName, $sourcerecordId, $destinationModuleName, $destinationRecordId);
	}

	public function deleteRelation($sourceRecordId, $relatedRecordId){
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
		DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}

	public function isDirectRelation() {
		return ($this->getRelationType() == self::RELATION_DIRECT);
	}

	public function getRelationType(){
		if(empty($this->relationType)){
			$this->relationType = self::RELATION_INDIRECT;
			if ($this->getRelationField()) {
				$this->relationType = self::RELATION_DIRECT;
			}
		}
		return $this->relationType;
	}

	/**
	 * Function which will specify whether the relation is editable
	 * @return <Boolean>
	 */
	public function isEditable() {
		return $this->getRelationModuleModel()->isPermitted('EditView');
	}

	/**
	 * Function which will specify whether the relation is deletable
	 * @return <Boolean>
	 */
	public function isDeletable() {
		return $this->getRelationModuleModel()->isPermitted('Delete');
	}

	public static function getInstance($parentModuleModel, $relatedModuleModel, $label=false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename FROM vtiger_relatedlists
					INNER JOIN vtiger_tab on vtiger_tab.tabid = vtiger_relatedlists.related_tabid AND vtiger_tab.presence != 1
					WHERE vtiger_relatedlists.tabid = ? AND related_tabid = ?';
		$params = array($parentModuleModel->getId(), $relatedModuleModel->getId());

		if(!empty($label)) {
			$query .= ' AND label = ?';
			$params[] = decode_html($label);
		}

		$result = $db->pquery($query, $params);
		if($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, 0);
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			return $relationModel;
		}
		return false;
	}

	public static function getInstanceByModuleName($moduleName, $relModuleName) {
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$relModuleModel = Vtiger_Module_Model::getInstance($relModuleName);
		return self::getInstance($moduleModel, $relModuleModel);
	}

	public static function getAllRelations($parentModuleModel, $selected = true, $onlyActive = true) {
		$db = PearDatabase::getInstance();
		$moduleRelations = Vtiger_Cache::get('moduleRelations',array($parentModuleModel->getName(),$selected,$onlyActive));
		if($moduleRelations){
			return $moduleRelations;
		}

		$skipReltionsList = array('get_history');
		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename FROM vtiger_relatedlists 
					INNER JOIN vtiger_tab on vtiger_relatedlists.related_tabid = vtiger_tab.tabid
					WHERE vtiger_relatedlists.tabid = ? AND related_tabid != 0';

		if ($selected) {
			$query .= ' AND vtiger_relatedlists.presence <> 1';
		}
		if($onlyActive){
			$query .= ' AND vtiger_tab.presence <> 1 ';
		}
		$query .= ' AND vtiger_relatedlists.name NOT IN ('.generateQuestionMarks($skipReltionsList).') ORDER BY sequence'; // TODO: Need to handle entries that has related_tabid 0

		$result = $db->pquery($query, array($parentModuleModel->getId(), $skipReltionsList));

		$relationModels = array();
		$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			//$relationModuleModel = Vtiger_Module_Model::getCleanInstance($moduleName);
			// Skip relation where target module does not exits or is no permitted for view.
			if (!Users_Privileges_Model::isPermitted($row['modulename'],'DetailView')) {
				continue;
			}
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->set('relatedModuleName',$row['modulename']);
			$relationModels[] = $relationModel;
		}
		Vtiger_Cache::set('moduleRelations',array($parentModuleModel->getName(),$selected,$onlyActive),$relationModels);
		return $relationModels;
	}

    public static function getInstanceFromId($relationId) {
		$db = PearDatabase::getInstance();
		$query = "SELECT * FROM vtiger_relatedlists WHERE relation_id=?";
		$result = $db->pquery($query, array($relationId));
		$relationModel = false;
		if ($db->num_rows($result) > 0) {
			$row = $db->query_result_rowdata($result, 0);
			$parentModuleId = $row['tabid'];
			$relationModuleId = $row['related_tabid'];
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleId);
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relationModuleId);
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
		}
		return $relationModel;
	}

	public static function getInstanceFromRelationFied($relationFieldId) {
		$db = PearDatabase::getInstance();
		$query = "SELECT * FROM vtiger_relatedlists WHERE relationfieldid=?";
		$result = $db->pquery($query, array($relationFieldId));
		$relationModel = false;
		if ($db->num_rows($result) > 0) {
			$row = $db->query_result_rowdata($result, 0);
			$parentModuleId = $row['tabid'];
			$relationModuleId = $row['related_tabid'];
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleId);
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relationModuleId);
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
		}
		return $relationModel;
	}

	/**
	 * Function to get relation field for relation module and parent module
	 * @return Vtiger_Field_Model
	 */
	public function getRelationField() {
		$db = PearDatabase::getInstance();
		$relationField = $this->get('relationField');
		if (!$relationField) {
			$relationField = false;

			$relationFieldSql = "SELECT relationfieldid FROM vtiger_relatedlists WHERE relation_id=?";
			$result = $db->pquery($relationFieldSql,array($this->getId()));
			if($db->num_rows($result) > 0) {
				$relationFieldId = $db->query_result($result,0,'relationfieldid');
				$relationField = Vtiger_Field_Model::getInstance($relationFieldId);
			}

		}
		return $relationField;
	}
    
    public static  function updateRelationSequenceAndPresence($relatedInfoList, $sourceModuleTabId) {
		$db = PearDatabase::getInstance();
		$query = 'UPDATE vtiger_relatedlists SET sequence=CASE ';
		$relation_ids = array();
		$paramArray = array();
		foreach($relatedInfoList as $relatedInfo){
			$relation_id = $relatedInfo['relation_id'];
			$relation_ids[] = $relation_id;
			$sequence = $relatedInfo['sequence'];
			$presence = $relatedInfo['presence'];
			array_push($paramArray, $relation_id);
			array_push($paramArray, $sequence);
			$query .= ' WHEN relation_id=? THEN ?';
		}
		$query.= ' END , ';
		$query.= ' presence = CASE ';
		foreach($relatedInfoList as $relatedInfo){
			$relation_id = $relatedInfo['relation_id'];
			$relation_ids[] = $relation_id;
			$sequence = $relatedInfo['sequence'];
			$presence = $relatedInfo['presence'];
			array_push($paramArray, $relation_id);
			array_push($paramArray, $presence);
			$query .= ' WHEN relation_id=? THEN ?';
		}
		array_push($paramArray, $sourceModuleTabId);
		$resultArray = array_merge($paramArray, $relation_ids);
		$query .= ' END WHERE tabid=? AND relation_id IN ('.  generateQuestionMarks($relation_ids).')';
		$result = $db->pquery($query, $resultArray);
	}
	
	public function isActive() {
		return $this->get('presence') == 0 ? true : false;
	}
}

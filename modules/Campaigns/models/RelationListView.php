<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_RelationListView_Model extends Vtiger_RelationListView_Model {

	/**
	 * Function to get list of record models in this relation
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <array> List of record models <Vtiger_Record_Model>
	 */
	public function getEntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		$emailEnabledModulesInfo = $relationModel->getEmailEnabledModulesInfoForDetailView();

		if (array_key_exists($relatedModuleName, $emailEnabledModulesInfo) && $relatedRecordModelsList) {
			$fieldName = $emailEnabledModulesInfo[$relatedModuleName]['fieldName'];
			$tableName = $emailEnabledModulesInfo[$relatedModuleName]['tableName'];

			$db = PearDatabase::getInstance();
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);

			$query = "SELECT campaignrelstatus, $fieldName FROM $tableName
						INNER JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = $tableName.campaignrelstatusid
						WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).") AND campaignid = ?";
			array_push($relatedRecordIdsList, $parentRecordModel->getId());

			$result = $db->pquery($query, $relatedRecordIdsList);
			$numOfrows = $db->num_rows($result);

			for($i=0; $i<$numOfrows; $i++) {
				$recordId = $db->query_result($result, $i, $fieldName);
				$relatedRecordModel = $relatedRecordModelsList[$recordId];

				$relatedRecordModel->set('status', $db->query_result($result, $i, 'campaignrelstatus'));
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
}

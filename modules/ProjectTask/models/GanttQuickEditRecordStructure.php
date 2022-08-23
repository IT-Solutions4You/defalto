<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class ProjectTask_GanttQuickEditRecordStructure_Model extends Vtiger_QuickCreateRecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$moduleModel = $this->getModule();

		$fieldModelList = $moduleModel->getQuickCreateFields();

		// end date should be there in the quick edit for gantt chart
		$fieldModelList['enddate'] = $moduleModel->getField('enddate');

		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$recordModelFieldValue = $recordModel->get($fieldName);
			if (!empty($recordModelFieldValue)) {
				$fieldModel->set('fieldvalue', $recordModelFieldValue);
			} else if ($fieldName == 'eventstatus') {
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$defaulteventstatus = $currentUserModel->get('defaulteventstatus');
				$fieldValue = $defaulteventstatus;
				if (!$defaulteventstatus || $defaulteventstatus == 'Select an Option') {
					$fieldValue = $fieldModel->getDefaultFieldValue();
				}
				$fieldModel->set('fieldvalue', $fieldValue);
			} else if ($fieldName == 'activitytype') {
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$defaultactivitytype = $currentUserModel->get('defaultactivitytype');
				$fieldValue = $defaultactivitytype;
				if (!$defaultactivitytype || $defaultactivitytype == 'Select an Option') {
					$fieldValue = $fieldModel->getDefaultFieldValue();
				}
				$fieldModel->set('fieldvalue', $fieldValue);
			} else {
				$defaultValue = $fieldModel->getDefaultFieldValue();
				if ($defaultValue) {
					$fieldModel->set('fieldvalue', $defaultValue);
				}
			}
			$values[$fieldName] = $fieldModel;
		}
		$this->structuredValues = $values;
		return $values;
	}

}

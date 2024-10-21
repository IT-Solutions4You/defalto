<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_Record_Model extends Vtiger_Record_Model {

	function getCreateInvoiceUrl() {
		$invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');
		return $invoiceModuleModel->getCreateRecordUrl().'&sourceRecord='.$this->getId().'&sourceModule='.$this->getModuleName().'&potential_id='.$this->getId().'&account_id='.$this->get('related_to').'&contact_id='.$this->get('contact_id');
	}

	/**
	 * Function to get List of Fields which are related from Contacts to Inventyory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		return array(
				array('parentField'=>'related_to', 'inventoryField'=>'account_id', 'defaultValue'=>''),
				array('parentField'=>'contact_id', 'inventoryField'=>'contact_id', 'defaultValue'=>''),
		);
	}

	/**
	 * Function returns the url for create quote
	 * @return <String>
	 */
	public function getCreateQuoteUrl() {
		$quoteModuleModel = Vtiger_Module_Model::getInstance('Quotes');
		return $quoteModuleModel->getCreateRecordUrl().'&sourceRecord='.$this->getId().'&sourceModule='.$this->getModuleName().'&potential_id='.$this->getId().'&account_id='.$this->get('related_to').'&contact_id='.$this->get('contact_id').'&relationOperation=true';
	}

	/**
	 * Function returns the url for create Sales Order
	 * @return <String>
	 */
	public function getCreateSalesOrderUrl() {
		$salesOrderModuleModel = Vtiger_Module_Model::getInstance('SalesOrder');
		return $salesOrderModuleModel->getCreateRecordUrl().'&sourceRecord='.$this->getId().'&sourceModule='.$this->getModuleName().
				'&potential_id='.$this->getId().'&account_id='.$this->get('related_to').'&contact_id='.$this->get('contact_id').
				'&relationOperation=true';
	}

	/**
	 * Function returns the url for converting potential
	 */
	function getConvertPotentialUrl() {
		return 'index.php?module='.$this->getModuleName().'&view=ConvertPotential&record='.$this->getId();
	}

	/**
	 * Function returns the fields required for Potential Convert
	 * @return <Array of Vtiger_Field_Model>
	 */
	function getConvertPotentialFields() {
		$convertFields = array();
		$projectFields = $this->getProjectFieldsForPotentialConvert();
		if(!empty($projectFields)) {
			$convertFields['Project'] = $projectFields;
		}

		return $convertFields;
	}

	/**
	 * Function returns Project fields for Potential Convert
	 * @return Array
	 */
	function getProjectFieldsForPotentialConvert() {
		$projectFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Project';

		if(!Users_Privileges_Model::isPermitted($moduleName, 'CreateView')) {
			return;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();
			foreach ($fieldModels as $fieldName => $fieldModel) {
				if($fieldModel->isMandatory() && !in_array($fieldName, array('assigned_user_id', 'potentialid'))) {
					$potentialMappedField = $this->getConvertPotentialMappedField($fieldName, $moduleName);
					if($this->get($potentialMappedField)) {
						$fieldModel->set('fieldvalue', $this->get($potentialMappedField));
					} else {
						$fieldModel->set('fieldvalue', $fieldModel->getDefaultFieldValue());
					} 
					$projectFields[] = $fieldModel;
				}
			}
		}
		return $projectFields;
	}


    /**
     * Function returns field mapped to Potentials field, used in Potential Convert for settings the field values
     * @param <String> $fieldName
     * @param $moduleName
     * @return mixed
     * @throws Exception
     */
    public function getConvertPotentialMappedField($fieldName, $moduleName)
    {
        $mappingFields = $this->get('mappingFields');

        if (!$mappingFields) {
            $db = PearDatabase::getInstance();
            $mappingFields = [];

            $result = $db->pquery('SELECT * FROM vtiger_convertpotentialmapping');

            $projectInstance = Vtiger_Module_Model::getInstance('Project');
            $projectFieldInstances = $projectInstance->getFields();

            $potentialInstance = Vtiger_Module_Model::getInstance('Potentials');
            $potentialFieldInstances = $potentialInstance->getFields();

            while($row = $db->fetchByAssoc($result)) {
                if (empty($row['potential_field']) || empty($row['project_field'])) {
                    continue;
                }

                $potentialFieldName = $row['potential_field'];
                $projectFieldName = $row['project_field'];
                $potentialFieldInstance = $potentialFieldInstances[$potentialFieldName];
                $projectFieldInstance = $projectFieldInstances[$projectFieldName];

                if (!$potentialFieldInstance || !$projectFieldInstance) {
                    continue;
                }

                $mappingFields['Project'][$projectFieldName] = $potentialFieldName;
            }

            $this->set('mappingFields', $mappingFields);
        }

        return $mappingFields[$moduleName][$fieldName];
    }

    /**
     * Function to check whether the Potential is converted or not
	 * @return True if the Potential is Converted false otherwise.
	 */
	function isPotentialConverted() {
		$db = PearDatabase::getInstance();
		$id = $this->getId();
		$sql = "select converted from  vtiger_potential where converted = 1 and potentialid=?";
		$result = $db->pquery($sql,array($id));
		$rowCount = $db->num_rows($result);
		if($rowCount > 0){
			return true;
		}
		return false;
	}

}

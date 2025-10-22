<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Leads_Module_Model extends Vtiger_Module_Model
{
    protected $supportedFieldIdsList;

    /**
     * Function to get fields of this model
     * @return <Array> list of field models <Settings_Leads_Field_Model>
     */
    public function getFields($blockInstance = false)
    {
        if (!$this->fields) {
            $fieldModelsList = [];
            $fieldIds = $this->getMappingSupportedFieldIdsList();

            foreach ($fieldIds as $fieldId) {
                $fieldModel = Settings_Leads_Field_Model::getInstance($fieldId, $this);
                $fieldModelsList[$fieldModel->getFieldDataType()][$fieldId] = $fieldModel;
            }
            $this->fields = $fieldModelsList;
        }

        return $this->fields;
    }

    /**
     * Function to get mapping supported field ids list
     * @return <Array> list of field ids
     */
    public function getMappingSupportedFieldIdsList()
    {
        if (!$this->supportedFieldIdsList) {
            $selectedTabidsList[] = getTabid($this->getName());
            $restrictedFieldNames = ['campaignrelstatus'];
            $restrictedUitypes = $this->getRestrictedUitypes();
            $selectedGeneratedTypes = [1, 2];

            $db = PearDatabase::getInstance();
            $query = 'SELECT fieldid FROM vtiger_field
						WHERE tabid IN (' . generateQuestionMarks($selectedTabidsList) . ')
						AND uitype NOT IN (' . generateQuestionMarks($restrictedUitypes) . ')
						AND fieldname NOT IN (' . generateQuestionMarks($restrictedFieldNames) . ')
						AND generatedtype IN (' . generateQuestionMarks($selectedGeneratedTypes) . ')';

            $params = array_merge($selectedTabidsList, $restrictedUitypes, $restrictedFieldNames, $selectedGeneratedTypes);

            $result = $db->pquery($query, $params);
            $numOfRows = $db->num_rows($result);

            $fieldIdsList = [];
            for ($i = 0; $i < $numOfRows; $i++) {
                $fieldIdsList[] = $db->query_result($result, $i, 'fieldid');
            }
            $this->supportedFieldIdsList = $fieldIdsList;
        }

        return $this->supportedFieldIdsList;
    }

    /**
     * Function to get the Restricted Ui Types
     * @return <array> Restricted ui types
     */
    public function getRestrictedUitypes()
    {
        return [4, 51, 52, 53, 57, 58, 70];
    }

    /**
     * Function to get instance of module
     *
     * @param <String> $moduleName
     *
     * @return <Settings_Leads_Module_Model>
     */
    public static function getInstance($moduleName)
    {
        $moduleModel = parent::getInstance($moduleName);
        $objectProperties = get_object_vars($moduleModel);

        $moduleModel = new self();
        foreach ($objectProperties as $properName => $propertyValue) {
            $moduleModel->$properName = $propertyValue;
        }

        return $moduleModel;
    }
}
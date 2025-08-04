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

class Settings_Potentials_Mapping_Model extends Settings_Leads_Mapping_Model
{
    var $name = 'Potentials';

    /**
     * Function to get headers for detail view
     * @return <Array> headers list
     */
    public function getHeaders()
    {
        return ['Potentials' => 'Potentials', 'Type' => 'Type', 'Projects' => 'Projects'];
    }

    /**
     * Function to get list of detail view link models
     * @return <Array> list of detail view link models <Vtiger_Link_Model>
     */
    public function getDetailViewLinks()
    {
        return [
            Vtiger_Link_Model::getInstanceFromValues([
                'linktype'  => 'DETAILVIEW',
                'linklabel' => 'LBL_EDIT',
                'linkurl'   => 'javascript:Settings_PotentialMapping_Js.triggerEdit("' . $this->getEditViewUrl() . '")',
                'linkicon'  => ''
            ])
        ];
    }

    /**
     * Function to get list of mapping link models
     * @return <Array> list of mapping link models <Vtiger_Link_Model>
     */
    public function getMappingLinks()
    {
        return [
            Vtiger_Link_Model::getInstanceFromValues([
                'linktype'  => 'DETAILVIEW',
                'linklabel' => 'LBL_DELETE',
                'linkurl'   => 'javascript:Settings_PotentialMapping_Js.triggerDelete(event,"' . $this->getMappingDeleteUrl() . '")',
                'linkicon'  => ''
            ])
        ];
    }

    /**
     * Function to get mapping details
     * @return <Array> list of mapping details
     */
    public function getMapping($editable = false)
    {
        if (!$this->mapping) {
            $db = PearDatabase::getInstance();
            $query = 'SELECT * FROM vtiger_convertpotentialmapping';

            if ($editable) {
                $query .= ' WHERE editable = 1';
            }

            $result = $db->pquery($query, []);
            $mapping = [];

            while ($row = $db->fetchByAssoc($result)) {
                $mapping[$row['cfmid']] = $row;
            }

            $finalMapping = $fieldNamesList = $fieldLabelsList = [];

            foreach ($mapping as $mappingDetails) {
                array_push($fieldNamesList, $mappingDetails['potential_field'], $mappingDetails['project_field']);
            }

            if (!empty($fieldNamesList)) {
                $fieldLabelsList = $this->getFieldsInfoByName(array_unique($fieldNamesList));
            }

            foreach ($mapping as $mappingId => $mappingDetails) {
                $finalMapping[$mappingId] = [
                    'editable'   => $mappingDetails['editable'],
                    'Potentials' => $fieldLabelsList[$mappingDetails['potential_field']],
                    'Project'    => $fieldLabelsList[$mappingDetails['project_field']],
                ];
            }

            $this->mapping = $finalMapping;
        }

        return $this->mapping;
    }

    /**
     * Function to save the mapping info
     *
     * @param <Array> $mapping info
     *
     * @throws Exception
     */
    public function save($mapping)
    {
        $deleteMappingsList = $updateMappingsList = $createMappingsList = [];

        foreach ($mapping as $mappingDetails) {
            $mappingId = $mappingDetails['mappingId'];

            if ($mappingDetails['potential']) {
                if ($mappingId) {
                    if ((array_key_exists('deletable', $mappingDetails)) || (!$mappingDetails['project'])) {
                        $deleteMappingsList[] = $mappingDetails;
                    } elseif ($mappingDetails['project']) {
                        $updateMappingsList[] = $mappingDetails;
                    }
                } elseif ($mappingDetails['project']) {
                    $createMappingsList[] = $mappingDetails;
                }
            }
        }

        $table = (new Core_DatabaseData_Model())->getTable('vtiger_convertpotentialmapping', null);

        if ($deleteMappingsList) {
            foreach ($deleteMappingsList as $deleteMapping) {
                $table->deleteData([
                    'cfmid'    => $deleteMapping['mappingId'],
                    'editable' => 1,
                ]);
            }
        }

        if ($updateMappingsList) {
            foreach ($updateMappingsList as $updateMapping) {
                $table->updateData([
                    'potential_field' => $updateMapping['potential'],
                    'project_field'   => $updateMapping['project'],
                ], [
                    'cfmid'    => $updateMapping['mappingId'],
                    'editable' => 1,
                ]);
            }
        }

        if ($createMappingsList) {
            foreach ($createMappingsList as $createMapping) {
                $params = ['potential_field' => $createMapping['potential'], 'project_field' => $createMapping['project'], 'editable' => 1];
                $data = $table->selectData([], $params);

                if (empty($data)) {
                    $table->insertData($params);
                }
            }
        }
    }

    /**
     * Function to get restricted field ids list
     * @return <Array> list of field ids
     */
    public static function getRestrictedFieldNamesList()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_convertpotentialmapping WHERE editable = ?', [0]);
        $fields = [];

        while ($row = $db->fetchByAssoc($result)) {
            if ($row['project_field']) {
                $fields[] = $row['project_field'];
            }
        }

        return $fields;
    }

    /**
     * Function to get mapping supported modules list
     * @return <Array>
     */
    public static function getSupportedModulesList()
    {
        return ['Project'];
    }

    /**
     * Function to delate the mapping
     *
     * @param <Array> $mappingIdsList
     */
    public static function deleteMapping($mappingIdsList)
    {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_convertpotentialmapping WHERE cfmid IN (' . generateQuestionMarks($mappingIdsList) . ')', $mappingIdsList);
    }

    /**
     * Function to get instance
     *
     * @param <Boolean> true/false
     *
     * @return <Settings_Potentials_Mapping_Model>
     */
    public static function getInstance($editable = false)
    {
        $instance = new self();
        $instance->getMapping($editable);

        return $instance;
    }

    /**
     * Function to get instance
     * @return <Settings_Potentials_Mapping_Model>
     */
    public static function getCleanInstance()
    {
        return new self();
    }
}
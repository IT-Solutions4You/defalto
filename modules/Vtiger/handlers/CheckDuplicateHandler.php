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

require_once 'include/events/VTEventHandler.inc';

class CheckDuplicateHandler extends VTEventHandler
{
    function handleEvent($eventName, $entityData)
    {
        if ($eventName == 'vtiger.entity.beforesave') {
            $this->triggerCheckDuplicateHandler($entityData);
        } elseif ($eventName == 'vtiger.entity.beforerestore') {
            $this->triggerCheckDuplicateHandler($entityData);
        }
    }

    public function triggerCheckDuplicateHandler($entityData)
    {
        global $skipDuplicateCheck;
        $fieldValues = $entityData->getData();

        $moduleName = $entityData->getModuleName();

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        if (!$moduleModel->allowDuplicates && !$skipDuplicateCheck) {
            $fields = $moduleModel->getFields();

            $baseTableName = $moduleModel->get('basetable');
            $baseTableId = $moduleModel->get('basetableid');
            $crmentityTable = 'vtiger_crmentity';
            $tabIndexes = $entityData->focus->tab_name_index;

            $uniqueFields = [];
            $tablesList = [];
            foreach ($fields as $fieldName => $fieldModel) {
                if ($fieldModel->isUniqueField() && $fieldModel->isEditable()) {
                    $uniqueFields[$fieldName] = $fieldModel;

                    $fieldTableName = $fieldModel->get('table');
                    if (!in_array($fieldTableName, [$baseTableName, $crmentityTable]) && $tabIndexes && $tabIndexes[$fieldTableName]) {
                        $tablesList[$fieldTableName] = $tabIndexes[$fieldTableName];
                    }
                }
            }

            if (php7_count($uniqueFields) > 0) {
                $checkDuplicates = false;
                $uniqueFieldsData = [];
                foreach ($uniqueFields as $fieldName => $fieldModel) {
                    $fieldDataType = $fieldModel->getFieldDataType();
                    $fieldValue = $fieldValues[$fieldName];

                    switch ($fieldDataType) {
                        case 'reference'    :
                            if ($fieldValue == 0) {
                                $fieldValue = '';
                            }
                            break;
                        case 'date'            :
                        case 'currency'        :
                        case 'multipicklist':
                            if ($fieldValue) {
                                $fieldValue = $fieldModel->getDBInsertValue($fieldValue);
                            }
                            break;
                    }

                    if ($fieldValue !== '' && $fieldValue !== null) {
                        if ($fieldDataType == 'currency') {
                            $countedDigits = 8;
                            if ($fieldModel->isCustomField()) {
                                $countedDigits = 5;
                            }
                            $fieldValue = round($fieldValue, $countedDigits);
                        }

                        $uniqueFieldsData[$fieldName] = $fieldValue;
                        $checkDuplicates = true;
                    }
                }

                if ($checkDuplicates) {
                    $db = PearDatabase::getInstance();
                    $recordId = $entityData->getId();

                    $query = "SELECT $crmentityTable.crmid, $crmentityTable.label FROM $crmentityTable INNER JOIN $baseTableName ON $baseTableName.$baseTableId = $crmentityTable.crmid";
                    foreach ($tablesList as $tableName => $tabIndex) {
                        $query .= " INNER JOIN $tableName ON $tableName.$tabIndex = $baseTableName.$baseTableId";
                    }
                    $query .= " WHERE $crmentityTable.deleted = ?";

                    $params = [0];
                    $conditions = [];
                    foreach ($uniqueFields as $fieldName => $fieldModel) {
                        $fieldTableName = $fieldModel->get('table');
                        $fieldColumnName = $fieldModel->get('column');

                        $fieldValue = $uniqueFieldsData[$fieldName];
                        if (isset($fieldValue)) {
                            if (is_array($fieldValue)) {
                                $fieldValue = empty($fieldValue) ? '' : $fieldValue;
                            }

                            array_push($conditions, "$fieldTableName.$fieldColumnName = ?");
                        } else {
                            $fieldValue = '';
                            array_push($conditions, "($fieldTableName.$fieldColumnName = ? OR $fieldTableName.$fieldColumnName IS NULL)");
                        }
                        $params[] = $fieldValue;

                        if ($fieldModel->get('uitype') == 72) {
                            array_push($conditions, "$fieldTableName.currency_id = ?");
                            $currencyIdDetails = explode('curname', $_REQUEST['base_currency']);
                            $params[] = $currencyIdDetails[1];
                        }
                    }

                    if (php7_count($conditions) > 0) {
                        $conditionsSql = implode(" AND ", $conditions);
                        $query .= " AND ($conditionsSql)";
                    }

                    if ($recordId) {
                        $query .= " AND $crmentityTable.crmid != ?";
                        $params[] = $recordId;
                    }

                    $query .= " AND $crmentityTable.setype = ?";
                    $params[] = $moduleName;

                    if ($moduleName == 'Leads' || $moduleName == 'Potentials') {
                        $query .= " AND $baseTableName.converted = 0";
                    }
                    $query .= ' LIMIT 6';

                    $result = $db->pquery($query, $params);

                    $duplicateRecordsList = [];
                    while ($result && $row = $db->fetch_array($result)) {
                        $duplicateRecordsList[$row['crmid']] = $row['label'];
                    }

                    if (php7_count($duplicateRecordsList) > 0) {
                        $exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
                        $exception->setModule($moduleName)
                            ->setDuplicateRecordLabels($duplicateRecordsList)
                            ->setDuplicateRecordIds(array_keys($duplicateRecordsList));
                        throw $exception;
                    }
                }
            }
        }
    }
}

class DuplicateException extends Exception
{

    private $duplicateRecordIds;

    public function setDuplicateRecordIds(array $duplicateRecordIds)
    {
        $this->duplicateRecordIds = $duplicateRecordIds;

        return $this;
    }

    public function getDuplicateRecordIds()
    {
        return $this->duplicateRecordIds;
    }

    private $duplicateRecordLabels;

    public function setDuplicateRecordLabels(array $duplicateRecordLabels)
    {
        $this->duplicateRecordLabels = $duplicateRecordLabels;

        return $this;
    }

    public function getDuplicateRecordLabels()
    {
        return $this->duplicateRecordLabels;
    }

    private $module;

    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getDuplicationMessage()
    {
        $moduleName = $this->getModule();
        $duplicateRecordsList = $this->getDuplicateRecordIds();

        return getDuplicatesPreventionMessage($moduleName, $duplicateRecordsList);
    }
}
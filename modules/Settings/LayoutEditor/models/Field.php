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

class Settings_LayoutEditor_Field_Model extends Vtiger_Field_Model
{
    /**
     * Function to Move the field
     *
     * @param <Array> $fieldNewDetails
     * @param <Array> $fieldOlderDetails
     */
    public function move($fieldNewDetails, $fieldOlderDetails)
    {
        $db = PearDatabase::getInstance();

        $newBlockId = $fieldNewDetails['blockId'];
        $olderBlockId = $fieldOlderDetails['blockId'];

        $newSequence = $fieldNewDetails['sequence'];
        $olderSequence = $fieldOlderDetails['sequence'];

        if ($olderBlockId == $newBlockId) {
            if ($newSequence > $olderSequence) {
                $updateQuery = 'UPDATE vtiger_field SET sequence = sequence-1 WHERE sequence > ? AND sequence <= ? AND block = ?';
                $params = [$olderSequence, $newSequence, $olderBlockId];
                $db->pquery($updateQuery, $params);
            } elseif ($newSequence < $olderSequence) {
                $updateQuery = 'UPDATE vtiger_field SET sequence = sequence+1 WHERE sequence < ? AND sequence >= ? AND block = ?';
                $params = [$olderSequence, $newSequence, $olderBlockId];
                $db->pquery($updateQuery, $params);
            }
            $query = 'UPDATE vtiger_field SET sequence = ? WHERE fieldid = ?';
            $params = [$newSequence, $this->getId()];
            $db->pquery($query, $params);
        } else {
            $updateOldBlockQuery = 'UPDATE vtiger_field SET sequence = sequence-1 WHERE sequence > ? AND block = ?';
            $params = [$olderSequence, $olderBlockId];
            $db->pquery($updateOldBlockQuery, $params);

            $updateNewBlockQuery = 'UPDATE vtiger_field SET sequence = sequence+1 WHERE sequence >= ? AND block = ?';
            $params = [$newSequence, $newBlockId];
            $db->pquery($updateNewBlockQuery, $params);

            $query = 'UPDATE vtiger_field SET sequence = ?, block = ? WHERE fieldid = ?';
            $params = [$newSequence, $newBlockId, $this->getId()];
            $db->pquery($query, $params);
        }
    }

    public static function makeFieldActive($fieldIdsList, $blockId, $moduleName = false)
    {
        $db = PearDatabase::getInstance();
        $maxSequenceQuery = "SELECT MAX(sequence) AS maxsequence FROM vtiger_field WHERE block = ? AND presence IN (0,2) ";
        $res = $db->pquery($maxSequenceQuery, [$blockId]);
        $maxSequence = $db->query_result($res, 0, 'maxsequence');

        $query = 'UPDATE vtiger_field SET presence = 2, sequence = CASE';
        foreach ($fieldIdsList as $fieldId) {
            $maxSequence = $maxSequence + 1;
            $query .= ' WHEN fieldid = ? THEN ' . $maxSequence;
        }
        $query .= ' ELSE sequence END';
        $query .= ' WHERE fieldid IN (' . generateQuestionMarks($fieldIdsList) . ')';

        $db->pquery($query, array_merge($fieldIdsList, $fieldIdsList));

        // Clearing cache
        $moduleModel = Vtiger_Module::getInstance($moduleName);
        Vtiger_Cache::flushModuleandBlockFieldsCache($moduleModel, $blockId);
    }

    /**
     * Function which specifies whether the field can have mandatory switch to happen
     * @return <Boolean> - true if we can make a field mandatory and non mandatory , false if we cant change previous state
     */
    public function isMandatoryOptionDisabled()
    {
        $moduleModel = $this->getModule();
        $complusoryMandatoryFieldList = $moduleModel->getCompulsoryMandatoryFieldList();
        //uitypes for which mandatory switch is disabled
        $mandatoryRestrictedUitypes = ['4', '70'];
        if (in_array($this->getName(), $complusoryMandatoryFieldList) || $this->isOptionsRestrictedField()) {
            return true;
        }
        if (in_array($this->get('uitype'), $mandatoryRestrictedUitypes) || (in_array($this->get('displaytype'), [2, 4]))) {
            return true;
        }

        if ($this->get('uitype') == '83' && $this->getName() == 'taxclass') {
            return true;
        }

        return false;
    }

    /**
     * Function which will specify whether the active option is disabled
     * @return boolean
     */
    public function isActiveOptionDisabled()
    {
        if ($this->get('presence') == 0 || $this->get('displaytype') == 2 || $this->isMandatoryOptionDisabled() || $this->isOptionsRestrictedField()) {
            return true;
        }

        return false;
    }

    /**
     * Function which will specify whether the quickcreate option is disabled
     * @return boolean
     */
    public function isQuickCreateOptionDisabled()
    {
        $moduleModel = $this->getModule();
        if ($this->get('quickcreate') == 0 || $this->get('quickcreate') == 3 || $this->get('displaytype') == 5 || !$moduleModel->isQuickCreateSupported() || $this->get(
                'uitype'
            ) == 69
            || $this->getName() == 'recurringtype' || $this->isOptionsRestrictedField()) {
            return true;
        }

        return false;
    }

    /**
     * Function which will specify whether the mass edit option is disabled
     * @return boolean
     */
    public function isMassEditOptionDisabled()
    {
        return $this->get('masseditable') == 0 || $this->get('displaytype') != 1 || $this->get('masseditable') == 3 || $this->isOptionsRestrictedField();
    }

    /**
     * Function which will specify whether the default value option is disabled
     * @return boolean
     */
    public function isDefaultValueOptionDisabled()
    {
        // for Record Source Field we should not show default value option as we are setting this while Record Save
        $defaultValueRestrictedFields = ['source'];
        $defaultValueRestrictedUitypes = ['4', '70', '69', '53', '6', '23'];
        if (in_array($this->getName(), $defaultValueRestrictedFields)
            || in_array($this->get('displaytype'), [4])
            || $this->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE
            || in_array($this->get('uitype'), $defaultValueRestrictedUitypes)
            || ($this->get('uitype') == '83' && $this->getName() == 'taxclass' && in_array($this->block->module->name, ['Products', 'Services']))
            || $this->isOptionsRestrictedField()) {
            return true;
        }

        return false;
    }

    /**
     * Function to check whether summary field option is disable or not
     * @return <Boolean> true/false
     */
    public function isSummaryFieldOptionDisabled()
    {
        return in_array($this->get('displaytype'), [4, 5])
            || ($this->get('uitype') == '83' && $this->getName() == 'taxclass' && in_array($this->block->module->name, ['Products', 'Services']));
    }

    public function isHeaderFieldOptionDisabled()
    {
        return $this->isSummaryFieldOptionDisabled();
    }

    /**
     * Function to check field is editable or not
     * @return <Boolean> true/false
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Function to get instance
     *
     * @param <String> $value  - fieldname or fieldid
     * @param <type>   $module - optional - module instance
     *
     * @return <Settings_LayoutEditor_Field_Model>
     */
    public static function getInstance($value, $module = false)
    {
        $fieldObject = parent::getInstance($value, $module);
        if ($fieldObject) {
            $objectProperties = get_object_vars($fieldObject);
            $fieldModel = new self();
            foreach ($objectProperties as $properName => $propertyValue) {
                $fieldModel->$properName = $propertyValue;
            }
            $fieldModel->parentField = $fieldObject;

            return $fieldModel;
        } else {
            return false;
        }
    }

    /**
     * Function get instance using field object
     *
     * @param Vtiger_Field_Model $fieldObject
     *
     * @return <Settings_LayoutEditor_Field_Model>
     */
    public static function getInstanceFromFieldObject(Vtiger_Field $fieldObject)
    {
        $objectProperties = get_object_vars($fieldObject);
        $fieldModel = new self();
        foreach ($objectProperties as $properName => $propertyValue) {
            $fieldModel->$properName = $propertyValue;
        }
        $fieldModel->parentField = $fieldObject;

        return $fieldModel;
    }

    public static function getDetailsForMove($fieldIdsList = [])
    {
        if ($fieldIdsList) {
            $db = PearDatabase::getInstance();
            $result = $db->pquery('SELECT fieldid, sequence, block, fieldlabel FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($fieldIdsList) . ')', $fieldIdsList);
            $numOfRows = $db->num_rows($result);

            for ($i = 0; $i < $numOfRows; $i++) {
                $blockIdsList[$db->query_result($result, $i, 'fieldid')] = [
                    'blockId'  => $db->query_result($result, $i, 'block'),
                    'sequence' => $db->query_result($result, $i, 'sequence'),
                    'label'    => $db->query_result($result, $i, 'fieldlabel')
                ];
            }

            return $blockIdsList;
        }

        return false;
    }

    /**
     * Function to get all fields list for all blocks
     *
     * @param <Array> List of block ids
     * @param <Vtiger_Module_Model> $moduleInstance
     *
     * @return <Array> List of Field models <Settings_LayoutEditor_Field_Model>
     */
    public static function getInstanceFromBlockIdList($blockId, $moduleInstance = false)
    {
        $db = PearDatabase::getInstance();

        if (!is_array($blockId)) {
            $blockId = [$blockId];
        }

        $query = 'SELECT * FROM vtiger_field WHERE block IN(' . generateQuestionMarks($blockId) . ') AND vtiger_field.displaytype IN (1,2,4,5) ORDER BY sequence';
        $result = $db->pquery($query, $blockId);
        $numOfRows = $db->num_rows($result);

        $fieldModelsList = [];
        for ($i = 0; $i < $numOfRows; $i++) {
            $rowData = $db->query_result_rowdata($result, $i);
            //static is use to refer to the called class instead of defined class
            //http://php.net/manual/en/language.oop5.late-static-bindings.php
            $fieldModel = new self();
            $fieldModel->initialize($rowData);
            if ($moduleInstance) {
                $fieldModel->setModule($moduleInstance);
            }
            $fieldModelsList[] = $fieldModel;
        }

        return $fieldModelsList;
    }

    /**
     * Function to get the field details
     * @return <Array> - array of field values
     */
    public function getFieldInfo()
    {
        $fieldInfo = parent::getFieldInfo();
        $fieldInfo['isQuickCreateDisabled'] = $this->isQuickCreateOptionDisabled();
        $fieldInfo['isSummaryField'] = $this->isSummaryField();
        $fieldInfo['isSummaryFieldDisabled'] = $this->isSummaryFieldOptionDisabled();
        $fieldInfo['isHeaderField'] = $this->isHeaderField();
        $fieldInfo['isHeaderFieldDisabled'] = $this->isHeaderFieldOptionDisabled();
        $fieldInfo['isMassEditDisabled'] = $this->isMassEditOptionDisabled();
        $fieldInfo['isDefaultValueDisabled'] = $this->isDefaultValueOptionDisabled();
        $fieldInfo['fieldTypeLabel'] = vtranslate($this->getFieldDataTypeLabel(), 'Settings:LayoutEditor');
        if (isset($fieldInfo['picklistvalues'])) {
            if ($fieldInfo['type'] != 'multipicklist') {
                $picklistValues = $fieldInfo['picklistvalues'];
                $emptyOption = [' ' => vtranslate('LBL_SELECT_OPTION')];

                $picklistValues = $emptyOption + $picklistValues;
                $fieldInfo['picklistvalues'] = $picklistValues;
            }
        } else {
            $picklistValues = [' ' => vtranslate('LBL_SELECT_OPTION')];
            $fieldInfo['picklistvalues'] = $picklistValues;
        }

        if (isset($fieldInfo['editablepicklistvalues'])) {
            if ($fieldInfo['type'] != 'multipicklist') {
                $picklistValues = $fieldInfo['editablepicklistvalues'];
                $emptyOption = [' ' => vtranslate('LBL_SELECT_OPTION')];

                $picklistValues = $emptyOption + $picklistValues;
                $fieldInfo['editablepicklistvalues'] = $picklistValues;
            }
        } else {
            $picklistValues = [' ' => vtranslate('LBL_SELECT_OPTION')];
            $fieldInfo['editablepicklistvalues'] = $picklistValues;
        }

        //for new field we need to have all attributes
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $fieldInfo['date-format'] = $currentUser->get('date_format');
        $fieldInfo['time-format'] = $currentUser->get('hour_format');
        $fieldInfo['currency_symbol'] = $currentUser->get('currency_symbol');
        $fieldInfo['decimal_separator'] = $currentUser->get('currency_decimal_separator');
        $fieldInfo['group_separator'] = $currentUser->get('currency_grouping_separator');

        return $fieldInfo;
    }

    public static function getInstanceFromFieldId($fieldId, $moduleTabId)
    {
        $db = PearDatabase::getInstance();

        if (is_string($fieldId)) {
            $fieldId = [$fieldId];
        }

        $query = 'SELECT * FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($fieldId) . ') AND tabid=?';
        $result = $db->pquery($query, [$fieldId, $moduleTabId]);
        $fieldModelList = [];
        $num_rows = $db->num_rows($result);
        for ($i = 0; $i < $num_rows; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $fieldModel = new self();
            $fieldModel->initialize($row);
            $fieldModelList[] = $fieldModel;
        }

        return $fieldModelList;
    }

    /**
     * Function which is used to get the field label that can be showin in layout editor
     * @return string
     */
    public function getFieldDataTypeLabel()
    {
        $fieldDataType = $this->getFieldDataType();
        $fieldDataTypeLabelMapping = [
            'string'        => 'Text',
            'reference'     => 'Relation',
            'double'        => 'Decimal',
            'percentage'    => 'Percent',
            'boolean'       => 'Checkbox',
            'text'          => 'TextArea',
            'multipicklist' => 'MultiSelectCombo',
            'salutation'    => 'Text'
        ];
        if (array_key_exists($fieldDataType, $fieldDataTypeLabelMapping)) {
            return $fieldDataTypeLabelMapping[$fieldDataType];
        }

        return ucfirst($fieldDataType);
    }

    public static function getCleanInstance(string $fieldName = '', string $moduleName = ''): object
    {
        $fieldInstance = new self();
        //We need to initialize these attributes since we use these for clean instance as well
        //$field->block->module->name at multiple places
        $fieldInstance->block = new Vtiger_Block();
        $fieldInstance->block->module = new Vtiger_Module();

        return $fieldInstance;
    }

    public function getDefaultFieldValueToViewInFieldsLayOut()
    {
        $defaultValue = $this->getDefaultFieldValue();
        $separator = ', ';

        if ($this->getFieldDataType() == 'multipicklist') {
            $defaultValue = str_replace(' |##| ', $separator, $defaultValue);
        }
        $defaultValue = trim($defaultValue, $separator);

        return $defaultValue;
    }

    public function getDefaultFieldValueToViewInV7FieldsLayOut()
    {
        $defaultValue = $this->getDefaultFieldValue();

        if ($defaultValue) {
            if ($this->getFieldDataType() == 'currency') {
                $defaultValue = $this->getCurrencyDisplayValue($defaultValue, true);
            } else {
                $defaultValue = $this->getDisplayValue($defaultValue);
            }
        }

        return $defaultValue;
    }

    /**
     * Function to retrieve display value in edit view
     *
     * @param <String> $value - value which need to be converted to display value
     *
     * @return <String> - converted display value
     */
    public function getEditViewDisplayValue($value, $skipConversion = false)
    {
        if (!$this->uitype_instance) {
            $this->uitype_instance = Vtiger_Base_UIType::getInstanceFromField($this);
        }
        $uiTypeInstance = $this->uitype_instance;

        return $uiTypeInstance->getEditViewDisplayValue($value, $skipConversion);
    }

    /**
     * Function to retieve display value for a value
     *
     * @param <String> $value - value which need to be converted to display value
     *
     * @return <String> - converted display value
     */
    public function getCurrencyDisplayValue($value, $skipConversion = false)
    {
        if (!$this->uitype_instance) {
            $this->uitype_instance = Vtiger_Base_UIType::getInstanceFromField($this);
        }
        $uiTypeInstance = $this->uitype_instance;

        return $uiTypeInstance->getDisplayValue($value, $skipConversion);
    }

    public function isOptionsRestrictedField()
    {
        $restrictedFields = ['isconvertedfrompotential', 'isconvertedfromlead'];
        if (in_array($this->getName(), $restrictedFields)) {
            return true;
        } else {
            return false;
        }
    }
}
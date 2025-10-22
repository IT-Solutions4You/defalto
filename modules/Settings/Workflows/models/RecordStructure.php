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

class Settings_Workflows_RecordStructure_Model extends Vtiger_RecordStructure_Model
{
    const RECORD_STRUCTURE_MODE_DEFAULT = '';
    const RECORD_STRUCTURE_MODE_FILTER = 'Filter';
    const RECORD_STRUCTURE_MODE_EDITTASK = 'EditTask';

    public array $emailFields;
    protected $workFlowModel;

    function setWorkFlowModel($workFlowModel)
    {
        $this->workFlowModel = $workFlowModel;
    }

    function getWorkFlowModel()
    {
        return $this->workFlowModel;
    }

    /**
     * Function to get the values in stuctured format
     * @return <array> - values in structure array('block'=>array(fieldinfo));
     */
    public function getStructure()
    {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $recordModel = $this->getWorkFlowModel();
        $recordId = $recordModel->getId();

        $taskTypeModel = $this->getTaskRecordModel()->getTaskType();
        $taskTypeName = $taskTypeModel->getName();
        $values = [];

        $baseModuleModel = $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();

        foreach ($blockModelList as $blockLabel => $blockModel) {
            $fieldModelList = $blockModel->getFields();
            if (!empty ($fieldModelList)) {
                $values[$blockLabel] = [];
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->isViewable()) {
                        //Should not show starred and tag fields in edit task view
                        if ($fieldModel->getDisplayType() == '6') {
                            continue;
                        }
                        if (!empty($recordId)) {
                            //Set the fieldModel with the valuetype for the client side.
                            $fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
                            $fieldInfo = $fieldModel->getFieldInfo();
                            $fieldInfo['workflow_valuetype'] = $fieldValueType;
                            $fieldInfo['workflow_columnname'] = $fieldName;
                            $fieldModel->setFieldInfo($fieldInfo);
                        }
                        // This will be used during editing task like email, sms etc
                        $fieldModel->set('workflow_columnname', $fieldName)->set('workflow_columnlabel', vtranslate($fieldModel->get('label'), $moduleModel->getName()));
                        // This is used to identify the field belongs to source module of workflow
                        $fieldModel->set('workflow_sourcemodule_field', true);
                        $fieldModel->set('workflow_fieldEditable', $fieldModel->isEditable());
                        $values[$blockLabel][$fieldName] = clone $fieldModel;
                    }
                }
            }
        }

        //All the reference fields should also be sent
        $fields = $moduleModel->getFieldsByType(['reference', 'owner', 'multireference']);
        foreach ($fields as $parentFieldName => $field) {
            $type = $field->getFieldDataType();
            $referenceModules = $field->getReferenceList();
            if ($type == 'owner') {
                $referenceModules = ['Users'];
            }
            foreach ($referenceModules as $refModule) {
                $moduleModel = Vtiger_Module_Model::getInstance($refModule);
                $blockModelList = $moduleModel->getBlocks();

                foreach ($blockModelList as $blockLabel => $blockModel) {
                    $fieldModelList = $blockModel->getFields();
                    if (!empty ($fieldModelList)) {
                        foreach ($fieldModelList as $fieldName => $fieldModel) {
                            if ($fieldModel->isViewable()) {
                                //Should not show starred and tag fields in edit task view
                                if ($fieldModel->getDisplayType() == '6') {
                                    continue;
                                }
                                $name = "($parentFieldName : ($refModule) $fieldName)";
                                $label = vtranslate($field->get('label'), $baseModuleModel->getName()) . ' : (' . vtranslate($refModule, $refModule) . ') ' . vtranslate(
                                        $fieldModel->get('label'),
                                        $refModule
                                    );
                                $fieldModel->set('workflow_columnname', $name)->set('workflow_columnlabel', $label);
                                if (!empty($recordId)) {
                                    $fieldValueType = $recordModel->getFieldFilterValueType($name);
                                    $fieldInfo = $fieldModel->getFieldInfo();
                                    $fieldInfo['workflow_valuetype'] = $fieldValueType;
                                    $fieldInfo['workflow_columnname'] = $name;
                                    $fieldModel->setFieldInfo($fieldInfo);
                                }
                                $fieldModel->set('workflow_fieldEditable', $fieldModel->isEditable());
                                //if field is not editable all the field of that reference field should also shd be not editable
                                //eg : created by is not editable . so all user field refered by created by field shd also be non editable
                                // owner fields should also be non editable
                                if (!$field->isEditable() || $type == "owner") {
                                    $fieldModel->set('workflow_fieldEditable', false);
                                }
                                $values[$field->get('label')][$name] = clone $fieldModel;
                            }
                        }
                    }
                }
            }
        }
        $this->structuredValues = $values;

        return $values;
    }

    /**
     * Function returns all the email fields for the workflow record structure
     * @return type
     */
    public function getAllEmailFields()
    {
        if (empty($this->emailFields)) {
            $this->emailFields = $this->getFieldsByType('email');
        }

        return $this->emailFields;
    }

    public function getFromEmailFields(): array
    {
        $emailFields = $this->getAllEmailFields();
        $nameFields = $this->getNameFields();
        $emailOptions = [
            '$(general : (__VtigerMeta__) supportName)<$(general : (__VtigerMeta__) supportEmailId)>' => vtranslate('LBL_HELPDESK_SUPPORT_EMAILID', 'Settings:Vtiger'),
        ];

        foreach ($emailFields as $metaKey => $emailField) {
            [$relationFieldName] = explode(' ', $metaKey);
            $value = '<$' . $metaKey . '>';

            if ($nameFields) {
                $nameFieldValues = '';

                foreach (array_keys($nameFields) as $fieldName) {
                    if (strstr($fieldName, $relationFieldName) || (php7_count(explode(' ', $metaKey)) === 1 && php7_count(explode(' ', $fieldName)) === 1)) {
                        $fieldName = '$' . $fieldName;
                        $nameFieldValues .= ' ' . $fieldName;
                    }
                }

                $value = trim($nameFieldValues) . $value;
            }

            $emailOptions[$value] = $emailField->get('workflow_columnlabel');
        }

        return $emailOptions;
    }

    public function getEmailFields(): array
    {
        $emailFields = $this->getAllEmailFields();
        $emailOptions = [];

        foreach ($emailFields as $metaKey => $emailField) {
            $emailOptions['$' . $metaKey] = $emailField->get('workflow_columnlabel');
        }

        $usersModuleModel = Vtiger_Module_Model::getInstance('Users');
        $moduleModel = $this->getModule();

        if ($moduleModel->getField('assigned_user_id')) {
            $specialKey = '$(general : (__VtigerMeta__) reports_to_id)';

            if(!isset($emailOptions[$specialKey])){
                $emailOptions[$specialKey] = '';
            }

            $assignedLabel = vtranslate($moduleModel->getField('assigned_user_id')->get('label'), 'Users');
            $usersLabel = vtranslate('Users', 'Users');
            $reportsToLabel = vtranslate($usersModuleModel->getField('reports_to_id')->get('label'), 'Users');
            $emailOptions[$specialKey] .= sprintf('%s : (%s) %s', $assignedLabel, $usersLabel, $reportsToLabel);
        }

        return $emailOptions;
    }

    public function getAllFields()
    {
        $fieldOptions = [];
        $structure = $this->getStructure();

        foreach ($structure as $fields) {
            foreach ($fields as $field) {
                if ($field->get('workflow_pt_lineitem_field')) {
                    $fieldOptions[$field->get('workflow_columnname')] = $field->get('workflow_columnlabel');
                } else {
                    $fieldOptions['$' . $field->get('workflow_columnname')] = $field->get('workflow_columnlabel');
                }
            }
        }

        return $fieldOptions;
    }

    public function getHtmlOptions($options)
    {
        $values = array_map(function ($value, $label) {
            return sprintf('<option value="%s">%s</option>', $value, $label);
        }, array_keys($options), array_values($options));

        return implode('', $values);
    }

    /**
     * Function returns all the date time fields for the workflow record structure
     * @return type
     */
    public function getAllDateTimeFields()
    {
        $fieldTypes = ['date', 'datetime'];

        return $this->getFieldsByType($fieldTypes);
    }

    /**
     * Function returns fields based on type
     * @return type
     */
    public function getFieldsByType($fieldTypes)
    {
        $fieldTypesArray = [];
        if (gettype($fieldTypes) == 'string') {
            array_push($fieldTypesArray, $fieldTypes);
        } else {
            $fieldTypesArray = $fieldTypes;
        }
        $structure = $this->getStructure();
        $fieldsBasedOnType = [];
        if (!empty($structure)) {
            foreach ($structure as $block => $fields) {
                foreach ($fields as $metaKey => $field) {
                    $type = $field->getFieldDataType();
                    if (in_array($type, $fieldTypesArray)) {
                        $fieldsBasedOnType[$metaKey] = $field;
                    }
                }
            }
        }

        return $fieldsBasedOnType;
    }

    /**
     * @param object $workFlowModel
     * @param string $mode
     *
     * @return self
     * @throws Exception
     */
    public static function getInstanceForWorkFlowModule($workFlowModel, $mode)
    {
        $className = Vtiger_Loader::getComponentClassName('Model', $mode . 'RecordStructure', 'Settings:Workflows');
        $instance = new $className();
        $instance->setWorkFlowModel($workFlowModel);
        $instance->setModule($workFlowModel->getModule());

        return $instance;
    }

    public function getNameFields()
    {
        $moduleModel = $this->getModule();
        $nameFieldsList[$moduleModel->getName()] = $moduleModel->getNameFields();

        $fields = $moduleModel->getFieldsByType(['reference', 'owner', 'multireference']);
        foreach ($fields as $parentFieldName => $field) {
            $type = $field->getFieldDataType();
            $referenceModules = $field->getReferenceList();
            if ($type == 'owner') {
                $referenceModules = ['Users'];
            }
            foreach ($referenceModules as $refModule) {
                $moduleModel = Vtiger_Module_Model::getInstance($refModule);
                $nameFieldsList[$refModule] = $moduleModel->getNameFields();
            }
        }

        $nameFields = [];
        $recordStructure = $this->getStructure();
        foreach ($nameFieldsList as $moduleName => $fieldNamesList) {
            foreach ($fieldNamesList as $fieldName) {
                foreach ($recordStructure as $block => $fields) {
                    foreach ($fields as $metaKey => $field) {
                        if ($fieldName === $field->get('name')) {
                            $nameFields[$metaKey] = $field;
                        }
                    }
                }
            }
        }

        return $nameFields;
    }
}
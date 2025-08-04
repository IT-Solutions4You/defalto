<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_RecordStructure_Model extends Vtiger_RecordStructure_Model
{
    const RECORD_STRUCTURE_MODE_DEFAULT = '';
    const RECORD_STRUCTURE_MODE_FILTER = 'Filter';
    const RECORD_STRUCTURE_MODE_EDITTASK = 'EditTask';

    public $EMAILMakerModel = false;

    public static function getInstanceForEMAILMakerModule($EMAILMakerModel, $mode)
    {
        $className = Vtiger_Loader::getComponentClassName('Model', $mode . 'RecordStructure', 'EMAILMaker');
        $instance = new $className();
        $instance->setEMAILMakerModel($EMAILMakerModel);

        $instance->setModule($EMAILMakerModel->getModule());

        return $instance;
    }

    public function getAllEmailFields()
    {
        return $this->getFieldsByType('email');
    }

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

    public function getStructure()
    {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $recordModel = $this->getEMAILMakerModel();
        if ($recordModel) {
            $recordId = $recordModel->getId();
        }

        $values = [];
        $baseModuleModel = $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();

        foreach ($blockModelList as $blockLabel => $blockModel) {
            $fieldModelList = $blockModel->getFields();
            if (!empty ($fieldModelList)) {
                $values[$blockLabel] = [];
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->isViewable()) {
                        if ($fieldModel->getDisplayType() == '6') {
                            continue;
                        }
                        if (!empty($recordId)) {
                            //Set the fieldModel with the valuetype for the client side.
                            $fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
                            $fieldInfo = $fieldModel->getFieldInfo();
                            $fieldInfo['emailmaker_valuetype'] = $fieldValueType;
                            $fieldInfo['emailmaker_columnname'] = $fieldName;
                            $fieldModel->setFieldInfo($fieldInfo);
                        }
                        // This will be used during editing task like email, sms etc
                        $fieldModel->set('emailmaker_columnname', $fieldName)->set('emailmaker_columnlabel', vtranslate($fieldModel->get('label'), $moduleModel->getName()));
                        // This is used to identify the field belongs to source module of pdfmaker
                        $fieldModel->set('emailmaker_sourcemodule_field', true);
                        $fieldModel->set('emailmaker_fieldEditable', $fieldModel->isEditable());
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
                                $fieldModel->set('emailmaker_columnname', $name)->set('emailmaker_columnlabel', $label);
                                if (!empty($recordId)) {
                                    $fieldValueType = $recordModel->getFieldFilterValueType($name);
                                    $fieldInfo = $fieldModel->getFieldInfo();
                                    $fieldInfo['emailmaker_valuetype'] = $fieldValueType;
                                    $fieldInfo['emailmaker_columnname'] = $name;
                                    $fieldModel->setFieldInfo($fieldInfo);
                                }
                                $fieldModel->set('emailmaker_fieldEditable', $fieldModel->isEditable());
                                //if field is not editable all the field of that reference field should also shd be not editable
                                //eg : created by is not editable . so all user field refered by created by field shd also be non editable
                                // owner fields should also be non editable
                                if (!$field->isEditable() || $type == "owner") {
                                    $fieldModel->set('emailmaker_fieldEditable', false);
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

    public function getEMAILMakerModel()
    {
        return $this->EMAILMakerModel;
    }

    public function setEMAILMakerModel($EMAILMakerModel)
    {
        $this->EMAILMakerModel = $EMAILMakerModel;
    }

    public function getAllDateTimeFields()
    {
        $fieldTypes = ['date', 'datetime'];

        return $this->getFieldsByType($fieldTypes);
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
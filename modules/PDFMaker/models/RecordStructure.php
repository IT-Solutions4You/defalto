<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_RecordStructure_Model extends Vtiger_RecordStructure_Model
{
    const RECORD_STRUCTURE_MODE_DEFAULT = '';
    const RECORD_STRUCTURE_MODE_FILTER = 'Filter';
    const RECORD_STRUCTURE_MODE_EDITTASK = 'EditTask';

    public static function getInstanceForPDFMakerModule($PDFMakerModel, $mode)
    {
        $className = Vtiger_Loader::getComponentClassName('Model', $mode . 'RecordStructure', 'PDFMaker');
        $instance = new $className();
        $instance->setPDFMakerModel($PDFMakerModel);
        $instance->setModule($PDFMakerModel->getModule());

        return $instance;
    }

    function setPDFMakerModel($PDFMakerModel)
    {
        $this->PDFMakerModel = $PDFMakerModel;
    }

    /**
     * Function returns all the email fields for the pdfmaker record structure
     * @return type
     */
    public function getAllEmailFields()
    {
        return $this->getFieldsByType('email');
    }

    /**
     * Function returns fields based on type
     * @return array
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
     * Function to get the values in stuctured format
     * @return array - values in structure array('block'=>array(fieldinfo));
     */
    public function getStructure()
    {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $recordModel = $this->getPDFMakerModel();
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
                            $fieldInfo['pdfmaker_valuetype'] = $fieldValueType;
                            $fieldInfo['pdfmaker_columnname'] = $fieldName;
                            $fieldModel->setFieldInfo($fieldInfo);
                        }

                        // This will be used during editing task like email, sms etc
                        $fieldModel->set('pdfmaker_columnname', $fieldName)->set('pdfmaker_columnlabel', vtranslate($fieldModel->get('label'), $moduleModel->getName()));
                        // This is used to identify the field belongs to source module of pdfmaker
                        $fieldModel->set('pdfmaker_sourcemodule_field', true);
                        $fieldModel->set('pdfmaker_fieldEditable', $fieldModel->isEditable());
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
                                $fieldModel->set('pdfmaker_columnname', $name)->set('pdfmaker_columnlabel', $label);

                                if (!empty($recordId)) {
                                    $fieldValueType = $recordModel->getFieldFilterValueType($name);
                                    $fieldInfo = $fieldModel->getFieldInfo();
                                    $fieldInfo['pdfmaker_valuetype'] = $fieldValueType;
                                    $fieldInfo['pdfmaker_columnname'] = $name;
                                    $fieldModel->setFieldInfo($fieldInfo);
                                }

                                $fieldModel->set('pdfmaker_fieldEditable', $fieldModel->isEditable());
                                //if field is not editable all the field of that reference field should also shd be not editable
                                //eg : created by is not editable . so all user field refered by created by field shd also be non editable
                                // owner fields should also be non editable

                                if (!$field->isEditable() || $type == 'owner') {
                                    $fieldModel->set('pdfmaker_fieldEditable', false);
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

    function getPDFMakerModel()
    {
        return $this->PDFMakerModel;
    }

    /**
     * Function returns all the date time fields for the pdfmaker record structure
     * @return type
     */
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
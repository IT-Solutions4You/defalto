<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_FilterRecordStructure_Model extends EMAILMaker_RecordStructure_Model
{

    /**
     * Function to get the values in stuctured format
     * @return <array> - values in structure array('block'=>array(fieldinfo));
     */
    public function getStructure()
    {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $recordModel = $this->getEMAILMakerModel();
        $recordId = $recordModel->getId();

        $values = array();

        $baseModuleModel = $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();

        foreach ($blockModelList as $blockLabel => $blockModel) {
            $fieldModelList = $blockModel->getFields();
            if (!empty ($fieldModelList)) {
                $values[$blockLabel] = array();
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->isViewableInFilterView()) {
                        if (in_array($moduleModel->getName(), array('Calendar', 'Events')) && $fieldModel->getDisplayType() == 3) {
                            /* Restricting the following fields(Event module fields) for "Calendar" module
                             * time_start, time_end, eventstatus, activitytype,	visibility, duration_hours,
                             * duration_minutes, reminder_time, recurringtype, notime
                             */
                            continue;
                        }
                        if (!empty($recordId)) {
                            //Set the fieldModel with the valuetype for the client side.
                            $fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
                            $fieldInfo = $fieldModel->getFieldInfo();
                            $fieldInfo['emailmaker_valuetype'] = $fieldValueType;
                            $fieldModel->setFieldInfo($fieldInfo);
                        }
                        // This will be used during editing task like email, sms etc
                        $fieldModel->set('emailmaker_columnname', $fieldName)->set('emailmaker_columnlabel', vtranslate($fieldModel->get('label'), $moduleModel->getName()));
                        // This is used to identify the field belongs to source module of emailmaker
                        $fieldModel->set('emailmaker_sourcemodule_field', true);
                        $values[$blockLabel][$fieldName] = clone $fieldModel;
                    }
                }
            }
        }

        if ($moduleModel->isCommentEnabled()) {
            $commentFieldModel = EMAILMaker_Field_Model::getCommentFieldForFilterConditions($moduleModel);
            $commentFieldModelsList = array($commentFieldModel->getName() => $commentFieldModel);

            $labelName = vtranslate($moduleModel->getSingularLabelKey(), $moduleModel->getName()) . ' ' . vtranslate('LBL_COMMENTS', $moduleModel->getName());
            foreach ($commentFieldModelsList as $commentFieldName => $commentFieldModel) {
                $commentFieldModel->set('emailmaker_columnname', $commentFieldName)
                    ->set('emailmaker_columnlabel', vtranslate($commentFieldModel->get('label'), $moduleModel->getName()))
                    ->set('emailmaker_sourcemodule_field', true);

                $values[$labelName][$commentFieldName] = $commentFieldModel;
            }
        }

        //All the reference fields should also be sent
        $fields = $moduleModel->getFieldsByType(array('reference', 'multireference'));
        foreach ($fields as $parentFieldName => $field) {
            $referenceModules = $field->getReferenceList();
            foreach ($referenceModules as $refModule) {
                if ($refModule == 'Users') {
                    continue;
                }
                $moduleModel = Vtiger_Module_Model::getInstance($refModule);
                $blockModelList = $moduleModel->getBlocks();
                foreach ($blockModelList as $blockLabel => $blockModel) {
                    $fieldModelList = $blockModel->getFields();
                    if (!empty ($fieldModelList)) {
                        if (count($referenceModules) > 1) {
                            // block label format : reference field label (modulename) - block label. Eg: Related To (Organization) Address Details
                            $newblockLabel = vtranslate($field->get('label'), $baseModuleModel->getName()) . ' (' . vtranslate($refModule, $refModule) . ') - ' .
                                vtranslate($blockLabel, $refModule);
                        } else {
                            $newblockLabel = vtranslate($field->get('label'), $baseModuleModel->getName()) . '-' . vtranslate($blockLabel, $refModule);
                        }
                        $values[$newblockLabel] = array();
                        $fieldModel = $fieldName = null;
                        foreach ($fieldModelList as $fieldName => $fieldModel) {
                            if ($fieldModel->isViewableInFilterView()) {
                                $name = "($parentFieldName : ($refModule) $fieldName)";
                                $label = vtranslate($field->get('label'), $baseModuleModel->getName()) . ' : (' . vtranslate($refModule, $refModule) . ') ' . vtranslate($fieldModel->get('label'), $refModule);
                                $fieldModel->set('emailmaker_columnname', $name)->set('emailmaker_columnlabel', $label);
                                if (!empty($recordId)) {
                                    $fieldValueType = $recordModel->getFieldFilterValueType($name);
                                    $fieldInfo = $fieldModel->getFieldInfo();
                                    $fieldInfo['emailmaker_valuetype'] = $fieldValueType;
                                    $fieldModel->setFieldInfo($fieldInfo);
                                }
                                $newFieldModel = clone $fieldModel;
                                $label = vtranslate($field->get('label'), $baseModuleModel->getName()) . '-' . vtranslate($fieldModel->get('label'), $refModule);
                                $newFieldModel->set('label', $label);
                                $values[$newblockLabel][$name] = $newFieldModel;
                            }
                        }
                    }
                }

                $commentFieldModelsList = array();
            }
        }
        $this->structuredValues = $values;
        return $values;
    }
}
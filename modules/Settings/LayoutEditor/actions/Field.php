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

class Settings_LayoutEditor_Field_Action extends Settings_Vtiger_Index_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('add');
        $this->exposeMethod('save');
        $this->exposeMethod('delete');
        $this->exposeMethod('move');
        $this->exposeMethod('unHide');
        $this->exposeMethod('updateDuplicateHandling');
    }

    public function add(Vtiger_Request $request)
    {
        $type = $request->get('fieldType');
        $moduleName = $request->get('sourceModule');
        $blockId = $request->get('blockid');
        $moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
        $response = new Vtiger_Response();
        try {
            $fieldModel = $moduleModel->addField($type, $blockId, $request->getAll());
            $fieldInfo = $fieldModel->getFieldInfo();
            $responseData = array_merge(['id' => $fieldModel->getId(), 'blockid' => $blockId, 'customField' => $fieldModel->isCustomField()], $fieldInfo);

            $defaultValue = $fieldModel->get('defaultvalue');
            $responseData['fieldDefaultValueRaw'] = $defaultValue;

            if (isset($defaultValue)) {
                if (is_array($defaultValue)) {
                    foreach ($defaultValue as $key => $value) {
                        $defaultValue[$key] = $fieldModel->getDisplayValue($value);
                    }

                    $defaultValue = Zend_Json::encode($defaultValue);
                } elseif ($defaultValue && $fieldInfo['type'] == 'date') {
                    $defaultValue = DateTimeField::convertToUserFormat($defaultValue);
                } elseif ($defaultValue) {
                    $defaultValue = $fieldModel->getDisplayValue($defaultValue);
                } else {
                    $defaultValue = '';
                }
            }

            $responseData['fieldDefaultValue'] = $defaultValue;

            $response->setResult($responseData);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function save(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $fieldId = $request->get('fieldid');
        $sourceModule = $request->get('sourceModule');

        $moduleInstance = Vtiger_Module_Model::getInstance($sourceModule);
        $fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId, $moduleInstance);

        $fieldLabel = $fieldInstance->get('label');
        $mandatory = $request->get('mandatory', null);
        $presence = $request->get('presence', null);
        $quickCreate = $request->get('quickcreate', null);
        $summaryField = $request->get('summaryfield', null);
        $massEditable = $request->get('masseditable', null);
        $headerField = $request->get('headerfield', null);

        if (!$fieldLabel) {
            $fieldInstance->set('label', $fieldLabel);
        }
        if (!empty($mandatory)) {
            $fieldInstance->updateTypeofDataFromMandatory($mandatory);
        }
        if (!empty($presence)) {
            $fieldInstance->set('presence', $presence);
        }

        if (!empty($quickCreate)) {
            $fieldInstance->set('quickcreate', $quickCreate);
        }

        if (isset($summaryField) && $summaryField != null) {
            $fieldInstance->set('summaryfield', $summaryField);
        }

        if (isset($headerField) && $headerField != null) {
            $fieldInstance->set('headerfield', $headerField);
        }

        if (!empty($massEditable)) {
            $fieldInstance->set('masseditable', $massEditable);
        }

        $defaultValue = $fieldInstance->get('defaultvalue');

        if (!is_null($request->get('fieldDefaultValue', null))) {
            if (is_array($request->get('fieldDefaultValue'))) {
                $defaultValue = decode_html(implode(' |##| ', $request->get('fieldDefaultValue')));
            } else {
                $defaultValue = decode_html($request->get('fieldDefaultValue'));
            }

            if (preg_match('/AM|PM/', $defaultValue) && ($fieldInstance->get('uitype') == '14')) {
                $defaultValue = Vtiger_Time_UIType::getTimeValueWithSeconds($defaultValue);
            }

            // Converting the date value to DB format (yyyy-mm-dd)
            if ($defaultValue && $fieldInstance->get('uitype') == '5') {
                $defaultValue = Vtiger_Date_UIType::getDBInsertedValue($defaultValue);
            }

            $fieldInstance->set('defaultvalue', $defaultValue);
        }

        $response = new Vtiger_Response();
        try {
            $fieldInstance->save();
            $fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
            $fieldLabel = decode_html($request->get('fieldLabel'));
            $fieldInfo = $fieldInstance->getFieldInfo();
            $fieldInfo['id'] = $fieldInstance->getId();

            $fieldInfo['fieldDefaultValueRaw'] = $defaultValue;
            if (isset($defaultValue)) {
                if ($defaultValue && $fieldInfo['type'] == 'date') {
                    $defaultValue = DateTimeField::convertToUserFormat($defaultValue);
                } elseif (!$defaultValue) {
                    $defaultValue = $fieldInstance->getDisplayValue($defaultValue);
                } elseif (is_array($defaultValue)) {
                    foreach ($defaultValue as $key => $value) {
                        $defaultValue[$key] = $fieldInstance->getDisplayValue($value);
                    }
                    $defaultValue = Zend_Json::encode($defaultValue);
                }
            }
            $fieldInfo['fieldDefaultValue'] = $defaultValue;

            $response->setResult(array_merge(['success' => true], $fieldInfo));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function delete(Vtiger_Request $request)
    {
        $fieldId = $request->get('fieldid');
        $fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
        $response = new Vtiger_Response();

        if (!$fieldInstance->isCustomField()) {
            $response->setError('122', 'Cannot delete Non custom field');
            $response->emit();

            return;
        }

        try {
            $this->_deleteField($fieldInstance);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    private function _deleteField($fieldInstance)
    {
        $sourceModule = $fieldInstance->get('block')->module->name;
        $fieldLabel = $fieldInstance->get('label');
        if ($fieldInstance->uitype == 16 || $fieldInstance->uitype == 33) {
            $pickListValues = $fieldInstance->getEditablePicklistValues($fieldInstance->name);
            $fieldLabel = array_merge([$fieldLabel], $pickListValues);
        }
        $fieldInstance->delete();
//        Settings_LayoutEditor_Module_Model::removeLabelFromLangFile($sourceModule, $fieldLabel);
        //we should delete any update field workflow associated with custom field
        $moduleName = $fieldInstance->getModule()->getName();
        Settings_Workflows_Record_Model::deleteUpadateFieldWorkflow($moduleName, $fieldInstance->getFieldName());
    }

    public function move(Vtiger_Request $request)
    {
        $updatedFieldsList = $request->get('updatedFields');

        // for Clearing cache we need Module Model
        $sourceModule = $request->get('selectedModule');
        $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);

        //This will update the fields sequence for the updated blocks
        Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($updatedFieldsList, $moduleModel);

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }

    public function unHide(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        try {
            $fieldIds = $request->get('fieldIdList');
            Settings_LayoutEditor_Field_Model::makeFieldActive($fieldIds, $request->get('blockId'), $request->get('selectedModule'));
            $responseData = [];
            foreach ($fieldIds as $fieldId) {
                $fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
                $fieldInfo = $fieldModel->getFieldInfo();
                //The default value is set to response after reactivating the field.
                $defaultValue = $fieldModel->getDefaultFieldValue();

                if (isset($defaultValue)) {
                    if ($defaultValue && $fieldInfo['type'] === 'date') {
                        $defaultValue = DateTimeField::convertToUserFormat($defaultValue);
                    } elseif (!$defaultValue) {
                        $defaultValue = $fieldModel->getDisplayValue($defaultValue);
                    } elseif (is_array($defaultValue)) {
                        foreach ($defaultValue as $key => $value) {
                            $defaultValue[$key] = $fieldModel->getDisplayValue($value);
                        }
                        $defaultValue = Zend_Json::encode($defaultValue);
                    }
                }

                $fieldInfo['fieldDefaultValue'] = $defaultValue;
                $responseData[] = array_merge(['id' => $fieldModel->getId(), 'blockid' => $fieldModel->get('block')->id, 'customField' => $fieldModel->isCustomField()],
                    $fieldInfo);
            }
            $response->setResult($responseData);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function updateDuplicateHandling(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        try {
            $sourceModule = $request->get('sourceModule');
            $moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);

            $fieldIdsList = $request->get('fieldIdsList');
            $result = $moduleModel->updateDuplicateHandling($request->get('rule'), $fieldIdsList, $request->get('syncActionId'));

            $response->setResult($result);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
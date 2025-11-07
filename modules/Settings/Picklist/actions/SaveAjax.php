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

class Settings_Picklist_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
    function __construct()
    {
        $this->exposeMethod('add');
        $this->exposeMethod('rename');
        $this->exposeMethod('remove');
        $this->exposeMethod('assignValueToRole');
        $this->exposeMethod('saveOrder');
        $this->exposeMethod('enableOrDisable');
        $this->exposeMethod('edit');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        $this->invokeExposedMethod($mode, $request);
    }

    public function add(Vtiger_Request $request)
    {
        $newValues = $request->getRaw('newValue');
        $pickListName = $request->get('picklistName');
        $moduleName = $request->get('source_module');
        $selectedColor = $request->get('selectedColor');
        $moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListName, $moduleModel);
        $rolesSelected = [];
        if ($fieldModel->isRoleBased()) {
            $userSelectedRoles = $request->get('rolesSelected', []);
            //selected all roles option
            if (in_array('all', $userSelectedRoles)) {
                $roleRecordList = Settings_Roles_Record_Model::getAll();
                foreach ($roleRecordList as $roleRecord) {
                    $rolesSelected[] = $roleRecord->getId();
                }
            } else {
                $rolesSelected = $userSelectedRoles;
            }
        }
        $response = new Vtiger_Response();
        try {
            $newValuesArray = explode(',', $newValues);
            $result = [];
            foreach ($newValuesArray as $i => $newValue) {
                $id = $moduleModel->addPickListValues($fieldModel, trim($newValue), $rolesSelected, $selectedColor);
                $result['id' . $i] = $id['id'];
            }
            $moduleModel->handleLabels($moduleName, $newValuesArray, [], 'add');
            $response->setResult($result);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function rename(Vtiger_Request $request)
    {
        $moduleName = $request->get('source_module');

        $newValue = $request->getRaw('newValue');
        $pickListFieldName = $request->get('picklistName');

        // we should clear cache to update with latest values
        $rolesList = $request->get('rolesList');
        $color = $request->get('selectedColor');

        $oldValue = $request->getRaw('oldValue');
        $id = $request->getRaw('id');

        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        try {
            $status = $moduleModel->renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id, $rolesList, $color);
            $moduleModel->handleLabels($moduleName, $newValue, $oldValue, 'rename');
            $response->setResult(['success', $status]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function remove(Vtiger_Request $request)
    {
        $moduleName = $request->get('source_module');
        $valueToDelete = $request->getRaw('delete_value');
        $replaceValue = $request->getRaw('replace_value');
        $pickListFieldName = $request->get('picklistName');
        $moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
        $pickListDeleteValue = $moduleModel->getActualPicklistValues($valueToDelete, $pickListFieldName);

        $response = new Vtiger_Response();
        try {
            $status = $moduleModel->remove($pickListFieldName, $valueToDelete, $replaceValue, $moduleName);
            $moduleModel->handleLabels($moduleName, [], $pickListDeleteValue, 'delete');
            $response->setResult(['success', $status]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    /**
     * Function which will assign existing values to the roles
     *
     * @param Vtiger_Request $request
     */
    public function assignValueToRole(Vtiger_Request $request)
    {
        $pickListFieldName = $request->get('picklistName');
        $valueToAssign = $request->getRaw('assign_values');
        $userSelectedRoles = $request->get('rolesSelected');

        $roleIdList = [];
        //selected all roles option
        if (in_array('all', $userSelectedRoles)) {
            $roleRecordList = Settings_Roles_Record_Model::getAll();
            foreach ($roleRecordList as $roleRecord) {
                $roleIdList[] = $roleRecord->getId();
            }
        } else {
            $roleIdList = $userSelectedRoles;
        }

        $moduleModel = new Settings_Picklist_Module_Model();

        $response = new Vtiger_Response();
        try {
            $moduleModel->enableOrDisableValuesForRole($pickListFieldName, $valueToAssign, [], $roleIdList);
            $response->setResult(['success', true]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function saveOrder(Vtiger_Request $request)
    {
        $pickListFieldName = $request->get('picklistName');

        // we should clear cache to update with latest values
        $rolesList = $request->get('rolesList');

        $picklistValues = $request->getRaw('picklistValues');

        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        try {
            $moduleModel->updateSequence($pickListFieldName, $picklistValues, $rolesList);
            $response->setResult(['success', true]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function enableOrDisable(Vtiger_Request $request)
    {
        $pickListFieldName = $request->get('picklistName');
        $enabledValues = $request->getRaw('enabled_values', []);
        $disabledValues = $request->getRaw('disabled_values', []);
        $roleSelected = $request->get('rolesSelected');

        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        try {
            $moduleModel->enableOrDisableValuesForRole($pickListFieldName, $enabledValues, $disabledValues, [$roleSelected]);
            $response->setResult(['success', true]);
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

    public function edit(Vtiger_Request $request)
    {
        $moduleName = $request->get('source_module');

        $newValue = $request->getRaw('newValue');
        $pickListFieldName = $request->get('picklistName');
        $nonEditablePicklistValues = Settings_Picklist_Field_Model::getNonEditablePicklistValues($pickListFieldName);

        // we should clear cache to update with latest values
        $rolesList = $request->get('rolesList');
        $color = $request->get('selectedColor');

        $oldValue = $request->getRaw('oldValue');
        $id = $request->getRaw('id');

        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        if ($oldValue != $newValue && empty($nonEditablePicklistValues[$id])) {
            try {
                $status = $moduleModel->renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id, $rolesList, $color);
                $response->setResult(['success', $status]);
            } catch (Exception $e) {
                $response->setError($e->getCode(), $e->getMessage());
            }
        } else {
            if ($color) {
                $status = $moduleModel->updatePicklistColor($pickListFieldName, $id, $color);
                $response->setResult(['success', $status]);
            }
        }

        $response->emit();
    }
}
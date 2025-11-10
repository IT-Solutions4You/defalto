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

class Settings_Webforms_Save_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        $moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if (!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $qualifiedModuleName = $request->getModule(false);

        if ($recordId) {
            $recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
            $recordModel->set('mode', 'edit');
        } else {
            $recordModel = Settings_Webforms_Record_Model::getCleanInstance($qualifiedModuleName);
            $recordModel->set('mode', '');
        }

        $fieldsList = $recordModel->getModule()->getFields();
        $supportedModules = Settings_Webforms_Module_Model::getSupportedModulesList();

        foreach ($fieldsList as $fieldName => $fieldModel) {
            $fieldValue = $request->get($fieldName);

            if (!$fieldValue) {
                $fieldValue = $fieldModel->get('defaultvalue');
            }

            $fieldValueString = is_array($fieldValue) ? implode(',', $fieldValue) : $fieldValue;

            if ($fieldModel->isMandatory() && empty(trim($fieldValueString))) {
                $label = vtranslate($fieldModel->get('label'), $qualifiedModuleName);
                throw new Exception(vtranslate('LBL_MANDATORY_FIELD_MISSING', 'Vtiger', $label));
            } elseif ($fieldName == 'targetmodule' && !array_key_exists($fieldValue, $supportedModules)) {
                throw new Exception(vtranslate('LBL_TARGET_MODULE_IS_NOT_SUPPORTED_TO_CREATE_WEBFORM', 'Vtiger'));
            }

            $recordModel->set($fieldName, $fieldValue);
        }

        $fileFields = [];
        if (is_array($request->get('file_field'))) {
            $fileFields = $request->get('file_field');
        }
        $recordModel->set('file_fields', $fileFields);

        $returnUrl = $recordModel->getModule()->getListViewUrl();
        $recordModel->set('selectedFieldsData', $request->get('selectedFieldsData'));
        $selectedFieldsData = $request->get('selectedFieldsData');

        if (empty($selectedFieldsData)) {
            throw new Exception(vtranslate('LBL_MANDATORY_FIELDS_MISSING', 'Vtiger'));
        }

        if (!$recordModel->checkDuplicate()) {
            $recordModel->save();
            $returnUrl = $recordModel->getDetailViewUrl();
        }

        header("Location: $returnUrl");
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
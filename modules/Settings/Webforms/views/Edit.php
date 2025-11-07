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

class Settings_Webforms_Edit_View extends Settings_Vtiger_Index_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);

        $moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if (!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $qualifiedModuleName = $request->getModule(false);
        $mode = '';
        $selectedFieldsList = $allFieldsList = $fileFields = [];
        $viewer = $this->getViewer($request);
        $supportedModules = Settings_Webforms_Module_Model::getSupportedModulesList();

        if ($recordId) {
            $recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
            $selectedFieldsList = $recordModel->getSelectedFieldsList();
            $fileFields = $recordModel->getFileFields();

            $sourceModule = $recordModel->get('targetmodule');
            $mode = 'edit';
        } else {
            $recordModel = Settings_Webforms_Record_Model::getCleanInstance($qualifiedModuleName);
            $sourceModule = $request->get('sourceModule');
            if (!$sourceModule) {
                $arrayKeys = array_keys($supportedModules);
                $sourceModule = reset($arrayKeys);
            }
            $recordModel->set('targetmodule', $sourceModule);
        }
        if (!$supportedModules[$sourceModule]) {
            $message = vtranslate('LBL_ENABLE_TARGET_MODULES_FOR_WEBFORM', $qualifiedModuleName);
            $viewer->assign('MESSAGE', $message);
            $viewer->view('OperationNotPermitted.tpl', 'Vtiger');

            return false;
        }

        $allFieldsList = $recordModel->getAllFieldsList($sourceModule);
        $recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $moduleModel = $recordModel->getModule();

        $viewer->assign('MODE', $mode);
        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('MODULE', $qualifiedModuleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        $viewer->assign('SOURCE_MODULE', $sourceModule);
        $viewer->assign('ALL_FIELD_MODELS_LIST', $allFieldsList);
        $viewer->assign('SELECTED_FIELD_MODELS_LIST', $selectedFieldsList);
        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructure);
        $viewer->assign('RECORD_STRUCTURE', $recordStructure->getStructure());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('DOCUMENT_FILE_FIELDS', $fileFields);
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('EditView.tpl', $qualifiedModuleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.Settings.$moduleName.resources.Field",
            "modules.Settings.$moduleName.resources.Edit"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    public function setModuleInfo($request, $moduleModel)
    {
        $record = $request->get('record');
        if ($record) {
            parent::setModuleInfo($request, $moduleModel);
        }
    }
}
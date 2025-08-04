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

class Vtiger_Edit_View extends Vtiger_Index_View
{
    protected $record = false;

    function __construct()
    {
        parent::__construct();
    }

    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $record = $request->get('record');
        $actionName = 'CreateView';
        if ($record && !$request->get('isDuplicate')) {
            $actionName = 'EditView';
        }
        $permissions[] = ['module_parameter' => 'module', 'action' => $actionName, 'record_parameter' => 'record'];

        return $permissions;
    }

    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $record = $request->get('record');

        $nonEntityModules = ['Users', 'Portal', 'Rss'];
        if ($record && !in_array($moduleName, $nonEntityModules)) {
            $recordEntityName = getSalesEntityType($record);
            if ($recordEntityName !== $moduleName) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return parent::checkPermission($request);
    }

    public function setModuleInfo($request, $moduleModel)
    {
        $fieldsInfo = [];
        $basicLinks = [];
        $settingLinks = [];

        $moduleFields = $moduleModel->getFields();
        foreach ($moduleFields as $fieldName => $fieldModel) {
            $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
        $viewer->assign('MODULE_BASIC_ACTIONS', $basicLinks);
        $viewer->assign('MODULE_SETTING_ACTIONS', $settingLinks);
    }

    function preProcess(Vtiger_Request $request, $display = true)
    {
        //Vtiger7 - TO show custom view name in Module Header
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $record = $request->get('record');

        if (!empty($record) && $moduleModel->isEntityModule()) {
            $recordModel = $this->record ?: Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('RECORD', $recordModel);
        }

        $duplicateRecordsList = [];
        $duplicateRecords = $request->get('duplicateRecords');

        if (is_array($duplicateRecords)) {
            $duplicateRecordsList = $duplicateRecords;
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('DUPLICATE_RECORDS', $duplicateRecordsList);

        parent::preProcess($request, $display);
    }

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $record = $request->get('record');

        if (!empty($record) && $request->get('isDuplicate') == true) {
            $recordModel = $this->record ?: Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('MODE', '');
            //While Duplicating record, If the related record is deleted then we are removing related record info in record model
            $mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();

            foreach ($mandatoryFieldModels as $fieldModel) {
                if ($fieldModel->isReferenceField()) {
                    $fieldName = $fieldModel->get('name');
                    if (Vtiger_Util_Helper::checkRecordExistance($recordModel->get($fieldName))) {
                        $recordModel->set($fieldName, '');
                    }
                }
            }
        } elseif (!empty($record)) {
            $recordModel = $this->record ?: Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('MODE', 'edit');
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $viewer->assign('MODE', '');
        }

        if ($request->has('sourceModule') && $request->has('sourceRecord')) {
            $sourceRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
            $mapping = Core_Base_Mapping::getInstance($recordModel, $sourceRecordModel);
            $mapping->mapFields();
        }

        if (!$this->record) {
            $this->record = $recordModel;
            $viewer->assign('RECORD', $recordModel);
        }

        $moduleModel = $recordModel->getModule();
        $fieldList = $moduleModel->getFields();
        $requestFieldList = array_intersect_key($request->getAllPurified(), $fieldList);
        $relContactId = $request->get('contact_id');

        foreach ($requestFieldList as $fieldName => $fieldValue) {
            $fieldModel = $fieldList[$fieldName];
            $specialField = false;

            if ($fieldModel->isEditable() || $specialField) {
                $recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
            }
        }

        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $isRelationOperation = $request->get('relationOperation');

        //if it is relation edit
        $viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);

        $viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
        $viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));

        // added to set the return values
        if ($request->get('returnview')) {
            $request->setViewerReturnValues($viewer);
        }

        $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());

        if ($request->get('displayMode') == 'overlay') {
            $viewer->assign('SCRIPTS', $this->getOverlayHeaderScripts($request));
            $viewer->view('OverlayEditView.tpl', $moduleName);
        } else {
            $viewer->view('EditView.tpl', $moduleName);
        }
    }

    public function getOverlayHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = [
            "modules.$moduleName.resources.Edit",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return $jsScriptInstances;
    }
}
<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Documents_EditAjax_View extends Vtiger_QuickCreateAjax_View
{
    public function getFields($documentType)
    {
        switch ($documentType) {
            case 'I' :
            case 'E' :
                return ['filename', 'assigned_user_id', 'folderid'];
            case 'W' :
                return ['notes_title', 'assigned_user_id', 'folderid', 'filename', 'fileversion'];
        }
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $moduleModel = $recordModel->getModule();
        $showType = $documentType = $request->get('type');

        $fieldNames = $this->getFields($documentType);
        $allFields = $moduleModel->getFields();
        if ($documentType == 'W') {
            $documentType = 'I';
            //To Add Custom fields for webdocument create view
            $fieldsToEliminate = [
                'document_source',
                'createdtime',
                'modifiedtime',
                'filetype',
                'filesize',
                'filedownloadcount',
                'folderid',
                'note_no',
                'modifiedby',
                'created_user_id'
            ];
            $fieldsToEliminate = array_merge($fieldNames, $fieldsToEliminate);
            $allFieldsNames = array_keys($allFields);
            $documentsCustomFields = array_diff($allFieldsNames, $fieldsToEliminate);
            $fieldNames = array_diff(array_merge($fieldNames, $documentsCustomFields), ['notecontent']);
            //Add note content as the last field
            $fieldNames[] = 'notecontent';
        }
        if ($request->get('relationOperation') == 'true') {
            $requestFieldList = array_intersect_key($request->getAll(), $allFields);
        }
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = $recordStructureInstance->getStructure();
        foreach ($recordStructure as $blocks) {
            foreach ($blocks as $fieldLabel => $fieldValue) {
                if ($requestFieldList && array_key_exists($fieldLabel, $requestFieldList)) {
                    $relationFieldName = $fieldLabel;
                    $fieldValue->set('fieldvalue', $request->get($fieldLabel));
                }
                if (in_array($fieldLabel, $fieldNames)) {
                    $fieldModel[] = $fieldValue;
                }
            }
        }
        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

        $viewer = $this->getViewer($request);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('FIELD_MODELS', $fieldModel);
        $viewer->assign('DOCUMENT_TYPE', $documentType);
        $viewer->assign('DOCUMENT_SHOW_TYPE', $showType);
        if ($request->get('relationOperation')) {
            $viewer->assign('RELATION_OPERATOR', $request->get('relationOperation'));
            $viewer->assign('PARENT_MODULE', $request->get('sourceModule'));
            $viewer->assign('PARENT_ID', $request->get('sourceRecord'));
            if ($relationFieldName) {
                $viewer->assign('RELATION_FIELD_NAME', $relationFieldName);
            }
        }
        $viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

        $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        echo $viewer->view('AjaxEdit.tpl', $moduleName, true);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $moduleName = $request->getModule();
        $jsFileNames = [
            "modules.$moduleName.resources.Edit"
        ];

        Core_Modifiers_Model::modifyVariableForClass(get_class($this), 'getHeaderScripts', $request->getModule(), $jsFileNames, $request);

        return $this->checkAndConvertJsScripts($jsFileNames);
    }
}
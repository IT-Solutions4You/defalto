<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_QuickEditAjax_View extends Vtiger_IndexAjax_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        if (!(Users_Privileges_Model::isPermitted($moduleName, 'EditView'))) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $projectId = $request->get('parentid');
        $recordId = $request->get('record');

        if ($recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        }

        $moduleModel = $recordModel->getModule();

        $fieldList = $moduleModel->getFields();
        $fieldsInfo = [];
        foreach ($fieldList as $name => $model) {
            $fieldsInfo[$name] = $model->getFieldInfo();
        }
        $requestFieldList = array_intersect_key($request->getAll(), $fieldList);

        if ($projectId) {
            $recordModel->set('projectid', $projectId);
        }

        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, 'GanttQuickEdit');
        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

        $viewer = $this->getViewer($request);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
        $viewer->assign('RECORD', $recordId);
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

        $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
        $viewer->assign('RETURN_VIEW', $request->get('returnview'));
        $viewer->assign('RETURN_MODE', $request->get('returnmode'));
        $viewer->assign('RETURN_MODULE', $request->get('returnmodule'));
        $viewer->assign('RETURN_RECORD', $request->get('returnrecord'));
        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('QuickEdit.tpl', $moduleName);
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

        $modifiers = Core_Modifiers_Model::getForClass(get_class($this), $request->getModule());

        foreach ($modifiers as $modifier) {
            if (method_exists($modifier, 'modifyGetHeaderScripts')) {
                $jsFileNames = array_merge($jsFileNames, $modifier->modifyGetHeaderScripts($request));
            }
        }

        return $this->checkAndConvertJsScripts($jsFileNames);
    }
}
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

class Users_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $recordModel = Users_Record_Model::getCleanInstance($moduleName);
        $moduleModel = $recordModel->getModule();

        $fieldList = $moduleModel->getFields();
        $requestFieldList = array_intersect_key($request->getAll(), $fieldList);

        foreach ($requestFieldList as $fieldName => $fieldValue) {
            $fieldModel = $fieldList[$fieldName];
            if ($fieldModel->isEditable()) {
                $recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
            }
        }

        $recordStructureInstance = Users_RecordStructure_Model::getInstanceFromRecordModel(
            $recordModel,
            Users_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE
        );

        $viewer = $this->getViewer($request);
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('SINGLE_MODULE', 'SINGLE_' . $moduleName);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        echo $viewer->view('QuickCreate.tpl', $moduleName, true);
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
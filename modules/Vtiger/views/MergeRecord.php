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

class Vtiger_MergeRecord_View extends Vtiger_Popup_View
{
    var $mergeRecordIds = [];

    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'EditView'];

        return $permissions;
    }

    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $records = $request->get('records');
        $records = explode(',', $records);

        foreach ($records as $record) {
            $moduleName = getSalesEntityType($record);
            $permissionStatus = Users_Privileges_Model::isPermitted($moduleName, 'EditView', $record);
            if ($permissionStatus) {
                $this->mergeRecordIds[] = $record;
            }
            if (empty($this->mergeRecordIds)) {
                throw new Exception(vtranslate('LBL_RECORD_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $fieldModels = $moduleModel->getFields();
        if (!empty($this->mergeRecordIds)) {
            $records = $this->mergeRecordIds;
        }

        foreach ($records as $record) {
            $recordModels[] = Vtiger_Record_Model::getInstanceById($record);
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORDS', $records);
        $viewer->assign('RECORDMODELS', $recordModels);
        $viewer->assign('FIELDS', $fieldModels);
        $viewer->assign('MODULE', $module);
        $viewer->view('MergeRecords.tpl', $module);
    }
}
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

class Vtiger_Delete_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];
        $permissions[] = ['module_parameter' => 'module', 'action' => 'Delete', 'record_parameter' => 'record'];

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $moduleName = $request->getModule();
        $record = $request->get('record');

        parent::checkPermission($request);

        $nonEntityModules = ['Users', 'Portal', 'Rss'];
        if ($record && !in_array($moduleName, $nonEntityModules)) {
            $recordEntityName = getSalesEntityType($record);
            if ($recordEntityName !== $moduleName) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $ajaxDelete = $request->get('ajaxDelete');
        $recurringEditMode = $request->get('recurringEditMode');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('recurringEditMode', $recurringEditMode);
        $moduleModel = $recordModel->getModule();

        $recordModel->delete();
        $cv = new CustomView();
        $cvId = $cv->getViewId($moduleName);
        deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);
        $listViewUrl = $moduleModel->getListViewUrl();
        if ($ajaxDelete) {
            $response = new Vtiger_Response();
            $response->setResult($listViewUrl);

            return $response;
        } else {
            header("Location: $listViewUrl");
        }
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
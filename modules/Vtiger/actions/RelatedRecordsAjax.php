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

class Vtiger_RelatedRecordsAjax_Action extends Core_Controller_Action
{
    var $relationModules = [];

    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getRelatedRecordsCount');
    }

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'recordId'];

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);
        $parentModule = $request->get("module");
        $parentModuleModel = Vtiger_Module_Model::getInstance($parentModule);
        $relationModels = $parentModuleModel->getRelations();
        foreach ($relationModels as $relation) {
            $relatedModuleName = $relation->get('relatedModuleName');
            $permissionStatus = Users_Privileges_Model::isPermitted($relatedModuleName, 'DetailView');
            if ($permissionStatus) {
                $this->relationModules[] = $relation;
            }
        }
        if (empty($this->relationModules)) {
            throw new Exception(vtranslate('LBL_RELATED_MODULES_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    /**
     * Function to get count of all related module records of a given record
     *
     * @param type $request
     */
    function getRelatedRecordsCount($request)
    {
        $parentRecordId = $request->getForSql("recordId");
        $parentModule = $request->get("module");
        $parentModuleModel = Vtiger_Module_Model::getInstance($parentModule);
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleModel);
        $relationModels = $this->relationModules;
        $relatedRecordsCount = [];
        foreach ($relationModels as $relation) {
            $relationId = $relation->getId();
            $relatedModuleName = $relation->get('relatedModuleName');
            $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $relation->get('label'));
            $count = $relationListView->getRelatedEntriesCount();
            $relatedRecordsCount[$relationId] = $count;
        }
        $response = new Vtiger_Response();
        $response->setResult($relatedRecordsCount);
        $response->emit();
    }
}
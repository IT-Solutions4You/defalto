<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class Campaigns_DetailAjax_Action extends Vtiger_BasicAjax_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getRecordsCount');
    }

    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $mode = $request->getMode();
        if (!empty($mode)) {
            switch ($mode) {
                case 'getRecordsCount':
                    $permissions[] = ['module_parameter' => 'relatedModule', 'action' => 'DetailView'];
                    break;
                default:
                    break;
            }
        }

        return $permissions;
    }

    public function checkPermission(Vtiger_Request $request)
    {
        return parent::checkPermission($request);
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
     * Function to get related Records count from this relation
     *
     * @param <Vtiger_Request> $request
     *
     * @return <Number> Number of record from this relation
     */
    public function getRecordsCount(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $relatedModuleName = $request->get('relatedModule');
        $parentId = $request->get('record');
        $label = $request->get('tab_label');

        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
        $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
        $count = $relationListView->getRelatedEntriesCount();
        $result = [];
        $result['module'] = $moduleName;
        $result['viewname'] = $cvId;
        $result['count'] = $count;

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult($result);
        $response->emit();
    }
}
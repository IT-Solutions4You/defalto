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

class Vtiger_MassDelete_Action extends Vtiger_Mass_Action
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'Delete'];

        return $permissions;
    }

    function preProcess(Vtiger_Request $request)
    {
        return true;
    }

    function postProcess(Vtiger_Request $request)
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if ($request->get('selected_ids') == 'all' && $request->get('mode') == 'FindDuplicates') {
            $recordIds = Vtiger_FindDuplicate_Model::getMassDeleteRecords($request);
        } else {
            $recordIds = $this->getRecordsListFromRequest($request);
        }
        $cvId = $request->get('viewname');
        foreach ($recordIds as $recordId) {
            if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
                $recordModel->delete();
                deleteRecordFromDetailViewNavigationRecords($recordId, $cvId, $moduleName);
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(['viewname' => $cvId, 'module' => $moduleName]);
        $response->emit();
    }
}
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

class CustomView_Delete_Action extends Vtiger_Action_Controller
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'sourceModule', 'action' => 'DetailView'];

        return $permissions;
    }

    public function checkPermission(Vtiger_Request $request)
    {
        return parent::checkPermission($request);
    }

    public function process(Vtiger_Request $request)
    {
        $customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));
        $moduleModel = $customViewModel->getModule();
        $customViewOwner = $customViewModel->getOwnerId();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        if ((!$currentUser->isAdminUser()) && ($customViewOwner != $currentUser->getId())) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }
        $customViewModel->delete();

        $listViewUrl = $moduleModel->getListViewUrl();
        if ($request->isAjax()) {
            $response = new Vtiger_Response();
            $response->setResult(['success' => true]);
            $response->emit();
        } else {
            header("Location: $listViewUrl");
        }
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
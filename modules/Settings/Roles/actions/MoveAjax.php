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

class Settings_Roles_MoveAjax_Action extends Settings_Vtiger_Basic_Action
{
    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $parentRoleId = $request->get('parent_roleid');

        $parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
        $recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        try {
            $recordModel->moveTo($parentRole);
            //on moving a role sharing privilages should be recalculated for all the users
            $allUsers = Users_Record_Model::getAll();
            foreach ($allUsers as $userId => $userModel) {
                require_once('modules/Users/CreateUserPrivilegeFile.php');
                createUserSharingPrivilegesfile($userId);
            }
        } catch (Exception $e) {
            $response->setError('Move Role Failed');
        }
        $response->emit();
    }
}
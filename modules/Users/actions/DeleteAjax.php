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

vimport('~~/include/Webservices/Custom/DeleteUser.php');

class Users_DeleteAjax_Action extends Vtiger_Delete_Action
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        return [];
    }

    public function checkPermission(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $ownerId = $request->get('userid');
        if (!$currentUser->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        } elseif ($currentUser->isAdminUser() && ($currentUser->getId() == $ownerId)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $ownerId = $request->get('userid');
        $newOwnerId = $request->get('transfer_user_id');

        $mode = $request->get('mode');
        $response = new Vtiger_Response();
        $result['message'] = vtranslate('LBL_USER_DELETED_SUCCESSFULLY', $moduleName);

        if ($mode == 'permanent') {
            Users_Record_Model::deleteUserPermanently($ownerId, $newOwnerId);
        } else {
            $userId = vtws_getWebserviceEntityId($moduleName, $ownerId);
            $transformUserId = vtws_getWebserviceEntityId($moduleName, $newOwnerId);

            $userModel = Users_Record_Model::getCurrentUserModel();

            vtws_deleteUser($userId, $transformUserId, $userModel);

            if ($request->get('permanent') == '1') {
                Users_Record_Model::deleteUserPermanently($ownerId, $newOwnerId);
            }
        }

        if ($request->get('mode') == 'deleteUserFromDetailView') {
            $usersModuleModel = Users_Module_Model::getInstance($moduleName);
            $listViewUrl = $usersModuleModel->getListViewUrl();
            $result['listViewUrl'] = $listViewUrl;
        }

        $response->setResult($result);
        $response->emit();
    }
}
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

class Settings_Profiles_Save_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        if (!$currentUser->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');

        if (!empty($recordId)) {
            $recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);
        } else {
            $recordModel = new Settings_Profiles_Record_Model();
        }
        if ($recordModel) {
            $recordModel->set('profilename', $request->get('profilename'));
            $recordModel->set('description', $request->get('description'));
            $recordModel->set('viewall', $request->get('viewall'));
            $recordModel->set('editall', $request->get('editall'));
            $recordModel->set('profile_permissions', $request->get('permissions'));
            $recordModel->save();
        }

        $redirectUrl = $recordModel->getDetailViewUrl();
        header("Location: $redirectUrl");
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
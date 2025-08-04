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

class Settings_Roles_Save_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
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
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $recordId = $request->get('record');
        $roleName = $request->get('rolename');
        $allowassignedrecordsto = $request->get('allowassignedrecordsto');
        $duplicate = Settings_Roles_Record_Model::getInstanceByName($roleName, [$recordId]);

        if ($duplicate) {
            throw new Exception(vtranslate('LBL_DUPLICATES_EXIST', $request->getModule(false)));
        }

        $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
        if (!empty($recordId)) {
            $recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
        } else {
            $recordModel = new Settings_Roles_Record_Model();
        }

        if ($request->get('profile_directly_related_to_role') == '1') {
            $profileId = $request->get('profile_directly_related_to_role_id');
            $profileName = $request->get('profilename');
            if (empty($profileName)) {
                $profileName = $roleName . '+' . vtranslate('LBL_PROFILE', $qualifiedModuleName);
            }
            if ($profileId) {
                $profileRecordModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
            } else {
                $profileRecordModel = Settings_Profiles_Record_Model::getInstanceByName($profileName, true);
                if (empty($profileRecordModel)) {
                    $profileRecordModel = new Settings_Profiles_Record_Model();
                }
            }
            $profileRecordModel->set('directly_related_to_role', '1');

            $profileRecordModel->set('profilename', $profileName)
                ->set('profile_permissions', $request->get('permissions'));
            $profileRecordModel->set('viewall', $request->get('viewall'));
            $profileRecordModel->set('editall', $request->get('editall'));
            $savedProfileId = $profileRecordModel->save();
            $roleProfiles = [$savedProfileId];
        } else {
            $roleProfiles = $request->get('profiles');
        }

        $parentRoleId = $request->get('parent_roleid');
        if ($recordModel && !empty($parentRoleId)) {
            $parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
            if (!empty($allowassignedrecordsto)) {
                $recordModel->set('allowassignedrecordsto', $allowassignedrecordsto);
            } // set the value of assigned records to
            if ($parentRole && !empty($roleName) && !empty($roleProfiles)) {
                $recordModel->set('rolename', $roleName);
                $recordModel->set('profileIds', $roleProfiles);
                $parentRole->addChildRole($recordModel);
            }

            //After role updation recreating user privilege files
            if ($roleProfiles) {
                foreach ($roleProfiles as $profileId) {
                    $profileRecordModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
                    $profileRecordModel->recalculate([$recordId]);
                }
            }
        }

        $redirectUrl = $moduleModel->getDefaultUrl();
        header("Location: $redirectUrl");
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
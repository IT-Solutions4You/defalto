<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Appointments_Groups_Model extends Vtiger_Base_Model
{
    public static function getInstance()
    {
        return new self();
    }

    public function getUsersList($groupId)
    {
        $userIdsList = $usersList = array();
        $membersModel = Settings_Groups_Record_Model::getInstance($groupId);
        $members = $membersModel->getMembers();

        foreach ($members['Users'] as $memberModel) {
            $userId = $memberModel->get('userId');
            $userIdsList[$userId] = $userId;
        }

        foreach ($members['Groups'] as $memberModel) {
            $groupModel = Settings_Groups_Record_Model::getInstance($memberModel->get('groupId'));
            $groupMembers = $groupModel->getMembers();

            foreach ($groupMembers['Users'] as $groupMemberModel) {
                $userId = $groupMemberModel->get('userId');
                $userIdsList[$userId] = $userId;
            }
        }

        foreach ($members['Roles'] as $memberModel) {
            $roleUsers = getRoleUsers($memberModel->get('roleId'));

            foreach ($roleUsers as $userId => $userLabel) {
                $userIdsList[$userId] = $userId;
            }
        }

        foreach ($members['RoleAndSubordinates'] as $memberModel) {
            $roleUsers = getRoleUsers($memberModel->get('roleId'));

            foreach ($roleUsers as $userId => $userLabel) {
                $userIdsList[$userId] = $userId;
            }

            $subordinateRoles = getRoleSubordinates($memberModel->get('roleId'));

            foreach ($subordinateRoles as $subordinateRole) {
                $roleUsers = getRoleUsers($subordinateRole);

                foreach ($roleUsers as $userId => $userLabel) {
                    $userIdsList[$userId] = $userId;
                }
            }
        }

        foreach ($userIdsList as $userId) {
            $userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
            $usersList[$userId] = $userRecordModel;
        }

        return $usersList;
    }
}
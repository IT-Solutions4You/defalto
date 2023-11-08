<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_UsersGroups_Model extends Vtiger_Base_Model
{
    /**
     * @return array
     */
    public function getAll(): array
    {
        $usersAndGroups = [];
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = (array)$currentUser->getAccessibleUsers();

        foreach ($users as $userId => $userName) {
            if (empty($userName)) {
                continue;
            }

            $userName = decode_html($userName);
            $usersAndGroups['Users'][$userName] = $userName;
        }

        $groups = (array)$currentUser->getAccessibleGroups();

        foreach ($groups as $groupId => $groupName) {
            if (empty($groupName)) {
                continue;
            }

            $groupName = decode_html($groupName);
            $usersAndGroups['Groups'][$groupName] = $groupName;
            $usersAndGroups['UsersByGroups'][$groupName] = vtranslate('LBL_USERS_BY_GROUP', 'ITS4YouCalendar') . $groupName;
        }

        return array_filter($usersAndGroups);
    }

    public static function getBackground(string $value): string
    {
        $code = dechex(crc32($value));
        $color = substr($code, 0, 6);

        return '#' . $color;
    }

    public function getGroupSelected()
    {
        $values = [];

        if (!empty($_SESSION['users_groups_selected'])) {
            $values = $_SESSION['users_groups_selected'];
        }

        return $values;
    }

    public function getInfo(): array
    {
        $images = [];

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = (array)$currentUser->getAccessibleUsers();

        foreach ($users as $userId => $userName) {
            if (empty($userName)) {
                continue;
            }

            $userName = decode_html($userName);
            $recordModel = Users_Record_Model::getInstanceById($userId, 'Users');
            $imageDetails = $recordModel->getImageDetails();
            $userBackground = self::getBackground($userId);
            $id = 'Users::::' . $userName;
            $images[$id][$userId] = [
                'id' => $id,
                'name' => $userName,
                'icon' => 'fa fa-user',
                'image' => $imageDetails[0]['url'],
                'label' => mb_strtoupper(mb_substr($userName, 0, 2)),
                'background' => $userBackground,
                'border' => $userBackground,
                'color' => Settings_Picklist_Module_Model::getTextColor($userBackground),
            ];
        }

        $groups = (array)$currentUser->getAccessibleGroups();

        foreach ($groups as $groupId => $groupName) {
            if (empty($groupName)) {
                continue;
            }

            $groupName = decode_html($groupName);
            $groupBackground = self::getBackground($groupId);
            $id = 'Groups::::' . $groupName;
            $images[$id][$groupId] = [
                'id' => $id,
                'name' => $groupName,
                'icon' => 'fa fa-group',
                'label' => mb_strtoupper(mb_substr($groupName, 0, 2)),
                'background' => $groupBackground,
                'border' => $groupBackground,
                'color' => Settings_Picklist_Module_Model::getTextColor($groupBackground),
            ];
            $id = 'UsersByGroups::::' . $groupName;
            $images[$id][$groupId] = [
                'id' => $id,
                'name' => vtranslate('Users by', 'ITS4YouCalendar') . ' ' . $groupName,
                'icon' => 'fa fa-group',
                'label' => mb_strtoupper(mb_substr($groupName, 0, 2)),
                'background' => $groupBackground,
                'border' => $groupBackground,
                'color' => Settings_Picklist_Module_Model::getTextColor($groupBackground),
            ];
        }

        return $images;
    }

    public static function getInstance()
    {
        return new self();
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        $userRecord = Users_Record_Model::getCurrentUserModel();
        $userId = $userRecord->getId();
        $groups = ITS4YouCalendar_Groups_Model::getInstance();
        $tabs = [];

        foreach ($userRecord->getAccessibleGroups() as $groupId => $groupName) {
            $groupName = decode_html($groupName);
            $groupUsers = $groups->getUsersList($groupId);
            $groupWithUsers = [];

            if (empty($groupUsers[$userId]) && !$userRecord->isAdminUser()) {
                continue;
            }

            $groupWithUsers[] = 'Groups::::' . $groupName;

            foreach ($groupUsers as $groupUser) {
                $groupWithUsers[] = 'Users::::' . decode_html($groupUser->getName());
            }

            $tabs['Groups::::' . $groupId] = [
                'name' => $groupName,
                'values' => $groupWithUsers,
            ];
        }

        return $tabs;
    }

    /**
     * @return array
     */
    public function getUserSelected()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        return [
            'Users::::' . $currentUser->getName(),
        ];
    }

    /**
     * @return array
     */
    public function getUsersSelected()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = getRoleAndSubordinateUsers($currentUser->get('roleid'));
        $values = [];

        foreach ($users as $userId => $userName) {
            $userRecord = Users_Record_Model::getInstanceById($userId, 'Users');
            $values[] = 'Users::::' . $userRecord->getName();
        }

        return $values;
    }
}
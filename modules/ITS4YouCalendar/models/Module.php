<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Module_Model extends Vtiger_Module_Model
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public static function retrieveDefaultValuesForEdit(Vtiger_Request $request)
    {
        if ($request->isEmpty('record')) {
            $currentUser = Users_Record_Model::getCurrentUserModel();

            $request->set('calendar_status', $currentUser->get('defaulteventstatus'));
            $request->set('calendar_type', $currentUser->get('defaultactivitytype'));
        }
    }

    /**
     * @return array
     */
    public function getModuleBasicLinks(): array
    {
        $basicLinks = [];

        if ('Calendar' !== $_REQUEST['view']) {
            $basicLinks[] = [
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_CALENDAR',
                'linkurl' => 'index.php?module=ITS4YouCalendar&view=Calendar',
                'linkicon' => 'fa-calendar',
            ];
        } else {
            $basicLinks[] = [
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_LIST',
                'linkurl' => 'index.php?module=ITS4YouCalendar&view=List',
                'linkicon' => 'fa-bars',
            ];
        }

        return array_merge($basicLinks, parent::getModuleBasicLinks());
    }

    /**
     * @return array
     */
    public function getUsersAndGroups(): array
    {
        $usersAndGroups = [];
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = (array)$currentUser->getAccessibleUsers();

        foreach ($users as $userId => $userName) {
            if(empty($userName)) {
                continue;
            }

            $usersAndGroups['Users'][$userName] = $userName;
        }

        $groups = (array)$currentUser->getAccessibleGroups();

        foreach($groups as $groupId => $groupName) {
            if(empty($groupName)) {
                continue;
            }

            $usersAndGroups['Groups'][$groupName] = $groupName;
            $usersAndGroups['UsersByGroups'][$groupName] = vtranslate('LBL_USERS_BY_GROUP', 'ITS4YouCalendar') . $groupName;
        }

        return array_filter($usersAndGroups);
    }

    /**
     * @return array
     */
    public function getUsersAndGroupsInfo(): array
    {
        $images = [];

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = (array)$currentUser->getAccessibleUsers();

        foreach ($users as $userId => $userName) {
            if(empty($userName)) {
                continue;
            }

            $recordModel = Users_Record_Model::getInstanceById($userId, 'Users');
            $imageDetails = $recordModel->getImageDetails();
            $userBackground = $this->getUserGroupBackground($userName);
            $images['Users::::' . $userName][$userId] = [
                'name' => $userName,
                'icon' => 'fa fa-user',
                'image' => $imageDetails[0]['url'],
                'label' => mb_strtoupper(mb_substr($userName, 0, 2)),
                'background' => $userBackground,
                'color' => Settings_Picklist_Module_Model::getTextColor($userBackground),
            ];
        }

        $groups = (array)$currentUser->getAccessibleGroups();

        foreach ($groups as $groupId => $groupName) {
            if(empty($groupName)) {
                continue;
            }

            $groupRecordModel = Settings_Groups_Record_Model::getInstance($groupId);
            $groupBackground = $this->getUserGroupBackground($groupName);
            $images['Groups::::' . $groupName][$groupId] = [
                'name' => $groupName,
                'icon' => 'fa fa-group',
                'label' => mb_strtoupper(mb_substr($groupName, 0, 2)),
                'background' => $groupBackground,
                'color' => Settings_Picklist_Module_Model::getTextColor($groupBackground),
            ];

            $usersRecordModel = $groupRecordModel->getUsersList();

            /** @var Users_Record_Model $userRecordModel */
            foreach ($usersRecordModel as $recordModel) {
                $imageDetails = $recordModel->getImageDetails();
                $userName = $recordModel->getName();
                $userBackground = $this->getUserGroupBackground($userName);
                $images['UsersByGroups::::' . $groupName][$recordModel->getId()] = [
                    'name' => $userName,
                    'image' => $imageDetails[0]['url'],
                    'icon' => 'fa fa-user',
                    'label' => mb_strtoupper(mb_substr($userName, 0, 2)),
                    'background' => $userBackground,
                    'color' => Settings_Picklist_Module_Model::getTextColor($userBackground),
                ];
            }
        }

        return $images;
    }

    public function getUserGroupBackground(string $value): string
    {
        $code = dechex(crc32($value));

        return '#' . mb_substr($code, 0, 6);
    }

    /**
     * @return array
     */
    public function getSettingLinks(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $settingsLinks = [];
        $settingsLinks[] = array(
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_CALENDAR_SETTINGS',
            'linkurl' => 'index.php?module=Users&parent=Settings&view=Calendar&record=' . $currentUser->getId(),
        );
        $settingsLinks[] = array(
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_INTEGRATION',
            'linkurl' => 'index.php?module=ITS4YouCalendar&parent=Settings&view=Integration',
        );

        return array_merge($settingsLinks, parent::getSettingLinks());
    }

    /**
     * @return int
     */
    public function getTodayRecordsCount(): int
    {
        $moduleName = $this->getName();
        $listModel = Vtiger_ListView_Model::getInstance($moduleName);

        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $today = date('Y-m-d');

        /** @var QueryGenerator $queryGenerator */
        $queryGenerator = $listModel->get('query_generator');

        $queryGenerator->startGroup('');
        $queryGenerator->addCondition('datetime_start', $today . ',' . $tomorrow, 'bw', 'OR');
        $queryGenerator->addCondition('datetime_end', $today . ',' . $tomorrow, 'bw', 'OR');
        $queryGenerator->endGroup();

        return intval($listModel->getListViewCount());
    }
}
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

    public static function updateTodayFilterDates($filter): bool
    {
        if (!$filter && 'Today' !== trim($filter->name)) {
            return false;
        }

        $date = self::getTodayDates();

        $adb = PearDatabase::getInstance();
        $adb->pquery('UPDATE vtiger_cvadvfilter SET value=? WHERE cvid=? AND columnname LIKE ?', [$date, $filter->id, '%datetime_start%']);
        $adb->pquery('UPDATE vtiger_cvadvfilter SET value=? WHERE cvid=? AND columnname LIKE ?', [$date, $filter->id, '%datetime_end%']);

        return true;
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
            if (empty($userName)) {
                continue;
            }

            $usersAndGroups['Users'][$userName] = $userName;
        }

        $groups = (array)$currentUser->getAccessibleGroups();

        foreach ($groups as $groupId => $groupName) {
            if (empty($groupName)) {
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
    public function getHideDays()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $weekDays = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            0 => 'Sunday',
        ];

        if (!$currentUser->isEmpty('week_days')) {
            $picklistWeekDays = explode(' |##| ', $currentUser->get('week_days'));
        } else {
            $picklistWeekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        }

        $hideWeekDays = array_diff($weekDays, $picklistWeekDays);

        return array_keys($hideWeekDays);
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
            if (empty($userName)) {
                continue;
            }

            $recordModel = Users_Record_Model::getInstanceById($userId, 'Users');
            $imageDetails = $recordModel->getImageDetails();
            $userBackground = self::getUserGroupBackground($userId);
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

            $groupBackground = self::getUserGroupBackground($groupId);
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
                'name' => vtranslate('Users by', $this->getName()) . ' ' . $groupName,
                'icon' => 'fa fa-group',
                'label' => mb_strtoupper(mb_substr($groupName, 0, 2)),
                'background' => $groupBackground,
                'border' => $groupBackground,
                'color' => Settings_Picklist_Module_Model::getTextColor($groupBackground),
            ];
        }

        return $images;
    }

    public static function getUserGroupBackground(string $value): string
    {
        $code = dechex(crc32($value));
        $color = substr($code, 0, 6);

        return '#' . $color;
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
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $queryGenerator = new EnhancedQueryGenerator($moduleName, $currentUser);

        /** @var Vtiger_ListView_Model $listModel */
        $listModel = Vtiger_ListView_Model::getCleanInstance($moduleName);
        $listModel->set('query_generator', $queryGenerator);


        $date = self::getTodayDates();
        /** @var QueryGenerator $queryGenerator */
        $queryGenerator = $listModel->get('query_generator');

        $queryGenerator->startGroup('');
        $queryGenerator->addCondition('datetime_start', $date, 'bw', 'OR');
        $queryGenerator->addCondition('datetime_end', $date, 'bw', 'OR');
        $queryGenerator->endGroup();

        return intval($listModel->getListViewCount());
    }

    public static function getTodayDates()
    {
        $tomorrow = DateTimeField::convertToDBTimeZone(date('Y-m-d'));
        $tomorrow->modify('+1439 minutes');
        $tomorrow = $tomorrow->format('Y-m-d H:i:s');
        $today = DateTimeField::convertToDBTimeZone(date('Y-m-d'));
        $today = $today->format('Y-m-d H:i:s');

        return $today . ',' . $tomorrow;
    }

    /**
     * @return string
     */
    public function getIconUrl(): string
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if ($currentUser && 'MyCalendar' === $currentUser->get('defaultcalendarview')) {
            return 'index.php?module=ITS4YouCalendar&view=Calendar';
        }

        return $this->getDefaultUrl();
    }

    public function getModuleIcon($type = '')
    {
        $icons = [
            'Meeting' => 'fa-users',
            'Call' => 'fa-phone',
            'Email' => 'fa-envelope',
            'Reminder' => 'fa-bell',
        ];

        if (!empty($icons[$type])) {
            return '<i style="color: #90989c; height: 50px; width: 50px; line-height: 50px; font-size: 23px;" class="fa ' . $icons[$type] . '"></i>';
        }

        return parent::getModuleIcon();
    }
}
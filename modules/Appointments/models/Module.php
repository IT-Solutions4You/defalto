<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Appointments_Module_Model extends Vtiger_Module_Model
{
    /** Calendar Widget: Copy to vtiger Module Model */
    public function getCalendarEvents($mode, $pagingModel, $user, $recordId = false)
    {
        $relatedParentId = $recordId;
        $relatedParentModule = getSalesEntityType($relatedParentId);

        $moduleName = 'Appointments';
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
        /** @var Vtiger_RelationListView_Model $relationListView */
        $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, '');
        $relationListView->set('whereCondition', [
            'calendar_status' => ['its4you_calendar.status', 'n', ['Completed', 'Cancelled'], 'picklist'],
        ]);

        return $relationListView ? $relationListView->getEntries($pagingModel) : [];
    }

    /**
     * @return string
     */
    public function getDefaultUrl(): string
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if ($currentUser && 'MyCalendar' === $currentUser->get('defaultcalendarview')) {
            return 'index.php?module=Appointments&view=Calendar';
        }

        return parent::getDefaultUrl();
    }

    /**
     * @return array
     */
    public function getHideDays(): array
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
            $picklistWeekDays = array_filter(explode(' |##| ', $currentUser->get('week_days')));
        }

        if (empty($picklistWeekDays)) {
            $picklistWeekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        }

        $hideWeekDays = array_diff($weekDays, $picklistWeekDays);

        return array_keys($hideWeekDays);
    }

    /**
     * @return string
     */
    public function getIconUrl(): string
    {
        return $this->getDefaultUrl();
    }

    /**
     * @return string
     */
    public function getListViewUrl(): string
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if ($currentUser && 'MyCalendar' === $currentUser->get('defaultcalendarview')) {
            return 'index.php?module=Appointments&view=Calendar';
        }

        return parent::getListViewUrl();
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
                'linkurl' => 'index.php?module=Appointments&view=Calendar',
                'linkicon' => 'fa-calendar',
            ];
        } else {
            $basicLinks[] = [
                'linktype' => 'BASIC',
                'linklabel' => 'LBL_LIST',
                'linkurl' => 'index.php?module=Appointments&view=List',
                'linkicon' => 'fa-bars',
            ];
        }

        return array_merge($basicLinks, parent::getModuleBasicLinks());
    }

    /**
     * @param string|int $height
     * @param string $type
     * @return string
     */
    public function getModuleIcon($height = '', string $type = ''): string
    {
        $icons = [
            'Meeting' => 'fa-users',
            'Call' => 'fa-phone',
            'Email' => 'fa-envelope',
            'Reminder' => 'fa-bell',
        ];

        if (!empty($icons[$type])) {
            return sprintf('<i style="font-size: %s;" class="fa %s"></i>', $height, $icons[$type]);
        }

        return sprintf('<i style="font-size: %s" class="fa-solid fa-calendar" title=""></i>', $height);
    }

    /**
     * @return array
     */
    public function getSettingLinks(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $settingsLinks = [];
        $settingsLinks[] = [
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_CALENDAR_SETTINGS',
            'linkurl' => 'index.php?module=Users&parent=Settings&view=Calendar&record=' . $currentUser->getId(),
        ];
        $settingsLinks[] = [
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_INTEGRATION',
            'linkurl' => 'index.php?module=Appointments&parent=Settings&view=Integration',
        ];

        return array_merge($settingsLinks, parent::getSettingLinks());
    }

    /**
     * @return string
     */
    public static function getTodayDate(): string
    {
        $today = DateTimeField::convertToDBTimeZone(date('Y-m-d'));

        return $today->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public static function getTodayDates(): string
    {
        $tomorrow = DateTimeField::convertToDBTimeZone(date('Y-m-d'));
        $tomorrow->modify('+1439 minutes');
        $tomorrow = $tomorrow->format('Y-m-d H:i:s');

        return self::getTodayDate() . ',' . $tomorrow;
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

        $queryGenerator->addUserSearchConditions(['search_field' => 'assigned_user_id', 'search_text' => decode_html($currentUser->getName()), 'operator' => 'c']);

        return intval($listModel->getListViewCount());
    }

    /**
     * @return bool
     */
    public function isEventTypesVisible(): bool
    {
        global $Appointments_IsEventTypesVisible;

        return false !== $Appointments_IsEventTypesVisible;
    }

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
     * @param object|bool $filter
     * @return bool
     */
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
     * @param string $sourceModule
     * @param string $field
     * @param int|string $record
     * @param string $listQuery
     * @return string
     */
    public static function getQueryByModuleField(string $sourceModule, string $field, $record, string $listQuery): string
    {
        if ('Appointments' === $sourceModule && 'invite_users' === $field) {
            $search = "its4you_calendar.invite_users = 'replace_invite_users'";
            $replace = sprintf("its4you_calendar.its4you_calendar_id IN (SELECT record_id FROM its4you_invited_users WHERE user_id='%s')", (int)$record);
            $listQuery = str_replace($search, $replace, $listQuery);
        }

        return $listQuery;
    }
}
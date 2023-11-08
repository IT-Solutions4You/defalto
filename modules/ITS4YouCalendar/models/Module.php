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
    /** Calendar Widget: Copy to vtiger Module Model */
    public function getCalendarEvents($mode, $pagingModel, $user, $recordId = false)
    {
        $relatedParentId = $recordId;
        $relatedParentModule = getSalesEntityType($relatedParentId);

        $moduleName = 'ITS4YouCalendar';
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
        /** @var Vtiger_RelationListView_Model $relationListView */
        $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, '');
        $relationListView->set('whereCondition', [
            'calendar_status' => ['its4you_calendar.status', 'n', ['Completed', 'Cancelled'], 'picklist'],
        ]);

        return $relationListView ? $relationListView->getEntries($pagingModel) : [];
    }

    public function getDefaultUrl()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if ($currentUser && 'MyCalendar' === $currentUser->get('defaultcalendarview')) {
            return 'index.php?module=ITS4YouCalendar&view=Calendar';
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

    public function getListViewUrl()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if ($currentUser && 'MyCalendar' === $currentUser->get('defaultcalendarview')) {
            return 'index.php?module=ITS4YouCalendar&view=Calendar';
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
            'linkurl' => 'index.php?module=ITS4YouCalendar&parent=Settings&view=Integration',
        ];

        return array_merge($settingsLinks, parent::getSettingLinks());
    }

    public static function getTodayDate(): string
    {
        $today = DateTimeField::convertToDBTimeZone(date('Y-m-d'));

        return $today->format('Y-m-d H:i:s');
    }

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

    public function isEventTypesVisible()
    {
        global $ITS4YouCalendar_IsEventTypesVisible;

        return false !== $ITS4YouCalendar_IsEventTypesVisible;
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
}
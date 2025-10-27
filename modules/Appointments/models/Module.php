<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Module_Model extends Vtiger_Module_Model
{
    public $todayRecordsListModel;

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
        return $this->getDefaultUrl() . '&initialView=Today';
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
                'linktype'  => 'BASIC',
                'linklabel' => 'LBL_CALENDAR',
                'linkurl'   => 'index.php?module=Appointments&view=Calendar',
                'linkicon'  => 'fa-calendar',
            ];
        } else {
            $basicLinks[] = [
                'linktype'  => 'BASIC',
                'linklabel' => 'LBL_LIST',
                'linkurl'   => 'index.php?module=Appointments&view=List',
                'linkicon'  => 'fa-bars',
            ];
        }

        return array_merge($basicLinks, parent::getModuleBasicLinks());
    }

    /**
     * @param string|int $height
     * @param string     $type
     *
     * @return string
     */
    public function getModuleIcon($height = '', string $type = ''): string
    {
        $type = strtolower($type);
        $icons = [
            'meeting'  => 'fa-users',
            'call'     => 'fa-phone',
            'email'    => 'fa-envelope',
            'reminder' => 'fa-bell',
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
            'linktype'  => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_CALENDAR_SETTINGS',
            'linkurl'   => 'index.php?module=Users&parent=Settings&view=Calendar&record=' . $currentUser->getId(),
        ];
        $settingsLinks[] = [
            'linktype'  => 'LISTVIEWSETTING',
            'linklabel' => 'LBL_INTEGRATION',
            'linkurl'   => 'index.php?module=Appointments&parent=Settings&view=Integration',
        ];

        return array_merge($settingsLinks, parent::getSettingLinks());
    }

    /**
     * @return int
     */
    public function getTodayRecordsCount(): int
    {
        $listModel = $this->getTodayRecordsListModel();

        return intval($listModel->getListViewCount());
    }

    /**
     * @return Vtiger_ListView_Model
     */
    public function getTodayRecordsListModel()
    {
        if (!empty($this->todayRecordsListModel)) {
            return $this->todayRecordsListModel;
        }

        $moduleName = $this->getName();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $queryGenerator = new EnhancedQueryGenerator($moduleName, $currentUser);
        $queryGenerator->setFields(['id', 'subject', 'calendar_type', 'datetime_start', 'datetime_end', 'is_all_day']);

        /** @var Vtiger_ListView_Model $listModel */
        $listModel = Vtiger_ListView_Model::getCleanInstance($moduleName);
        $listModel->set('query_generator', $queryGenerator);

        $date = Appointments_Dates_Helper::getTodayDatetimesForUser();
        $currentDate = Appointments_Dates_Helper::getCurrentDatetimeForUser();

        /**
         * @var EnhancedQueryGenerator $queryGenerator
         * Query generator require user dates to convert to database utc
         */
        $queryGenerator = $listModel->get('query_generator');
        $queryGenerator->startGroup('');
        /** Show events with time between start and end date */
        $queryGenerator->startGroup('');
        $queryGenerator->addCondition('datetime_start', $date, 'bw', '');
        $queryGenerator->addCondition('datetime_end', $date, 'bw', 'OR');
        $queryGenerator->endGroup();
        /** Show events with start date less than current time and end date more than current time */
        $queryGenerator->startGroup($queryGenerator::$OR);
        $queryGenerator->addCondition('datetime_start', $currentDate, 'l');
        $queryGenerator->addConditionGlue($queryGenerator::$AND);
        $queryGenerator->addCondition('datetime_end', $currentDate, 'g');
        $queryGenerator->endGroup('');
        $queryGenerator->endGroup('');

        $queryGenerator->addCondition('calendar_status', 'Completed', 'n', 'AND');
        $queryGenerator->addCondition('calendar_status', 'Cancelled', 'n', 'AND');
        $queryGenerator->addCondition('assigned_user_id', decode_html($currentUser->getName()), 'c', 'AND');

        $this->todayRecordsListModel = $listModel;

        return $listModel;
    }

    public function getFirstTodayRecord()
    {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $listModel = $this->getTodayRecordsListModel();

        $currentDate = Appointments_Dates_Helper::getCurrentDatetimeForUser();
        $listModel->get('query_generator')->addCondition('datetime_start', $currentDate, 'g', 'AND');

        $controller = new ListViewController($db, $currentUser, $listModel->get('query_generator'));

        $listModel->set('module', $this);
        $listModel->set('listview_controller', $controller);
        $listModel->set('orderby', 'datetime_start');
        $listModel->set('sortorder', 'ASC');

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('limit', 1);

        $records = $listModel->getListViewEntries($pagingModel);

        return !empty($records) ? array_values($records)[0] : false;
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
     *
     * @return void
     */
    public static function retrieveDefaultValuesForEdit(Vtiger_Request $request)
    {
        if ($request->isEmpty('record')) {
            $currentUser = Users_Record_Model::getCurrentUserModel();

            $request->set('calendar_status', $currentUser->get('defaulteventstatus'));
            $request->set('calendar_type', $currentUser->get('defaultactivitytype'));

            if (!$request->isEmpty('sourceModule')) {
                $referenceField = match ($request->get('sourceModule')) {
                    'Contacts' => 'contact_id',
                    'Accounts' => 'account_id',
                    default => 'parent_id',
                };

                $request->set($referenceField, (int)$request->get('sourceRecord'));
            }
        }
    }

    /**
     * @param string     $sourceModule
     * @param string     $field
     * @param int|string $record
     * @param string     $listQuery
     *
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

    public function getConfigureRelatedListFields()
    {
        $fields = parent::getConfigureRelatedListFields();
        $fields['is_all_day'] = 'is_all_day';

        return $fields;
    }
}
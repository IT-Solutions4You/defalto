<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Events_Model extends Vtiger_Base_Model
{
    public bool $useUserColors = false;
    /**
     * @var array
     */
    public static array $popoverDisabledFields = [
        'ITS4YouCalendar' => [
            'datetime_start',
            'datetime_end',
            'subject',
        ],
    ];
    /**
     * @var array
     */
    public static array $popoverMarkAsDone = [
        'ITS4YouCalendar' => [
            'calendar_status' => ['Completed', 'Cancelled',],
        ],
    ];

    /**
     * @var array
     */
    public static array $eventTypes = [];
    /**
     * @var array
     */
    public static array $defaultEventTypes = [
        [
            'module' => 'Potentials',
            'fields' => ['closingdate'],
            'color' => '#AA6705',
        ],
        [
            'module' => 'Contacts',
            'fields' => ['support_end_date'],
            'color' => '#953B39',
        ],
        [
            'module' => 'Contacts',
            'fields' => ['birthday'],
            'color' => '#545252',
        ],
        [
            'module' => 'Invoice',
            'fields' => ['duedate'],
            'color' => '#87865D',
        ],
        [
            'module' => 'Project',
            'fields' => ['startdate', 'targetenddate'],
            'color' => '#C71585',
        ],
        [
            'module' => 'ProjectTask',
            'fields' => ['startdate', 'enddate'],
            'color' => '#3788d8',
        ],
    ];
    /**
     * @var object
     */
    public object $listViewModel;
    /**
     * @var array
     */
    public array $fieldColors = [];
    /**
     * @var PearDatabase
     */
    public PearDatabase $adb;

    /**
     * @param string $value
     * @return int
     */
    public static function getDayOfWeekId(string $value): int
    {
        $days = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];

        return $days[$value];
    }

    /**
     * @param string $value
     * @return string
     */
    public static function getInitialView(string $value): string
    {
        $views = [
            'Today' => 'timeGridDay',
            'This Week' => 'timeGridWeek',
            'This Month' => 'dayGridMonth',
            'This Year' => 'dayGridMonth',
            'Agenda' => 'listWeek',
        ];

        return $views[$value];
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public static function getEventsFromRequest(Vtiger_Request $request): array
    {
        self::retrieveEventTypesByRequest($request);

        $filter = (array)$request->get('filter');
        $visibleEventTypes = (array)$filter['event_types'];
        $events = [];

        /** @var ITS4YouCalendar_Events_Model $instance */
        foreach (self::getEventTypes() as $instance) {
            if (!$instance->isEmptyId()) {
                $visible = !in_array($instance->getId(), $visibleEventTypes) ? 0 : 1;

                if ($visible !== $instance->get('visible')) {
                    $instance->set('visible', $visible);
                    $instance->save();
                }
            }

            if (!$instance->isVisible()) {
                continue;
            }

            $instance->set('start_date', $request->get('start'));
            $instance->set('end_date', $request->get('end'));
            $instance->set('filter', $request->get('filter'));

            $events = array_merge($events, $instance->getRecords());
        }

        return $events;
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public static function retrieveEventTypesByRequest(Vtiger_Request $request)
    {
        $filter = $request->get('filter');

        if (!empty($filter['calendar_type'])) {
            self::$eventTypes[] = self::getEventTypeCalendarData();
        }
    }

    /**
     * @return array
     */
    public static function getEventTypeCalendarData()
    {
        return [
            'id' => 0,
            'module' => 'ITS4YouCalendar',
            'fields' => ['datetime_start', 'datetime_end'],
            'color' => '#08f',
            'visible' => 1,
        ];
    }

    /**
     * @return array
     */
    public static function getEventTypes(): array
    {
        self::retrieveEventTypes();

        $instances = [];

        foreach (self::$eventTypes as $eventId => $eventType) {
            $instance = self::getInstance();
            $instance->setData($eventType);
            $instance->set('id', $eventId);

            $instances[] = $instance;
        }

        return $instances;
    }

    /**
     * @return void
     */
    public static function retrieveEventTypes()
    {
        self::insertDefaultEventTypes();

        $adb = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $result = $adb->pquery(
            'SELECT its4you_calendar_default_types.*, its4you_calendar_user_types.color, its4you_calendar_user_types.visible
                FROM its4you_calendar_default_types 
                LEFT JOIN its4you_calendar_user_types ON its4you_calendar_user_types.default_id=its4you_calendar_default_types.id 
                WHERE is_default=? OR its4you_calendar_user_types.user_id=? ORDER BY id ASC',
            [1, $currentUser->getId()]
        );

        while ($row = $adb->fetchByAssoc($result)) {
            self::$eventTypes[$row['id']] = [
                'id' => $row['id'],
                'module' => $row['module'],
                'fields' => (array)json_decode(decode_html($row['fields'])),
                'color' => !empty($row['color']) ? $row['color'] : $row['default_color'],
                'visible' => 1 === $row['visible'],
            ];
        }
    }

    /**
     * @return void
     */
    public static function insertDefaultEventTypes()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->query('SELECT * FROM its4you_calendar_default_types');

        if (!$adb->num_rows($result)) {
            foreach (self::$defaultEventTypes as $eventType) {
                $adb->pquery(
                    'INSERT INTO its4you_calendar_default_types (module,fields,default_color,is_default) VALUES (?,?,?,?)',
                    [$eventType['module'], json_encode($eventType['fields']), $eventType['color'], 1]
                );
            }
        }
    }

    /**
     * @param int $id
     * @return self
     */
    public static function getInstance(int $id = 0): self
    {
        $instance = new self();
        $instance->adb = PearDatabase::getInstance();
        $instance->set('id', $id);
        $instance->retrieveData();

        return $instance;
    }

    /**
     * @return void
     */
    public function retrieveData()
    {
        if (!$this->isEmpty('id')) {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $result = $this->adb->pquery(
                'SELECT its4you_calendar_default_types.*, its4you_calendar_user_types.color, its4you_calendar_user_types.visible
                FROM its4you_calendar_default_types 
                LEFT JOIN its4you_calendar_user_types ON its4you_calendar_user_types.default_id=its4you_calendar_default_types.id AND its4you_calendar_user_types.user_id=?
                WHERE its4you_calendar_default_types.id=?',
                [$currentUser->getId(), $this->getId()]
            );

            $row = $this->adb->fetchByAssoc($result);

            $fields = (array)json_decode(decode_html($row['fields']));

            $this->set('module', $row['module']);
            $this->set('fields', $fields);
            $this->set('field', $fields[0]);
            $this->set('range_field', $fields[1]);
            $this->set('color', !empty($row['color']) ? $row['color'] : $row['default_color']);
            $this->set('visible', 1 === $row['visible'] || null === $row['visible']);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->get('id');
    }

    /**
     * @return bool
     */
    public function isEmptyId()
    {
        return $this->isEmpty('id');
    }

    /**
     * @return void
     */
    public function save()
    {
        if ($this->isEmpty('id')) {
            $this->adb->pquery(
                'INSERT INTO its4you_calendar_default_types (module,fields,default_color,is_default) VALUES (?,?,?,?)',
                [$this->get('module'), json_encode($this->get('fields')), $this->get('color'), 0]
            );
            $this->set('id', $this->adb->getLastInsertID());
        }

        $this->saveUser();
    }

    /**
     * @return void
     */
    public function saveUser()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $result = $this->adb->pquery(
            'SELECT id FROM its4you_calendar_user_types WHERE default_id=? AND user_id=?',
            [$this->getId(), $currentUser->getId()]
        );

        $params = [$this->get('color'), $this->get('visible'), $this->getId(), $currentUser->getId()];

        if ($this->adb->num_rows($result)) {
            $query = 'UPDATE its4you_calendar_user_types SET color=?, visible=? WHERE default_id=? AND user_id=?';
        } else {
            $query = 'INSERT INTO its4you_calendar_user_types (color, visible, default_id, user_id) VALUES (?,?,?,?)';
        }

        $this->adb->pquery($query, $params);
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return (bool)$this->get('visible');
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRecords(): array
    {
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', 1);
        $pagingModel->set('limit', 1000);

        $this->retrieveListViewModel();
        $this->retrieveDateConditions();
        $this->retrieveFilterConditions();

        $records = [];
        $listViewRecords = $this->listViewModel->getListViewEntries($pagingModel);

        /** @var Vtiger_Record_Model $listViewRecord */
        foreach ($listViewRecords as $listViewRecord) {
            $this->setRecordModel(Vtiger_Record_Model::getInstanceById($listViewRecord->getId()));
            $data = $this->getRecord();

            if (!empty($data)) {
                $records[] = $data;
            }
        }

        return $records;
    }

    /**
     * @return void
     */
    public function retrieveListViewModel()
    {
        $db = PearDatabase::getInstance();
        $module = $this->getModule();
        $user = Users_Record_Model::getCurrentUserModel();

        $queryGenerator = new EnhancedQueryGenerator($module, $user);
        $queryGenerator->setFields($this->getListFields());

        $moduleSpecificControllerPath = 'modules/'.$module.'/controllers/ListViewController.php';

        if(file_exists($moduleSpecificControllerPath)) {
            include_once $moduleSpecificControllerPath;
            $moduleSpecificControllerClassName = $module.'ListViewController';
            $controller = new $moduleSpecificControllerClassName($db, $user, $queryGenerator);
        } else {
            $controller = new ListViewController($db, $user, $queryGenerator);
        }

        $this->listViewModel = Vtiger_ListView_Model::getCleanInstance($module)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->get('module');
    }

    /**
     * @return array
     */
    public function getListFields(): array
    {
        $fields = $this->get('fields');

        if ($this->isCalendar()) {
            $fields[] = 'calendar_type';
            $fields[] = 'is_all_day';
        }

        $fields[] = 'id';

        return $fields;
    }

    /**
     * @return bool
     */
    public function isCalendar()
    {
        return 'ITS4YouCalendar' === $this->get('module');
    }

    /**
     * @return void
     */
    public function retrieveDateConditions()
    {
        $betweenValue = $this->get('start_date') . ',' . $this->get('end_date');
        $fields = $this->get('fields');

        list($startField, $endField) = $fields;

        /** @var EnhancedQueryGenerator $queryGenerator */
        $queryGenerator = $this->listViewModel->get('query_generator');

        $queryGenerator->startGroup('');
        $queryGenerator->addCondition($startField, $betweenValue, 'bw', $queryGenerator::$OR);

        if (!empty($endField)) {
            $queryGenerator->addCondition($endField, $betweenValue, 'bw', $queryGenerator::$OR);

            $queryGenerator->startGroup($queryGenerator::$OR);
            $queryGenerator->addCondition($startField, $this->get('start_date'), 'l');
            $queryGenerator->addConditionGlue($queryGenerator::$AND);
            $queryGenerator->addCondition($endField, $this->get('end_date'), 'g');
            $queryGenerator->endGroup('');
        }

        $queryGenerator->endGroup();
    }

    /**
     * @return void
     */
    public function retrieveFilterConditions()
    {
        $filter = $this->get('filter');

        if ($this->isCalendar() && !empty($filter['calendar_type'])) {
            $this->listViewModel->get('query_generator')->addUserSearchConditions(['search_field' => 'calendar_type', 'search_text' => implode(',', $filter['calendar_type']), 'operator' => 'e']);
        }

        if (!empty($filter['users_groups'])) {
            $selectedUsers = (array)$filter['users_groups'];
            $searchUsers = [];

            foreach ($selectedUsers as $selectedUser) {
                list($type, $name) = explode('::::', $selectedUser);

                if ('UsersByGroups' === $type) {
                    $searchUsers = array_merge($searchUsers, $this->getGroupUserNames($name));
                } else {
                    $searchUsers[] = $name;
                }
            }

            $this->useUserColors = 1 < count($selectedUsers);
            $this->listViewModel->get('query_generator')->addUserSearchConditions(['search_field' => 'assigned_user_id', 'search_text' => implode(',', $searchUsers), 'operator' => 'c']);
        }
    }

    /**
     * @param string $name
     * @return array
     */
    public function getGroupUserNames(string $name): array
    {
        $group = Settings_Groups_Record_Model::getInstance($name);
        $userNames = [];

        if ($group) {
            $users = $group->getUsersList();

            foreach ($users as $user) {
                $userNames[] = $user->getName();
            }
        }

        return $userNames;
    }

    /**
     * @param $value
     * @return void
     */
    public function setRecordModel($value)
    {
        $this->set('record_model', $value);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRecord(): array
    {
        list($startField, $endField) = $this->get('fields');

        $recordModel = $this->getRecordModel();
        $startDateValue = $recordModel->get($startField);

        if (empty($startDateValue)) {
            return [];
        }

        $startDate = $this->getDate($recordModel->get($startField));
        $record = [
            'id' => $this->get('id') . 'x' . $recordModel->getId(),
            'title' => decode_html($recordModel->getName()),
            'start' => $startDate,
            'end' => $startDate,
            'url' => $recordModel->getDetailViewUrl(),
            'backgroundColor' => $this->getBackgroundColor(),
            'borderColor' => $this->getBackgroundColor(),
            'color' => $this->getTextColor(),
            'className' => 'eventTypeRecord eventTypeId' . $this->get('id') . ' eventRecordId' . $recordModel->getId() . ' eventType' . preg_replace('/([^\w+\d+])+/','', $recordModel->get('calendar_type')),
            'eventDisplay' => 'list-item',
        ];

        if (!empty($endField)) {
            $endDate = $this->getDate($recordModel->get($endField));

            list($endDateDate, $endDateTime) = explode(' ', $endDate);

            if (empty($endDateTime)) {
                $record['end'] = date('Y-m-d', strtotime($endDateDate) + 86400);
            } else {
                $record['end'] = $endDate;
            }

            if (1 === (int)$recordModel->get('is_all_day')) {
                $record['allDay'] = 1;
            }
        }

        return $record;
    }

    /**
     * @return object|Vtiger_Record_Model
     */
    public function getRecordModel()
    {
        return $this->get('record_model');
    }

    /**
     * @param $value
     * @return string
     * @throws Exception
     */
    public function getDate($value)
    {
        $dateInfo = explode(' ', $value);
        $date = DateTimeField::convertToUserTimeZone($value);

        if (!empty($dateInfo[1])) {
            return $date->format('Y-m-d H:i:s');
        }

        return $date->format('Y-m-d');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getName(): string
    {
        $module = $this->getModule();
        $name = vtranslate($module, $module) . ' ';
        $moduleModel = Vtiger_Module_Model::getInstance($module);

        if ($moduleModel) {
            foreach ($this->get('fields') as $field) {
                $fieldModel = Vtiger_Field_Model::getInstance($field, $moduleModel);

                if ($fieldModel) {
                    $name .= $fieldModel->get('label') . ', ';
                }
            }
        }

        return trim($name, ', ');
    }

    /**
     * @return string
     */
    public function getBackgroundColor(): string
    {
        $recordModel = $this->getRecordModel();

        if ($recordModel) {
            if ($this->useUserColors) {

                return ITS4YouCalendar_Module_Model::getUserGroupBackground($recordModel->get('assigned_user_id'));
            } elseif ($this->isCalendar()) {

                return $this->getFieldColor('calendar_type', $recordModel->get('calendar_type'));
            }
        }

        return $this->get('color');
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public function getFieldColor(string $name, string $value): string
    {
        if (!isset($this->fieldColors[$name])) {
            $this->fieldColors[$name] = Settings_Picklist_Module_Model::getPicklistColorMap($name, true);
        }

        if (!empty($this->fieldColors[$name][$value])) {
            return $this->fieldColors[$name][$value];
        }

        return $this->get('color');
    }

    /**
     * @return string
     */
    public function getTextColor(): string
    {
        return Settings_Picklist_Module_Model::getTextColor($this->getBackgroundColor());
    }

    /**
     * @return array
     */
    public static function getEventFields(): array
    {
        $entityModules = Vtiger_Module_Model::getEntityModules();
        $eventFieldsInfo = [];
        $ignoredFields = ['modifiedtime', 'createdtime', 'CreatedTime', 'ModifiedTime'];
        $ignoredModules = ['ITS4YouCalendar'];

        /** @var Vtiger_Module_Model $entityModule */
        foreach ($entityModules as $entityModule) {
            $entityModuleName = $entityModule->getName();
            $eventFields = [];

            if (in_array($entityModuleName, $ignoredModules)) {
                continue;
            }

            $dateFields = array_merge($entityModule->getFieldsByType('date'), $entityModule->getFieldsByType('datetime'));

            foreach ($dateFields as $dateField) {
                if (in_array($dateField->get('name'), $ignoredFields)) {
                    continue;
                }

                $eventFields[$dateField->get('name')] = $dateField->get('label');
            }

            $eventFieldsInfo[$entityModuleName] = $eventFields;
        }

        return array_filter($eventFieldsInfo);
    }

    /**
     * @return string
     */
    public function getDetailLink()
    {
        /** @var Vtiger_Record_Model $recordModel */
        $recordModel = $this->get('record_model');

        if ($this->isEmpty('app_names')) {
            $this->set('app_names', $recordModel->getModule()->getAppName());
        }

        $appName = array_key_first($this->get('app_names'));

        return 'javascript:Vtiger_Index_Js.getInstance().showQuickPreviewForId(' . $recordModel->getId() . ',\'' . $recordModel->getModuleName() . '\', \'' . $appName . '\')';
    }

    /**
     * @return string
     */
    public function getFormattedDates(): string
    {
        $dateFields = [];
        $timeFields = [];
        $recordModel = $this->getRecordModel();
        $userModel = Users_Privileges_Model::getCurrentUserModel();

        foreach ($this->get('fields') as $fieldName) {
            $fieldDateTime = $recordModel->get($fieldName);

            if (2 === php7_count(explode(' ', $fieldDateTime))) {
                $timeValue = DateTimeField::convertToUserTimeZone($fieldDateTime)->format('H:i');

                if (12 === (int)$userModel->get('hour_format')) {
                    $timeValue = Vtiger_Time_UIType::getTimeValueInAMorPM($timeValue);
                }

                $timeFields[] = $timeValue;
            }

            $dateFields[] = Vtiger_Util_Helper::formatDateIntoStrings($fieldDateTime);
        }

        return implode(' - ', array_unique($dateFields)) . ' • ' . implode(' - ', array_unique($timeFields));
    }

    /**
     * @throws Exception
     */
    public function getPopoverValues(): array
    {
        /** @var Vtiger_Record_Model $recordModel */
        $recordModel = $this->getRecordModel();
        $moduleModel = $recordModel->getModule();
        $moduleName = $recordModel->getModuleName();
        $fields = $moduleModel->getHeaderAndSummaryViewFieldsList();
        $values = [];

        /** @var Vtiger_Field_Model $field */
        foreach ($fields as $field) {
            if ($this->isPopoverDisabledField($field->getName(), $moduleName)) {
                continue;
            }

            $values[$field->get('label')] = $recordModel->getDisplayValue($field->getName());
        }

        return $values;
    }

    /**
     * @param string $fieldName
     * @param string $moduleName
     * @return bool
     */
    public function isPopoverDisabledField(string $fieldName, string $moduleName): bool
    {
        return isset(self::$popoverDisabledFields[$moduleName]) && in_array($fieldName, self::$popoverDisabledFields[$moduleName]);
    }

    /**
     * @return bool
     */
    public function isMarkAsDone(): bool
    {
        $recordModel = $this->getRecordModel();
        $moduleName = $recordModel->getModuleName();

        if (isset(self::$popoverMarkAsDone[$moduleName])) {
            $values = self::$popoverMarkAsDone[$moduleName];
            $fieldName = array_keys($values)[0];
            $fieldValues = $values[$fieldName];

            if (!in_array($recordModel->get($fieldName), $fieldValues)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getMarkAsDoneField(): string
    {
        $recordModel = $this->getRecordModel();
        $moduleName = $recordModel->getModuleName();

        return (string)array_keys((array)self::$popoverMarkAsDone[$moduleName])[0];
    }

    /**
     * @return string
     */
    public function getMarkAsDoneValue(): string
    {
        $recordModel = $this->getRecordModel();
        $moduleName = $recordModel->getModuleName();

        return (string)array_values((array)self::$popoverMarkAsDone[$moduleName])[0][0];
    }

    /**
     * @return void
     */
    public function retrieveCalendarData()
    {
        $this->setData(self::getEventTypeCalendarData());
    }

    /**
     * @return bool
     */
    public function isDuplicate(): bool
    {
        $result = $this->adb->pquery(
            'SELECT * FROM its4you_calendar_default_types WHERE module=? AND fields=?',
            [$this->get('module'), json_encode($this->get('fields'))]
        );

        return (bool)$this->adb->num_rows($result);
    }

    /**
     * @param string $value
     * @return string
     * @throws Exception
     */
    public function getDatetime(string $value): string
    {
        $date_value = explode(' ', $value);

        if (!empty($date_value[1])) {
            $date = new DateTime($value);

            return $date->format(DateTime::ATOM);
        }

        return '';
    }

    /**
     * @return void
     */
    public function delete()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $this->adb->pquery(
            'DELETE FROM its4you_calendar_default_types WHERE id=?',
            [$this->getId()]
        );

        $this->adb->pquery(
            'DELETE FROM its4you_calendar_user_types WHERE default_id=? AND user_id=?',
            [$this->getId(), $currentUser->getId()]
        );
    }
}
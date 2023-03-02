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
            self::$eventTypes[] = [
                'id' => 0,
                'module' => 'ITS4YouCalendar',
                'fields' => ['datetime_start', 'datetime_end'],
                'color' => '#08f',
                'visible' => 1,
            ];
        }
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
        return intval($this->get('id'));
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
        return boolval($this->get('visible'));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRecords(): array
    {
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', 1);

        list($startField, $endField) = $this->get('fields');

        $this->retrieveListViewModel();
        $this->retrieveDateConditions();
        $this->retrieveFilterConditions();

        $records = [];
        $listViewRecords = $this->listViewModel->getListViewEntries($pagingModel);

        /** @var Vtiger_Record_Model $listViewRecord */
        foreach ($listViewRecords as $listViewRecord) {
            $this->setRecordModel($listViewRecord);

            $startDateValue = $listViewRecord->get($startField);

            if (empty($startDateValue)) {
                continue;
            }

            $startDate = $this->getDate($listViewRecord->get($startField));
            $record = [
                'id' => $this->get('id') . 'x' . $listViewRecord->getId(),
                'title' => decode_html($listViewRecord->getName()),
                'start' => $startDate,
                'end' => $startDate,
                'url' => $this->getDetailLink(),
                'backgroundColor' => $this->getBackgroundColor(),
                'borderColor' => $this->getBackgroundColor(),
                'color' => $this->getTextColor(),
                'eventClassNames' => 'eventTypeRecord eventTypeId' . $this->get('id') . ' eventRecordId' . $listViewRecord->getId()
            ];

            if (!empty($endField)) {
                $endDate = $this->getDate($listViewRecord->get($endField));

                list($endDateDate, $endDateTime) = explode(' ', $endDate);

                if (empty($endDateTime)) {
                    $record['end'] = date('Y-m-d', strtotime($endDateDate) + 86400);
                } else {
                    $record['end'] = $endDate;
                }

                if ('Yes' === $listViewRecord->get('is_all_day')) {
                    $record['allDay'] = 1;
                }
            }

            $records[] = $record;
        }

        return $records;
    }

    /**
     * @return void
     */
    public function retrieveListViewModel()
    {
        $this->listViewModel = Vtiger_ListView_Model::getInstance($this->getModule(), 0, $this->getListFields());
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

        $this->listViewModel->get('query_generator')->addUserSearchConditions(array('search_field' => $startField, 'search_text' => $betweenValue, 'operator' => 'bw'));

        if (!empty($endField)) {
            $this->listViewModel->get('query_generator')->addUserSearchConditions(array('search_field' => $endField, 'search_text' => $betweenValue, 'operator' => 'bw'));
        }
    }

    /**
     * @return void
     */
    public function retrieveFilterConditions()
    {
        $filter = $this->get('filter');

        if ($this->isCalendar() && !empty($filter['calendar_type'])) {
            $this->listViewModel->get('query_generator')->addUserSearchConditions(array('search_field' => 'calendar_type', 'search_text' => implode(',', $filter['calendar_type']), 'operator' => 'e'));
        }

        if (!empty($filter['users_groups'])) {
            $this->listViewModel->get('query_generator')->addUserSearchConditions(array('search_field' => 'assigned_user_id', 'search_text' => implode(',', $filter['users_groups']), 'operator' => 'c'));
        }
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
     * @param $value
     * @return string
     * @throws Exception
     */
    public function getDate($value)
    {
        $dateInfo = explode(' ', $value);
        $date = new DateTime($value);

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
    public function getBackgroundColor()
    {
        $recordModel = $this->getRecordModel();

        if ($recordModel && $this->isCalendar()) {
            return $this->getFieldColor('calendar_type', $recordModel->get('calendar_type'));
        }

        return $this->get('color');
    }

    /**
     * @return object|Vtiger_Record_Model
     */
    public function getRecordModel()
    {
        return $this->get('record_model');
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
     * @return bool
     */
    public function isDuplicate(): bool
    {
        $result = $this->adb->pquery(
            'SELECT * FROM its4you_calendar_default_types WHERE module=? AND fields=?',
            [$this->get('module'), json_encode($this->get('fields'))]
        );

        return boolval($this->adb->num_rows($result));
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
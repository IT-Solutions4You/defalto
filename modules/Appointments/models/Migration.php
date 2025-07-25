<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Appointments_Migration_Model extends Vtiger_Base_Model
{
    /**
     * @var null|int
     */
    public int $accountId;
    /**
     * @var int
     */
    public int $calendarId;
    /**
     * @var array
     */
    public array $calendarInfo = [];
    /**
     * @var array
     */
    public array $contactRecords;
    /**
     * @var array
     */
    public array $customParams = [];
    /**
     * @var string
     */
    public string $customSql;
    /**
     * @var PearDatabase
     */
    public PearDatabase $db;
    /**
     * @var array
     */
    public array $entityParams = [];
    /**
     * @var string
     */
    public string $entitySql;
    /**
     * @var array
     */
    public array $inviteUsers;
    /**
     * @var array
     */
    public array $mainParams = [];
    /**
     * @var string
     */
    public string $mainSql;
    /**
     * @var string
     */
    public string $moduleName = 'Appointments';
    /**
     * @var null|int
     */
    public $parentId;
    /**
     * @var array
     */
    public array $recurringCalendarIds = [];

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        $self = new self();
        $self->db = PearDatabase::getInstance();

        return $self;
    }

    /**
     * @return void
     */
    public function insertInvited()
    {
        if (!empty($this->inviteUsers)) {
            $invitedUsersModel = Appointments_InvitedUsers_Model::getInstance($this->calendarId);
            $invitedUsersModel->setUsers($this->inviteUsers);
            $invitedUsersModel->saveUsers();
        }
    }

    /**
     * @return void
     */
    public function insertRecord()
    {
        $this->db->pquery($this->entitySql, $this->entityParams);
        $this->db->pquery($this->mainSql, $this->mainParams);
        $this->db->pquery($this->customSql, $this->customParams);
    }

    /**
     * @return void
     */
    public function insertRecurring()
    {
        $this->recurringCalendarIds[] = $this->calendarId;
        $result = $this->db->pquery('SELECT * FROM vtiger_recurringevents WHERE activityid=?', [$this->calendarId]);
        $resultData = $this->db->fetchByAssoc($result);

        if (!empty($resultData)) {
            $this->db->pquery(
                'INSERT INTO its4you_recurring (record_id, recurring_date, recurring_end_date, recurring_type, recurring_frequency, recurring_info) VALUES (?,?,?,?,?,?)',
                [
                    $this->calendarId,
                    $resultData['recurringdate'],
                    $resultData['recurringenddate'],
                    $resultData['recurringtype'],
                    $resultData['recurringfreq'],
                    $resultData['recurringinfo'],
                ]
            );
        }
    }

    /**
     * @return void
     */
    public function insertRecurringRelations()
    {
        if (empty($this->recurringCalendarIds)) {
            return;
        }

        $questionMarks = implode(',', $this->recurringCalendarIds);
        $result = $this->db->pquery('SELECT * FROM vtiger_activity_recurring_info WHERE activityid IN (' . $questionMarks . ') OR recurrenceid IN (' . $questionMarks . ')', []);

        while ($row = $this->db->fetchByAssoc($result)) {
            if (empty($row)) {
                continue;
            }

            $recordId = $row['activityid'];
            $recurrenceId = $row['recurrenceid'];

            $this->db->pquery('INSERT INTO its4you_recurring_rel (record_id, recurrence_id) VALUES (?,?)', [$recordId, $recurrenceId]);
        }
    }

    /**
     * @return void
     */
    public function insertRelations()
    {
        if (!empty($this->contactRecords)) {
            $focus = CRMEntity::getInstance($this->moduleName);
            $focus->save_related_module($this->moduleName, $this->calendarId, 'Contacts', $this->contactRecords);
        }
    }

    /**
     * @return void
     */
    public function insertReminder()
    {
        $result = $this->db->pquery('SELECT * FROM vtiger_activity_reminder WHERE activity_id=?', [$this->calendarId]);
        $resultData = $this->db->fetchByAssoc($result);

        if (!empty($resultData)) {
            $this->db->pquery(
                'INSERT INTO its4you_remindme (record_id, reminder_time, reminder_sent, recurring_id) VALUES (?,?,?,?)',
                [
                    $this->calendarId,
                    $resultData['reminder_time'],
                    $resultData['reminder_sent'],
                    $resultData['recurringid'],
                ]
            );
        }

        $result = $this->db->pquery('SELECT * FROM vtiger_activity_reminder_popup WHERE recordid=?', [$this->calendarId]);
        $resultData = $this->db->fetchByAssoc($result);

        if (!empty($resultData)) {
            $this->db->pquery(
                'INSERT INTO its4you_remindme_popup (record_id, datetime_start, status) VALUES (?,?,?)',
                [
                    $this->calendarId,
                    trim($resultData['date_start'] . ' ' . $resultData['time_start']),
                    $resultData['status'],
                ]
            );
        }
    }

    /**
     * @return void
     */
    public function migrate()
    {
        $this->migratePicklistValues();
        $this->migrateCustomFields();
        $this->migrateRecords();
    }

    /**
     * @return void
     */
    public function migrateCustomFields()
    {
        $modules = [16, 9];
        $result = $this->db->pquery(
            sprintf('SELECT * FROM vtiger_field WHERE tabid IN(%s) AND tablename=?', generateQuestionMarks($modules)),
            [$modules, 'vtiger_activitycf']
        );
        $moduleInstance = Vtiger_Module_Model::getInstance($this->moduleName);
        $blockInstance = Vtiger_Block_Model::getInstance('LBL_CUSTOM_INFORMATION', $moduleInstance);

        while ($row = $this->db->fetchByAssoc($result)) {
            if (!$moduleInstance->getField($row['fieldname'])) {
                $fieldInstance = new Vtiger_Field();
                $fieldInstance->initialize($row);
                $fieldInstance->id = null;
                $fieldInstance->block = null;
                $fieldInstance->table = 'its4you_calendarcf';

                $blockInstance->addField($fieldInstance);
            }
        }
    }

    /**
     * @return void
     */
    public function migratePicklistValues()
    {
        $moduleInstance = Vtiger_Module_Model::getInstance($this->moduleName);
        $fields = [
            'taskpriority' => 'calendar_priority',
            'activitytype' => 'calendar_type',
            'eventstatus' => 'calendar_status',
            'taskstatus' => 'calendar_status',
        ];

        foreach ($fields as $fromField => $toField) {
            $picklistResult = $this->db->pquery('SELECT picklistid FROM vtiger_picklist WHERE name=?', [$fromField]);

            if (!$this->db->num_rows($picklistResult)) {
                continue;
            }

            $fieldInstance = Vtiger_Field_Model::getInstance($toField, $moduleInstance);
            $picklistValues = array_keys($fieldInstance ? $fieldInstance->getPicklistValues() : []);

            $result = $this->db->pquery(sprintf('SELECT %s AS value FROM vtiger_%s', $fromField, $fromField));

            while ($row = $this->db->fetchByAssoc($result)) {
                $value = $row['value'];

                if (!in_array($value, $picklistValues)) {
                    $picklistValues[] = $value;

                    $fieldInstance->setPicklistValues([$value]);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function migrateRecords()
    {
        $result = $this->db->pquery('SELECT * FROM vtiger_crmentity WHERE setype=?', ['Calendar']);

        while ($row = $this->db->fetchByAssoc($result)) {
            if (empty($row)) {
                continue;
            }

            $this->calendarInfo = $row;
            $this->calendarId = $this->calendarInfo['crmid'];
            $this->retrieveRelations();
            $this->retrieveEntity();
            $this->retrieveMain();
            $this->retrieveCustom();
            $this->insertRecord();
            $this->insertRelations();
            $this->insertInvited();
            $this->insertRecurring();
            $this->insertReminder();
        }

        $this->insertRecurringRelations();
    }

    /**
     * @return void
     */
    public function retrieveCustom()
    {
        $customResult = $this->db->pquery('SELECT * FROM vtiger_activitycf WHERE activityid=?', [$this->calendarId]);
        $customData = $this->db->fetchByAssoc($customResult);
        $this->customParams = array_flip($this->db->getColumnNames('its4you_calendarcf'));

        foreach ($this->customParams as $customKey => $customParam) {
            if (array_key_exists($customKey, $this->customParams)) {
                $this->customParams[$customKey] = $customData[$customKey];
            } else {
                $this->customParams[$customKey] = null;
            }
        }

        $this->customParams['its4you_calendar_id'] = $this->calendarId;
        $this->customSql = 'INSERT INTO its4you_calendarcf (' . implode(',', array_keys($this->customParams)) . ') VALUES (' . generateQuestionMarks($this->customParams) . ')';
    }

    /**
     * @return void
     */
    public function retrieveEntity()
    {
        $this->entityParams = [
            'setype' => $this->moduleName,
            'crmid' => $this->calendarId,
        ];
        $this->entitySql = 'UPDATE vtiger_crmentity SET setype=? WHERE crmid=?';
    }

    /**
     * @return void
     */
    public function retrieveMain()
    {
        $checkActivityTableRes = $this->db->pquery('SHOW TABLES LIKE "vtiger_activity"');

        if (!$this->db->num_rows($checkActivityTableRes)) {
            return;
        }

        $mainResult = $this->db->pquery('SELECT * FROM vtiger_activity WHERE activityid=?', [$this->calendarId]);
        $mainData = $this->db->fetchByAssoc($mainResult);
        $this->mainParams = [
            'its4you_calendar_id' => $this->calendarId,
            'subject' => decode_html($mainData['subject']),
            'location' => decode_html($mainData['location']),
            'datetime_start' => trim($mainData['date_start'] . ' ' . $mainData['time_start']),
            'datetime_end' => trim($mainData['due_date'] . ' ' . $mainData['time_end']),
            'is_all_day' => 'Task' === $mainData['activitytype'] ? '1' : '0',
            'calendar_status' => $mainData['status'] ?: $mainData['eventstatus'],
            'calendar_priority' => $mainData['priority'],
            'calendar_type' => $mainData['activitytype'],
            'recurring_type' => $mainData['recurringtype'],
            'parent_id' => $this->parentId,
            'contact_id' => implode(';', $this->contactRecords),
            'account_id' => $this->accountId,
            'invite_users' => implode(';', $this->inviteUsers),
            'duration_hours' => $mainData['duration_hours'] . '.' . $mainData['duration_minutes'],
            'calendar_visibility' => $mainData['visibility'],
            'send_notification' => $mainData['sendnotification'],
        ];
        $this->mainSql = 'INSERT INTO its4you_calendar (' . implode(',', array_keys($this->mainParams)) . ') VALUES (' . generateQuestionMarks($this->mainParams) . ')';
    }

    /**
     * @return void
     * @throws Exception
     */
    public function retrieveRelations()
    {
        $parentResult = $this->db->pquery('SELECT crmid FROM vtiger_seactivityrel WHERE activityid=?', [$this->calendarId]);
        $this->parentId = (int)$this->db->query_result($parentResult, 0, 'crmid');
        $this->accountId = 0;

        if (!empty($this->parentId) && 'Accounts' === getSalesEntityType($this->parentId)) {
            $this->accountId = $this->parentId;
            $this->parentId = 0;
        }

        $contactResult = $this->db->pquery('SELECT contactid FROM vtiger_cntactivityrel WHERE activityid=?', [$this->calendarId]);
        $this->contactRecords = [];

        while ($contactRow = $this->db->fetchByAssoc($contactResult)) {
            if (empty($contactRow)) {
                continue;
            }

            $this->contactRecords[] = $contactRow['contactid'];
        }

        $inviteResult = $this->db->pquery('SELECT * FROM vtiger_invitees WHERE activityid=?', [$this->calendarId]);
        $this->inviteUsers = [];

        while ($inviteRow = $this->db->fetchByAssoc($inviteResult)) {
            if (empty($inviteRow)) {
                continue;
            }

            $this->inviteUsers[] = $inviteRow['inviteeid'];
        }
    }
}
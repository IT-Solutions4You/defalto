<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Appointments extends CRMEntity
{
    /**
     * @var array|TrackableObject
     */
    public $column_fields;
    /**
     * @var array
     */
    public array $customFieldTable = [
        'its4you_calendarcf',
        'its4you_calendar_id',
    ];
    /**
     * @var PearDatabase
     */
    public $db;
    /**
     * @var string
     */
    public string $entity_table = 'vtiger_crmentity';
    /**
     * @var
     */
    public $id;
    /**
     * @var array
     */
    public array $list_fields = [
        'Subject' => ['its4you_calendar' => 'subject'],
        'Assigned To' => ['vtiger_crmentity' => 'smownerid'],
        'Description' => ['vtiger_crmentity' => 'description'],
    ];
    /**
     * @var array
     */
    public array $list_fields_name = [
        'Subject' => 'subject',
        'Assigned To' => 'assigned_user_id',
        'Description' => 'description',
    ];
    /**
     * @var Logger
     */
    public $log;
    /**
     * @var string
     */
    public string $moduleLabel = 'Appointments';
    /**
     * @var string
     */
    public string $moduleName = 'Appointments';
    /**
     * @var string
     */
    public string $parentName = 'Tools';

    /**
     * @var array
     */
    public array $tab_name = [
        'vtiger_crmentity',
        'its4you_calendar',
        'its4you_calendarcf',
        'its4you_remindme',
    ];
    /**
     * @var array
     */
    public array $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'its4you_calendar' => 'its4you_calendar_id',
        'its4you_calendarcf' => 'its4you_calendar_id',
        'its4you_remindme' => 'record_id',
    ];
    /**
     * @var string
     */
    public string $table_index = 'its4you_calendar_id';
    /**
     * @var string
     */
    public string $table_name = 'its4you_calendar';

    /**
     * @param string $name
     * @return void
     */
    protected function createRelationFromMultiReference(string $name)
    {
        $relatedRecords = explode(';', $this->column_fields[$name]);

        foreach ($relatedRecords as $relatedRecord) {
            $this->createRelationFromRecord(intval($relatedRecord));
        }
    }

    /**
     * @param int $recordId
     * @return void
     */
    protected function createRelationFromRecord(int $recordId)
    {
        if (!empty($recordId)) {
            $module = Vtiger_Module_Model::getInstance($this->moduleName);
            $parentModuleName = getSalesEntityType($recordId);
            $parentModule = Vtiger_Module_Model::getInstance($parentModuleName);

            if ($parentModule) {
                $relationModel = Vtiger_Relation_Model::getInstance($parentModule, $module);

                if ($relationModel) {
                    $relationModel->addRelation($recordId, $this->id);
                }
            }
        }
    }

    /**
     * @param string $name
     * @return void
     */
    protected function createRelationFromReference(string $name)
    {
        $recordId = intval($this->column_fields[$name]);

        $this->createRelationFromRecord($recordId);
    }

    /**
     * @return void
     * @throws phpmailerException
     * @throws Exception
     */
    protected function insertIntoInvitedUsers()
    {
        $recordId = $this->id;
        $invitedUsers = explode(';', $this->column_fields['invite_users']);
        $users = [];

        if (!empty($invitedUsers)) {
            $groupsModel = Appointments_Groups_Model::getInstance();

            foreach ($invitedUsers as $invitedUser) {
                if (Settings_Groups_Record_Model::getInstance($invitedUser)) {
                    $groupUsers = array_keys($groupsModel->getUsersList($invitedUser));
                } else {
                    $groupUsers = [$invitedUser];
                }

                $users = array_merge($users, $groupUsers);
            }
        }

        $invitedUsers = $users;

        $invitedUsersModel = Appointments_InvitedUsers_Model::getInstance($recordId);
        $invitedUsersModel->setUsers($invitedUsers);
        $invitedUsersModel->deleteUsers();

        $sharingUsers = Appointments_SharingUsers_Model::getInstance($recordId);
        $sharingUsers->setUsers($invitedUsers);
        $sharingUsers->deleteUsers();

        if (!empty($invitedUsers)) {
            $invitedUsersModel->saveUsers();
            $invitedUsersModel->sendInvitation();

            $sharingUsers->saveUsers();
        }
    }

    /**
     * @return void
     */
    protected function insertIntoRecurring()
    {
        if (!in_array($_REQUEST['action'], ['Save', 'SaveOverlay'])) {
            return;
        }

        $recurringObject = Vtiger_Functions::getRecurringObjValue();
        $recordId = (int)$this->id;

        if ($recurringObject) {
            Appointments_Recurrence_Model::saveRecurring($recordId, $recurringObject);
        } else {
            Appointments_Recurrence_Model::deleteRecurring($recordId);
        }
    }

    /**
     * @return void
     */
    protected function insertIntoReminder()
    {
        $recordId = $this->id;
        $dateTimeStart = $this->column_fields['datetime_start'];
        $dateTimeStart = DateTimeField::convertToDBTimeZone($dateTimeStart);

        Appointments_Reminder_Model::saveRecord($recordId, $dateTimeStart->format('Y-m-d H:i:s'));
    }

    /**
     * This function is for Google Calendar Sync Extension
     * @return void
     */
    public function retrieveAttendees()
    {
        if (!empty($this->column_fields['attendees_contact_id'])) {
            $this->column_fields['contact_id'] = $this->column_fields['attendees_contact_id'];
        }
    }

    /**
     * @return void
     */
    public function saveDurationHours()
    {
        $datetimeEndTime = strtotime(Vtiger_Datetime_UIType::getDBDateTimeValue($this->column_fields['datetime_end']));
        $datetimeStartTime = strtotime(Vtiger_Datetime_UIType::getDBDateTimeValue($this->column_fields['datetime_start']));
        $duration = ($datetimeEndTime - $datetimeStartTime) / 60 / 60;

        $adb = PearDatabase::getInstance();
        $adb->pquery('UPDATE its4you_calendar SET duration_hours=? WHERE its4you_calendar_id=?', [$duration, $this->id]);
    }

    /**
     * @param $fieldName
     * @param $relatedModule
     * @return void
     */
    protected function saveMultiReference($fieldName, $relatedModule)
    {
        $recordId = $this->id;
        $recordModule = $this->moduleName;
        $relatedRecords = array_filter(explode(';', $this->column_fields[$fieldName]));

        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM vtiger_crmentityrel WHERE crmid=? AND module=? AND relmodule=?', [$recordId, $recordModule, $relatedModule]);

        if (!empty($relatedRecords)) {
            $this->save_related_module($recordModule, $recordId, $relatedModule, $relatedRecords);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function save_module()
    {
        $this->retrieveAttendees();

        $this->insertIntoReminder();
        $this->insertIntoInvitedUsers();
        $this->insertIntoRecurring();

        $this->saveDurationHours();
        $this->saveMultiReference('contact_id', 'Contacts');
        $this->createRelationFromMultiReference('contact_id');
        $this->createRelationFromReference('parent_id');
        $this->createRelationFromReference('account_id');
    }

    /**
     * @param string $moduleName
     * @param string $eventType
     * @return void
     * @throws AppException
     */
    public function vtlib_handler(string $moduleName, string $eventType)
    {
        Appointments_Install_Model::getInstance($eventType, $moduleName)->install();
    }

    /**
     *
     */
    public function __construct()
    {
        global $log;
        $this->column_fields = getColumnFields(get_class($this));
        $this->db = PearDatabase::getInstance();
        $this->log = $log;
    }
}
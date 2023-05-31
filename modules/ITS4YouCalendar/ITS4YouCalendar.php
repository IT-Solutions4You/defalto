<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar extends CRMEntity
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
    public string $moduleLabel = 'Calendar 4 You';
    /**
     * @var string
     */
    public string $moduleName = 'ITS4YouCalendar';
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

        $invitedUsersModel = ITS4YouCalendar_InvitedUsers_Model::getInstance($recordId);
        $invitedUsersModel->setUsers($invitedUsers);
        $invitedUsersModel->deleteUsers();

        $sharingUsers = ITS4YouCalendar_SharingUsers_Model::getInstance($recordId);
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
            ITS4YouCalendar_Recurrence_Model::saveRecurring($recordId, $recurringObject);
        } else {
            ITS4YouCalendar_Recurrence_Model::deleteRecurring($recordId);
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

        ITS4YouCalendar_Reminder_Model::saveRecord($recordId, $dateTimeStart->format('Y-m-d H:i:s'));
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
        $relatedRecords = explode(';', $this->column_fields[$fieldName]);

        PearDatabase::getInstance()->pquery('DELETE FROM vtiger_crmentityrel WHERE crmid=? AND module=? AND relmodule=?', [$recordId, $recordModule, $relatedModule]);

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
        $this->insertIntoReminder();
        $this->insertIntoInvitedUsers();
        $this->insertIntoRecurring();

        $this->saveMultiReference('contact_id', 'Contacts');
        $this->createRelationFromMultiReference('contact_id');
        $this->createRelationFromReference('parent_id');
        $this->createRelationFromReference('account_id');
    }

    /**
     * @param string $moduleName
     * @param string $eventType
     * @return void
     */
    public function vtlib_handler(string $moduleName, string $eventType)
    {
        ITS4YouCalendar_Install_Model::getInstance($eventType, $moduleName)->install();
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
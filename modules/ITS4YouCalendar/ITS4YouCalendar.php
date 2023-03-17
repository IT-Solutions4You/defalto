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
     * @var
     */
    public $id;
    /**
     * @var array|TrackableObject
     */
    public $column_fields;
    /**
     * @var Logger
     */
    public $log;
    /**
     * @var PearDatabase
     */
    public $db;
    /**
     * @var string
     */
    public string $moduleName = 'ITS4YouCalendar';
    /**
     * @var string
     */
    public string $parentName = 'Tools';
    /**
     * @var string
     */
    public string $moduleLabel = 'Calendar 4 You';
    /**
     * @var string
     */
    public string $table_name = 'its4you_calendar';
    /**
     * @var string
     */
    public string $table_index = 'its4you_calendar_id';
    /**
     * @var string
     */
    public string $entity_table = 'vtiger_crmentity';

    /**
     * @var array
     */
    public array $customFieldTable = array(
        'its4you_calendarcf',
        'its4you_calendar_id',
    );

    /**
     * @var array
     */
    public array $tab_name = array(
        'vtiger_crmentity',
        'its4you_calendar',
        'its4you_calendarcf',
        'its4you_remindme',
    );

    /**
     * @var array
     */
    public array $tab_name_index = array(
        'vtiger_crmentity' => 'crmid',
        'its4you_calendar' => 'its4you_calendar_id',
        'its4you_calendarcf' => 'its4you_calendar_id',
        'its4you_remindme' => 'record_id',
    );

    /**
     * @var array
     */
    public array $list_fields = array(
        'Subject' => array('its4you_calendar' => 'subject'),
        'Assigned To' => array('vtiger_crmentity' => 'smownerid'),
        'Description' => array('vtiger_crmentity' => 'description'),
    );

    /**
     * @var array
     */
    public array $list_fields_name = array(
        'Subject' => 'subject',
        'Assigned To' => 'assigned_user_id',
        'Description' => 'description',
    );

    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = array(
        ['ITS4YouCalendarReminder', 'modules/ITS4YouCalendar/cron/Reminder.service', 900, 'ITS4YouCalendar', 0, ''],
    );

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

    /**
     * @param string $moduleName
     * @param string $eventType
     * @return void
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        switch ($eventType) {
            case 'module.postinstall':
            case 'module.enabled':
            case 'module.postupdate':
                $this->addCustomLinks();
                break;
            case 'module.disabled':
            case 'module.preuninstall':
            case 'module.preupdate':
                $this->deleteCustomLinks();
                break;
        }
    }

    /**
     * @return void
     */
    public function addCustomLinks()
    {
        $this->installTables();
        $this->updateCron();
    }

    /**
     * @return void
     */
    public function installTables()
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `its4you_remindme` (
          `its4you_remindme_id` int(11) AUTO_INCREMENT,
          `record_id` int(11) NOT NULL,
          `reminder_time` int(11) NOT NULL,
          `reminder_sent` int(2) NOT NULL,
          `recuring_id` int(19) NOT NULL,
          PRIMARY KEY (its4you_remindme_id)
        ) ENGINE=InnoDB'
        );
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `its4you_remindme_popup` (
          `its4you_remindme_id` int(19) AUTO_INCREMENT,
          `record_id` int(19) NOT NULL,
          `datetime_start` datetime NOT NULL,
          `status` int(2) NOT NULL,
          PRIMARY KEY (its4you_remindme_id)
        ) ENGINE=InnoDB'
        );
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `its4you_invited_users` (
            `invited_users_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `record_id` int(11) NOT NULL,
            `status` varchar(50) DEFAULT NULL
            ) ENGINE=InnoDB'
        );
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `its4you_recurring` (
          `recurring_id` int(19) NOT NULL,
          `record_id` int(19) NOT NULL,
          `recurring_date` date NOT NULL,
          `recurring_end_date` date NOT NULL,
          `recurring_type` varchar(30) NOT NULL,
          `recurring_frequency` int(19) NOT NULL,
          `recurring_info` varchar(50) NOT NULL,
           UNIQUE (recurring_id)
        ) ENGINE=InnoDB'
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `its4you_recurring_rel` (
            `record_id` int(11) NOT NULL COMMENT 'first recurrence record',      
            `recurrence_id` int(11) NOT NULL COMMENT 'other or first recurrence record',
          UNIQUE (recurrence_id)
        ) ENGINE=InnoDB"
        );
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `its4you_sharing_users` (
            `crmid` INT(19) NOT NULL,
            `userid` INT(19) NOT NULL,
            `type` INT(1) NOT NULL,
            INDEX `crmid` (`crmid`) USING BTREE,
            INDEX `userid` (`userid`) USING BTREE,
            INDEX `crmid_2` (`crmid`, `userid`) USING BTREE
        ) ENGINE=InnoDB'
        );
        $this->db->query(
            'CREATE TABLE `its4you_calendar_user_types` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `default_id` int(11) DEFAULT NULL,
          `user_id` int(11) DEFAULT NULL,
          `color` varchar(8) DEFAULT NULL,
          `visible` int(1) DEFAULT NULL
        ) ENGINE=InnoDB'
        );
        $this->db->query(
            'CREATE TABLE `its4you_calendar_default_types` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `module` varchar(50) DEFAULT NULL,
          `fields` varchar(200) DEFAULT NULL,
          `default_color` varchar(8) DEFAULT NULL,
          `is_default` int(1) DEFAULT NULL
        ) ENGINE=InnoDB'
        );
        /** Database references to crmentity
         * $this->db->query(
         * 'ALTER TABLE `its4you_recurring`
         * ADD CONSTRAINT `its4you_recurring_record_id`
         * FOREIGN KEY (`record_id`)
         * REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ON UPDATE NO ACTION'
         * );
         * $this->db->query(
         * 'ALTER TABLE `its4you_invited_users`
         * ADD CONSTRAINT `its4you_invited_users_record_id`
         * FOREIGN KEY (`record_id`)
         * REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ON UPDATE NO ACTION'
         * );
         * $this->db->query(
         * 'ALTER TABLE `its4you_invited_users`
         * ADD CONSTRAINT `its4you_invited_users_user_id`
         * FOREIGN KEY (`user_id`)
         * REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION'
         * );
         * $this->db->query(
         * 'ALTER TABLE `its4you_remindme`
         * ADD CONSTRAINT `its4you_remindme_record_id`
         * FOREIGN KEY (`record_id`)
         * REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ON UPDATE NO ACTION'
         * );
         * $this->db->query(
         * 'ALTER TABLE `its4you_remindme_popup`
         * ADD CONSTRAINT `its4you_remindme_popup_record_id`
         * FOREIGN KEY (`record_id`)
         * REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ON UPDATE NO ACTION'
         * );
         * $this->db->query(
         * 'ALTER TABLE `its4you_recurring_rel`
         * ADD CONSTRAINT `its4you_recurring_rel_record_id`
         * FOREIGN KEY (`record_id`)
         * REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ON UPDATE NO ACTION'
         * );
         * $this->db->query(
         * 'ALTER TABLE `its4you_recurring_rel`
         * ADD CONSTRAINT `its4you_recurring_rel_recurrence_id`
         * FOREIGN KEY (`recurrence_id`)
         * REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ON UPDATE NO ACTION'
         * );
         */
    }

    /**
     * @param $register
     * @return void
     */
    public function updateCron($register = true)
    {
        $this->db->pquery('ALTER TABLE vtiger_cron_task MODIFY COLUMN id INT auto_increment ');

        foreach ($this->registerCron as $cronInfo) {
            list($name, $handler, $frequency, $module, $sequence, $description) = $cronInfo;

            Vtiger_Cron::deregister($name);

            if ($register) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
    }

    /**
     * @return void
     */
    public function deleteCustomLinks()
    {
        $this->updateCron(false);
    }

    /**
     * @return void
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
     * @param string $name
     * @return void
     */
    public function createRelationFromMultiReference(string $name)
    {
        $relatedRecords = explode(';', $this->column_fields[$name]);

        foreach ($relatedRecords as $relatedRecord) {
            $this->createRelationFromRecord($relatedRecord);
        }
    }

    public function createRelationFromReference(string $name)
    {
        $recordId = intval($this->column_fields[$name]);

        $this->createRelationFromRecord($recordId);
    }

    /**
     * @param $fieldName
     * @param $relatedModule
     * @return void
     */
    public function saveMultiReference($fieldName, $relatedModule)
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
     */
    public function insertIntoReminder()
    {
        $recordId = $this->id;
        $dateTimeStart = $this->column_fields['datetime_start'];

        ITS4YouCalendar_Reminder_Model::saveRecord($recordId, $dateTimeStart);
    }

    /**
     * @return void
     * @throws phpmailerException
     */
    public function insertIntoInvitedUsers()
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
    public function insertIntoRecurring()
    {
        $recurringObject = Vtiger_Functions::getRecurringObjValue();
        $recordId = $this->id;

        if ($recurringObject) {
            $recurringEndDate = null;
            $startDate = $recurringObject->startdate->get_DB_formatted_date();
            $type = $recurringObject->getRecurringType();
            $frequency = $recurringObject->getRecurringFrequency();
            $info = $recurringObject->getDBRecurringInfoString();

            if (!empty($recurringObject->recurringenddate)) {
                $recurringEndDate = $recurringObject->recurringenddate->get_DB_formatted_date();
            }

            $recurrence = ITS4YouCalendar_Recurrence_Model::getInstanceByRecord($recordId);
            $recurrence->set('recurring_date', $startDate);
            $recurrence->set('recurring_end_date', $recurringEndDate);
            $recurrence->set('recurring_type', $type);
            $recurrence->set('recurring_frequency', $frequency);
            $recurrence->set('recurring_info', $info);
            $recurrence->save();
        } else {
            $recurrence = ITS4YouCalendar_Recurrence_Model::getInstanceByRecord($recordId);
            $recurrence->delete();
        }
    }
}
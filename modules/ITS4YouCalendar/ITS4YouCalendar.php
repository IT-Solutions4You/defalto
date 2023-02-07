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
    public $id;
    public $column_fields;
    public $log;
    public $db;
    public string $moduleName = 'ITS4YouCalendar';
    public string $parentName = 'Tools';
    public string $moduleLabel = 'Calendar 4 You';
    public string $table_name = 'its4you_calendar';
    public string $table_index = 'its4you_calendar_id';
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
            'CREATE TABLE `its4you_remindme_popup` (
          `its4you_remindme_id` int(19) AUTO_INCREMENT,
          `record_id` int(19) NOT NULL,
          `datetime_start` datetime NOT NULL,
          `status` int(2) NOT NULL,
          PRIMARY KEY (its4you_remindme_id)
        ) ENGINE=InnoDB'
        );
        $this->db->query(
            'CREATE TABLE `its4you_invited_users` (
              `invited_users_id` int(11) NOT NULL,
              `user_id` int(11) NOT NULL,
              `record_id` int(11) NOT NULL
            ) ENGINE=InnoDB'
        );
    }

    /**
     * @return void
     */
    public function deleteCustomLinks()
    {
        $this->updateCron(false);
    }

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
    public function save_module()
    {
        $recordId = $this->id;
        $dateTimeStart = $this->column_fields['datetime_start'];

        ITS4YouCalendar_Reminder_Model::saveRecord($this->id, $dateTimeStart);

        $invitedUsers = [
            $this->column_fields['assigned_user_id']
        ];

        ITS4YouCalendar_Reminder_Model::saveInvitedUsers($recordId, $invitedUsers);
    }
}
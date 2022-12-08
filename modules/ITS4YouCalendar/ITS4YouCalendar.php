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
    }

    /**
     * @return void
     */
    public function installTables()
    {
        $this->db->query(
            'CREATE TABLE `its4you_remindme` (
          `its4you_remindme_id` int(11) NOT NULL,
          `record_id` int(11) NOT NULL,
          `reminder_time` int(11) NOT NULL,
          `reminder_sent` int(2) NOT NULL,
          `recuring_id` int(19) NOT NULL
        ) ENGINE=InnoDB'
        );
    }

    /**
     * @return void
     */
    public function deleteCustomLinks()
    {
    }


    /**
     * @return void
     */
    public function save_module()
    {
    }
}
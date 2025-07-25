<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Appointments_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = [
        ['AppointmentsReminder', 'modules/Appointments/cron/Reminder.php', 900, 'Appointments', 0, ''],
    ];

    public array $registerWorkflows = [
        ['Appointments', 'VTCalendarTask', 'Create Calendar Record'],
    ];
    protected PearDatabase $db;
    protected string $eventType = '';
    protected string $moduleName = 'Appointments';

    /**
     * @throws AppException
     */
    public function addCustomLinks(): void
    {
        $this->installFields();
        $this->insertEmailTemplates();
        $this->updateCron();
        $this->updateParentIdModules();
        $this->updateWorkflows();
        $this->updateFilters();
        $this->updateIcons();
        $this->updatePicklists();
        $this->updateHistory();
        $this->updateComments();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateCron(false);
        $this->updateWorkflows(false);
        $this->updateHistory(false);
        $this->updateComments(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_BASIC_INFORMATION' => [
                'subject' => [
                    'label' => 'Subject',
                    'columntype' => 'VARCHAR(255)',
                    'uitype' => 2,
                    'typeofdata' => 'V~M',
                    'summaryfield' => 1,
                    'filter' => 1,
                    'entity_identifier' => 1,
                    'masseditable' => 1,
                ],
                'is_all_day' => [
                    'column' => 'is_all_day',
                    'label' => 'Is All Day',
                    'uitype' => 56,
                    'typeofdata' => 'C~O',
                    'columntype' => 'VARCHAR(3)',
                    'quickcreate' => 0,
                ],
                'datetime_start' => [
                    'column' => 'datetime_start',
                    'label' => 'Start Datetime',
                    'uitype' => 6,
                    'typeofdata' => 'DT~M',
                    'columntype' => 'datetime',
                    'filter' => 1,
                    'headerfield' => 1,
                ],
                'datetime_end' => [
                    'column' => 'datetime_end',
                    'label' => 'End Datetime',
                    'uitype' => 6,
                    'typeofdata' => 'DT~M',
                    'columntype' => 'datetime',
                    'filter' => 1,
                    'headerfield' => 1,
                ],
                'calendar_status' => [
                    'column' => 'calendar_status',
                    'label' => 'Status',
                    'uitype' => 15,
                    'typeofdata' => 'V~M',
                    'picklist_values' => [
                        'Planned',
                        'Completed',
                        'Cancelled',
                    ],
                    'columntype' => 'VARCHAR(200)',
                    'filter' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'location' => [
                    'column' => 'location',
                    'label' => 'Location',
                    'uitype' => 1,
                    'typeofdata' => 'V~O',
                    'columntype' => 'VARCHAR(150)',
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'quickcreate' => 0,
                ],
                'calendar_priority' => [
                    'column' => 'calendar_priority',
                    'label' => 'Priority',
                    'uitype' => 15,
                    'typeofdata' => 'V~O',
                    'picklist_values' => [
                        'High',
                        'Medium',
                        'Low',
                    ],
                    'columntype' => 'VARCHAR(200)',
                    'filter' => 1,
                    'masseditable' => 1,
                ],
                'calendar_type' => [
                    'column' => 'calendar_type',
                    'label' => 'Type',
                    'uitype' => 15,
                    'typeofdata' => 'V~M',
                    'picklist_values' => [
                        ['Call', '#3B71CA'],
                        ['Meeting', '#9FA6B2'],
                        ['Email', '#14A44D'],
                        ['Reminder', '#E4A11B'],
                    ],
                    'columntype' => 'VARCHAR(200)',
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'calendar_visibility' => [
                    'column' => 'calendar_visibility',
                    'label' => 'Visibility',
                    'uitype' => 16,
                    'typeofdata' => 'V~O',
                    'picklist_values' => [
                        'Private',
                        'Public',
                    ],
                    'columntype' => 'VARCHAR(50)',
                ],
                'send_notification' => [
                    'column' => 'send_notification',
                    'label' => 'Send Notification',
                    'uitype' => 56,
                    'typeofdata' => 'C~O',
                    'columntype' => 'VARCHAR(3)',
                ],
                'assigned_user_id' => [
                    'column' => 'assigned_user_id',
                    'label' => 'Assigned To',
                    'uitype' => 53,
                    'typeofdata' => 'V~M',
                    'summaryfield' => 1,
                    'table' => 'vtiger_crmentity',
                    'filter' => 1,
                    'masseditable' => 1,
                ],
            ],
            'LBL_REMINDER_INFORMATION' => [
                'reminder_time' => [
                    'column' => 'reminder_time',
                    'table' => 'its4you_remindme',
                    'label' => 'Send Reminder',
                    'uitype' => 30,
                    'typeofdata' => 'I~O',
                    'columntype' => 'VARCHAR(150)',
                ],
            ],
            'LBL_RECURENCE_INFORMATION' => [
                'recurring_type' => [
                    'column' => 'recurring_type',
                    'label' => 'Recurrence',
                    'uitype' => 16,
                    'typeofdata' => 'O~O',
                    'columntype' => 'VARCHAR(200)',
                    'picklist_values' => [
                        'Daily',
                        'Weekly',
                        'Monthly',
                        'Yearly',
                    ],
                ],
            ],
            'LBL_REFERENCE_INFORMATION' => [
                'parent_id' => [
                    'column' => 'parent_id',
                    'label' => 'Related To',
                    'uitype' => 10,
                    'typeofdata' => 'I~O',
                    'related_modules' => [],
                    'quickcreate' => 0,
                    'columntype' => 'INT(11)',
                ],
                'contact_id' => [
                    'column' => 'contact_id',
                    'label' => 'Contact Name',
                    'uitype' => 57,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 0,
                    'related_modules' => [
                        'Contacts',
                    ],
                    'columntype' => 'VARCHAR(255)',
                ],
                'account_id' => [
                    'column' => 'account_id',
                    'label' => 'Account Name',
                    'uitype' => 10,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 0,
                    'related_modules' => [
                        'Accounts',
                    ],
                    'columntype' => 'INT(11)',
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [

            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'label' => 'Description',
                    'columntype' => 'text',
                    'uitype' => 19,
                    'typeofdata' => 'V~O',
                    'summaryfield' => 0,
                    'table' => 'vtiger_crmentity',
                ],
            ],
            'LBL_INVITE_USER_BLOCK' => [
                'invite_users' => [
                    'label' => 'Invite Users',
                    'columntype' => 'VARCHAR(255)',
                    'uitype' => 1,
                    'typeofdata' => 'V~O',
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'its4you_calendar_no' => [
                    'label' => 'Calendar No',
                    'uitype' => 4,
                    'typeofdata' => 'V~O',
                ],
                'duration_hours' => [
                    'column' => 'duration_hours',
                    'label' => 'Duration',
                    'displaytype' => 2,
                    'presence' => 2,
                    'uitype' => 63,
                    'typeofdata' => 'T~O',
                    'columntype' => 'VARCHAR(200)',
                ],
            ],
        ];
    }


    public function getTables(): array
    {
        return [
            'its4you_remindme',
            'its4you_remindme_popup',
            'its4you_invited_users',
            'its4you_recurring',
            'its4you_recurring_rel',
            'its4you_calendar_user_types',
            'its4you_calendar_default_types',
        ];
    }

    /**
     * @return void
     */
    protected function insertEmailTemplates()
    {
        if (!method_exists('EMAILMaker_Record_Model', 'saveTemplate') || !Vtiger_Utils::CheckTable('vtiger_emakertemplates')) {
            return;
        }

        $templates = [
            [
                'templatename' => 'Reminder',
                'module' => $this->moduleName,
                'description' => 'Reminder',
                'subject' => 'Reminder',
                'body' => 'Reminder',
                'owner' => Users::getActiveAdminId(),
                'sharingtype' => 'public',
                'category' => 'system',
                'is_listview' => 0,
            ],
            [
                'templatename' => 'Invitation',
                'module' => $this->moduleName,
                'description' => 'Invitation',
                'subject' => 'Invitation',
                'body' => 'Invitation',
                'owner' => Users::getActiveAdminId(),
                'sharingtype' => 'public',
                'category' => 'system',
                'is_listview' => 0,
            ],
        ];

        foreach ($templates as $template) {
            $result = $this->db->pquery('SELECT templatename FROM vtiger_emakertemplates WHERE subject=? AND deleted=? AND module=?', [$template['subject'], '0', $this->moduleName]);

            if (!$this->db->num_rows($result)) {
                EMAILMaker_Record_Model::saveTemplate($template, 0);
            }
        }
    }

    /**
     * @throws AppException
     */
    public function install()
    {
        require_once 'include/utils/utils.php';
        require_once 'vtlib/Vtiger/Module.php';
        require_once 'vtlib/Vtiger/Cron.php';
        require_once 'modules/ModComments/ModComments.php';
        require_once 'modules/ModTracker/ModTracker.php';

        switch ($this->eventType) {
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
    protected function installFields()
    {
        $moduleModel = Vtiger_Module_Model::getInstance('Users');
        $block = Vtiger_Block_Model::getInstance('LBL_CALENDAR_SETTINGS', $moduleModel);

        $fieldName = 'week_days';
        $field = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);

        if (!$field) {
            $field = new Vtiger_Field();
            $field->column = $fieldName;
            $field->name = $fieldName;
            $field->table = 'vtiger_users';
            $field->label = 'Week days';
            $field->uitype = 33;
            $field->save($block);
            $field->setPicklistValues([
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ]);

            $this->db->pquery(
                sprintf('UPDATE vtiger_users SET %s=? WHERE %s IS NULL OR %s LIKE ?', $fieldName, $fieldName, $fieldName), ['Monday |##| Tuesday |##| Wednesday |##| Thursday |##| Friday', '']
            );
        }

        $fieldName = 'slot_duration';
        $field = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);

        if (!$field) {
            $field = new Vtiger_Field();
            $field->column = $fieldName;
            $field->name = $fieldName;
            $field->table = 'vtiger_users';
            $field->label = 'Slot duration';
            $field->uitype = 15;
            $field->save($block);
            $field->setPicklistValues([
                '30 minutes',
                '15 minutes',
            ]);
            $this->db->pquery(
                sprintf('UPDATE vtiger_users SET %s=? WHERE %s IS NULL OR %s LIKE ?', $fieldName, $fieldName, $fieldName), ['30 minutes', '']
            );
        }
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('its4you_calendar', null)
            ->createTable('its4you_calendar_id',self::$COLUMN_INT)
            ->renameColumn('status','calendar_status')
            ->renameColumn('priority','calendar_priority')
            ->renameColumn('type','calendar_type')
            ->renameColumn('visibility','calendar_visibility')
            ->createColumn('subject','varchar(255) DEFAULT NULL')
            ->createColumn('is_all_day','varchar(3) DEFAULT NULL')
            ->createColumn('datetime_start','datetime DEFAULT NULL')
            ->createColumn('datetime_end','datetime DEFAULT NULL')
            ->createColumn('calendar_status','varchar(200) DEFAULT NULL')
            ->createColumn('location','varchar(150) DEFAULT NULL')
            ->createColumn('calendar_priority','varchar(200) DEFAULT NULL')
            ->createColumn('calendar_type','varchar(200) DEFAULT NULL')
            ->createColumn('calendar_visibility','varchar(50) DEFAULT NULL')
            ->createColumn('send_notification','varchar(3) DEFAULT NULL')
            ->createColumn('recurring_type','varchar(200) DEFAULT NULL')
            ->createColumn('parent_id',self::$COLUMN_INT)
            ->createColumn('contact_id','varchar(255) DEFAULT NULL')
            ->createColumn('account_id',self::$COLUMN_INT)
            ->createColumn('invite_users','varchar(255) DEFAULT NULL')
            ->createColumn('its4you_calendar_no','varchar(100) DEFAULT NULL')
            ->createColumn('duration_hours','varchar(200) DEFAULT NULL')
            ->createColumn('tags','varchar(1) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`its4you_calendar_id`)')
            ->createKey('CONSTRAINT `fk_crmid_its4you_calendar` FOREIGN KEY IF NOT EXISTS (`its4you_calendar_id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
        ;

        $this->getTable('its4you_remindme', 'remindme_id')
            ->createTable()
            ->createColumn('record_id', 'INT(11)')
            ->createColumn('reminder_time', 'int(11)')
            ->createColumn('reminder_sent', 'int(2)')
            ->createColumn('recurring_id', 'int(19)');

        $this->getTable('its4you_remindme_popup', 'remindme_popup_id')
            ->createTable()
            ->createColumn('record_id', 'int(19)')
            ->createColumn('datetime_start', 'datetime')
            ->createColumn('status', 'int(2)');

        $this->getTable('its4you_invited_users', 'invited_users_id')
            ->createTable()
            ->createColumn('user_id', 'int(11)')
            ->createColumn('record_id', 'int(11)')
            ->createColumn('status', 'varchar(50)');

        $this->getTable('its4you_recurring', 'recurring_id')
            ->createTable()
            ->createColumn('record_id', 'int(19)')
            ->createColumn('recurring_date', 'date')
            ->createColumn('recurring_end_date', 'date')
            ->createColumn('recurring_type', 'varchar(30)')
            ->createColumn('recurring_frequency', 'int(19)')
            ->createColumn('recurring_info', 'varchar(50)');

        $this->getTable('its4you_recurring_rel', 'recurring_rel_id')
            ->createTable()
            ->createColumn('record_id', 'int(11)')
            ->createColumn('recurrence_id', 'int(11)');

        $this->getTable('its4you_calendar_user_types', 'id')
            ->createTable()
            ->createColumn('default_id', 'int(11)')
            ->createColumn('user_id', 'int(11)')
            ->createColumn('color', 'varchar(8)')
            ->createColumn('visible', 'int(1)');

        $this->getTable('its4you_calendar_default_types', 'id')
            ->createTable()
            ->createColumn('module', 'varchar(50)')
            ->createColumn('fields', 'varchar(200)')
            ->createColumn('default_color', 'varchar(8)')
            ->createColumn('is_default', 'int(1)');
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $moduleName = $this->getModuleName();
        $fields = [
            'status' => 'calendar_status',
            'priority' => 'calendar_priority',
            'type' => 'calendar_type',
            'visibility' => 'calendar_visibility',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $fields);
    }

    /**
     * @return void
     */
    protected function updateFilters()
    {
        $filter = Vtiger_Filter::getInstance('Today');

        if (!$filter) {
            $moduleModel = Vtiger_Module_Model::getInstance($this->moduleName);

            $filter = new Vtiger_Filter();
            $filter->name = 'Today';
            $filter->save($moduleModel);

            $fields = ['subject', 'datetime_start', 'datetime_end', 'calendar_status', 'calendar_type', 'assigned_user_id'];

            foreach ($fields as $sequence => $field) {
                $fieldInstance = $moduleModel->getField($field);

                if ($fieldInstance) {
                    $filter->addField($fieldInstance, $sequence);
                }
            }

            $today = date('Y-m-d');
            $rules = [
                [$filter->id, 0, 'its4you_calendar:calendar_status:calendar_status:Appointments_Status:V', 'n', 'Completed,Cancelled', 1, ''],
                [$filter->id, 1, 'its4you_calendar:datetime_start:datetime_start:Appointments_Start_Datetime:DT', 'today', $today, 2, 'or'],
                [$filter->id, 2, 'its4you_calendar:datetime_end:datetime_end:Appointments_End_Datetime:DT', 'today', $today, 2, ''],
            ];

            $groups = [
                [1, $filter->id, 'and', ' 0 '],
                [2, $filter->id, null, ' 1 or 2 '],
            ];

            foreach ($groups as $group) {
                $this->db->pquery(
                    'INSERT INTO vtiger_cvadvfilter_grouping(groupid,cvid,group_condition,condition_expression) VALUES (?,?,?,?)',
                    $group
                );
            }

            foreach ($rules as $rule) {
                $this->db->pquery(
                    'INSERT INTO vtiger_cvadvfilter(cvid, columnindex, columnname, comparator, value, groupid, column_condition) VALUES(?,?,?,?,?,?,?)',
                    $rule
                );
            }
        }
    }

    /**
     * @return void
     */
    protected function updateIcons()
    {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $from = sprintf('layouts/%s/modules/%s/%s.png', $layout, $this->moduleName, $this->moduleName);
        $to = sprintf('layouts/%s/skins/images/%s.png', $layout, $this->moduleName);

        if (is_file($from) && !is_file($to)) {
            copy($from, $to);
        }
    }

    /**
     * @return void
     */
    protected function updateParentIdModules()
    {
        $moduleModel = Vtiger_Module_Model::getInstance($this->moduleName);
        $fieldModel = Vtiger_Field_Model::getInstance('parent_id', $moduleModel);

        if ($fieldModel && empty($fieldModel->getReferenceList())) {
            $integrationModels = Settings_Appointments_Integration_Model::getModules();

            foreach ($integrationModels as $integrationModel) {
                $integrationModel->setField();
                $integrationModel->setRelation();
            }
        }
    }

    /**
     * @return void
     */
    protected function updatePicklists()
    {
        $this->db->pquery('DELETE FROM vtiger_defaultcalendarview WHERE defaultcalendarview IN (?)', ['SharedCalendar']);
        $this->db->pquery('DELETE FROM vtiger_activity_view WHERE activity_view IN (?,?)', ['This Year', 'Agenda']);
        $this->db->pquery('UPDATE vtiger_calendar_type SET presence=? WHERE calendar_type IN (?,?,?,?)', ['0', 'Call', 'Meeting', 'Email', 'Reminder']);
    }
}
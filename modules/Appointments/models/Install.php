<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Appointments_Install_Model extends Vtiger_Base_Model
{
    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = [
        ['AppointmentsReminder', 'modules/Appointments/cron/Reminder.service', 900, 'Appointments', 0, ''],
    ];
    protected PearDatabase $db;
    protected string $eventType = '';
    protected string $moduleName = 'Appointments';

    /**
     * @throws AppException
     */
    protected function addCustomLinks()
    {
        $this->installTables();
        $this->installFields();
        $this->insertEmailTemplates();
        $this->updateCron();
        $this->updateParentIdModules();
        $this->updateWorkflow();
        $this->updateFilters();
        $this->updateIcons();
        $this->updatePicklists();

        ModComments::addWidgetTo([$this->moduleName]);
        ModTracker::enableTrackingForModule(getTabid($this->moduleName));
    }

    /**
     * @param $column
     * @param $type
     * @return $this
     * @throws AppException
     */
    protected function createColumn($column, $type): self
    {
        if ($this->isEmpty('table')) {
            throw new AppException('Table is empty for create column');
        }

        $sql = sprintf('ALTER TABLE %s ADD %s %s NULL', $this->get('table'), $column, $type);

        if (!columnExists($column, $this->get('table'))) {
            $this->db->query($sql);
        }

        return $this;
    }

    /**
     * @throws AppException
     */
    protected function createTable(): self
    {
        if ($this->isEmpty('table')) {
            throw new AppException('Table is empty for create table');
        }

        if ($this->isEmpty('table_id')) {
            throw new AppException('Table id is empty for create table');
        }

        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s (%s int(11) AUTO_INCREMENT,PRIMARY KEY (%s)) ENGINE=InnoDB',
            $this->get('table'),
            $this->get('table_id'),
            $this->get('table_id')
        );
        $this->db->query($sql);

        return $this;
    }

    /**
     * @return void
     */
    protected function deleteCustomLinks()
    {
        $this->updateCron(false);
        $this->updateWorkflow(false);

        ModComments::removeWidgetFrom([$this->moduleName]);
        ModTracker::disableTrackingForModule(getTabid($this->moduleName));
    }

    /**
     * @return void
     */
    public function deleteModule()
    {
        $moduleName = $this->moduleName;
        $moduleInstance = Vtiger_Module::getInstance($moduleName);
        $moduleFocus = CRMEntity::getInstance($moduleName);

        if ($moduleInstance) {
            if (!empty($moduleInstance->basetable)) {
                self::logError('Drop tables');

                $cfTable = $moduleFocus->customFieldTable[0];
                $dropTables = [
                    $moduleInstance->basetable,
                    $moduleInstance->basetable . '_seq',
                    $cfTable,
                    $cfTable . '_seq',
                ];

                self::logSuccess($dropTables);

                foreach ($dropTables as $dropTable) {
                    $this->db->pquery('DROP TABLE IF EXISTS ' . $dropTable);
                }
            }

            self::logError('Delete records, fields, blocks, related lists');

            $this->db->pquery('DELETE FROM vtiger_crmentity WHERE setype=?', [$moduleName]);
            $this->db->pquery('DELETE FROM vtiger_field WHERE tabid=?', [getTabid($moduleName)]);
            $this->db->pquery('DELETE FROM vtiger_blocks WHERE tabid=?', [getTabid($moduleName)]);
            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE tabid=? OR related_tabid=?', [getTabid($moduleName), getTabid($moduleName)]);

            self::logError('Delete picklist values');

            foreach (self::getBlocks() as $blockName => $blockInfo) {
                foreach ($blockInfo as $fieldName => $fieldInfo) {
                    if (!empty($fieldInfo['picklist_values'])) {
                        self::logError('Delete picklist values: ' . $fieldName);

                        $this->db->query('DROP TABLE IF EXISTS vtiger_' . $fieldName);
                        $this->db->pquery('DELETE FROM vtiger_picklist WHERE name=?', [$fieldName]);
                    }
                }
            }

            self::logError('Delete filter');

            $filter = Vtiger_Filter::getInstance('All', $moduleInstance);

            if ($filter) {
                $filter->delete();
            }

            self::logError('Delete module');
            $moduleInstance->delete();

            die('Delete module');
        }
    }

    /**
     * @return array
     */
    public static function getBlocks(): array
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
                'is_all_day' => [
                    'column' => 'is_all_day',
                    'label' => 'Is All Day',
                    'uitype' => 56,
                    'typeofdata' => 'C~O',
                    'columntype' => 'VARCHAR(3)',
                    'quickcreate' => 0,
                ],
                'calendar_status' => [
                    'column' => 'status',
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
                'calendar_priority' => [
                    'column' => 'priority',
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
                    'column' => 'type',
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
                    'column' => 'visibility',
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
                    'column' => 'smownerid',
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
                    'columntype' => 'INT(11)',
                ],
                'contact_id' => [
                    'column' => 'contact_id',
                    'label' => 'Contact Name',
                    'uitype' => 57,
                    'typeofdata' => 'V~O',
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
            'LBL_SYSTEM_INFO' => [
                'its4you_calendar_no' => [
                    'label' => 'Calendar No',
                    'uitype' => 4,
                    'typeofdata' => 'V~O',
                ],
                'source' => [
                    'label' => 'Source',
                    'uitype' => 1,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                    'summaryfield' => 1,
                ],
                'creator' => [
                    'column' => 'smcreatorid',
                    'label' => 'Creator',
                    'uitype' => 52,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                ],
                'createdtime' => [
                    'label' => 'Created Time',
                    'uitype' => 70,
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                    'headerfield' => 0,
                    'summaryfield' => 1,
                ],
                'modifiedby' => [
                    'label' => 'Last Modified By',
                    'uitype' => 52,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                ],
                'modifiedtime' => [
                    'label' => 'Modified Time',
                    'uitype' => 70,
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                    'summaryfield' => 1,
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

    /**
     * @param string $eventType
     * @param string $moduleName
     * @return self
     */
    public static function getInstance(string $eventType, string $moduleName): self
    {
        $instance = new self();
        $instance->eventType = $eventType;
        $instance->moduleName = $moduleName;
        $instance->db = PearDatabase::getInstance();

        return $instance;
    }

    /**
     * @param string $table
     * @param string $tableId
     * @return self
     */
    public function getTable(string $table, string $tableId): self
    {
        $clone = new self();
        $clone->db = PearDatabase::getInstance();
        $clone->set('table', $table);
        $clone->set('table_id', $tableId);

        return $clone;
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
                sprintf('UPDATE vtiger_users SET %s=? WHERE %s IS NULL OR %s LIKE ?', $fieldName, $fieldName, $fieldName),
                ['Monday |##| Tuesday |##| Wednesday |##| Thursday |##| Friday', '']
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
                sprintf('UPDATE vtiger_users SET %s=? WHERE %s IS NULL OR %s LIKE ?', $fieldName, $fieldName, $fieldName),
                ['30 minutes', '']
            );
        }
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installModule()
    {
        $this->installTables();

        self::logSuccess('Install tables');

        $moduleName = $this->moduleName;
        /** @var Appointments $moduleFocus */
        $moduleFocus = CRMEntity::getInstance($moduleName);

        $entity = in_array(get_parent_class($moduleFocus), ['CRMEntity', 'Vtiger_CRMEntity']);
        $baseTableId = $moduleFocus->table_index;
        $baseTable = $moduleFocus->table_name;
        $label = $moduleFocus->moduleLabel;
        $name = $moduleFocus->moduleName;
        $parent = $moduleFocus->parentName;
        $cfTable = $moduleFocus->customFieldTable[0];
        $version = 0.1;
        $blocks = self::getBlocks();

        if (!empty($entity) && empty($baseTableId)) {
            self::logError('Empty base table ID');
            die('Empty base table ID');
        }

        if (!empty($entity) && empty($baseTable)) {
            self::logError('Empty base table ID');
            die('Empty base table');
        }

        if (empty($label)) {
            self::logError('Dynamic created label');

            $label = str_replace('ITS', '', $name);
            $label = str_replace('4You', '', $label);
        }

        if (empty($cfTable)) {
            self::logError('Dynamic custom field table');

            $cfTable = $baseTable . 'cf';
        }

        if (empty($groupRelTable)) {
            self::logError('Dynamic group relation table');

            $groupRelTable = $baseTable . 'grouprel';
        }

        $crmVersion = Vtiger_Version::current();
        $moduleInstance = Vtiger_Module::getInstance($name);

        if (!$moduleInstance) {
            $moduleInstance = new Vtiger_Module();

            self::logSuccess('Module creating');
        } else {
            self::logSuccess('Module Updating');
        }

        $moduleInstance->name = $name;
        $moduleInstance->parent = $parent;
        $moduleInstance->label = $label;
        $moduleInstance->version = $version;
        $moduleInstance->minversion = $crmVersion;
        $moduleInstance->maxversion = $crmVersion;
        $moduleInstance->isentitytype = $entity;
        $moduleInstance->basetable = $baseTable;
        $moduleInstance->basetableid = $baseTableId;
        $moduleInstance->customtable = $cfTable;
        $moduleInstance->grouptable = $groupRelTable;
        $moduleInstance->save();

        self::logSuccess('Module created');

        $moduleInstance->setDefaultSharing();

        self::logSuccess('Sharing Access Setup');

        $moduleInstance->initWebservice();

        self::logSuccess('Webservice Setup');

        if ($entity) {
            $moduleInstance->initTables($moduleInstance->basetable, $moduleInstance->basetableid);

            Vtiger_Filter::deleteForModule($moduleInstance);

            $filterSequence = 0;
            $filter = Vtiger_Filter::getInstance('All', $moduleInstance);

            if (!$filter) {
                $filter = new Vtiger_Filter();
            }

            $filter->name = 'All';
            $filter->isdefault = true;
            $filter->save($moduleInstance);

            if (isset($blocks['LBL_ITEM_DETAILS'])) {
                $taxResult = $this->db->pquery('SELECT * FROM vtiger_inventorytaxinfo');

                while ($row = $this->db->fetchByAssoc($taxResult)) {
                    $blocks['LBL_ITEM_DETAILS'][$row['taxname']] = [
                        'table' => 'vtiger_inventoryproductrel',
                        'label' => $row['taxlabel'],
                        'uitype' => 83,
                        'typeofdata' => 'V~O',
                        'displaytype' => 5,
                        'masseditable' => 0,
                    ];
                }
            }

            foreach ($blocks as $block => $fields) {
                self::logSuccess('Block create: ' . $block);

                $blockInstance = Vtiger_Block::getInstance($block, $moduleInstance);

                if (!$blockInstance) {
                    $blockInstance = new Vtiger_Block();
                }

                    $blockInstance->label = $block;

                    $moduleInstance->addBlock($blockInstance);

                foreach ($fields as $fieldName => $fieldParams) {
                    if (empty($fieldName)) {
                        continue;
                    }

                    self::logSuccess('Field create: ' . $fieldName);

                    $relatedModules = [];
                    $picklistValues = [];

                    $fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleInstance);

                    if (!$fieldInstance) {
                        $fieldInstance = new Vtiger_Field();
                    }

                    $fieldInstance->name = $fieldName;
                    $fieldInstance->column = $fieldName;
                    $fieldInstance->table = $baseTable;

                    foreach ($fieldParams as $fieldParamName => $fieldParam) {
                        if ('picklist_values' === $fieldParamName) {
                            $picklistValues = $fieldParam;
                        } elseif ('related_modules' === $fieldParamName) {
                            $relatedModules = $fieldParam;
                        } else {
                            $fieldInstance->$fieldParamName = $fieldParam;
                        }
                    }

                    $blockInstance->addField($fieldInstance);

                    $params = [
                        'block' => $fieldInstance->getBlockId(),
                        'presence' => $fieldInstance->presence,
                        'displaytype' => $fieldInstance->displaytype,
                    ];
                    $sql = sprintf('UPDATE vtiger_field SET %s=? WHERE fieldid=?', implode('=?,', array_keys($params)));
                    $this->db->pquery($sql, [$params, $fieldInstance->id]);

                    if (!empty($picklistValues)) {
                        self::logSuccess('Picklist values create: ' . $fieldName);
                        self::logSuccess($picklistValues);

                        $picklistTable = 'vtiger_' . $fieldName;
                        $currentPicklistValues = [];

                        if (Vtiger_Utils::checkTable($picklistTable)) {
                            $currentPicklistValues = $fieldInstance->getPicklistValues();
                        }

                        foreach ($picklistValues as $picklistKey => $picklistValue) {
                            $picklistName = is_array($picklistValue) ? $picklistValue[0] : $picklistValue;

                            if (!isset($currentPicklistValues[$picklistName])) {
                                $fieldInstance->setPicklistValues([$picklistKey => $picklistValue]);
                            }
                        }
                    }

                    if (!empty($relatedModules)) {
                        self::logSuccess('Related modules create: ' . $fieldName . ' - ' . implode(',', $relatedModules));

                        foreach ($relatedModules as $relatedModule => $relatedLabel) {
                            if (!is_numeric($relatedModule)) {
                                $relModule = Vtiger_Module::getInstance($relatedModule);
                                $relModule->unsetRelatedList($moduleInstance, $relatedLabel, 'get_dependents_list');
                            }

                            $fieldInstance->setRelatedModules([$relatedModule => $relatedLabel]);
                        }
                    }

                    if (isset($fieldParams['filter'])) {
                        self::logSuccess('Filter create: ' . $fieldName);

                        $filterSequence++;
                        $filter->addField($fieldInstance, $filterSequence);
                    }

                    if (isset($fieldParams['entity_identifier'])) {
                        self::logSuccess('Entity Identifier: ' . $fieldName);

                        $moduleInstance->setEntityIdentifier($fieldInstance);
                    }
                }
            }

            self::logSuccess('Link start creating');

            if (!empty($name) && !empty($parent)) {
                Settings_MenuEditor_Module_Model::updateModuleApp($name, $parent);
                Settings_MenuEditor_Module_Model::addModuleToApp($name, $parent);

                self::logSuccess('Link created');
            } else {
                self::logError('Link not created');
            }
        }

        $moduleManagerModel = new Settings_ModuleManager_Module_Model();
        $moduleManagerModel->disableModule($moduleName);
        $moduleManagerModel->enableModule($moduleName);

        self::logSuccess('Module result: ' . $moduleName);
        self::logSuccess($moduleInstance);
    }

    /**
     * @return void
     * @throws AppException
     */
    protected function installTables()
    {
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
     * @param mixed $message
     * @return void
     */
    public static function logError($message): void
    {
        echo '<pre style="font-size: 20px; color: red;">' . print_r($message, true) . '</pre>';
    }

    /**
     * @param mixed $message
     * @return void
     */
    public static function logSuccess($message): void
    {
        echo '<pre style="font-size: 20px; color: darkolivegreen;">' . print_r($message, true) . '</pre>';
    }

    /**
     * @param bool $register
     * @return void
     */
    protected function updateCron(bool $register = true)
    {
        $this->db->pquery('ALTER TABLE vtiger_cron_task MODIFY COLUMN id INT auto_increment ');

        foreach ($this->registerCron as $cronInfo) {
            [$name, $handler, $frequency, $module, $sequence, $description] = $cronInfo;

            Vtiger_Cron::deregister($name);

            if ($register) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
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

            $tomorrow = DateTimeField::convertToDBTimeZone(date('Y-m-d'));
            $tomorrow->modify('+1439 minutes');
            $tomorrow = $tomorrow->format('Y-m-d H:i:s');
            $today = DateTimeField::convertToDBTimeZone(date('Y-m-d'));
            $today = $today->format('Y-m-d H:i:s');
            $groupId = 2;
            $rules = [
                ['its4you_calendar:datetime_start:datetime_start:Appointments_Start_Datetime:DT', 'bw', $today . ',' . $tomorrow, $groupId, 'or'],
                ['its4you_calendar:datetime_end:datetime_end:Appointments_End_Datetime:DT', 'bw', $today . ',' . $tomorrow, $groupId, ''],
            ];


            $this->db->pquery(
                'INSERT INTO vtiger_cvadvfilter_grouping(groupid,cvid,group_condition,condition_expression) VALUES (?,?,?,?)',
                [$groupId, $filter->id, null, implode(' or ', array_keys($rules))]
            );

            foreach ($rules as $index => $rule) {
                $this->db->pquery(
                    'INSERT INTO vtiger_cvadvfilter(cvid, columnindex, columnname, comparator, value, groupid, column_condition) VALUES(?,?,?,?,?,?,?)',
                    [$filter->id, $index, $rule[0], $rule[1], $rule[2], $rule[3], $rule[4]]
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

    /**
     * @param bool $register
     * @return void
     */
    protected function updateWorkflow(bool $register = true)
    {
        vimport('~~modules/com_vtiger_workflow/include.inc');
        vimport('~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc');
        vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.inc');
        vimport('~~modules/com_vtiger_workflow/VTTaskManager.inc');

        $name = 'VTCalendarTask';
        $label = 'Create Calendar Record';
        $taskType = [
            'name' => $name,
            'label' => $label,
            'classname' => $name,
            'classpath' => '',
            'templatepath' => '',
            'modules' => [
                'include' => [],
                'exclude' => [],
            ],
            'sourcemodule' => $this->moduleName,
        ];
        $files = [
            'modules/' . $this->moduleName . '/workflows/%s.inc' => 'modules/com_vtiger_workflow/tasks/%s.inc',
            'layouts/v7/modules/' . $this->moduleName . '/workflows/%s.tpl' => 'layouts/v7/modules/Settings/Workflows/Tasks/%s.tpl',
        ];

        foreach ($files as $fromFile => $toFile) {
            $fromFile = sprintf($fromFile, $name);
            $toFile = sprintf($toFile, $name);

            if (empty($taskType['classpath'])) {
                $taskType['classpath'] = $toFile;
            } elseif (empty($taskType['templatepath'])) {
                $taskType['templatepath'] = $toFile;
            }

            $copied = copy($fromFile, $toFile);
        }

        $this->db->pquery(
            'DELETE FROM com_vtiger_workflow_tasktypes WHERE tasktypename=?',
            [$name]
        );

        if ($copied && $register) {
            VTTaskType::registerTaskType($taskType);
        }
    }
}
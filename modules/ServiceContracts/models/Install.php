<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ServiceContracts_Install_Model extends Core_Install_Model
{
    /**
     * @var string
     */
    public string $moduleNumbering = 'SERCON';

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['Accounts', 'ServiceContracts', 'Service Contracts', ['ADD'], 'get_dependents_list', 'account_id'],
        ['Contacts', 'ServiceContracts', 'Service Contracts', ['ADD'], 'get_dependents_list', 'contact_id'],
        ['HelpDesk', 'ServiceContracts', 'Service Contracts', ['ADD', 'SELECT'], 'get_related_list'],
        ['ServiceContracts', 'HelpDesk', 'HelpDesk', ['ADD', 'SELECT'], 'get_related_list'],
        ['ServiceContracts', 'Documents', 'Documents', ['ADD', 'SELECT'], 'get_attachments'],
    ];

    /**
     * @var array
     * [events, file, class, condition, dependOn, modules]
     */
    public array $registerEventHandler = [
        [['vtiger.entity.beforesave', 'vtiger.entity.aftersave'], 'modules/ServiceContracts/ServiceContractsHandler.php', 'ServiceContractsHandler']
    ];

    public array $blocksHeaderFields = [
        'account_id',
        'contract_status',
        'total_units',
        'used_units',
        'due_date',
    ];

    public array $blocksSummaryFields = [
        'contract_no',
        'subject',
        'contract_status',
        'account_id',
        'contact_id',
        'tracking_unit',
        'total_units',
        'used_units',
        'start_date',
        'due_date',
        'contract_priority',
        'contract_type',
        'progress',
        'planned_duration',
        'description',
    ];

    public array $blocksListFields = [
        'contract_no',
        'subject',
        'account_id',
        'contract_status',
        'start_date',
        'due_date',
        'total_units',
        'used_units',
        'tracking_unit',
        'assigned_user_id',
    ];

    public array $blocksQuickCreateFields = [
        'subject',
        'contract_status',
        'account_id',
        'contact_id',
        'tracking_unit',
        'total_units',
        'start_date',
        'due_date',
        'contract_priority',
        'contract_type',
        'assigned_user_id',
        'description',
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateRelatedList();
        $this->updateEventHandler();
        $this->updateNumbering();
        $this->updateComments();
        $this->updateHistory();
        $this->updateContractStatusPresence();
        $this->updateToStandardModule();
    }

    /**
     * @return void
     */
    public function updateContractStatusPresence(): void
    {
        // Make the picklist value 'Complete' for status as non-editable
        $this->db->pquery('UPDATE vtiger_contract_status SET presence=0 WHERE contract_status=?', ['Complete']);
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
        $this->updateComments(false);
        $this->updateHistory(false);
        $this->updateEventHandler(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_SERVICE_CONTRACT_INFORMATION' => [
                'subject' => [
                    'column' => 'subject',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Subject',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'entity_identifier' => 1,
                ],
                'contract_status' => [
                    'uitype' => 15,
                    'column' => 'contract_status',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Status',
                    'picklist_values' => [
                        'Undefined',
                        'In Planning',
                        'In Progress',
                        'On Hold',
                        'Complete',
                        'Archived',
                    ],
                ],
                'account_id' => [
                    'uitype' => 10,
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'related_modules' => [
                        'Accounts',
                    ],
                ],
                'contact_id' => [
                    'uitype' => 10,
                    'label' => 'Contact Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'related_modules' => [
                        'Contacts',
                    ],
                ],
                'tracking_unit' => [
                    'uitype' => 15,
                    'column' => 'tracking_unit',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Tracking Unit',
                    'quickcreate' => 1,
                    'picklist_values' => [
                        'None',
                        'Days',
                        'Incidents',
                    ],
                ],
                'total_units' => [
                    'uitype' => 7,
                    'column' => 'total_units',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Total Units',
                    'quickcreate' => 1,
                    'typeofdata' => 'NN~O',
                ],
                'used_units' => [
                    'uitype' => 7,
                    'column' => 'used_units',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Used Units',
                    'quickcreate' => 1,
                    'typeofdata' => 'NN~O',
                ],
                'start_date' => [
                    'uitype' => 5,
                    'column' => 'start_date',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Start Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                ],
                'end_date' => [
                    'uitype' => 5,
                    'column' => 'end_date',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Start Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                ],
                'due_date' => [
                    'uitype' => 23,
                    'column' => 'due_date',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Due date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                ],
                'contract_priority' => [
                    'uitype' => 15,
                    'column' => 'contract_priority',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Priority',
                    'picklist_values' => [
                        'Low',
                        'Normal',
                        'High',
                    ],
                ],
                'contract_type' => [
                    'uitype' => 15,
                    'column' => 'contract_type',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Type',
                    'picklist_values' => [
                        'Support',
                        'Services',
                        'Administrative',
                    ],
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                ],
                'progress' => [
                    'uitype' => 9,
                    'column' => 'progress',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Progress',
                    'typeofdata' => 'N~O~2~2',
                    'quickcreate' => 1,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'planned_duration' => [
                    'column' => 'planned_duration',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Planned Duration',
                    'quickcreate' => 1,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'name' => 'description',
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [],
            'LBL_SYSTEM_INFORMATION' => [
                'contract_no' => [
                    'uitype' => 4,
                    'column' => 'contract_no',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Contract No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                ],
            ]
        ];
    }

    /**
     * @return string[]
     */
    public function getTables(): array
    {
        return [
            'vtiger_servicecontracts',
            'vtiger_servicecontractscf',
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_servicecontracts', null)
            ->createTable('servicecontractsid')
            ->renameColumn('priority', 'contract_priority')
            ->createColumn('start_date','date default NULL')
            ->createColumn('end_date','date default NULL')
            ->createColumn('account_id',self::$COLUMN_INT)
            ->createColumn('contact_id',self::$COLUMN_INT)
            ->createColumn('tracking_unit','varchar(100) default NULL')
            ->createColumn('total_units','decimal(5,2) default NULL')
            ->createColumn('used_units','decimal(5,2) default NULL')
            ->createColumn('subject','varchar(100) default NULL')
            ->createColumn('due_date','date default NULL')
            ->createColumn('planned_duration','varchar(256) default NULL')
            ->createColumn('actual_duration','varchar(256) default NULL')
            ->createColumn('contract_status','varchar(200) default NULL')
            ->createColumn('contract_priority','varchar(200) default NULL')
            ->createColumn('contract_type','varchar(200) default NULL')
            ->createColumn('progress','decimal(5,2) default NULL')
            ->createColumn('contract_no','varchar(100) default NULL')
            ;

        $this->getTable('vtiger_servicecontractscf', null)
            ->createTable('servicecontractsid')
            ;
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $moduleName = $this->getModuleName();
        $data = [
            'priority' => 'contract_priority',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $data);

        if (!columnExists('sc_related_to', 'vtiger_servicecontracts')) {
            return;
        }

        $this->retrieveDB();
        $fieldMap = [
            'Accounts' => 'account_id',
            'Contacts' => 'contact_id',
        ];
        $result = $this->getDB()->pquery('SELECT sc_related_to, servicecontractsid FROM vtiger_servicecontracts WHERE sc_related_to > 0');

        while ($row = $this->getDB()->fetchByAssoc($result)) {
            $recordId = $row['sc_related_to'];
            $recordModule = getSalesEntityType($recordId);
            $query = sprintf('UPDATE vtiger_servicecontracts SET %s=? WHERE servicecontractsid=?', $fieldMap[$recordModule]);
            $this->getDB()->pquery($query, [$recordId, $row['servicecontractsid']]);
        }
    }
}
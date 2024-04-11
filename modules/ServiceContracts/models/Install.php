<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ServiceContracts_Install_Model extends Vtiger_Install_Model {

    /**
     * @var string
     */
    public string $moduleNumbering = 'SERCON';

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        'Accounts', 'ServiceContracts', 'Service Contracts', ['ADD'], 'get_dependents_list',
        'Contacts', 'ServiceContracts', 'Service Contracts', ['ADD'], 'get_dependents_list',
        'HelpDesk', 'ServiceContracts', 'Service Contracts', ['ADD','SELECT'], 'get_dependents_list',
        'ServiceContracts', 'HelpDesk', 'Service Contracts', ['ADD','SELECT'], 'get_dependents_list',
        'ServiceContracts', 'Documents', 'Documents', ['ADD','SELECT'], 'get_attachments',
    ];

    /**
     * @var array
     * [events, file, class, condition, dependOn, modules]
     */
    public array $registerEventHandler = [
        [['vtiger.entity.beforesave', 'vtiger.entity.aftersave'], 'modules/ServiceContracts/ServiceContractsHandler.php', 'ServiceContractsHandler']
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateRelatedList();
        $this->updateNumbering();
        $this->updateComments();
        $this->updateHistory();
        $this->updateContractStatusPresence();
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
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_SERVICE_CONTRACT_INFORMATION' => [
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'filter' => 1,
                ],
                'createdtime' => [
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'modifiedtime' => [
                    'uitype' => 70,
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Modified Time',
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'start_date' => [
                    'uitype' => 5,
                    'column' => 'start_date',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Start Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 2,
                    'filter' => 1,
                ],
                'end_date' => [
                    'uitype' => 5,
                    'column' => 'end_date',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'End Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'sc_related_to' => [
                    'uitype' => 10,
                    'column' => 'sc_related_to',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Related to',
                    'quickcreate' => 2,
                    'relatedmodules' => [
                        'relatedmodule' => [
                            0 => 'Contacts',
                            1 => 'Accounts',
                        ],
                    ],
                    'filter' => 1,
                ],
                'tracking_unit' => [
                    'uitype' => 15,
                    'column' => 'tracking_unit',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Tracking Unit',
                    'quickcreate' => 2,
                    'picklist_values' => [
                        'None',
                        'Hours',
                        'Days',
                        'Incidents',
                    ],
                ],
                'total_units' => [
                    'uitype' => 7,
                    'column' => 'total_units',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Total Units',
                    'quickcreate' => 2,
                ],
                'used_units' => [
                    'uitype' => 7,
                    'column' => 'used_units',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Used Units',
                    'quickcreate' => 2,
                ],
                'subject' => [
                    'column' => 'subject',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Subject',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'entity_identifier' => 1,
                    'filter' => 1,
                ],
                'due_date' => [
                    'uitype' => 23,
                    'column' => 'due_date',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Due date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 2,
                    'filter' => 1,
                ],
                'planned_duration' => [
                    'column' => 'planned_duration',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Planned Duration',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'actual_duration' => [
                    'column' => 'actual_duration',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Actual Duration',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
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
                    'filter' => 1,
                ],
                'contract_priority' => [
                    'uitype' => 15,
                    'column' => 'priority',
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
                    'picklistvalues' => [
                        'picklistvalue' => [
                            0 => 'Support',
                            1 => 'Services',
                            2 => 'Administrative',
                        ],
                    ],
                ],
                'progress' => [
                    'uitype' => 9,
                    'column' => 'progress',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Progress',
                    'typeofdata' => 'N~O~2~2',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'filter' => 1,
                ],
                'contract_no' => [
                    'uitype' => 4,
                    'column' => 'contract_no',
                    'table' => 'vtiger_servicecontracts',
                    'label' => 'Contract No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                    'filter' => 1,
                ],
                'modifiedby' => [
                    'uitype' => 52,
                    'column' => 'modifiedby',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Last Modified By',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [

            ],
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
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_servicecontracts', null)
            ->createTable('servicecontractsid')
            ->createColumn('start_date','date default NULL')
            ->createColumn('end_date','date default NULL')
            ->createColumn('sc_related_to','int(11) default NULL')
            ->createColumn('tracking_unit','varchar(100) default NULL')
            ->createColumn('total_units','decimal(5,2) default NULL')
            ->createColumn('used_units','decimal(5,2) default NULL')
            ->createColumn('subject','varchar(100) default NULL')
            ->createColumn('due_date','date default NULL')
            ->createColumn('planned_duration','varchar(256) default NULL')
            ->createColumn('actual_duration','varchar(256) default NULL')
            ->createColumn('contract_status','varchar(200) default NULL')
            ->createColumn('priority','varchar(200) default NULL')
            ->createColumn('contract_type','varchar(200) default NULL')
            ->createColumn('progress','decimal(5,2) default NULL')
            ->createColumn('contract_no','varchar(100) default NULL')
            ;

        $this->getTable('vtiger_servicecontractscf', null)
            ->createTable('servicecontractsid')
            ;
    }
}
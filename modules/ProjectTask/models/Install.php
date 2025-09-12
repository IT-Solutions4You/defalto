<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_Install_Model extends Core_Install_Model
{
    public static array $progressValues = [
        '10%' => '10%',
        '20%' => '20%',
        '30%' => '30%',
        '40%' => '40%',
        '50%' => '50%',
        '60%' => '60%',
        '70%' => '70%',
        '80%' => '80%',
        '90%' => '90%',
        '100%' => '100%',
    ];

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['ProjectTask', 'Documents', 'Documents', ['ADD', 'SELECT'], 'get_attachments'],
        ['Project', 'ProjectTask', 'Project Tasks', ['ADD'], 'get_dependents_list', 'projectid'],
        ['ProjectMilestone' , 'ProjectTask', 'Project Task', ['ADD'], 'get_dependents_list', 'milestoneid'],
    ];

    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [
        [
            'ProjectTask',
            'DETAILVIEWBASIC',
            'Add Note',
            'index.php?module=Documents&action=EditView&return_module=ProjectTask&return_action=DetailView&return_id=$RECORD$&parent_id=$RECORD$',
            '<i class="fa-solid fa-clipboard-check"></i>',
            0,
            ['path' => 'modules/Documents/Documents.php', 'class' => 'Documents', 'method' => 'isLinkPermitted'],
        ],
    ];

    /**
     * @var array
     */
    public array $blocksHeaderFields = [
        'projectid',
        'milestoneid',
        'projecttaskstatus',
        'startdate',
        'planed_hours',
    ];

    /**
     * @var array
     */
    public array $blocksSummaryFields = [
        'projecttask_no',
        'projecttaskname',
        'projecttasknumber',
        'projectid',
        'milestoneid',
        'startdate',
        'enddate',
        'projecttaskstatus',
        'projecttaskpriority',
        'projecttasktype',
        'projecttaskprogress',
        'planed_hours',
        'projecttaskhours',
        'description',
    ];

    /**
     * @var array
     */
    public array $blocksListFields = [
        'projecttaskname',
        'projecttaskpriority',
        'projectid',
        'projectid:Project:account_id',
        'milestoneid',
        'startdate',
        'enddate',
        'projecttaskprogress',
        'planed_hours',
        'projecttaskhours',
        'assigned_user_id',
    ];

    /**
     * @var array
     */
    public array $blocksQuickCreateFields = [
        'projecttaskname',
        'projecttasknumber',
        'projectid',
        'milestoneid',
        'startdate',
        'enddate',
        'projecttaskstatus',
        'projecttaskpriority',
        'projecttasktype',
        'projecttaskprogress',
        'planed_hours',
        'assigned_user_id',
        'description',
    ];

    protected string $moduleNumbering = 'PT';

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateNumbering();
        $this->updateCustomLinks();
        $this->updateRelatedList();
        $this->updateComments();
        $this->updateHistory();
        $this->updateToStandardModule();
        $this->addModuleToCustomerPortal();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);
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
            'LBL_PROJECT_TASK_INFORMATION' => [
                'projecttaskname' => [
                    'uitype' => 2,
                    'column' => 'projecttaskname',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Task Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'entity_identifier' => 1,
                    'summaryfield' => 0,
                ],
                'projecttasknumber' => [
                    'uitype' => 7,
                    'column' => 'projecttasknumber',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Task Number',
                    'typeofdata' => 'I~O',
                ],
                'projectid' => [
                    'uitype' => 10,
                    'column' => 'projectid',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Project',
                    'presence' => 0,
                    'typeofdata' => 'I~M',
                    'quickcreate' => 1,
                    'related_modules' => [
                        'Project',
                    ],
                    'headerfield' => 0,
                ],
                'milestoneid' => [
                    'uitype' => 10,
                    'column' => 'milestoneid',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Milestone',
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'related_modules' => [
                        'ProjectMilestone',
                    ],
                    'headerfield' => 0,
                ],
                'startdate' => [
                    'uitype' => 5,
                    'column' => 'startdate',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Start Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                    'summaryfield' => 0,
                ],
                'enddate' => [
                    'uitype' => 5,
                    'column' => 'enddate',
                    'table' => 'vtiger_projecttask',
                    'label' => 'End Date',
                    'typeofdata' => 'D~O~OTH~GE~startdate~Start Date',
                    'summaryfield' => 0,
                ],
                'projecttaskstatus' => [
                    'uitype' => 15,
                    'column' => 'projecttaskstatus',
                    'table' => 'vtiger_projecttask',
                    'generatedtype' => 2,
                    'label' => 'Status',
                    'presence' => 0,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                    'picklist_values' => [
                        'Open',
                        'In Progress',
                        'Completed',
                        'Deferred',
                        'Canceled',
                    ],
                    'headerfield' => 0,
                ],
                'projecttaskpriority' => [
                    'uitype' => 15,
                    'column' => 'projecttaskpriority',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Priority',
                    'picklist_values' => [
                        'low',
                        'normal',
                        'high',
                    ],
                ],
                'projecttasktype' => [
                    'uitype' => 15,
                    'column' => 'projecttasktype',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Type',
                    'picklist_values' => [
                        'administrative',
                        'operative',
                        'other',
                    ],
                    'summaryfield' => 0,
                ],
                'projecttaskprogress' => [
                    'uitype' => 15,
                    'column' => 'projecttaskprogress',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Progress',
                    'picklist_values' => self::$progressValues,
                    'picklist_overwrite' => 1,
                    'summaryfield' => 0,
                ],
                'planed_hours' => [
                    'uitype' => 7,
                    'column' => 'planed_hours',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Planed Hours',
                    'typeofdata' => 'N~O',
                ],
                'projecttaskhours' => [
                    'uitype' => 7,
                    'column' => 'projecttaskhours',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Worked Hours',
                    'typeofdata' => 'N~O',
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'description',
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'projecttask_no' => [
                    'uitype' => 4,
                    'column' => 'projecttask_no',
                    'table' => 'vtiger_projecttask',
                    'generatedtype' => 2,
                    'label' => 'Task No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [
            'vtiger_projecttask',
            'vtiger_projecttaskcf',
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_projecttask', null)
            ->createTable('projecttaskid')
            ->createColumn('projecttaskname', 'varchar(255) default NULL')
            ->createColumn('projecttask_no', 'varchar(100) default NULL')
            ->createColumn('projecttasktype', 'varchar(100) default NULL')
            ->createColumn('projecttaskpriority', 'varchar(100) default NULL')
            ->createColumn('projecttaskprogress', 'varchar(100) default NULL')
            ->createColumn('projecttaskhours', 'decimal(11,2) default NULL')
            ->createColumn('startdate', 'date default NULL')
            ->createColumn('enddate', 'date default NULL')
            ->createColumn('projectid', 'varchar(100) default NULL')
            ->createColumn('projecttasknumber', 'int(11) default NULL')
            ->createColumn('planed_hours', 'decimal(11,1) default NULL');

        $this->getTable('vtiger_projecttaskcf', '')
            ->createTable('projecttaskid');
    }
}
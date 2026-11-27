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
    public array $registerWorkflows = [
        ['ProjectTask', 'VTProjectProgressTask', 'LBL_PROJECT_PROGRESS_TASK', '{"include":["ProjectTask"],"exclude":[]}',
            'modules/ProjectTask/models/ProgressTask.php', 'layouts/d1/modules/ProjectTask/taskforms/Progress.tpl'],
        ['ProjectTask', 'VTProjectCompletionTask', 'LBL_PROJECT_COMPLETION_TASK', '{"include":["ProjectTask"],"exclude":[]}',
            'modules/ProjectTask/models/CompletionTask.php', 'layouts/d1/modules/ProjectTask/taskforms/Completion.tpl'],
    ];
    public array $registerWorkflowTasks = [
        ['Calculate project progress', 'ProjectTask', '3', '3', [], [
            ['modules/ProjectTask/models/ProgressTask.php', 'Calculate project progress', 'VTProjectProgressTask', []],
        ]],
        ['Complete project when all tasks are completed', 'ProjectTask', '3', '3', [], [
            ['modules/ProjectTask/models/CompletionTask.php', 'Complete project when all tasks are completed', 'VTProjectCompletionTask', []],
        ]],
    ];
    public array $registerEventHandler = [
        [['vtiger.entity.beforedelete', 'vtiger.entity.afterdelete'], 'modules/ProjectTask/handlers/Progress.php',
            'ProjectTask_Progress_Handler', '[]', ['ProjectTask']],
    ];
    public array $registerClosedStatuses = ['Completed', 'Canceled'];
    public array $registerSummaryWidgets = [
        ['ProjectTask', 'LBL_CHECKLIST', 'module=ProjectTask&view=Widget&record=$RECORD$&mode=showChecklist&field=checklists', '', 5],
    ];

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        self::DOCUMENTS_RELATED_LIST,
        self::EMAILS_RELATED_LIST,
        self::APPOINTMENTS_RELATED_LIST,
    ];

    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [];

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

    public array $relatedListFields = [
        [
            'projecttask_no',
            'projecttaskname',
            'projectid',
            'milestoneid',
            'projecttaskstatus',
            'projecttaskpriority',
            'startdate',
            'planed_hours',
            'assigned_user_id',
        ],
        'startdate',
        'ASC'
    ];
    public array $popupFields = [
        'projecttask_no',
        'projecttaskname',
        'projectid',
        'milestoneid',
        'projecttaskstatus',
        'projecttaskpriority',
        'startdate',
        'planed_hours',
        'assigned_user_id',
    ];

    /**
     * @return void
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateNumbering();
        $this->updateCustomLinks();
        $this->updateComments();
        $this->updateRelatedList();
        $this->updateHistory();
        $this->updateToStandardModule();
        $this->addModuleToCustomerPortal();
        $this->updateWorkflows();
        $this->updateWorkflowTasks();
        $this->updateEventHandler();

        $this->registerDefaultClosedStatuses();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);
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
                        ['Open', '#E5E7EB', 0],
                        ['In Progress', '#5FA6FF', 0],
                        ['Completed', '#5CE573', 0],
                        ['Deferred', '#F0D98B', 0],
                        ['Canceled', '#F5587D', 0],
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
                    'uitype' => 9,
                    'column' => 'projecttaskprogress',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Progress',
                    'typeofdata' => 'N~O',
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
                    'label' => 'LBL_TASK_ASSIGNEE',
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
                'checklists' => [
                    'uitype' => Core_Checklist_Model::UI_TYPE,
                    'column' => 'checklists',
                    'table' => 'vtiger_projecttask',
                    'label' => 'LBL_CHECKLIST',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
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
                    'ajaxeditable' => 0,
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
            'df_projecttask_closed_status',
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('df_projecttask_closed_status', null)
            ->createTable('statusid', 'int(19) NOT NULL PRIMARY KEY');

        $this->getTable('vtiger_projecttask', null)
            ->createTable('projecttaskid')
            ->createColumn('projecttaskname', 'varchar(255) default NULL')
            ->createColumn('projecttask_no', 'varchar(100) default NULL')
            ->createColumn('projecttasktype', 'varchar(100) default NULL')
            ->createColumn('projecttaskpriority', 'varchar(100) default NULL')
            ->createColumn('projecttaskprogress', 'int DEFAULT NULL')
            ->createColumn('projecttaskhours', 'decimal(11,2) default NULL')
            ->createColumn('startdate', 'date default NULL')
            ->createColumn('enddate', 'date default NULL')
            ->createColumn('projectid', 'varchar(100) default NULL')
            ->createColumn('projecttasknumber', 'int(11) default NULL')
            ->createColumn('planed_hours', 'decimal(11,1) default NULL')
            ->createColumn('checklists', 'MEDIUMTEXT DEFAULT NULL');

        $this->getTable('vtiger_projecttaskcf', '')
            ->createTable('projecttaskid');
    }

    public function registerDefaultClosedStatuses(): void
    {
        if (!$this->registerClosedStatuses) {
            return;
        }

        if ((new ProjectTask_ClosedStatus_Model())->hasClosedStatuses()) {
            return;
        }

        $this->retrieveDB();
        $this->getDB()->pquery('INSERT IGNORE INTO df_projecttask_closed_status (statusid)'
            . ' SELECT projecttaskstatusid FROM vtiger_projecttaskstatus WHERE projecttaskstatus IN ('
            . generateQuestionMarks($this->registerClosedStatuses) . ')', $this->registerClosedStatuses);
    }
}

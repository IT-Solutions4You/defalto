<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                    'label' => 'Project Task Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'summaryfield' => 1,
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
                    'summaryfield' => 1,
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
                    'filter' => 1,
                ],
                'projectid' => [
                    'uitype' => 10,
                    'column' => 'projectid',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Related to',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'related_modules' => [
                        'Project',
                    ],
                    'filter' => 1,
                    'headerfield' => 1,
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'filter' => 1,
                    'summaryfield' => 1,
                ],
                'projecttasknumber' => [
                    'uitype' => 7,
                    'column' => 'projecttasknumber',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Project Task Number',
                    'typeofdata' => 'I~O',
                ],
                'projecttask_no' => [
                    'uitype' => 4,
                    'column' => 'projecttask_no',
                    'table' => 'vtiger_projecttask',
                    'generatedtype' => 2,
                    'label' => 'Project Task No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                ],
                'projecttaskstatus' => [
                    'uitype' => 15,
                    'column' => 'projecttaskstatus',
                    'table' => 'vtiger_projecttask',
                    'generatedtype' => 2,
                    'label' => 'Status',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                    'picklist_values' => [
                        'Open',
                        'In Progress',
                        'Completed',
                        'Deferred',
                        'Canceled',
                    ],
                    'headerfield' => 1,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
                'projecttaskprogress' => [
                    'uitype' => 15,
                    'column' => 'projecttaskprogress',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Progress',
                    'picklist_values' => self::$progressValues,
                    'picklist_overwrite' => 1,
                    'filter' => 1,
                    'summaryfield' => 1,
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
                    'filter' => 1,
                    'typeofdata' => 'N~O',
                ],
                'startdate' => [
                    'uitype' => 5,
                    'column' => 'startdate',
                    'table' => 'vtiger_projecttask',
                    'label' => 'Start Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 0,
                    'filter' => 1,
                    'summaryfield' => 1,
                ],
                'enddate' => [
                    'uitype' => 5,
                    'column' => 'enddate',
                    'table' => 'vtiger_projecttask',
                    'label' => 'End Date',
                    'typeofdata' => 'D~O~OTH~GE~startdate~Start Date',
                    'filter' => 1,
                    'summaryfield' => 1,
                ],
                'createdtime' => [
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
                ],
                'modifiedtime' => [
                    'uitype' => 70,
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Modified Time',
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
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
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'description',
                ],
            ],
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
     * @throws AppException
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
            ->createColumn('projecttasknumber', 'int(11) default NULL');

        $this->getTable('vtiger_projecttaskcf', '')
            ->createTable('projecttaskid');
    }
}
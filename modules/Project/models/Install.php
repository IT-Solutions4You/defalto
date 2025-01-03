<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Project_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['Accounts', 'Project', 'Projects', ['ADD', 'SELECT'], 'get_merged_list'],
        ['Contacts', 'Project', 'Projects', ['ADD', 'SELECT'], 'get_dependents_list'],
        ['HelpDesk', 'Project', 'Projects', ['SELECT'], 'get_related_list'],

        ['Project', 'ProjectTask', 'Project Tasks', ['ADD'], 'get_dependents_list', 'projectid'],
        ['Project', 'ProjectMilestone', 'Project Milestones', ['ADD'], 'get_dependents_list', 'projectid'],
        ['Project', 'ProjectTeam', 'Project Team', ['ADD'], 'get_dependents_list'],
        ['Project', 'Documents', 'Documents', ['ADD', 'SELECT',], 'get_attachments'],
        ['Project', 'HelpDesk', 'HelpDesk', ['ADD', 'SELECT',], 'get_related_list'],
        ['Project', 'Quotes', 'Quotes', ['SELECT',], 'get_related_list'],
    ];

    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [
        [
            'Project',
            'DETAILVIEWBASIC',
            'Add Project Task',
            'index.php?module=ProjectTask&action=EditView&projectid=$RECORD$&return_module=Project&return_action=DetailView&return_id=$RECORD$',
            '<i class="fa-solid fa-clipboard-check"></i>',
            0,
            ['path' => 'modules/ProjectTask/ProjectTask.php', 'class' => 'ProjectTask', 'method' => 'isLinkPermitted',],
        ],
        [
            'Project',
            'DETAILVIEWBASIC',
            'Add Note',
            'index.php?module=Documents&action=EditView&return_module=Project&return_action=DetailView&return_id=$RECORD$&parent_id=$RECORD$',
            '<i class="fa-solid fa-file"></i>',
            0,
            ['path' => 'modules/Documents/Documents.php', 'class' => 'Documents', 'method' => 'isLinkPermitted',],
        ],
    ];

    protected string $moduleNumbering = 'PROJ';

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateNumbering();
        $this->updateRelatedList();
        $this->updateCustomLinks();
        $this->updateComments();
        $this->updateHistory();
        $this->addRelatedListCharts();
        $this->addModuleToCustomerPortal();
    }



    public function addRelatedListCharts()
    {
        // Add Gnatt chart to the related list of the module
        $tabId = getTabid($this->moduleName);
        $relationId = $this->db->getUniqueID('vtiger_relatedlists');
        $sequenceResult = $this->db->pquery('SELECT max(sequence) as maxsequence FROM vtiger_relatedlists WHERE tabid=?', [$tabId]);
        $sequence = (int)$this->db->query_result($sequenceResult, 0, 'maxsequence') + 1;
        $this->db->pquery(
            'INSERT INTO vtiger_relatedlists(relation_id,tabid,related_tabid,name,sequence,label,presence) VALUES(?,?,?,?,?,?,?)',
            [$relationId, $tabId, 0, 'get_gantt_chart', $sequence, 'Charts', 0]
        );
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
        $this->updateCustomLinks(false);
        $this->updateComments(false);
        $this->updateHistory(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_PROJECT_INFORMATION' => [
                'projectname' => [
                    'uitype' => 2,
                    'column' => 'projectname',
                    'table' => 'vtiger_project',
                    'label' => 'Project Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'entity_identifier' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'startdate' => [
                    'uitype' => 23,
                    'column' => 'startdate',
                    'table' => 'vtiger_project',
                    'label' => 'Start Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'targetenddate' => [
                    'uitype' => 23,
                    'column' => 'targetenddate',
                    'table' => 'vtiger_project',
                    'label' => 'Target End Date',
                    'typeofdata' => 'D~O~OTH~GE~startdate~Start Date',
                    'quickcreate' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'actualenddate' => [
                    'uitype' => 23,
                    'column' => 'actualenddate',
                    'table' => 'vtiger_project',
                    'label' => 'Actual End Date',
                    'typeofdata' => 'D~O~OTH~GE~startdate~Start Date',
                    'filter' => 1,
                ],
                'projectstatus' => [
                    'uitype' => 15,
                    'column' => 'projectstatus',
                    'table' => 'vtiger_project',
                    'label' => 'Status',
                    'picklist_values' => [
                        'prospecting',
                        'initiated',
                        'in progress',
                        'waiting for feedback',
                        'on hold',
                        'completed',
                        'delivered',
                        'archived',
                    ],
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'projecttype' => [
                    'uitype' => 15,
                    'column' => 'projecttype',
                    'table' => 'vtiger_project',
                    'label' => 'Type',
                    'picklist_values' => [
                        'administrative',
                        'operative',
                        'other',
                    ],
                ],
                'linktoaccountscontacts' => [
                    'uitype' => 10,
                    'column' => 'linktoaccountscontacts',
                    'table' => 'vtiger_project',
                    'label' => 'Related to',
                    'related_modules' => [
                        'Accounts',
                        'Contacts',
                    ],
                    'filter' => 1,
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'project_no' => [
                    'uitype' => 4,
                    'column' => 'project_no',
                    'table' => 'vtiger_project',
                    'generatedtype' => 2,
                    'label' => 'Project No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                ],
                'potentialid' => [
                    'uitype' => 10,
                    'column' => 'potentialid',
                    'table' => 'vtiger_project',
                    'label' => 'Potential Name',
                    'typeofdata' => 'I~O',
                ],
                'isconvertedfrompotential' => [
                    'uitype' => 56,
                    'column' => 'isconvertedfrompotential',
                    'table' => 'vtiger_project',
                    'label' => 'Is Converted From Opportunity',
                    'typeofdata' => 'C~O',
                    'displaytype'=> 1,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
                'targetbudget' => [
                    'uitype' => 7,
                    'column' => 'targetbudget',
                    'table' => 'vtiger_project',
                    'label' => 'Target Budget',
                    'filter' => 1,
                ],
                'projecturl' => [
                    'uitype' => 17,
                    'column' => 'projecturl',
                    'table' => 'vtiger_project',
                    'label' => 'Project Url',
                ],
                'projectpriority' => [
                    'uitype' => 15,
                    'column' => 'projectpriority',
                    'table' => 'vtiger_project',
                    'label' => 'Priority',
                    'picklist_values' => [
                        'low',
                        'normal',
                        'high',
                    ],
                ],
                'progress' => [
                    'uitype' => 15,
                    'column' => 'progress',
                    'table' => 'vtiger_project',
                    'label' => 'Progress',
                    'picklist_values' => [
                        '10%',
                        '20%',
                        '30%',
                        '40%',
                        '50%',
                        '60%',
                        '70%',
                        '80%',
                        '90%',
                        '100%',
                    ],
                    'filter' => 1,
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
                    'label' => 'Description',
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
            'vtiger_project',
            'vtiger_projectcf',
        ];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_project', null)
            ->createTable('projectid')
            ->createColumn('projectname', 'varchar(255) default NULL')
            ->createColumn('project_no', 'varchar(100) default NULL')
            ->createColumn('startdate', 'date default NULL')
            ->createColumn('targetenddate', 'date default NULL')
            ->createColumn('actualenddate', 'date default NULL')
            ->createColumn('targetbudget', 'varchar(255) default NULL')
            ->createColumn('projecturl', 'varchar(255) default NULL')
            ->createColumn('projectstatus', 'varchar(100) default NULL')
            ->createColumn('projectpriority', 'varchar(100) default NULL')
            ->createColumn('projecttype', 'varchar(100) default NULL')
            ->createColumn('progress', 'varchar(100) default NULL')
            ->createColumn('linktoaccountscontacts', 'varchar(100) default NULL')
            ->createColumn('potentialid', 'int(19) default NULL')
            ->createColumn('isconvertedfrompotential', 'INT(1) NOT NULL DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`projectid`)')
        ;

        $this->getTable('vtiger_projectcf', null)
            ->createTable('projectid');
    }
}
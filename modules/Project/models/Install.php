<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_Install_Model extends Core_Install_Model
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
        ['Accounts', 'Project', 'Projects', ['ADD', 'SELECT'], 'get_merged_list', 'account_id'],
        ['Contacts', 'Project', 'Projects', ['ADD', 'SELECT'], 'get_dependents_list', 'contact_id'],
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

    public array $blocksHeaderFields = [
        'projectstatus',
        'account_id',
        'startdate',
        'targetenddate',
        'progress',
    ];

    public array $blocksSummaryFields = [
        'project_no',
        'projectname',
        'startdate',
        'targetenddate',
        'actualenddate',
        'projectstatus',
        'projectpriority',
        'account_id',
        'contact_id',
        'progress',
        'targetbudget',
        'projecttype',
        'projecturl',
        'potentialid',
        'description',
    ];

    public array $blocksListFields = [
        'projectname',
        'startdate',
        'targetenddate',
        'actualenddate',
        'account_id',
        'projectstatus',
        'targetbudget',
        'progress',
        'assigned_user_id',
    ];

    public array $blocksQuickCreateFields = [
        'projectname',
        'startdate',
        'targetenddate',
        'actualenddate',
        'projectstatus',
        'projectpriority',
        'account_id',
        'contact_id',
        'progress',
        'targetbudget',
        'projecttype',
        'projecturl',
        'assigned_user_id',
        'description'
    ];

    protected string $moduleNumbering = 'PROJ';

    /**
     * @return void
     * @throws Exception
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

    /**
     * @return void
     * @throws Exception
     */
    public function addRelatedListCharts(): void
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
                    'quickcreate' => 1,
                    'entity_identifier' => 1,
                    'summaryfield' => 0,
                ],
                'startdate' => [
                    'uitype' => 23,
                    'column' => 'startdate',
                    'table' => 'vtiger_project',
                    'label' => 'Start Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 0,
                ],
                'targetenddate' => [
                    'uitype' => 23,
                    'column' => 'targetenddate',
                    'table' => 'vtiger_project',
                    'label' => 'Target End Date',
                    'typeofdata' => 'D~O~OTH~GE~startdate~Start Date',
                    'quickcreate' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 0,
                ],
                'actualenddate' => [
                    'uitype' => 23,
                    'column' => 'actualenddate',
                    'table' => 'vtiger_project',
                    'label' => 'Actual End Date',
                    'typeofdata' => 'D~O~OTH~GE~startdate~Start Date',
                    'summaryfield' => 0,
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
                    'summaryfield' => 0,
                    'headerfield' => 0,
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
                    'summaryfield' => 0,
                ],
                'account_id' => [
                    'name' => 'account_id',
                    'uitype' => 10,
                    'column' => 'account_id',
                    'table' => 'vtiger_project',
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'Accounts',
                    ],
                    'headerfield' => 0,
                ],
                'contact_id' => [
                    'name' => 'contact_id',
                    'uitype' => 10,
                    'column' => 'contact_id',
                    'table' => 'vtiger_project',
                    'label' => 'Contact Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 0,
                    'related_modules' => [
                        'Contacts',
                    ],
                ],
                'progress' => [
                    'uitype' => 15,
                    'column' => 'progress',
                    'table' => 'vtiger_project',
                    'label' => 'Progress',
                    'picklist_values' => self::$progressValues,
                ],
                'targetbudget' => [
                    'uitype' => 71,
                    'column' => 'targetbudget',
                    'table' => 'vtiger_project',
                    'label' => 'Target Budget',
                    'typeofdata' => 'N~O',
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
                'projecturl' => [
                    'uitype' => 17,
                    'column' => 'projecturl',
                    'table' => 'vtiger_project',
                    'label' => 'Project Url',
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
                'potentialid' => [
                    'uitype' => 10,
                    'column' => 'potentialid',
                    'table' => 'vtiger_project',
                    'label' => 'Potential Name',
                    'typeofdata' => 'I~O',
                    'related_modules' => [
                        'Potentials',
                    ],
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
            'LBL_SYSTEM_INFORMATION' => [
                'isconvertedfrompotential' => [
                    'uitype' => 56,
                    'column' => 'isconvertedfrompotential',
                    'table' => 'vtiger_project',
                    'label' => 'Is Converted From Opportunity',
                    'typeofdata' => 'C~O',
                    'displaytype'=> 1,
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
                    'summaryfield' => 0,
                    'ajaxeditable' => 0,
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
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_project', null)
            ->createTable('projectid')
            ->renameColumn('accountid', 'account_id')
            ->renameColumn('contactid', 'contact_id')
            ->createColumn('projectname', 'varchar(255) default NULL')
            ->createColumn('project_no', 'varchar(100) default NULL')
            ->createColumn('startdate', 'date default NULL')
            ->createColumn('targetenddate', 'date default NULL')
            ->createColumn('actualenddate', 'date default NULL')
            ->createColumn('targetbudget', self::$COLUMN_DECIMAL)
            ->createColumn('projecturl', 'varchar(255) default NULL')
            ->createColumn('projectstatus', 'varchar(100) default NULL')
            ->createColumn('projectpriority', 'varchar(100) default NULL')
            ->createColumn('projecttype', 'varchar(100) default NULL')
            ->createColumn('progress', 'varchar(100) default NULL')
            ->createColumn('account_id', self::$COLUMN_INT)
            ->createColumn('contact_id', self::$COLUMN_INT)
            ->createColumn('potentialid', self::$COLUMN_INT)
            ->createColumn('isconvertedfrompotential', 'INT(1) NOT NULL DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`projectid`)')
        ;

        $this->getTable('vtiger_projectcf', null)
            ->createTable('projectid');
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $moduleName = $this->getModuleName();
        $fields = [
            'accountid' => 'account_id',
            'contactid' => 'contact_id',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $fields);

        $table = $this->getTable('vtiger_project', 'projectid');

        if (columnExists('linktoaccountscontacts', 'vtiger_project')) {
            $adb = $this->getDB();
            $result = $adb->pquery('SELECT projectid, linktoaccountscontacts FROM vtiger_project WHERE linktoaccountscontacts > 0');

            while ($row = $adb->fetchByAssoc($result)) {
                $recordId = $row['linktoaccountscontacts'];
                $data = ['linktoaccountscontacts' => null];

                if ('Accounts' === getSalesEntityType($recordId)) {
                    $data['account_id'] = $recordId;
                } else {
                    $data['contact_id'] = $recordId;
                }

                $table->updateData($data, ['projectid' => $row['projectid']]);
            }

            $moduleModel = Vtiger_Module_Model::getInstance('Project');
            $fieldModel = Vtiger_Field_Model::getInstance('linktoaccountscontacts', $moduleModel);

            if($fieldModel) {
                $fieldModel->delete();
            }
        }
    }
}
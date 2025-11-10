<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectMilestone_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['ProjectTask', 'Documents', 'Documents', ['ADD', 'SELECT'], 'get_attachments'],
        ['Project', 'ProjectMilestone', 'Project Milestones', ['ADD'], 'get_dependents_list', 'projectid'],
        ['ProjectMilestone' , 'ProjectTask', 'Project Task', ['ADD'], 'get_dependents_list', 'milestoneid'],
    ];

    protected string $moduleNumbering = 'PM';

    /**
     * @return void
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateNumbering();
        $this->updateComments();
        $this->updateHistory();
        $this->addModuleToCustomerPortal();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateComments(false);
        $this->updateHistory(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_PROJECT_MILESTONE_INFORMATION' => [
                'projectmilestonename' => [
                    'uitype' => 2,
                    'column' => 'projectmilestonename',
                    'table' => 'vtiger_projectmilestone',
                    'label' => 'Milestone Name',
                    'typeofdata' => 'V~M',
                    'masseditable' => 1,
                    'entity_identifier' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                    'quickcreate' => 2,
                    'quicksequence' => 1,
                ],
                'projectmilestonedate' => [
                    'uitype' => 5,
                    'column' => 'projectmilestonedate',
                    'table' => 'vtiger_projectmilestone',
                    'label' => 'Milestone Date',
                    'typeofdata' => 'D~O',
                    'masseditable' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 4,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 4,
                    'quickcreate' => 2,
                    'quicksequence' => 2,
                ],
                'projectid' => [
                    'uitype' => 10,
                    'column' => 'projectid',
                    'table' => 'vtiger_projectmilestone',
                    'label' => 'Project',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'masseditable' => 1,
                    'related_modules' => [
                        'Project',
                    ],
                    'headerfield' => 1,
                    'headerfieldsequence' => 2,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 5,
                    'quickcreate' => 2,
                    'quicksequence' => 3,
                ],
                'projectmilestonestatus' => [
                    'uitype' => 15,
                    'column' => 'projectmilestonestatus',
                    'table' => 'vtiger_projectmilestone',
                    'label' => 'Milestone Status',
                    'masseditable' => 1,
                    'picklist_values' => [
                        'Planned',
                        'In progress',
                        'Completed',
                    ],
                    'headerfield' => 1,
                    'headerfieldsequence' => 3,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 3,
                    'quickcreate' => 2,
                    'quicksequence' => 4,
                ],
                'projectmilestonetype' => [
                    'uitype' => 15,
                    'column' => 'projectmilestonetype',
                    'table' => 'vtiger_projectmilestone',
                    'label' => 'Type',
                    'masseditable' => 1,
                    'picklist_values' => [
                        'administrative',
                        'operative',
                        'other',
                    ],
                    'headerfield' => 1,
                    'headerfieldsequence' => 5,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 6,
                    'quickcreate' => 2,
                    'quicksequence' => 5,
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'masseditable' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
                    'quickcreate' => 2,
                    'quicksequence' => 6,
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'quickcreate' => 2,
                    'quicksequence' => 7,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'projectmilestone_no' => [
                    'uitype' => 4,
                    'column' => 'projectmilestone_no',
                    'table' => 'vtiger_projectmilestone',
                    'generatedtype' => 2,
                    'label' => 'Milestone No',
                    'presence' => 0,
                    'masseditable' => 0,
                    'headerfield' => 1,
                    'headerfieldsequence' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
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
        return [];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_projectmilestone', null)
            ->createTable('projectmilestoneid')
            ->createColumn('projectmilestonename', 'varchar(255) default NULL')
            ->createColumn('projectmilestone_no', 'varchar(100) default NULL')
            ->createColumn('projectmilestonedate', 'varchar(255) default NULL')
            ->createColumn('projectid', 'varchar(100) default NULL')
            ->createColumn('projectmilestonetype', 'varchar(100) default NULL')
            ->createColumn('projectmilestonestatus', 'varchar(100) default NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`projectmilestoneid`)');

        $this->getTable('vtiger_projectmilestonecf', null)
            ->createTable('projectmilestoneid');
    }
}
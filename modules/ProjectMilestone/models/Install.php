<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    ];

    protected string $moduleNumbering = 'PM';

    /**
     * @return void
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
                    'label' => 'Project Milestone Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'masseditable' => 1,
                    'entity_identifier' => 1,
                    'filter' => 1,
                ],
                'projectmilestonedate' => [
                    'uitype' => 5,
                    'column' => 'projectmilestonedate',
                    'table' => 'vtiger_projectmilestone',
                    'label' => 'Milestone Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 0,
                    'masseditable' => 1,
                    'filter' => 1,
                ],
                'projectid' => [
                    'uitype' => 10,
                    'column' => 'projectid',
                    'table' => 'vtiger_projectmilestone',
                    'label' => 'Related to',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'masseditable' => 1,
                    'related_modules' => [
                        'Project',
                    ],
                    'filter' => 1,
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
                    'filter' => 1,
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'masseditable' => 1,
                    'filter' => 1,
                ],
                'projectmilestone_no' => [
                    'uitype' => 4,
                    'column' => 'projectmilestone_no',
                    'table' => 'vtiger_projectmilestone',
                    'generatedtype' => 2,
                    'label' => 'Project Milestone No',
                    'presence' => 0,
                    'quickcreate' => '3',
                    'masseditable' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
                'createdtime' => [
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
                    'masseditable' => 1,
                ],
                'modifiedtime' => [
                    'uitype' => 70,
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Modified Time',
                    'typeofdata' => 'DT~O',
                    'displaytype' => 2,
                    'masseditable' => 1,
                ],
                'modifiedby' => [
                    'uitype' => 52,
                    'column' => 'modifiedby',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Last Modified By',
                    'presence' => 0,
                    'quickcreate' => '3',
                    'displaytype' => '3',
                    'masseditable' => 0,
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                    'masseditable' => 1,
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
     * @throws AppException
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
            ->createKey('PRIMARY KEY IF NOT EXISTS (`projectmilestoneid`)');

        $this->getTable('vtiger_projectmilestonecf', null)
            ->createTable('projectmilestoneid');
    }
}
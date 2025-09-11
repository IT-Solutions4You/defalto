<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class HelpDesk_Install_Model extends Core_Install_Model
{
    public array $registerRelatedLists = [
        ['HelpDesk', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['HelpDesk', 'ServiceContracts', 'Service Contracts', ['ADD','SELECT'], 'get_related_list', '',],
        ['HelpDesk', 'Services', 'Services', 'SELECT', 'get_related_list', '',],
        ['HelpDesk', 'Project', 'Projects', 'SELECT', 'get_related_list', '',],
        ['HelpDesk', 'Appointments', 'Appointments', '', 'get_related_list', '',],
        ['HelpDesk', 'ITS4YouEmails', 'ITS4YouEmails', ['SELECT'], 'get_related_list', '',],
        ['ServiceContracts', 'HelpDesk', 'HelpDesk', ['ADD', 'SELECT'], 'get_related_list'],
    ];

    public array $registerEventHandler = [
        [['vtiger.entity.aftersave.final'], 'modules/HelpDesk/HelpDeskHandler.php', 'HelpDeskHandler'],
        [['vtiger.entity.aftersave'], 'modules/HelpDesk/handlers/Comments.php', 'HelpDesk_Comments_Handler', '', ['ModComments']],
    ];

    public array $blocksHeaderFields = [
        'ticket_no',
        'parent_id',
        'ticketstatus',
        'first_comment_hours',
        'last_comment_hours',
    ];

    public array $blocksSummaryFields = [
        'ticket_title',
        'description',
        'product_id',
        'parent_id',
        'contact_id',
        'ticketstatus',
        'ticketcategories',
        'ticketpriorities',
        'ticketseverities',
        'hours',
        'solution',
        'first_comment_hours',
        'first_comment',
        'last_comment_hours',
        'last_comment',
    ];

    public array $blocksListFields = [
        'ticket_no',
        'ticket_title',
        'createdtime',
        'ticketstatus',
        'parent_id',
        'ticketpriorities',
        'description',
        'last_comment',
        'assigned_user_id',
    ];

    public array $blocksQuickCreateFields = [
        'ticket_title',
        'product_id',
        'parent_id',
        'contact_id',
        'ticketstatus',
        'ticketcategories',
        'ticketpriorities',
        'ticketseverities',
        'assigned_user_id',
        'description',
    ];

    /**
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateComments();
        $this->updateHistory();
        $this->updateRelatedList();
        $this->updateEventHandler();
        $this->updateWorkflowTasks();
    }

    /**
     * @throws Exception
     */
    public function updateWorkflowTasks(): void
    {
        $name = 'Employee response to ticket';
        $moduleName = 'HelpDesk';
        $conditions = [
            0 => (object)[
                'fieldname' => '_VT_add_comment',
                'operation' => 'is added',
                'value' => null,
                'valuetype' => 'rawtext',
                'joincondition' => 'and',
                'groupjoin' => 'and',
                'groupid' => '0',
            ],
            1 => (object)[
                'fieldname' => '_VT_add_comment',
                'operation' => 'is comment source',
                'value' => 'CRM',
                'valuetype' => 'rawtext',
                'joincondition' => '',
                'groupjoin' => 'and',
                'groupid' => '0',
            ],
        ];
        $trigger = '3';
        $recurrence = '3';

        $workflowModel = $this->updateWorkflowTask($name, $moduleName, $conditions, $trigger, $recurrence);

        require_once 'modules/com_vtiger_workflow/tasks/VTEmailTask.inc';
        $taskName = 'Send notification to employee';
        $taskType = 'VTEmailTask';
        $data = [
            'subject' => '$ticket_no: $ticket_title$(general : (__VtigerMeta__) supportEmailid)',
            'executeImmediately' => '1',
            'signature' => 'on',
            'content' => '<html>
                <head>
                    <title></title>
                    <style type="text/css">
                        .comment-box {
                            border: 1px solid #ddd;
                            background-color: #f9f9f9;
                            padding: 10px;
                            margin: 10px 0;
                            min-width: 30%;
                        }
                    </style>
                </head>
                <body>
                    <p>Dobrý deň,</p>
                    <p><strong>$(assigned_user_id : (Users) first_name) $(assigned_user_id : (Users) last_name)</strong> responded to your suggestion: <strong>$ticket_title</strong>.</p>
                    <p class="comment-box">$lastComment</p>
                    <a href="mailto:$(modifiedby : (Users) email1)?subject=$ticket_no: $ticket_title">Reply to ticket: $ticket_no </a><br />
                </body>
            </html>',
            'fromEmail' => '$(general : (__VtigerMeta__) supportName)<$(general : (__VtigerMeta__) supportEmailId)>',
            'recepient' => ['$(contact_id : (Contacts) email)'],
            'template_language' => 'en_us',
            'template' => 'custom_template',
        ];

        $this->updateWorkflowAction($taskType, $taskName, $data, $workflowModel);

        require_once 'modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.inc';
        $taskName = 'Update fields';
        $taskType = 'VTUpdateFieldsTask';
        $data = [
            'field_value_mapping' => '[{"fieldname":"ticketstatus","value":"Wait For Response","valuetype":"rawtext"}]',
        ];

        $this->updateWorkflowAction($taskType, $taskName, $data, $workflowModel);
    }

    public function deleteCustomLinks(): void
    {
        $this->updateComments(false);
        $this->updateHistory(false);
        $this->updateRelatedList(false);
        $this->updateEventHandler(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_TICKET_INFORMATION' => [
                'ticket_title' => [
                    'name' => 'ticket_title',
                    'uitype' => 22,
                    'column' => 'ticket_title',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Title',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'entity_identifier' => 1,
                ],
                'product_id' => [
                    'name' => 'product_id',
                    'uitype' => 10,
                    'column' => 'product_id',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Related to',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'Products',
                        'Services',
                    ],
                ],
                'parent_id' => [
                    'name' => 'parent_id',
                    'uitype' => 10,
                    'column' => 'parent_id',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Related To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'Accounts',
                    ],
                ],
                'contact_id' => [
                    'name' => 'contact_id',
                    'uitype' => 10,
                    'column' => 'contact_id',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Contact Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'Contacts',
                    ],
                ],
                'ticketstatus' => [
                    'name' => 'ticketstatus',
                    'uitype' => 15,
                    'column' => 'ticketstatus',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Open',
                        'In Progress',
                        'Wait For Response',
                        'Closed',
                    ],
                ],
                'ticketcategories' => [
                    'name' => 'ticketcategories',
                    'uitype' => 15,
                    'column' => 'ticketcategories',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Category',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Big Problem',
                        'Small Problem',
                        'Other Problem',
                    ],
                ],
                'ticketpriorities' => [
                    'name' => 'ticketpriorities',
                    'uitype' => 15,
                    'column' => 'ticketpriorities',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Priority',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 0,
                    'picklist_values' => [
                        'Low',
                        'Normal',
                        'High',
                        'Urgent',
                    ],
                ],
                'ticketseverities' => [
                    'name' => 'ticketseverities',
                    'uitype' => 15,
                    'column' => 'ticketseverities',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Severity',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Minor',
                        'Major',
                        'Feature',
                        'Critical',
                    ],
                ],
                'hours' => [
                    'name' => 'hours',
                    'uitype' => 1,
                    'column' => 'hours',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Hours',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'from_portal' => [
                    'name' => 'from_portal',
                    'uitype' => 56,
                    'column' => 'from_portal',
                    'table' => 'vtiger_ticketcf',
                    'label' => 'From Portal',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'name' => 'description',
                    'uitype' => 31,
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
            'LBL_TICKET_RESOLUTION' => [
                'solution' => [
                    'name' => 'solution',
                    'uitype' => 19,
                    'column' => 'solution',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Solution',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_COMMENTS' => [
                'comments' => [
                    'name' => 'comments',
                    'uitype' => 19,
                    'column' => 'comments',
                    'table' => 'vtiger_ticketcomments',
                    'label' => 'Add Comment',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'first_comment_hours' => [
                    'name' => 'first_comment_hours',
                    'uitype' => 1,
                    'column' => 'first_comment_hours',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'First Comment Hours',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'first_comment' => [
                    'name' => 'first_comment',
                    'column' => 'first_comment',
                    'uitype' => 70,
                    'table' => 'vtiger_troubletickets',
                    'label' => 'First Comment Time',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'last_comment_hours' => [
                    'name' => 'last_comment_hours',
                    'uitype' => 1,
                    'column' => 'last_comment_hours',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Last Comment Hours',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'last_comment' => [
                    'name' => 'last_comment',
                    'column' => 'last_comment',
                    'uitype' => 70,
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Last Comment Time',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'ticket_no' => [
                    'name' => 'ticket_no',
                    'uitype' => 4,
                    'column' => 'ticket_no',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Ticket No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
        ];
    }

    public function getTables(): array
    {
        return [
            'vtiger_troubletickets',
            'vtiger_ticketcf',
            'vtiger_ticketcategories',
            'vtiger_ticketcategories_seq',
            'vtiger_ticketpriorities',
            'vtiger_ticketpriorities_seq',
            'vtiger_ticketseverities',
            'vtiger_ticketseverities_seq',
            'vtiger_ticketstatus',
            'vtiger_ticketstatus_seq',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->db->pquery('SET FOREIGN_KEY_CHECKS=0');
        $this->createPicklistTable('vtiger_ticketcategories', 'ticketcategories_id', 'ticketcategories');
        $this->createPicklistTable('vtiger_ticketpriorities', 'ticketpriorities_id', 'ticketpriorities');
        $this->createPicklistTable('vtiger_ticketseverities', 'ticketseverities_id', 'ticketseverities');
        $this->createPicklistTable('vtiger_ticketstatus', 'ticketstatus_id', 'ticketstatus');

        $this->getTable('vtiger_troubletickets', null)
            ->createTable('ticketid',self::$COLUMN_INT)
            ->renameColumn('title', 'ticket_title')
            ->renameColumn('priority', 'ticketpriorities')
            ->renameColumn('severity', 'ticketseverities')
            ->renameColumn('status', 'ticketstatus')
            ->renameColumn('category', 'ticketcategories')
            ->createColumn('ticket_no','varchar(100) NOT NULL')
            ->createColumn('groupname','varchar(100) DEFAULT NULL')
            ->createColumn('parent_id',self::$COLUMN_INT)
            ->createColumn('product_id',self::$COLUMN_INT)
            ->createColumn('ticketpriorities','varchar(200) DEFAULT NULL')
            ->createColumn('ticketseverities','varchar(200) DEFAULT NULL')
            ->createColumn('ticketstatus','varchar(200) DEFAULT NULL')
            ->createColumn('ticketcategories','varchar(200) DEFAULT NULL')
            ->createColumn('ticket_title','varchar(255) NOT NULL')
            ->createColumn('solution','text DEFAULT NULL')
            ->createColumn('version_id',self::$COLUMN_INT)
            ->createColumn('hours', 'decimal(10,2) DEFAULT NULL')
            ->createColumn('contact_id',self::$COLUMN_INT)
            ->createColumn('tags','varchar(1) DEFAULT NULL')
            ->createColumn('currency_id',self::$COLUMN_INT)
            ->createColumn('conversion_rate','decimal(10,3) DEFAULT NULL')
            ->createColumn('first_comment','datetime DEFAULT NULL')
            ->createColumn('first_comment_hours','decimal(10,3) DEFAULT NULL')
            ->createColumn('last_comment','datetime DEFAULT NULL')
            ->createColumn('last_comment_hours','decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`ticketid`)')
            ->createKey('KEY IF NOT EXISTS `troubletickets_ticketid_idx` (`ticketid`)')
            ->createKey('KEY IF NOT EXISTS `troubletickets_ticketstatus_idx` (`ticketstatus`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_troubletickets` FOREIGN KEY IF NOT EXISTS (`ticketid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        /** @var $fields array column name => field name */
        $fields = [
            'title' => 'ticket_title',
            'priority' => 'ticketpriorities',
            'severity' => 'ticketseverities',
            'status' => 'ticketstatus',
            'category' => 'ticketcategories',
        ];

        CustomView_Record_Model::updateColumnNames('HelpDesk', $fields);
    }
}
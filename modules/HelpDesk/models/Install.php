<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class HelpDesk_Install_Model extends Core_Install_Model {

    public array $registerRelatedLists = [
        ['HelpDesk', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['HelpDesk', 'ServiceContracts', 'Service Contracts', 'ADD,SELECT', 'get_related_list', '',],
        ['HelpDesk', 'Services', 'Services', 'SELECT', 'get_related_list', '',],
        ['HelpDesk', 'Project', 'Projects', 'SELECT', 'get_related_list', '',],
        ['HelpDesk', 'Appointments', 'Appointments', '', 'get_related_list', '',],
        ['HelpDesk', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list', '',],
    ];

    public array $registerEventHandler = [
        [['vtiger.entity.aftersave.final'], 'modules/HelpDesk/HelpDeskHandler.php', 'HelpDeskHandler'],
        [['vtiger.entity.aftersave'], 'modules/HelpDesk/handlers/Comments.php', 'HelpDesk_Comments_Handler', '', ['ModComments']],
    ];

    public function addCustomLinks(): void
    {
        $this->updateComments();
        $this->updateHistory();
        $this->updateRelatedList();
        $this->updateEventHandler();
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
                    'column' => 'title',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Title',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                    'entity_identifier' => 1,
                ],
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
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
                    'summaryfield' => 1,
                    'related_modules' => [
                        'Accounts',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'ticketpriorities' => [
                    'name' => 'ticketpriorities',
                    'uitype' => 15,
                    'column' => 'priority',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Priority',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                    'picklist_values' => [
                        'Low',
                        'Normal',
                        'High',
                        'Urgent',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 6,
                ],
                'product_id' => [
                    'name' => 'product_id',
                    'uitype' => 59,
                    'column' => 'product_id',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Product Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ticketseverities' => [
                    'name' => 'ticketseverities',
                    'uitype' => 15,
                    'column' => 'severity',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Severity',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'Minor',
                        'Major',
                        'Feature',
                        'Critical',
                    ],
                ],
                'ticketstatus' => [
                    'name' => 'ticketstatus',
                    'uitype' => 15,
                    'column' => 'status',
                    'table' => 'vtiger_troubletickets',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'Open',
                        'In Progress',
                        'Wait For Response',
                        'Closed',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'ticketcategories' => [
                    'name' => 'ticketcategories',
                    'uitype' => 15,
                    'column' => 'category',
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
                    'summaryfield' => 1,
                    'related_modules' => [
                        'Contacts',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 5,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
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
                    'quickcreate' => 2,
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
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
                'source' => [
                    'name' => 'source',
                    'uitype' => 1,
                    'column' => 'source',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Source',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
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
                'creator' => [
                    'name' => 'creator',
                    'column' => 'smcreatorid',
                    'label' => 'Creator',
                    'uitype' => 52,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                ],
                'createdtime' => [
                    'name' => 'createdtime',
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                ],
                'modifiedby' => [
                    'name' => 'modifiedby',
                    'column' => 'modifiedby',
                    'label' => 'Last Modified By',
                    'uitype' => 52,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
                ],
                'modifiedtime' => [
                    'name' => 'modifiedtime',
                    'uitype' => 70,
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Modified Time',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
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

    public function installTables(): void
    {
        $this->db->pquery('SET FOREIGN_KEY_CHECKS=0');

        $this->getTable('vtiger_ticketcategories', 'ticketcategories_id')
            ->createTable('ticketcategories_id')
            ->createColumn('ticketcategories','varchar(200) DEFAULT NULL')
            ->createColumn('presence','int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('picklist_valueid','int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('sortorderid','int(11) DEFAULT NULL')
            ->createColumn('color','varchar(10) DEFAULT NULL');

        $this->getTable('vtiger_ticketpriorities', 'ticketpriorities_id')
            ->createTable()
            ->createColumn('ticketpriorities','varchar(200) DEFAULT NULL')
            ->createColumn('presence','int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('picklist_valueid','int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('sortorderid','int(11) DEFAULT NULL')
            ->createColumn('color','varchar(10) DEFAULT NULL');

        $this->getTable('vtiger_ticketseverities', 'ticketseverities_id')
            ->createTable()
            ->createColumn('ticketseverities','varchar(200) DEFAULT NULL')
            ->createColumn('presence','int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('picklist_valueid','int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('sortorderid','int(11) DEFAULT NULL')
            ->createColumn('color','varchar(10) DEFAULT NULL');

        $this->getTable('vtiger_ticketstatus', 'ticketstatus_id')
            ->createTable()
            ->createColumn('ticketstatus','varchar(200) DEFAULT NULL')
            ->createColumn('presence','int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('picklist_valueid','int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('sortorderid','int(11) DEFAULT NULL')
            ->createColumn('color','varchar(10) DEFAULT NULL');

        $this->getTable('vtiger_troubletickets', null)
            ->createTable('ticketid','int(19) NOT NULL')
            ->createColumn('ticket_no','varchar(100) NOT NULL')
            ->createColumn('groupname','varchar(100) DEFAULT NULL')
            ->createColumn('parent_id','varchar(100) DEFAULT NULL')
            ->createColumn('product_id','varchar(100) DEFAULT NULL')
            ->createColumn('priority','varchar(200) DEFAULT NULL')
            ->createColumn('severity','varchar(200) DEFAULT NULL')
            ->createColumn('status','varchar(200) DEFAULT NULL')
            ->createColumn('category','varchar(200) DEFAULT NULL')
            ->createColumn('title','varchar(255) NOT NULL')
            ->createColumn('solution','text DEFAULT NULL')
            ->createColumn('version_id','int(11) DEFAULT NULL')
            ->createColumn('hours','decimal(25,8) DEFAULT NULL')
            ->createColumn('days','decimal(25,8) DEFAULT NULL')
            ->createColumn('contact_id','int(19) DEFAULT NULL')
            ->createColumn('tags','varchar(1) DEFAULT NULL')
            ->createColumn('currency_id','int(19) DEFAULT NULL')
            ->createColumn('conversion_rate','decimal(10,3) DEFAULT NULL')
            ->createColumn('first_comment','datetime DEFAULT NULL')
            ->createColumn('first_comment_hours','decimal(10,3) DEFAULT NULL')
            ->createColumn('last_comment','datetime DEFAULT NULL')
            ->createColumn('last_comment_hours','decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`ticketid`)')
            ->createKey('KEY IF NOT EXISTS `troubletickets_ticketid_idx` (`ticketid`)')
            ->createKey('KEY IF NOT EXISTS `troubletickets_status_idx` (`status`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_troubletickets` FOREIGN KEY IF NOT EXISTS (`ticketid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');
    }
}
<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Faq_Install_Model extends Core_Install_Model
{
    public array $registerRelatedLists = [
        ['Faq', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Faq', 'ModComments', 'ModComments', '', 'get_comments', '',],
        ['Faq', 'Appointments', 'Appointments', '', 'get_related_list', '',],
        ['Faq', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list', '',],
        ['Documents', 'Faq', 'Faq', '1', 'get_related_list', '',],
    ];

    public array $blocksHeaderFields = [
        'faq_answer',
        'faqstatus',
        'product_id',
        'faqcategories',
    ];

    public array $blocksSummaryFields = [
        'faq_no',
        'question',
        'faqcategories',
        'faqstatus',
        'product_id',
        'faq_answer',
        'description',
    ];

    public array $blocksListFields = [
        'faq_no',
        'question',
        'faqstatus',
        'faqcategories',
        'product_id',
        'faq_answer',
        'assigned_user_id',
    ];

    public array $blocksQuickCreateFields = [
        'question',
        'faqcategories',
        'faqstatus',
        'product_id',
        'faq_answer',
        'assigned_user_id',
    ];

    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateComments();
        $this->updateHistory();
        $this->updateRelatedList();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
        $this->updateComments(false);
        $this->updateHistory(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_FAQ_INFORMATION' => [
                'question' => [
                    'name' => 'question',
                    'uitype' => 20,
                    'column' => 'question',
                    'table' => 'vtiger_faq',
                    'label' => 'Question',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'entity_identifier' => 1,
                ],
                'faqcategories' => [
                    'name' => 'faqcategories',
                    'uitype' => 15,
                    'column' => 'faqcategories',
                    'table' => 'vtiger_faq',
                    'label' => 'Category',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'General',
                    ],
                ],
                'faqstatus' => [
                    'name' => 'faqstatus',
                    'uitype' => 15,
                    'column' => 'faqstatus',
                    'table' => 'vtiger_faq',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Draft',
                        'Reviewed',
                        'Published',
                        'Obsolete',
                    ],
                ],
                'product_id' => [
                    'name' => 'product_id',
                    'uitype' => 10,
                    'column' => 'product_id',
                    'table' => 'vtiger_faq',
                    'label' => 'Related to',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'Products', 'Services'
                    ]
                ],
                'faq_answer' => [
                    'name' => 'faq_answer',
                    'uitype' => 20,
                    'column' => 'faq_answer',
                    'table' => 'vtiger_faq',
                    'label' => 'Answer',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
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
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION' => self::$fieldsDescription,
            'LBL_COMMENT_INFORMATION' => [
                'comments' => [
                    'name' => 'comments',
                    'uitype' => 19,
                    'column' => 'comments',
                    'table' => 'vtiger_faqcomments',
                    'label' => 'Add Comment',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'faq_no' => [
                    'name' => 'faq_no',
                    'uitype' => 4,
                    'column' => 'faq_no',
                    'table' => 'vtiger_faq',
                    'label' => 'Faq No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ]
        ];
    }

    public function getTables(): array
    {
        return [
            'vtiger_faq',
            'vtiger_faqcomments',
            'vtiger_faqcf',
            'vtiger_faqcategories',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_faq', null)
            ->createTable('id')
            ->renameColumn('category', 'faqcategories')
            ->renameColumn('status', 'faqstatus')
            ->renameColumn('answer', 'faq_answer')
            ->createColumn('faq_no', 'varchar(100) NOT NULL')
            ->createColumn('product_id', 'varchar(100) DEFAULT NULL')
            ->createColumn('question', 'text DEFAULT NULL')
            ->createColumn('faq_answer', 'text DEFAULT NULL')
            ->createColumn('faqcategories', 'varchar(200) NOT NULL')
            ->createColumn('faqstatus', 'varchar(200) NOT NULL')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)')
            ->createKey('KEY IF NOT EXISTS `faq_id_idx` (`id`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_faq` FOREIGN KEY IF NOT EXISTS (`id`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_faqcf', null)
            ->createTable('faqid')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`faqid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_faqcf` FOREIGN KEY IF NOT EXISTS (`faqid`) REFERENCES `vtiger_faq` (`id`) ON DELETE CASCADE');

        $this->getTable('vtiger_faqcomments', 'commentid')
            ->createTable()
            ->createColumn('faqid', 'int(19) DEFAULT NULL')
            ->createColumn('comments', 'text DEFAULT NULL')
            ->createColumn('createdtime', 'datetime NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`commentid`)')
            ->createKey('KEY IF NOT EXISTS `faqcomments_faqid_idx` (`faqid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_faqcomments` FOREIGN KEY IF NOT EXISTS (`faqid`) REFERENCES `vtiger_faq` (`id`) ON DELETE CASCADE');

        $this->getTable('vtiger_faqcategories', 'faqcategories_id')
            ->createTable()
            ->createColumn('faqcategories', 'varchar(200) DEFAULT NULL')
            ->createColumn('presence', 'int(1) NOT NULL DEFAULT 1')
            ->createColumn('picklist_valueid', 'int(19) NOT NULL DEFAULT "0"')
            ->createColumn('sortorderid', 'int(11) DEFAULT NULL')
            ->createColumn('color', 'varchar(10) DEFAULT NULL');

        $this->getTable('vtiger_faqstatus', 'faqstatus_id')
            ->createTable()
            ->createColumn('faqstatus', 'varchar(200) DEFAULT NULL')
            ->createColumn('presence', 'int(1) NOT NULL DEFAULT 1')
            ->createColumn('picklist_valueid', 'int(19) NOT NULL DEFAULT "0"')
            ->createColumn('sortorderid', 'int(11) DEFAULT NULL')
            ->createColumn('color', 'varchar(10) DEFAULT NULL');
    }

    public function migrate(): void
    {
        $moduleName = $this->getModuleName();
        $fields = [
            'category' => 'faqcategories',
            'status' => 'faqstatus',
            'answer' => 'faq_answer',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $fields);
    }
}
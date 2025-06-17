<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                'product_id' => [
                    'name' => 'product_id',
                    'uitype' => 59,
                    'column' => 'product_id',
                    'table' => 'vtiger_faq',
                    'label' => 'Product Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'faqcategories' => [
                    'name' => 'faqcategories',
                    'uitype' => 15,
                    'column' => 'category',
                    'table' => 'vtiger_faq',
                    'label' => 'Category',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'General',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'faqstatus' => [
                    'name' => 'faqstatus',
                    'uitype' => 15,
                    'column' => 'status',
                    'table' => 'vtiger_faq',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 3,
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
                'question' => [
                    'name' => 'question',
                    'uitype' => 20,
                    'column' => 'question',
                    'table' => 'vtiger_faq',
                    'label' => 'Question',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
                'faq_answer' => [
                    'name' => 'faq_answer',
                    'uitype' => 20,
                    'column' => 'answer',
                    'table' => 'vtiger_faq',
                    'label' => 'Answer',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'smownerid',
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
                    'quickcreate' => 3,
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
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_faq', null)
            ->createTable('id')
            ->createColumn('faq_no', 'varchar(100) NOT NULL')
            ->createColumn('product_id', 'varchar(100) DEFAULT NULL')
            ->createColumn('question', 'text DEFAULT NULL')
            ->createColumn('answer', 'text DEFAULT NULL')
            ->createColumn('category', 'varchar(200) NOT NULL')
            ->createColumn('status', 'varchar(200) NOT NULL')
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
}
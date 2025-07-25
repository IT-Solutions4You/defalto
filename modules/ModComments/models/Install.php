<?php

class ModComments_Install_Model extends Core_Install_Model
{

    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
    }

    public function getBlocks(): array
    {
        return [
            'LBL_MODCOMMENTS_INFORMATION' => [
                'commentcontent' => [
                    'name' => 'commentcontent',
                    'uitype' => 19,
                    'column' => 'commentcontent',
                    'table' => 'vtiger_modcomments',
                    'label' => 'Comment',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 2,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                ],
                'customer' => [
                    'name' => 'customer',
                    'uitype' => 10,
                    'column' => 'customer',
                    'table' => 'vtiger_modcomments',
                    'label' => 'Customer',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        0 => 'Contacts',
                    ],
                ],
                'userid' => [
                    'name' => 'userid',
                    'uitype' => 10,
                    'column' => 'userid',
                    'table' => 'vtiger_modcomments',
                    'label' => 'UserId',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'reasontoedit' => [
                    'name' => 'reasontoedit',
                    'uitype' => 19,
                    'column' => 'reasontoedit',
                    'table' => 'vtiger_modcomments',
                    'label' => 'ReasonToEdit',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'is_private' => [
                    'name' => 'is_private',
                    'uitype' => 7,
                    'column' => 'is_private',
                    'table' => 'vtiger_modcomments',
                    'label' => 'Is Private',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'filename' => [
                    'name' => 'filename',
                    'uitype' => 61,
                    'column' => 'filename',
                    'table' => 'vtiger_modcomments',
                    'label' => 'Attachment',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'related_email_id' => [
                    'name' => 'related_email_id',
                    'uitype' => 1,
                    'column' => 'related_email_id',
                    'table' => 'vtiger_modcomments',
                    'label' => 'Related Email Id',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_OTHER_INFORMATION' => [
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 2,
                    'summaryfield' => 1,
                ],
                'related_to' => [
                    'name' => 'related_to',
                    'uitype' => 10,
                    'column' => 'related_to',
                    'table' => 'vtiger_modcomments',
                    'label' => 'Related To',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 2,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'Leads',
                        'Contacts',
                        'Accounts',
                        'Potentials',
                        'HelpDesk',
                        'Faq',
                        'Quotes',
                        'PurchaseOrder',
                        'SalesOrder',
                        'ITS4YouEmails',
                        'Invoice',
                        'Appointments',
                        'ServiceContracts',
                        'Services',
                        'Assets',
                        'Project',
                        'ProjectMilestone',
                        'ProjectTask',
                    ],
                ],
                'parent_comments' => [
                    'name' => 'parent_comments',
                    'uitype' => 10,
                    'column' => 'parent_comments',
                    'table' => 'vtiger_modcomments',
                    'label' => 'Related To Comments',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        0 => 'ModComments',
                    ],
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
        ];
    }

    public function getTables(): array
    {
        return [
            'vtiger_modcomments',
            'vtiger_modcommentscf',
        ];
    }

    /**
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_modcomments', null)
            ->createTable('modcommentsid')
            ->createColumn('commentcontent', 'text DEFAULT NULL')
            ->createColumn('related_to', 'int(19) NOT NULL')
            ->createColumn('parent_comments', 'int(19) DEFAULT NULL')
            ->createColumn('customer', 'int(19) DEFAULT NULL')
            ->createColumn('userid', 'int(19) DEFAULT NULL')
            ->createColumn('reasontoedit', 'varchar(100) DEFAULT NULL')
            ->createColumn('is_private', 'int(1) DEFAULT \'0\'')
            ->createColumn('filename', 'varchar(255) DEFAULT NULL')
            ->createColumn('related_email_id', 'int(11) DEFAULT NULL')
            ->createKey('INDEX IF NOT EXISTS relatedto_idx (related_to)')
            ->createKey('KEY IF NOT EXISTS `relatedto_idx` (`related_to`)')
            ->createKey('KEY IF NOT EXISTS `fk_crmid_vtiger_modcomments` (`modcommentsid`)')
            ->createKey('CONSTRAINT `fk_crmid_vtiger_modcomments` FOREIGN KEY IF NOT EXISTS (`modcommentsid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_modcommentscf', null)
            ->createTable('modcommentsid');
    }
}
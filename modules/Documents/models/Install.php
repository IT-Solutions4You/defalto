<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Documents_Install_Model extends Core_Install_Model
{

    public array $registerRelatedLists = [
        ['Accounts', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Leads', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Contacts', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Potentials', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Products', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['HelpDesk', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Quotes', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['PurchaseOrder', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['SalesOrder', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Invoice', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Faq', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['ServiceContracts', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments', '',],
        ['Services', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments', '',],
        ['Assets', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments', '',],
        ['ProjectTask', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments', '',],
        ['Project', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments', '',],
        ['ITS4YouEmails', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments', '',],
        ['Documents', 'Contacts', 'Contacts', '', 'get_related_list', '',],
        ['Documents', 'Accounts', 'Accounts', '', 'get_related_list', '',],
        ['Documents', 'Potentials', 'Potentials', '', 'get_related_list', '',],
        ['Documents', 'Leads', 'Leads', '', 'get_related_list', '',],
        ['Documents', 'Products', 'Products', '', 'get_related_list', '',],
        ['Documents', 'Services', 'Services', '', 'get_related_list', '',],
        ['Documents', 'Project', 'Project', '', 'get_related_list', '',],
        ['Documents', 'Assets', 'Assets', '', 'get_related_list', '',],
        ['Documents', 'ServiceContracts', 'ServiceContracts', '', 'get_related_list', '',],
        ['Documents', 'Quotes', 'Quotes', '', 'get_related_list', '',],
        ['Documents', 'Invoice', 'Invoice', '', 'get_related_list', '',],
        ['Documents', 'SalesOrder', 'SalesOrder', '', 'get_related_list', '',],
        ['Documents', 'PurchaseOrder', 'PurchaseOrder', '', 'get_related_list', '',],
        ['Documents', 'HelpDesk', 'HelpDesk', '', 'get_related_list', '',],
        ['Documents', 'Faq', 'Faq', '', 'get_related_list', '',],
        ['Documents', 'Appointments', 'Appointments', '', 'get_related_list', '',],
        ['Documents', 'ITS4YouEmails', 'ITS4YouEmails', '', 'get_related_list', '',],
    ];

    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateRelatedList();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_NOTE_INFORMATION' => [
                'notes_title' => [
                    'name' => 'notes_title',
                    'uitype' => 2,
                    'column' => 'notes_title',
                    'table' => 'vtiger_notes',
                    'label' => 'Title',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                    'entity_identifier' => 1,
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
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'folderid' => [
                    'name' => 'folderid',
                    'uitype' => 26,
                    'column' => 'folderid',
                    'table' => 'vtiger_notes',
                    'label' => 'Folder Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'defaultvalue' => 1,
                    'filter' => 1,
                    'filter_sequence' => 5,
                ],
                'modifiedby' => [
                    'name' => 'modifiedby',
                    'uitype' => 52,
                    'column' => 'modifiedby',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Last Modified By',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 0,
                    'summaryfield' => 0,
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
            ],
            'LBL_FILE_INFORMATION' => [
                'filename' => [
                    'name' => 'filename',
                    'uitype' => 28,
                    'column' => 'filename',
                    'table' => 'vtiger_notes',
                    'label' => 'File Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'filetype' => [
                    'name' => 'filetype',
                    'uitype' => 1,
                    'column' => 'filetype',
                    'table' => 'vtiger_notes',
                    'label' => 'File Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'filesize' => [
                    'name' => 'filesize',
                    'uitype' => 1,
                    'column' => 'filesize',
                    'table' => 'vtiger_notes',
                    'label' => 'File Size',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'filelocationtype' => [
                    'name' => 'filelocationtype',
                    'uitype' => 27,
                    'column' => 'filelocationtype',
                    'table' => 'vtiger_notes',
                    'label' => 'Download Type',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'defaultvalue' => 'I',
                    'filter' => 1,
                    'filter_sequence' => 6,
                ],
                'fileversion' => [
                    'name' => 'fileversion',
                    'uitype' => 1,
                    'column' => 'fileversion',
                    'table' => 'vtiger_notes',
                    'label' => 'Version',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'filestatus' => [
                    'name' => 'filestatus',
                    'uitype' => 56,
                    'column' => 'filestatus',
                    'table' => 'vtiger_notes',
                    'label' => 'Active',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => '1',
                    'filter' => 1,
                    'filter_sequence' => 7,
                ],
                'filedownloadcount' => [
                    'name' => 'filedownloadcount',
                    'uitype' => 1,
                    'column' => 'filedownloadcount',
                    'table' => 'vtiger_notes',
                    'label' => 'Download Count',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_DESCRIPTION' => [
                'notecontent' => [
                    'name' => 'notecontent',
                    'uitype' => 19,
                    'column' => 'notecontent',
                    'table' => 'vtiger_notes',
                    'label' => 'Note',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'Custom Informations' => [],
            'LBL_SYSTEM_INFORMATION' => [
                'note_no' => [
                    'name' => 'note_no',
                    'uitype' => 4,
                    'column' => 'note_no',
                    'table' => 'vtiger_notes',
                    'label' => 'Document No',
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
            'vtiger_notes',
            'vtiger_notescf',
            'vtiger_senotesrel',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_notes', null)
            ->createTable('notesid', 'INT(19)')
            ->renameColumn('title', 'notes_title')
            ->createColumn('note_no', 'varchar(100) NOT NULL')
            ->createColumn('notes_title', 'varchar(50) NOT NULL')
            ->createColumn('filename', 'varchar(200) DEFAULT NULL')
            ->createColumn('notecontent', 'text DEFAULT NULL')
            ->createColumn('folderid', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('filetype', 'varchar(50) DEFAULT NULL')
            ->createColumn('filelocationtype', 'varchar(5) DEFAULT null')
            ->createColumn('filedownloadcount', 'int(19) DEFAULT NULL')
            ->createColumn('filestatus', 'int(19) DEFAULT 1')
            ->createColumn('filesize', 'int(19) NOT NULL DEFAULT "0"')
            ->createColumn('fileversion', 'varchar(50) DEFAULT NULL')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`notesid`)')
            ->createKey('KEY IF NOT EXISTS `notes_title_idx` (`notes_title`)')
            ->createKey('KEY IF NOT EXISTS `notes_notesid_idx` (`notesid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_notes` FOREIGN KEY IF NOT EXISTS (`notesid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_notescf', null)
            ->createTable('notesid')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`notesid`)')
            ->createKey('CONSTRAINT `fk_notesid_vtiger_notescf` FOREIGN KEY IF NOT EXISTS (`notesid`) REFERENCES `vtiger_notes` (`notesid`) ON DELETE CASCADE');

        $this->getTable('vtiger_senotesrel', null)
            ->createTable('crmid', 'int(19) NOT NULL DEFAULT 0')
            ->createColumn('notesid', 'int(19) NOT NULL DEFAULT 0')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`crmid`,`notesid`)')
            ->createKey('KEY IF NOT EXISTS `senotesrel_notesid_idx` (`notesid`)')
            ->createKey('KEY IF NOT EXISTS `senotesrel_crmid_idx` (`crmid`)')
            ->createKey('CONSTRAINT `fk1_crmid` FOREIGN KEY IF NOT EXISTS (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_2_vtiger_senotesrel` FOREIGN KEY IF NOT EXISTS (`notesid`) REFERENCES `vtiger_notes` (`notesid`) ON DELETE CASCADE');
    }

    public function migrate()
    {
        $moduleName = $this->getModuleName();
        $fields = [
            'title' => 'notes_title',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $fields);
    }
}
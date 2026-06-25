<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Accounts_Install_Model extends Core_Install_Model
{
    public static array $TYPES = [
        'Customer',
        'Potential client',
        'Other',
    ];
    public array $blocksHeaderFields = [
        'account_no',
        'accounttype',
        'phone',
        'email1',
        'currency_id',
    ];
    public array $blocksListFields = [
        'accountname',
        'bill_city',
        'bill_country_id',
        'accounttype',
        'phone',
        'email1',
        'vat_id',
        'currency_id',
        'assigned_user_id',
    ];
    public array $blocksQuickCreateFields = [
        'accountname',
        'phone',
        'email1',
        'accounttype',
        'reg_no',
        'vat_id',
        'tax_id',
        'assigned_user_id',
        'bill_street',
        'bill_city',
        'bill_code',
        'bill_country_id',
        'description',
        'currency_id',
    ];
    public array $blocksSummaryFields = [
        'accountname',
        'phone',
        'email1',
        'accounttype',
        'account_id',
        'reg_no',
        'vat_id',
        'tax_id',
        'currency_id',
        'region_id',
        'assigned_user_id',
        'bill_street',
        'bill_code',
        'bill_city',
        'bill_country_id',
    ];
    public array $popupFields = ['account_no', 'accountname', 'phone', 'email1', 'reg_no', 'vat_id', 'tax_id', 'currency_id', 'assigned_user_id',];
    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [
        [
            'Accounts',
            'DETAILVIEW',
            'LBL_SHOW_ACCOUNT_HIERARCHY',
            'javascript:Accounts_Detail_Js.triggerAccountHierarchy("index.php?module=Accounts&view=AccountHierarchy&record=$RECORD$");',
            '<i class="fa-solid fa-sitemap"></i>',
        ],
    ];
    public array $registerRelatedLists = [
        ['Accounts', 'Contacts', 'Contacts', 'add', 'get_dependents_list', 'account_id',],
        ['Accounts', 'Potentials', 'Potentials', 'add', 'get_dependents_list', 'related_to',],
        ['Accounts', 'Quotes', 'Quotes', 'add', 'get_dependents_list', 'account_id',],
        ['Accounts', 'SalesOrder', 'SalesOrder', 'add', 'get_dependents_list', 'account_id',],
        ['Accounts', 'Invoice', 'Invoice', 'add', 'get_dependents_list', 'account_id',],
        ['Accounts', 'HelpDesk', 'HelpDesk', 'add', 'get_dependents_list', 'parent_id',],
        ['Accounts', 'Products', 'Products', 'select', 'get_related_list', '',],
        ['Accounts', 'Services', 'Services', 'select', 'get_related_list', '',],
        ['Accounts', 'ServiceContracts', 'Service Contracts', ['ADD'], 'get_dependents_list', 'account_id'],
        ['Accounts', 'Project', 'Projects', 'ADD,SELECT', 'get_dependents_list', 'account_id'],
        ['Accounts', 'Campaigns', 'Campaigns', 'select', 'get_related_list',],
        ['Accounts', 'Assets', 'Assets', 'add', 'get_dependents_list', 'account'],
        self::DOCUMENTS_RELATED_LIST,
        self::EMAILS_RELATED_LIST,
        self::APPOINTMENTS_RELATED_LIST,
        ['Accounts', 'SalesOrder', 'Sales Order', '', 'delete_related_list', '',],
        ['Accounts', 'HelpDesk', 'Documents', '', 'delete_related_list', '',],
    ];
    public array $registerWorkflowTasks = [
        [
            'Send Email to user when Notifyowner is True',
            'Accounts',
            '2',
            '2',
            [
                [
                    'fieldname' => 'notify_owner',
                    'operation' => 'is',
                    'value' => '1',
                    'valuetype' => 'rawtext',
                    'joincondition' => '',
                    'groupjoin' => 'and',
                    'groupid' => '0',
                ],
            ],
            [
                [
                    'modules/com_vtiger_workflow/tasks/VTEmailTask.inc',
                    'An account has been created ',
                    'VTEmailTask',
                    [
                        'content' => 'An Account has been assigned to you on Defalto CRM<br>Details of account are :<br><br>AccountId:<b>$account_no</b><br>AccountName:<b>$accountname</b><br>Rating:<b>$rating</b><br>Industry:<b>$industry</b><br>AccountType:<b>$accounttype</b><br>Description:<b>$description</b><br><br><br>Thank You<br>Admin',
                        'subject' => 'Regarding Account Creation',
                        'recepient' => '$(assigned_user_id : (Users) email1)',
                        'methodName' => 'NotifyOwner',
                    ],
                ],
            ],
        ],
    ];
    public array $relatedListFields = [['accountname', 'account_no', 'accounttype', 'phone', 'email1', 'currency_id',]];

    /**
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateComments();
        $this->updateRelatedList();
        $this->updateCustomLinks();
        $this->updateHistory();
        $this->updateWorkflowTasks();
    }

    /**
     * @throws Exception
     */
    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
        $this->updateCustomLinks(false);
        $this->updateComments(false);
        $this->updateHistory(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_ACCOUNT_INFORMATION' => [
                'accountname' => [
                    'uitype' => 2,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~M',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'entity_identifier' => 1,
                    'isunique' => 1,
                ],
                'website' => [
                    'uitype' => 17,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Website',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 0,
                ],
                'phone' => [
                    'uitype' => 11,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'otherphone' => [
                    'uitype' => 11,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Other Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'email1' => [
                    'uitype' => 13,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'E~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'email2' => [
                    'uitype' => 13,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Other Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'E~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'accounttype' => [
                    'uitype' => 15,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'picklist_values' => self::$TYPES,
                ],
                'account_id' => [
                    'uitype' => 51,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Member Of',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                ],
                'reg_no' => [
                    'uitype' => 1,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Company Reg. No.',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 2,
                ],
                'vat_id' => [
                    'uitype' => 1,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'VAT Number',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 2,
                ],
                'tax_id' => [
                    'uitype' => 1,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'VAT Nr',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 2,
                ],
                'currency_id' => [
                    'table' => 'vtiger_account',
                    'label' => 'Currency',
                    'uitype' => 117,
                    'typeofdata' => 'I~O',
                ],
                'region_id' => [
                    'label' => 'Tax Region',
                    'uitype' => 29,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'columntype' => 'int(19)',
                    'readonly' => 0,
                    'presence' => 2,
                ],
                'annual_revenue' => [
                    'uitype' => 71,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Annual Revenue',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'employees' => [
                    'uitype' => 7,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Employees',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'notify_owner' => [
                    'uitype' => 56,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Notify Owner',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => '10',
                    'typeofdata' => 'C~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'emailoptout' => [
                    'uitype' => 56,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Email Opt Out',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~M',
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'pricebookid' => [
                    'uitype' => 10,
                    'table' => 'vtiger_account',
                    'label' => 'Price Book',
                    'readonly' => 0,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'PriceBooks',
                    ],
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [],
            'LBL_ADDRESS_INFORMATION' => [
                'bill_street' => [
                    'uitype' => 21,
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'ship_street' => [
                    'uitype' => 21,
                    'table' => 'vtiger_accountshipads',
                    'generatedtype' => 1,
                    'label' => 'Shipping Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_code' => [
                    'uitype' => 1,
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing Code',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'ship_code' => [
                    'uitype' => 1,
                    'table' => 'vtiger_accountshipads',
                    'generatedtype' => 1,
                    'label' => 'Shipping Code',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_city' => [
                    'uitype' => 1,
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing City',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'ship_city' => [
                    'uitype' => 1,
                    'table' => 'vtiger_accountshipads',
                    'generatedtype' => 1,
                    'label' => 'Shipping City',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_state' => [
                    'uitype' => 1,
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing State',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_state' => [
                    'uitype' => 1,
                    'table' => 'vtiger_accountshipads',
                    'generatedtype' => 1,
                    'label' => 'Shipping State',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_country_id' => [
                    'uitype' => 18,
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'ship_country_id' => [
                    'uitype' => 18,
                    'table' => 'vtiger_accountshipads',
                    'generatedtype' => 1,
                    'label' => 'Shipping Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => '19',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Description',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'account_no' => [
                    'uitype' => 4,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Account No',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'isconvertedfromlead' => [
                    'uitype' => 56,
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Is Converted From Lead',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'campaignrelstatus' => [
                    'uitype' => 16,
                    'table' => 'vtiger_campaignrelstatus',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'picklist_values' => Campaigns_Install_Model::$CAMPAIGN_STATUS,
                ],
            ],
        ];
    }

    public function getTables(): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_account', null)
            ->createTable('accountid')
            ->renameColumn('account_type', 'accounttype')
            ->renameColumn('annualrevenue', 'annual_revenue')
            ->renameColumn('parentid', 'account_id')
            ->createColumn('account_no', 'varchar(100) NOT NULL')
            ->createColumn('accountname', 'varchar(100) NOT NULL')
            ->createColumn('account_id', 'int(19) DEFAULT 0')
            ->createColumn('accounttype', 'varchar(200) DEFAULT NULL')
            ->createColumn('annual_revenue', self::$COLUMN_DECIMAL)
            ->createColumn('phone', 'varchar(30) DEFAULT NULL')
            ->createColumn('otherphone', 'varchar(30) DEFAULT NULL')
            ->createColumn('email1', 'varchar(100) DEFAULT NULL')
            ->createColumn('email2', 'varchar(100) DEFAULT NULL')
            ->createColumn('website', 'varchar(100) DEFAULT NULL')
            ->createColumn('fax', 'varchar(30) DEFAULT NULL')
            ->createColumn('employees', 'int(10) DEFAULT 0')
            ->createColumn('emailoptout', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('notify_owner', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('isconvertedfromlead', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('reg_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('vat_id', 'varchar(100) DEFAULT NULL')
            ->createColumn('tax_id', 'varchar(100) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`accountid`)')
            ->createKey('KEY IF NOT EXISTS `account_account_type_idx` (`accounttype`)')
            ->createKey('KEY IF NOT EXISTS `email_idx` (`email1`,`email2`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_account` FOREIGN KEY IF NOT EXISTS (`accountid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ->createKey('INDEX IF NOT EXISTS email_idx (email1, email2)');

        $this->getTable('vtiger_accountscf', null)
            ->createTable('accountid');

        $this->getTable('vtiger_accountshipads', null)
            ->createTable('accountaddressid')
            ->createColumn('ship_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('ship_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_street', 'varchar(250) DEFAULT NULL');

        $this->getTable('vtiger_accountbillads', null)
            ->createTable('accountaddressid')
            ->createColumn('bill_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('bill_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_street', 'varchar(250) DEFAULT NULL');

        $this->createPicklistTable('vtiger_accounttype', 'accounttypeid', 'accounttype');
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $moduleName = $this->getModuleName();
        $fields = [
            'account_type' => 'accounttype',
            'annualrevenue' => 'annual_revenue',
            'parentid' => 'account_id',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $fields);
        Core_Install_Model::logSuccess('Update column names');
    }
}

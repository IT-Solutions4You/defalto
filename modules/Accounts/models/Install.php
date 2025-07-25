<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Accounts_Install_Model extends Core_Install_Model {

    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [
        ['Accounts', 'DETAILVIEW', 'LBL_SHOW_ACCOUNT_HIERARCHY', 'javascript:Accounts_Detail_Js.triggerAccountHierarchy("index.php?module=Accounts&view=AccountHierarchy&record=$RECORD$");']
    ];

    public static array $TYPES = [
        'Customer',
        'Potential client',
        'Other',
    ];

    public function addCustomLinks(): void
    {
        $this->updateCustomLinks();
        $this->updateComments();
        $this->updateHistory();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);
        $this->updateComments(false);
        $this->updateHistory(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_ACCOUNT_INFORMATION' => [
                'accountname' => [
                    'name' => 'accountname',
                    'uitype' => 2,
                    'column' => 'accountname',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'quicksequence' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                    'isunique' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
                'website' => [
                    'name' => 'website',
                    'uitype' => 17,
                    'column' => 'website',
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
                    'name' => 'phone',
                    'uitype' => 11,
                    'column' => 'phone',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                    'headerfieldsequence' => 3,
                    'filter' => 1,
                    'filter_sequence' => 5,
                ],
                'otherphone' => [
                    'name' => 'otherphone',
                    'uitype' => 11,
                    'column' => 'otherphone',
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
                    'name' => 'email1',
                    'uitype' => 13,
                    'column' => 'email1',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'E~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'quickcreate' => 2,
                    'quicksequence' => 3,
                    'headerfield' => 1,
                    'headerfieldsequence' => 4,
                    'filter' => 1,
                    'filter_sequence' => 6,
                ],
                'email2' => [
                    'name' => 'email2',
                    'uitype' => 13,
                    'column' => 'email2',
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
                    'name' => 'accounttype',
                    'uitype' => 15,
                    'column' => 'accounttype',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 4,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => self::$TYPES,
                    'headerfield' => 1,
                    'headerfieldsequence' => 2,
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'account_id' => [
                    'name' => 'account_id',
                    'uitype' => 51,
                    'column' => 'account_id',
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
                    'summaryfield' => 1,
                ],
                'vat_id' => [
                    'name' => 'vat_id',
                    'uitype' => 1,
                    'column' => 'vat_id',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'VAT Number',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 5,
                    'displaytype' => 1,
                    'masseditable' => 2,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
                ],
                'reg_no' => [
                    'name' => 'reg_no',
                    'uitype' => 1,
                    'column' => 'reg_no',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Company Reg. No.',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 6,
                    'displaytype' => 1,
                    'masseditable' => 2,
                    'summaryfield' => 1,
                ],
                'currency_id' => [
                    'name' => 'currency_id',
                    'column' => 'currency_id',
                    'table' => 'vtiger_account',
                    'label' => 'Currency',
                    'uitype' => 117,
                    'typeofdata' => 'I~O',
                    'headerfield' => 1,
                    'headerfieldsequence' => 5,
                    'summaryfield' => 1,
                    'quickcreate' => 2,
                    'quicksequence' => 7,
                    'filter' => 1,
                    'filter_sequence' => 8,
                ],
                'region_id' => [
                    'column' => 'region_id',
                    'label' => 'Tax Region',
                    'uitype' => 29,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'columntype' => 'int(19)',
                    'readonly' => 0,
                    'presence' => 2,
                    'summaryfield' => 1,
                ],
                'annual_revenue' => [
                    'name' => 'annual_revenue',
                    'uitype' => 71,
                    'column' => 'annual_revenue',
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
                    'name' => 'employees',
                    'uitype' => 7,
                    'column' => 'employees',
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
                    'name' => 'notify_owner',
                    'uitype' => 56,
                    'column' => 'notify_owner',
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
                    'name' => 'emailoptout',
                    'uitype' => 56,
                    'column' => 'emailoptout',
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
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'quicksequence' => 8,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 9,
                ],
                'pricebookid' => [
                    'name' => 'pricebookid',
                    'uitype' => 10,
                    'column' => 'pricebookid',
                    'table' => 'vtiger_account',
                    'label' => 'Price Book',
                    'readonly' => 0,
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
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
                    'name' => 'bill_street',
                    'uitype' => 21,
                    'column' => 'bill_street',
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 9,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'ship_street' => [
                    'name' => 'ship_street',
                    'uitype' => 21,
                    'column' => 'ship_street',
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
                    'name' => 'bill_code',
                    'uitype' => 1,
                    'column' => 'bill_code',
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing Code',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 11,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'ship_code' => [
                    'name' => 'ship_code',
                    'uitype' => 1,
                    'column' => 'ship_code',
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
                    'name' => 'bill_city',
                    'uitype' => 1,
                    'column' => 'bill_city',
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing City',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 10,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'ship_city' => [
                    'name' => 'ship_city',
                    'uitype' => 1,
                    'column' => 'ship_city',
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
                    'name' => 'bill_state',
                    'uitype' => 1,
                    'column' => 'bill_state',
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
                    'name' => 'ship_state',
                    'uitype' => 1,
                    'column' => 'ship_state',
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
                    'name' => 'bill_country_id',
                    'uitype' => 18,
                    'column' => 'bill_country_id',
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 12,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'ship_country_id' => [
                    'name' => 'ship_country_id',
                    'uitype' => 18,
                    'column' => 'ship_country_id',
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
                    'name' => 'description',
                    'uitype' => '19',
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Description',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 13,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'account_no' => [
                    'name' => 'account_no',
                    'uitype' => 4,
                    'column' => 'account_no',
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
                    'headerfield' => 1,
                    'headerfieldsequence' => 1,
                ],
                'isconvertedfromlead' => [
                    'name' => 'isconvertedfromlead',
                    'uitype' => 56,
                    'column' => 'isconvertedfromlead',
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
                    'name' => 'campaignrelstatus',
                    'uitype' => 16,
                    'column' => 'campaignrelstatus',
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
     * @throws AppException
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
            ->createColumn('currency_id', 'int(19) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`accountid`)')
            ->createKey('KEY IF NOT EXISTS `account_account_type_idx` (`accounttype`)')
            ->createKey('KEY IF NOT EXISTS `email_idx` (`email1`,`email2`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_account` FOREIGN KEY IF NOT EXISTS (`accountid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ->createKey('INDEX IF NOT EXISTS email_idx (email1, email2)')
        ;

        $this->getTable('vtiger_accountscf', null)
            ->createTable('accountid');

        $this->getTable('vtiger_accountshipads', null)
            ->createTable('accountaddressid')
            ->createColumn('ship_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('ship_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_street', 'varchar(250) DEFAULT NULL')
        ;

        $this->getTable('vtiger_accountbillads', null)
            ->createTable('accountaddressid')
            ->createColumn('bill_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('bill_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_street', 'varchar(250) DEFAULT NULL')
            ;

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
    }
}
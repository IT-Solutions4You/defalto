<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Accounts_Install_Model extends Vtiger_Install_Model {

    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
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
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                ],
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
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
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
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
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
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'tickersymbol' => [
                    'name' => 'tickersymbol',
                    'uitype' => 1,
                    'column' => 'tickersymbol',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Ticker Symbol',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
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
                'account_id' => [
                    'name' => 'account_id',
                    'uitype' => 51,
                    'column' => 'parentid',
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
                'ownership' => [
                    'name' => 'ownership',
                    'uitype' => 1,
                    'column' => 'ownership',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Ownership',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'rating' => [
                    'name' => 'rating',
                    'uitype' => 15,
                    'column' => 'rating',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Rating',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' =>
                        [
                            'Acquired',
                            'Active',
                            'Market Failed',
                            'Project Cancelled',
                            'Shutdown',
                        ],
                ],
                'industry' =>
                    [
                        'name' => 'industry',
                        'uitype' => 15,
                        'column' => 'industry',
                        'table' => 'vtiger_account',
                        'generatedtype' => 1,
                        'label' => 'industry',
                        'readonly' => 1,
                        'presence' => 2,
                        'maximumlength' => 100,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                        'picklist_values' =>
                            [
                                'Apparel',
                                'Banking',
                                'Biotechnology',
                                'Chemicals',
                                'Communications',
                                'Construction',
                                'Consulting',
                                'Education',
                                'Electronics',
                                'Energy',
                                'Engineering',
                                'Entertainment',
                                'Environmental',
                                'Finance',
                                'Food & Beverage',
                                'Government',
                                'Healthcare',
                                'Hospitality',
                                'Insurance',
                                'Machinery',
                                'Manufacturing',
                                'Media',
                                'Not For Profit',
                                'Recreation',
                                'Retail',
                                'Shipping',
                                'Technology',
                                'Telecommunications',
                                'Transportation',
                                'Utilities',
                                'Other',
                            ],
                    ],
                'siccode' => [
                    'name' => 'siccode',
                    'uitype' => 1,
                    'column' => 'siccode',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'SIC Code',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'accounttype' => [
                    'name' => 'accounttype',
                    'uitype' => 15,
                    'column' => 'account_type',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' =>
                        [
                            'Analyst',
                            'Competitor',
                            'Customer',
                            'Integrator',
                            'Investor',
                            'Partner',
                            'Press',
                            'Prospect',
                            'Reseller',
                            'Other',
                        ],
                ],
                'annual_revenue' => [
                    'name' => 'annual_revenue',
                    'uitype' => 71,
                    'column' => 'annualrevenue',
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
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'createdtime' => [
                    'name' => 'createdtime',
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Created Time',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'modifiedtime' => [
                    'name' => 'modifiedtime',
                    'uitype' => 70,
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Modified Time',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'modifiedby' => [
                    'name' => 'modifiedby',
                    'uitype' => 52,
                    'column' => 'modifiedby',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Last Modified By',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 0,
                    'summaryfield' => 0,
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
                'source' => [
                    'name' => 'source',
                    'uitype' => 1,
                    'column' => 'source',
                    'table' => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label' => 'Source',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
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
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'vat_id' => [
                    'name' => 'vat_id',
                    'uitype' => 1,
                    'column' => 'vat_id',
                    'table' => 'vtiger_account',
                    'generatedtype' => 1,
                    'label' => 'VAT ID',
                    'readonly' => 1,
                    'presence' => 0,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
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
                    'label' => 'Billing Address',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_street' => [
                    'name' => 'ship_street',
                    'uitype' => 21,
                    'column' => 'ship_street',
                    'table' => 'vtiger_accountshipads',
                    'generatedtype' => 1,
                    'label' => 'Shipping Address',
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
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
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
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
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
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
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
                'bill_pobox' => [
                    'name' => 'bill_pobox',
                    'uitype' => 1,
                    'column' => 'bill_pobox',
                    'table' => 'vtiger_accountbillads',
                    'generatedtype' => 1,
                    'label' => 'Billing Po Box',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 100,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_pobox' => [
                    'name' => 'ship_pobox',
                    'uitype' => 1,
                    'column' => 'ship_pobox',
                    'table' => 'vtiger_accountshipads',
                    'generatedtype' => 1,
                    'label' => 'Shipping Po Box',
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
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
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
            ->createColumn('account_no', 'varchar(100) NOT NULL')
            ->createColumn('accountname', 'varchar(100) NOT NULL')
            ->createColumn('parentid', 'int(19) DEFAULT 0')
            ->createColumn('account_type', 'varchar(200) DEFAULT NULL')
            ->createColumn('industry', 'varchar(200) DEFAULT NULL')
            ->createColumn('annualrevenue', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('rating', 'varchar(200) DEFAULT NULL')
            ->createColumn('ownership', 'varchar(50) DEFAULT NULL')
            ->createColumn('siccode', 'varchar(50) DEFAULT NULL')
            ->createColumn('tickersymbol', 'varchar(30) DEFAULT NULL')
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
            ->createKey('PRIMARY KEY IF NOT EXISTS (`accountid`)')
            ->createKey('KEY IF NOT EXISTS `account_account_type_idx` (`account_type`)')
            ->createKey('KEY IF NOT EXISTS `email_idx` (`email1`,`email2`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_account` FOREIGN KEY IF NOT EXISTS (`accountid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_accountscf', null)
            ->createTable('accountid');

        $this->getTable('vtiger_accountshipads', null)
            ->createTable('accountaddressid')
            ->createColumn('ship_city', 'varchar(30) DEFAULT NULL')
            ->createColumn('ship_code', 'varchar(30) DEFAULT NULL')
            ->createColumn('ship_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('ship_state', 'varchar(30) DEFAULT NULL')
            ->createColumn('ship_pobox', 'varchar(30) DEFAULT NULL')
            ->createColumn('ship_street', 'varchar(250) DEFAULT NULL')
        ;

        $this->getTable('vtiger_accountbillads', null)
            ->createTable('accountaddressid')
            ->createColumn('bill_city', 'varchar(30) DEFAULT NULL')
            ->createColumn('bill_code', 'varchar(30) DEFAULT NULL')
            ->createColumn('bill_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('bill_state', 'varchar(30) DEFAULT NULL')
            ->createColumn('bill_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('bill_pobox', 'varchar(30) DEFAULT NULL')
            ;
    }
}
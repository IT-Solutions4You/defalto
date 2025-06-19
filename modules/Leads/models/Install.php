<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Leads_Install_Model extends Core_Install_Model {

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        // TODO: Implement addCustomLinks() method.
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        // TODO: Implement deleteCustomLinks() method.
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_LEAD_INFORMATION' => [
                'salutationtype' => [
                    'name' => 'salutationtype',
                    'uitype' => 55,
                    'column' => 'salutation',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Salutation',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Mr.',
                        'Ms.',
                        'Mrs.',
                        'Dr.',
                        'Prof.',
                    ],
                ],
                'firstname' => [
                    'name' => 'firstname',
                    'uitype' => 55,
                    'column' => 'firstname',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'First Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'phone' => [
                    'name' => 'phone',
                    'uitype' => 11,
                    'column' => 'phone',
                    'table' => 'vtiger_leadaddress',
                    'label' => 'Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'lastname' => [
                    'name' => 'lastname',
                    'uitype' => 255,
                    'column' => 'lastname',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Last Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'mobile' => [
                    'name' => 'mobile',
                    'uitype' => 11,
                    'column' => 'mobile',
                    'table' => 'vtiger_leadaddress',
                    'label' => 'Mobile',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'company' => [
                    'name' => 'company',
                    'uitype' => 2,
                    'column' => 'company',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Company',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'designation' => [
                    'name' => 'designation',
                    'uitype' => 1,
                    'column' => 'designation',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Designation',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'email' => [
                    'name' => 'email',
                    'uitype' => 13,
                    'column' => 'email',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'E~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'leadsource' => [
                    'name' => 'leadsource',
                    'uitype' => 15,
                    'column' => 'leadsource',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Lead Source',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'Cold Call',
                        'Existing Customer',
                        'Self Generated',
                        'Employee',
                        'Partner',
                        'Public Relations',
                        'Direct Mail',
                        'Conference',
                        'Trade Show',
                        'Web Site',
                        'Word of mouth',
                        'Other',
                    ],
                ],
                'website' => [
                    'name' => 'website',
                    'uitype' => 17,
                    'column' => 'website',
                    'table' => 'vtiger_leadsubdetails',
                    'label' => 'Website',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'industry' => [
                    'name' => 'industry',
                    'uitype' => 15,
                    'column' => 'industry',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Industry',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
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
                'leadstatus' => [
                        'name' => 'leadstatus',
                        'uitype' => 15,
                        'column' => 'leadstatus',
                        'table' => 'vtiger_leaddetails',
                        'label' => 'Lead Status',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                        'picklist_values' => [
                            'Attempted to Contact',
                            'Cold',
                            'Contact in Future',
                            'Contacted',
                            'Hot',
                            'Junk Lead',
                            'Lost Lead',
                            'Not Contacted',
                            'Pre Qualified',
                            'Qualified',
                            'Warm',
                        ],
                    ],
                'annualrevenue' => [
                        'name' => 'annualrevenue',
                        'uitype' => 71,
                        'column' => 'annualrevenue',
                        'table' => 'vtiger_leaddetails',
                        'label' => 'Annual Revenue',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'N~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                'rating' => [
                    'name' => 'rating',
                    'uitype' => 15,
                    'column' => 'rating',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Rating',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Acquired',
                        'Active',
                        'Market Failed',
                        'Project Cancelled',
                        'Shutdown',
                    ],
                ],
                'noofemployees' => [
                    'name' => 'noofemployees',
                    'uitype' => 1,
                    'column' => 'noofemployees',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'No Of Employees',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
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
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'secondaryemail' => [
                    'name' => 'secondaryemail',
                    'uitype' => 13,
                    'column' => 'secondaryemail',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Secondary Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'E~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'emailoptout' => [
                    'name' => 'emailoptout',
                    'uitype' => 56,
                    'column' => 'emailoptout',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Email Opt Out',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_ADDRESS_INFORMATION' =>
                [
                    'lane' => [
                        'name' => 'lane',
                        'uitype' => 21,
                        'column' => 'lane',
                        'table' => 'vtiger_leadaddress',
                        'label' => 'Street',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'code' => [
                        'name' => 'code',
                        'uitype' => 1,
                        'column' => 'code',
                        'table' => 'vtiger_leadaddress',
                        'label' => 'Postal Code',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'city' => [
                        'name' => 'city',
                        'uitype' => 1,
                        'column' => 'city',
                        'table' => 'vtiger_leadaddress',
                        'label' => 'City',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 1,
                    ],
                    'country_id' => [
                        'name' => 'country_id',
                        'uitype' => 18,
                        'column' => 'country_id',
                        'table' => 'vtiger_leadaddress',
                        'label' => 'Country',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 1,
                    ],
                    'state' => [
                        'name' => 'state',
                        'uitype' => 1,
                        'column' => 'state',
                        'table' => 'vtiger_leadaddress',
                        'label' => 'State',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 1,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'pobox' => [
                        'name' => 'pobox',
                        'uitype' => 1,
                        'column' => 'pobox',
                        'table' => 'vtiger_leadaddress',
                        'label' => 'Po Box',
                        'readonly' => 1,
                        'presence' => 2,
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
                    'uitype' => 19,
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
            'LBL_SYSTEM_INFORMATION' => [
                'lead_no' => [
                    'name' => 'lead_no',
                    'uitype' => 4,
                    'column' => 'lead_no',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Lead No',
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
        $this->getTable('vtiger_leadsubdetails', null)
            ->createTable('leadsubscriptionid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('website', 'varchar(255) DEFAULT NULL')
            ->createColumn('callornot', 'int(1) DEFAULT \'0\'')
            ->createColumn('readornot', 'int(1) DEFAULT \'0\'')
            ->createColumn('empct', 'int(10) DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`leadsubscriptionid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_leadsubdetails` FOREIGN KEY IF NOT EXISTS (`leadsubscriptionid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE');

        $this->getTable('vtiger_leaddetails', null)
            ->createTable('leadid', 'int(19) NOT NULL')
            ->createColumn('lead_no', 'varchar(100) NOT NULL')
            ->createColumn('email', 'varchar(100) DEFAULT NULL')
            ->createColumn('interest', 'varchar(50) DEFAULT NULL')
            ->createColumn('firstname', 'varchar(40) DEFAULT NULL')
            ->createColumn('salutation', 'varchar(200) DEFAULT NULL')
            ->createColumn('lastname', 'varchar(80) NOT NULL')
            ->createColumn('company', 'varchar(100) NOT NULL')
            ->createColumn('annualrevenue', self::$COLUMN_DECIMAL)
            ->createColumn('industry', 'varchar(200) DEFAULT NULL')
            ->createColumn('campaign', 'varchar(30) DEFAULT NULL')
            ->createColumn('rating', 'varchar(200) DEFAULT NULL')
            ->createColumn('leadstatus', 'varchar(200) DEFAULT NULL')
            ->createColumn('leadsource', 'varchar(200) DEFAULT NULL')
            ->createColumn('converted', 'int(1) DEFAULT 0')
            ->createColumn('designation', 'varchar(50) DEFAULT \'SalesMan\'')
            ->createColumn('licencekeystatus', 'varchar(50) DEFAULT NULL')
            ->createColumn('space', 'varchar(250) DEFAULT NULL')
            ->createColumn('comments', 'text DEFAULT NULL')
            ->createColumn('priority', 'varchar(50) DEFAULT NULL')
            ->createColumn('demorequest', 'varchar(50) DEFAULT NULL')
            ->createColumn('partnercontact', 'varchar(50) DEFAULT NULL')
            ->createColumn('productversion', 'varchar(20) DEFAULT NULL')
            ->createColumn('product', 'varchar(50) DEFAULT NULL')
            ->createColumn('maildate', 'date DEFAULT NULL')
            ->createColumn('nextstepdate', 'date DEFAULT NULL')
            ->createColumn('fundingsituation', 'varchar(50) DEFAULT NULL')
            ->createColumn('purpose', 'varchar(50) DEFAULT NULL')
            ->createColumn('evaluationstatus', 'varchar(50) DEFAULT NULL')
            ->createColumn('transferdate', 'date DEFAULT NULL')
            ->createColumn('revenuetype', 'varchar(50) DEFAULT NULL')
            ->createColumn('noofemployees', 'int(50) DEFAULT NULL')
            ->createColumn('secondaryemail', 'varchar(100) DEFAULT NULL')
            ->createColumn('assignleadchk', 'int(1) DEFAULT 0')
            ->createColumn('emailoptout', 'varchar(3) DEFAULT NULL')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`leadid`)')
            ->createKey('KEY IF NOT EXISTS `leaddetails_converted_leadstatus_idx` (`converted`,`leadstatus`)')
            ->createKey('KEY IF NOT EXISTS `email_idx` (`email`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_leaddetails` FOREIGN KEY IF NOT EXISTS (`leadid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ->createKey('INDEX IF NOT EXISTS email_idx (email)');

        $this->getTable('vtiger_leadaddress', null)
            ->createColumn('leadaddressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('city', 'varchar(150) DEFAULT NULL')
            ->createColumn('code', 'varchar(150) DEFAULT NULL')
            ->createColumn('state', 'varchar(150) DEFAULT NULL')
            ->createColumn('pobox', 'varchar(30) DEFAULT NULL')
            ->createColumn('phone', 'varchar(50) DEFAULT NULL')
            ->createColumn('mobile', 'varchar(50) DEFAULT NULL')
            ->createColumn('fax', 'varchar(50) DEFAULT NULL')
            ->createColumn('lane', 'varchar(250) DEFAULT NULL')
            ->createColumn('leadaddresstype', 'varchar(30) DEFAULT \'Billing\'')
            ->createColumn('country_id', 'varchar(2) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`leadaddressid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_leadaddress` FOREIGN KEY IF NOT EXISTS (`leadaddressid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE')
        ;
        
        $this->createPicklistTable('vtiger_leadstatus', 'leadstatusid', 'leadstatus');
    }
}
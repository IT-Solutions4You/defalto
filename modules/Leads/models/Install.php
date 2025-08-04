<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Leads_Install_Model extends Core_Install_Model {

    /**
     * @return void
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateHistory();
        $this->updateComments();
        self::addDefaultLeadMapping();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        // TODO: Implement deleteCustomLinks() method.
    }

    /**
     * @throws Exception
     */
    public static function addDefaultLeadMapping(): void
    {
        $fieldMap = [
            ['company', 'accountname', null, 'potentialname', 0],
            ['phone', 'phone', 'phone', null, 1],
            ['fax', 'fax', 'fax', null, 1],
            ['email', 'email1', 'email', null, 0],
            ['city', 'bill_city', 'mailingcity', null, 1],
            ['code', 'bill_code', 'mailingcode', null, 1],
            ['country_id', 'bill_country_id', 'mailingcountry_id', null, 1],
            ['state', 'bill_state', 'mailingstate', null, 1],
            ['lane', 'bill_street', 'mailingstreet', null, 1],
            ['pobox', 'bill_pobox', 'mailingpobox', null, 1],
            ['city', 'ship_city', null, null, 1],
            ['code', 'ship_code', null, null, 1],
            ['country_id', 'ship_country_id', null, null, 1],
            ['state', 'ship_state', null, null, 1],
            ['lane', 'ship_street', null, null, 1],
            ['pobox', 'ship_pobox', null, null, 1],
            ['description', 'description', 'description', 'description', 1],
            ['salutationtype', null, 'salutationtype', null, 1],
            ['firstname', null, 'firstname', null, 0],
            ['lastname', null, 'lastname', null, 0],
            ['mobile', null, 'mobile', null, 1],
            ['title', null, 'title', null, 1],
            ['secondaryemail', null, 'secondaryemail', null, 1],
            ['leadsource', null, 'leadsource', 'leadsource', 1],
            ['leadstatus', null, null, null, 1],
            ['noofemployees', 'employees', null, null, 1],
            ['annualrevenue', 'annual_revenue', null, null, 1],
        ];
        $leadTab = getTabid('Leads');
        $accountTab = getTabid('Accounts');
        $contactTab = getTabid('Contacts');
        $potentialTab = getTabid('Potentials');
        $table = (new self())->getTable('vtiger_convertleadmapping', 'leadfid');
        $table->deleteData(['leadfid' => 0]);

        foreach ($fieldMap as $values) {
            $leadFieldId = getFieldid($leadTab, $values[0]);
            $accountFieldId = getFieldid($accountTab, $values[1]);
            $contactFieldId = getFieldid($contactTab, $values[2]);
            $potentialFieldId = getFieldid($potentialTab, $values[3]);
            $editable = $values[4];

            $data = $table->selectData(['leadfid as id'], ['leadfid' => $leadFieldId]);

            if (empty($data['id']) && !empty($leadFieldId)) {
                $table->insertData([
                    'leadfid' => $leadFieldId,
                    'accountfid' => $accountFieldId,
                    'contactfid' => $contactFieldId,
                    'potentialfid' => $potentialFieldId,
                    'editable' => $editable,
                ]);
            }
        }
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
                    'column' => 'salutationtype',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Salutation',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                    'picklist_values' => [
                        'Mr.',
                        'Ms.',
                        'Mrs.',
                    ],
                ],
                'title' => [
                    'name' => 'title',
                    'uitype' => 1,
                    'column' => 'title',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Title',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'firstname' => [
                    'name' => 'firstname',
                    'uitype' => 1,
                    'column' => 'firstname',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'First Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
	                'filter' => 1,
	                'filter_sequence' => 2,
                    'entity_identifier' => 1,
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
                    'quickcreate' => 2,
                    'quicksequence' => 4,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 3,
                    'entity_identifier' => 1,
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
                    'quicksequence' => 6,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
	                'headerfield' => 1,
	                'headerfieldsequence' => 2,
	                'filter' => 1,
	                'filter_sequence' => 5,
                ],
                'mobile' => [
                    'name' => 'mobile',
                    'uitype' => 11,
                    'column' => 'mobile',
                    'table' => 'vtiger_leadaddress',
                    'label' => 'Private Phone',
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
                    'quicksequence' => 8,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 3,
                    'filter' => 1,
                    'filter_sequence' => 7,
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
                'leadstatus' => [
                    'name' => 'leadstatus',
                    'uitype' => 15,
                    'column' => 'leadstatus',
                    'table' => 'vtiger_leaddetails',
                    'label' => 'Lead Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'quicksequence' => 5,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 1,
                    'filter' => 1,
                    'filter_sequence' => 4,
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
                    'quicksequence' => 7,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 5,
                    'filter' => 1,
                    'filter_sequence' => 6,
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
                    'quickcreate' => 2,
                    'quicksequence' => 9,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 4,
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
                    'filter' => 1,
                    'filter_sequence' => 8,
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
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'quicksequence' => 10,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 9,
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
                        'quickcreate' => 2,
                        'quicksequence' => 11,
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
                        'quickcreate' => 2,
                        'quicksequence' => 12,
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
                        'quickcreate' => 2,
                        'quicksequence' => 13,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
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
                    'country_id' => [
                        'name' => 'country_id',
                        'uitype' => 18,
                        'column' => 'country_id',
                        'table' => 'vtiger_leadaddress',
                        'label' => 'Country',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 2,
                        'quicksequence' => 14,
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
                    'quickcreate' => 2,
                    'quicksequence' => 15,
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
                'campaignrelstatus' => [
                    'name' => 'campaignrelstatus',
                    'uitype' => 16,
                    'column' => 'campaignrelstatus',
                    'table' => 'vtiger_campaignrelstatus',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'picklist_values' => Campaigns_Install_Model::$CAMPAIGN_STATUS,
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
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_leadsubdetails', null)
            ->createTable('leadsubscriptionid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('callornot', 'int(1) DEFAULT \'0\'')
            ->createColumn('readornot', 'int(1) DEFAULT \'0\'')
            ->createColumn('empct', 'int(10) DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`leadsubscriptionid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_leadsubdetails` FOREIGN KEY IF NOT EXISTS (`leadsubscriptionid`) REFERENCES `vtiger_leaddetails` (`leadid`) ON DELETE CASCADE');

        $this->getTable('vtiger_leaddetails', null)
            ->createTable('leadid', 'int(19) NOT NULL')
            ->renameColumn('salutation', 'salutationtype')
            ->createColumn('lead_no', 'varchar(100) NOT NULL')
            ->createColumn('email', 'varchar(100) DEFAULT NULL')
            ->createColumn('interest', 'varchar(50) DEFAULT NULL')
            ->createColumn('firstname', 'varchar(40) DEFAULT NULL')
            ->createColumn('salutationtype', 'varchar(200) DEFAULT NULL')
            ->createColumn('lastname', 'varchar(80) NOT NULL')
            ->createColumn('company', 'varchar(100) NOT NULL')
            ->createColumn('campaign', 'varchar(30) DEFAULT NULL')
            ->createColumn('leadstatus', 'varchar(200) DEFAULT NULL')
            ->createColumn('leadsource', 'varchar(200) DEFAULT NULL')
            ->createColumn('converted', 'int(1) DEFAULT 0')
            ->createColumn('title', 'varchar(50) DEFAULT NULL')
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

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $moduleName = 'Leads';
        $fields = [
            'salutation' => 'salutationtype',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $fields);
    }
}
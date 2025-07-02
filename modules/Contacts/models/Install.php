<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Contacts_Install_Model extends Core_Install_Model
{

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_CONTACT_INFORMATION' => [
                'salutationtype' => [
                    'name' => 'salutationtype',
                    'uitype' => 55,
                    'column' => 'salutation',
                    'table' => 'vtiger_contactdetails',
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
                    'table' => 'vtiger_contactdetails',
                    'label' => 'First Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [],
	                'filter' => 1,
	                'filter_sequence' => 1,
	                'entity_identifier' => 1,
                ],
                'phone' => [
                    'name' => 'phone',
                    'uitype' => 11,
                    'column' => 'phone',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
	                'headerfield' => 1,
	                'filter' => 1,
	                'filter_sequence' => 6,
                ],
                'lastname' => [
                    'name' => 'lastname',
                    'uitype' => 255,
                    'column' => 'lastname',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Last Name',
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
                'mobile' => [
                    'name' => 'mobile',
                    'uitype' => 11,
                    'column' => 'mobile',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Private Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'account_id' => [
                    'name' => 'account_id',
                    'uitype' => 51,
                    'column' => 'accountid',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
	                'filter' => 1,
	                'filter_sequence' => 4,
                ],
                'leadsource' => [
                    'name' => 'leadsource',
                    'uitype' => 15,
                    'column' => 'leadsource',
                    'table' => 'vtiger_contactsubdetails',
                    'label' => 'Lead Source',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
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
                'title' => [
                    'name' => 'title',
                    'uitype' => 1,
                    'column' => 'title',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Title',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
	                'filter' => 1,
	                'filter_sequence' => 3,
                ],
                'department' => [
                    'name' => 'department',
                    'uitype' => 1,
                    'column' => 'department',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Department',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'birthday' => [
                    'name' => 'birthday',
                    'uitype' => 5,
                    'column' => 'birthday',
                    'table' => 'vtiger_contactsubdetails',
                    'label' => 'Birthdate',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'email' => [
                    'name' => 'email',
                    'uitype' => 13,
                    'column' => 'email',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'E~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
	                'filter' => 1,
	                'filter_sequence' => 5,
	                'headerfield' => 1,
                ],
                'contact_id' => [
                    'name' => 'contact_id',
                    'uitype' => 57,
                    'column' => 'reportsto',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Reports To',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'assistant' => [
                    'name' => 'assistant',
                    'uitype' => 1,
                    'column' => 'assistant',
                    'table' => 'vtiger_contactsubdetails',
                    'label' => 'Assistant',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'secondaryemail' => [
                    'name' => 'secondaryemail',
                    'uitype' => 13,
                    'column' => 'secondaryemail',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Secondary Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'E~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'donotcall' => [
                    'name' => 'donotcall',
                    'uitype' => 56,
                    'column' => 'donotcall',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Do Not Call',
                    'readonly' => 1,
                    'presence' => 2,
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
                    'table' => 'vtiger_contactdetails',
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
                'reference' => [
                    'name' => 'reference',
                    'uitype' => 56,
                    'column' => 'reference',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Reference',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 10,
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
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Notify Owner',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 10,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_CUSTOMER_PORTAL_INFORMATION' => [
                'portal' => [
                    'name' => 'portal',
                    'uitype' => 56,
                    'column' => 'portal',
                    'table' => 'vtiger_customerdetails',
                    'label' => 'Portal User',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'support_start_date' => [
                    'name' => 'support_start_date',
                    'uitype' => 5,
                    'column' => 'support_start_date',
                    'table' => 'vtiger_customerdetails',
                    'label' => 'Support Start Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'support_end_date' => [
                    'name' => 'support_end_date',
                    'uitype' => 5,
                    'column' => 'support_end_date',
                    'table' => 'vtiger_customerdetails',
                    'label' => 'Support End Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O~OTH~GE~support_start_date~Support Start Date',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_ADDRESS_INFORMATION' => [
                'mailingstreet' => [
                    'name' => 'mailingstreet',
                    'uitype' => 21,
                    'column' => 'mailingstreet',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Mailing Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'otherstreet' => [
                    'name' => 'otherstreet',
                    'uitype' => 21,
                    'column' => 'otherstreet',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'mailingcity' => [
                    'name' => 'mailingcity',
                    'uitype' => 1,
                    'column' => 'mailingcity',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Mailing City',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'othercity' => [
                    'name' => 'othercity',
                    'uitype' => 1,
                    'column' => 'othercity',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other City',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'mailingstate' => [
                    'name' => 'mailingstate',
                    'uitype' => 1,
                    'column' => 'mailingstate',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Mailing State',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'otherstate' => [
                    'name' => 'otherstate',
                    'uitype' => 1,
                    'column' => 'otherstate',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other State',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'mailingzip' => [
                    'name' => 'mailingzip',
                    'uitype' => 1,
                    'column' => 'mailingzip',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Mailing Zip',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'otherzip' => [
                    'name' => 'otherzip',
                    'uitype' => 1,
                    'column' => 'otherzip',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other Zip',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'mailingcountry_id' => [
                    'name' => 'mailingcountry_id',
                    'uitype' => 18,
                    'column' => 'mailingcountry_id',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Mailing Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'othercountry_id' => [
                    'name' => 'othercountry_id',
                    'uitype' => 18,
                    'column' => 'othercountry_id',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'mailingpobox' => [
                    'name' => 'mailingpobox',
                    'uitype' => 1,
                    'column' => 'mailingpobox',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Mailing Po Box',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'otherpobox' => [
                    'name' => 'otherpobox',
                    'uitype' => 1,
                    'column' => 'otherpobox',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other Po Box',
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
            'LBL_IMAGE_INFORMATION' => [
                'imagename' => [
                    'name' => 'imagename',
                    'uitype' => 69,
                    'column' => 'imagename',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Contact Image',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'contact_no' => [
                    'name' => 'contact_no',
                    'uitype' => 4,
                    'column' => 'contact_no',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Contact Id',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'isconvertedfromlead' => [
                    'name' => 'isconvertedfromlead',
                    'uitype' => 56,
                    'column' => 'isconvertedfromlead',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Is Converted From Lead',
                    'readonly' => 1,
                    'presence' => 2,
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
                    'picklist_values' => [
                        'Contacted - Successful',
                        'Contacted - Unsuccessful',
                        'Contacted - Never Contact Again',
                    ],
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
        $this->getTable('vtiger_contactdetails', null)
            ->createTable('contactid', 'int(19) NOT NULL')
            ->createColumn('contact_no', 'varchar(100) NOT NULL')
            ->createColumn('accountid', 'int(19) DEFAULT NULL')
            ->createColumn('salutation', 'varchar(200) DEFAULT NULL')
            ->createColumn('firstname', 'varchar(40) DEFAULT NULL')
            ->createColumn('lastname', 'varchar(80) NOT NULL')
            ->createColumn('email', 'varchar(100) DEFAULT NULL')
            ->createColumn('phone', 'varchar(50) DEFAULT NULL')
            ->createColumn('mobile', 'varchar(50) DEFAULT NULL')
            ->createColumn('title', 'varchar(50) DEFAULT NULL')
            ->createColumn('department', 'varchar(30) DEFAULT NULL')
            ->createColumn('fax', 'varchar(50) DEFAULT NULL')
            ->createColumn('reportsto', 'varchar(30) DEFAULT NULL')
            ->createColumn('training', 'varchar(50) DEFAULT NULL')
            ->createColumn('usertype', 'varchar(50) DEFAULT NULL')
            ->createColumn('contacttype', 'varchar(50) DEFAULT NULL')
            ->createColumn('otheremail', 'varchar(100) DEFAULT NULL')
            ->createColumn('secondaryemail', 'varchar(100) DEFAULT NULL')
            ->createColumn('donotcall', 'varchar(3) DEFAULT NULL')
            ->createColumn('emailoptout', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('imagename', 'varchar(150) DEFAULT NULL')
            ->createColumn('reference', 'varchar(3) DEFAULT NULL')
            ->createColumn('notify_owner', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('isconvertedfromlead', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`contactid`)')
            ->createKey('KEY IF NOT EXISTS `contactdetails_accountid_idx` (`accountid`)')
            ->createKey('KEY IF NOT EXISTS `email_idx` (`email`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_contactdetails` FOREIGN KEY IF NOT EXISTS (`contactid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ->createKey('INDEX IF NOT EXISTS email_idx (email)');

	    $this->getTable('vtiger_contactaddress', null)
		    ->createTable('contactaddressid', 'int(19) NOT NULL DEFAULT \'0\'')
		    ->createColumn('mailingcity', 'varchar(150) DEFAULT NULL')
		    ->createColumn('mailingstreet', 'varchar(250) DEFAULT NULL')
		    ->createColumn('mailingcountry_id', 'varchar(2) DEFAULT NULL')
		    ->createColumn('othercountry_id', 'varchar(2) DEFAULT NULL')
		    ->createColumn('mailingstate', 'varchar(150) DEFAULT NULL')
		    ->createColumn('mailingpobox', 'varchar(30) DEFAULT NULL')
		    ->createColumn('othercity', 'varchar(150) DEFAULT NULL')
		    ->createColumn('otherstate', 'varchar(150) DEFAULT NULL')
		    ->createColumn('mailingzip', 'varchar(150) DEFAULT NULL')
		    ->createColumn('otherzip', 'varchar(150) DEFAULT NULL')
		    ->createColumn('otherstreet', 'varchar(250) DEFAULT NULL')
		    ->createColumn('otherpobox', 'varchar(30) DEFAULT NULL')
		    ->createKey('PRIMARY KEY IF NOT EXISTS (`contactaddressid`)')
		    ->createKey('CONSTRAINT `fk_1_vtiger_contactaddress` FOREIGN KEY IF NOT EXISTS (`contactaddressid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE');

        $this->getTable('vtiger_contactsubdetails', null)
            ->createTable('contactsubscriptionid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('assistant', 'varchar(30) DEFAULT NULL')
            ->createColumn('birthday', 'date DEFAULT NULL')
            ->createColumn('laststayintouchrequest', 'int(30) DEFAULT \'0\'')
            ->createColumn('laststayintouchsavedate', 'int(19) DEFAULT \'0\'')
            ->createColumn('leadsource', 'varchar(200) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`contactsubscriptionid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_contactsubdetails` FOREIGN KEY IF NOT EXISTS (`contactsubscriptionid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE');

        $this->getTable('vtiger_customerdetails', null)
            ->createTable('customerid', 'int(19) NOT NULL')
            ->createColumn('portal', 'varchar(3) DEFAULT NULL')
            ->createColumn('support_start_date', 'date DEFAULT NULL')
            ->createColumn('support_end_date', 'date DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`customerid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_customerdetails` FOREIGN KEY IF NOT EXISTS (`customerid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE');
        
        $this->createPicklistTable('vtiger_salutationtype', 'salutationid', 'salutationtype');
    }
}
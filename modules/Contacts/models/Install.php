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
    public array $registerCustomLinks = [
        ['Accounts','Contacts','Contacts','add','get_contacts','',],
        ['Contacts','HelpDesk','HelpDesk','add','get_tickets','',],
        ['Contacts','Quotes','Quotes','add','get_quotes','',],
        ['Contacts','PurchaseOrder','Purchase Order','add','get_purchase_orders','',],
        ['Contacts','SalesOrder','Sales Order','add','get_salesorder','',],
        ['Contacts','Invoice','Invoice','add','get_invoices','',],
        ['Vendors','Contacts','Contacts','select','get_contacts','',],
        ['Contacts','Appointments','Appointments','','get_related_list','',],
        ['Contacts','Assets','Assets','ADD','get_dependents_list','',],
        ['Contacts','Vendors','Vendors','SELECT','get_vendors','',],
        ['Contacts','ServiceContracts','Service Contracts','ADD','get_dependents_list','',],
        ['Contacts','Services','Services','SELECT','get_related_list','',],
        ['Services','Contacts','Contacts','SELECT','get_related_list','',],
        ['Contacts','Project','Projects','ADD,SELECT','get_dependents_list','',],
        ['Contacts','ITS4YouEmails','ITS4YouEmails','SELECT','get_related_list','',],
        ['Contacts','Campaigns','Campaigns','SELECT','get_campaigns','',],
        ['Campaigns','Contacts','Contacts','ADD,SELECT','get_contacts','',],
        ['Contacts','Documents','Documents','ADD,SELECT','get_attachments','',],
        ['Documents','Contacts','Contacts','1','get_related_list','',],
        ['Products','Contacts','Contacts','SELECT','get_contacts','',],
        ['Contacts','Products','Products','SELECT','get_products','',],
        ['Potentials','Contacts','Contacts','SELECT','get_contacts','',],
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateHistory();
        $this->updateComments();
        $this->updateRelatedList();
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
                    'column' => 'salutationtype',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Salutation',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'Mr.',
                        'Ms.',
                        'Mrs.',
                    ],
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
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
                    'quickcreate' => 2,
                    'quicksequence' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'firstname' => [
                    'name' => 'firstname',
                    'uitype' => 1,
                    'column' => 'firstname',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'First Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [],
	                'filter' => 1,
	                'filter_sequence' => 2,
	                'entity_identifier' => 1,
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
                    'quicksequence' => 4,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 3,
                    'entity_identifier' => 1,
                ],
                'account_id' => [
                    'name' => 'account_id',
                    'uitype' => 51,
                    'column' => 'account_id',
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 2,
                    'quicksequence' => 5,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 1,
                    'filter' => 1,
                    'filter_sequence' => 4,
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
                    'quickcreate' => 2,
                    'quicksequence' => 6,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                    'headerfieldsequence' => 5,
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
                    'quicksequence' => 7,
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
                    'table' => 'vtiger_contactdetails',
                    'label' => 'Private Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
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
                    'quicksequence' => 8,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 6,
                    'headerfield' => 1,
                    'headerfieldsequence' => 3,
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
                    'summaryfield' => 1,
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
                    'summaryfield' => 1,
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
                    'headerfield' => 1,
                    'headerfieldsequence' => 4,
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
                    'summaryfield' => 1,
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
                    'summaryfield' => 1,
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
                    'quicksequence' => 14,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 9,
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
                    'quickcreate' => 2,
                    'quicksequence' => 9,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'otherstreet' => [
                    'name' => 'otherstreet',
                    'uitype' => 21,
                    'column' => 'otherstreet',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other Street',
                    'readonly' => 1,
                    'presence' => 1,
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
                    'quickcreate' => 2,
                    'quicksequence' => 10,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
                ],
                'othercity' => [
                    'name' => 'othercity',
                    'uitype' => 1,
                    'column' => 'othercity',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other City',
                    'readonly' => 1,
                    'presence' => 1,
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
                    'filter' => 1,
                    'filter_sequence' => 8,
                ],
                'otherstate' => [
                    'name' => 'otherstate',
                    'uitype' => 1,
                    'column' => 'otherstate',
                    'table' => 'vtiger_contactaddress',
                    'label' => 'Other State',
                    'readonly' => 1,
                    'presence' => 1,
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
                    'quickcreate' => 2,
                    'quicksequence' => 11,
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
                    'presence' => 1,
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
                    'quickcreate' => 2,
                    'quicksequence' => 12,
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
                    'presence' => 1,
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
                    'quickcreate' => 2,
                    'quicksequence' => 13,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
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
                'user_name' => [
                    'name' => 'user_name',
                    'uitype' => Vtiger_Field_Model::UITYPE_EMAIL,
                    'column' => 'user_name',
                    'table' => 'vtiger_portalinfo',
                    'label' => 'Portal User Name',
                    'readonly' => 0,
                    'presence' => 2,
                    'displaytype' => Vtiger_Field_Model::DISPLAYTYPE_DETAIL_AND_LIST,
                    'typeofdata' => 'V~O',
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
                'last_login_time' => [
                    'name' => 'last_login_time',
                    'uitype' => Vtiger_Field_Model::UITYPE_DATE_TIME,
                    'column' => 'last_login_time',
                    'table' => 'vtiger_portalinfo',
                    'label' => 'Portal Last Login',
                    'readonly' => 0,
                    'presence' => 2,
                    'displaytype' => Vtiger_Field_Model::DISPLAYTYPE_DETAIL_AND_LIST,
                    'typeofdata' => 'V~O',
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
            'LBL_CUSTOM_INFORMATION' => [
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
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_contactdetails', null)
            ->createTable('contactid', self::$COLUMN_INT)
            ->renameColumn('salutation', 'salutationtype')
            ->renameColumn('accountid', 'account_id')
            ->clearTableColumns()
            ->createColumn('contact_no', 'varchar(100) NOT NULL')
            ->createColumn('account_id', self::$COLUMN_INT)
            ->createColumn('salutationtype', 'varchar(200) DEFAULT NULL')
            ->createColumn('firstname', 'varchar(40) DEFAULT NULL')
            ->createColumn('lastname', 'varchar(80) NOT NULL')
            ->createColumn('email', 'varchar(100) DEFAULT NULL')
            ->createColumn('phone', 'varchar(50) DEFAULT NULL')
            ->createColumn('mobile', 'varchar(50) DEFAULT NULL')
            ->createColumn('title', 'varchar(50) DEFAULT NULL')
            ->createColumn('department', 'varchar(30) DEFAULT NULL')
            ->createColumn('fax', 'varchar(50) DEFAULT NULL')
            ->createColumn('training', 'varchar(50) DEFAULT NULL')
            ->createColumn('usertype', 'varchar(50) DEFAULT NULL')
            ->createColumn('contacttype', 'varchar(50) DEFAULT NULL')
            ->createColumn('otheremail', 'varchar(100) DEFAULT NULL')
            ->createColumn('secondaryemail', 'varchar(100) DEFAULT NULL')
            ->createColumn('donotcall', 'varchar(3) DEFAULT NULL')
            ->createColumn('emailoptout', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('imagename', 'varchar(150) DEFAULT NULL')
            ->createColumn('notify_owner', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('isconvertedfromlead', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('currency_id', self::$COLUMN_INT)
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`contactid`)')
            ->createKey('KEY IF NOT EXISTS `contactdetails_accountid_idx` (`account_id`)')
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
		    ->createColumn('othercity', 'varchar(150) DEFAULT NULL')
		    ->createColumn('otherstate', 'varchar(150) DEFAULT NULL')
		    ->createColumn('mailingzip', 'varchar(150) DEFAULT NULL')
		    ->createColumn('otherzip', 'varchar(150) DEFAULT NULL')
		    ->createColumn('otherstreet', 'varchar(250) DEFAULT NULL')
		    ->createKey('PRIMARY KEY IF NOT EXISTS (`contactaddressid`)')
		    ->createKey('CONSTRAINT `fk_1_vtiger_contactaddress` FOREIGN KEY IF NOT EXISTS (`contactaddressid`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE');

        $this->getTable('vtiger_contactsubdetails', null)
            ->createTable('contactsubscriptionid', 'int(19) NOT NULL DEFAULT \'0\'')
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

        $this->getTable('vtiger_portalinfo', null)
            ->createTable('id',self::$COLUMN_INT)
            ->createColumn('user_name','varchar(50) DEFAULT NULL')
            ->createColumn('user_password','varchar(255) DEFAULT NULL')
            ->createColumn('type','varchar(5) DEFAULT NULL')
            ->createColumn('cryptmode','varchar(20) DEFAULT NULL')
            ->createColumn('last_login_time','datetime DEFAULT NULL')
            ->createColumn('login_time','datetime DEFAULT NULL')
            ->createColumn('logout_time','datetime DEFAULT NULL')
            ->createColumn('isactive','int(1) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_portalinfo` FOREIGN KEY IF NOT EXISTS (`id`) REFERENCES `vtiger_contactdetails` (`contactid`) ON DELETE CASCADE')
        ;

        $this->createPicklistTable('vtiger_salutationtype', 'salutationid', 'salutationtype');
    }

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $moduleName = $this->getModuleName();
        $fields = [
            'salutation' => 'salutationtype',
            'accountid' => 'account_id',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $fields);

        $deleteFields = [
            'portal_user',
            'portal_last_login',
        ];
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        foreach ($deleteFields as $fieldName) {
            $fieldModel = $moduleModel->getField($fieldName);

            if ($fieldModel) {
                $fieldModel->delete();
            }
        }
    }
}
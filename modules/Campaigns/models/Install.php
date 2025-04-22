<?php

class Campaigns_Install_Model extends Core_Install_Model {

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['Leads', 'Campaigns', 'Campaigns', 'select', 'get_campaigns', ],
        ['Contacts', 'Campaigns', 'Campaigns', 'select', 'get_campaigns', ],
        ['Campaigns', 'Contacts', 'Contacts', 'add,select', 'get_contacts', ],
        ['Campaigns', 'Leads', 'Leads', 'add,select', 'get_leads', ],
        ['Campaigns', 'Potentials', 'Potentials', 'add', 'get_opportunities', ],
        ['Campaigns', 'Calendar', 'Activities', 'add', 'get_activities', ],
        ['Accounts', 'Campaigns', 'Campaigns', 'select', 'get_campaigns', ],
        ['Campaigns', 'Accounts', 'Accounts', 'add,select', 'get_accounts', ],
        ['Campaigns', 'Appointments', 'Appointments', '', 'get_related_list', ],
        ['Campaigns', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list', ],
    ];


    public function addCustomLinks(): void
    {
        $this->updateRelatedList();
        $this->updateComments();
        $this->updateHistory();
        $this->updateToStandardModule();
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
            'LBL_CAMPAIGN_INFORMATION' => [
                'campaignname' => [
                    'name' => 'campaignname',
                    'uitype' => 2,
                    'column' => 'campaignname',
                    'table' => 'vtiger_campaign',
                    'label' => 'Campaign Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
                'campaign_no' => [
                    'name' => 'campaign_no',
                    'uitype' => 4,
                    'column' => 'campaign_no',
                    'table' => 'vtiger_campaign',
                    'label' => 'Campaign No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'campaigntype' => [
                    'name' => 'campaigntype',
                    'uitype' => 15,
                    'column' => 'campaigntype',
                    'table' => 'vtiger_campaign',
                    'label' => 'Campaign Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'Conference',
                        'Webinar',
                        'Trade Show',
                        'Public Relations',
                        'Partners',
                        'Referral Program',
                        'Advertisement',
                        'Banner Ads',
                        'Direct Mail',
                        'Email',
                        'Telemarketing',
                        'Others',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'product_id' => [
                    'name' => 'product_id',
                    'uitype' => 59,
                    'column' => 'product_id',
                    'table' => 'vtiger_campaign',
                    'label' => 'Product',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'campaignstatus' => [
                    'name' => 'campaignstatus',
                    'uitype' => 15,
                    'column' => 'campaignstatus',
                    'table' => 'vtiger_campaign',
                    'label' => 'Campaign Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'Planning',
                        'Active',
                        'Inactive',
                        'Completed',
                        'Cancelled',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'closingdate' => [
                    'name' => 'closingdate',
                    'uitype' => 23,
                    'column' => 'closingdate',
                    'table' => 'vtiger_campaign',
                    'label' => 'Expected Close Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~M',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 5,
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
                    'filter_sequence' => 6,
                ],
                'numsent' => [
                    'name' => 'numsent',
                    'uitype' => 9,
                    'column' => 'numsent',
                    'table' => 'vtiger_campaign',
                    'label' => 'Num Sent',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'sponsor' => [
                    'name' => 'sponsor',
                    'uitype' => 1,
                    'column' => 'sponsor',
                    'table' => 'vtiger_campaign',
                    'label' => 'Sponsor',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'targetaudience' => [
                    'name' => 'targetaudience',
                    'uitype' => 1,
                    'column' => 'targetaudience',
                    'table' => 'vtiger_campaign',
                    'label' => 'Target Audience',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'targetsize' => [
                    'name' => 'targetsize',
                    'uitype' => 1,
                    'column' => 'targetsize',
                    'table' => 'vtiger_campaign',
                    'label' => 'TargetSize',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'createdtime' => [
                    'name' => 'createdtime',
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'readonly' => 1,
                    'presence' => 0,
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
                    'label' => 'Modified Time',
                    'readonly' => 1,
                    'presence' => 0,
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
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_EXPECTATIONS_AND_ACTUALS' => [
                'expectedresponse' => [
                    'name' => 'expectedresponse',
                    'uitype' => 15,
                    'column' => 'expectedresponse',
                    'table' => 'vtiger_campaign',
                    'label' => 'Expected Response',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Excellent',
                        'Good',
                        'Average',
                        'Poor',
                    ],
                ],
                'expectedrevenue' => [
                    'name' => 'expectedrevenue',
                    'uitype' => 71,
                    'column' => 'expectedrevenue',
                    'table' => 'vtiger_campaign',
                    'label' => 'Expected Revenue',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'budgetcost' => [
                    'name' => 'budgetcost',
                    'uitype' => 71,
                    'column' => 'budgetcost',
                    'table' => 'vtiger_campaign',
                    'label' => 'Budget Cost',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'actualcost' => [
                    'name' => 'actualcost',
                    'uitype' => 71,
                    'column' => 'actualcost',
                    'table' => 'vtiger_campaign',
                    'label' => 'Actual Cost',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'expectedresponsecount' => [
                    'name' => 'expectedresponsecount',
                    'uitype' => 1,
                    'column' => 'expectedresponsecount',
                    'table' => 'vtiger_campaign',
                    'label' => 'Expected Response Count',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'expectedsalescount' => [
                    'name' => 'expectedsalescount',
                    'uitype' => 1,
                    'column' => 'expectedsalescount',
                    'table' => 'vtiger_campaign',
                    'label' => 'Expected Sales Count',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'expectedroi' => [
                    'name' => 'expectedroi',
                    'uitype' => 71,
                    'column' => 'expectedroi',
                    'table' => 'vtiger_campaign',
                    'label' => 'Expected ROI',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'actualresponsecount' => [
                    'name' => 'actualresponsecount',
                    'uitype' => 1,
                    'column' => 'actualresponsecount',
                    'table' => 'vtiger_campaign',
                    'label' => 'Actual Response Count',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'actualsalescount' => [
                    'name' => 'actualsalescount',
                    'uitype' => 1,
                    'column' => 'actualsalescount',
                    'table' => 'vtiger_campaign',
                    'label' => 'Actual Sales Count',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'actualroi' => [
                    'name' => 'actualroi',
                    'uitype' => 71,
                    'column' => 'actualroi',
                    'table' => 'vtiger_campaign',
                    'label' => 'Actual ROI',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
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
        $this->getTable('vtiger_campaign', '')
            ->createTable('campaignid')
            ->createColumn('campaign_no','varchar(100) NOT NULL')
            ->createColumn('campaignname','varchar(255) DEFAULT NULL')
            ->createColumn('campaigntype','varchar(200) DEFAULT NULL')
            ->createColumn('campaignstatus','varchar(200) DEFAULT NULL')
            ->createColumn('expectedrevenue','decimal(25,8) DEFAULT NULL')
            ->createColumn('budgetcost','decimal(25,8) DEFAULT NULL')
            ->createColumn('actualcost','decimal(25,8) DEFAULT NULL')
            ->createColumn('expectedresponse','varchar(200) DEFAULT NULL')
            ->createColumn('numsent','decimal(11,0) DEFAULT NULL')
            ->createColumn('product_id','int(19) DEFAULT NULL')
            ->createColumn('sponsor','varchar(255) DEFAULT NULL')
            ->createColumn('targetaudience','varchar(255) DEFAULT NULL')
            ->createColumn('targetsize','int(19) DEFAULT NULL')
            ->createColumn('expectedresponsecount','int(19) DEFAULT NULL')
            ->createColumn('expectedsalescount','int(19) DEFAULT NULL')
            ->createColumn('expectedroi','decimal(25,8) DEFAULT NULL')
            ->createColumn('actualresponsecount','int(19) DEFAULT NULL')
            ->createColumn('actualsalescount','int(19) DEFAULT NULL')
            ->createColumn('actualroi','decimal(25,8) DEFAULT NULL')
            ->createColumn('closingdate','date DEFAULT NULL')
            ->createColumn('tags','varchar(1) DEFAULT NULL')
            ->createColumn('currency_id','int(19) DEFAULT NULL')
            ->createColumn('conversion_rate','decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`campaignid`)')
            ->createKey('KEY IF NOT EXISTS `campaign_campaignstatus_idx` (`campaignstatus`)')
            ->createKey('KEY IF NOT EXISTS `campaign_campaignname_idx` (`campaignname`)')
            ->createKey('KEY IF NOT EXISTS `campaign_campaignid_idx` (`campaignid`)')
            ->createKey('CONSTRAINT `fk_crmid_vtiger_campaign` FOREIGN KEY IF NOT EXISTS (`campaignid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ;

        $this->getTable('vtiger_campaignscf', '')
            ->createTable('campaignid')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`campaignid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_campaignscf` FOREIGN KEY IF NOT EXISTS (`campaignid`) REFERENCES `vtiger_campaign` (`campaignid`) ON DELETE CASCADE')
        ;
    }
}
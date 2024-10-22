<?php

class Potentials_Install_Model extends Core_Install_Model
{
    public array $registerRelatedLists = [
        ['Potentials', 'Contacts', 'Contacts', 'select', 'get_contacts', '',],
        ['Potentials', 'Products', 'Products', 'select', 'get_products', '',],
        ['Potentials', null, 'Sales Stage History', '', 'get_stage_history', '',],
        ['Potentials', 'Documents', 'Documents', 'add,select', 'get_attachments', '',],
        ['Potentials', 'Quotes', 'Quotes', 'add', 'get_Quotes', '',],
        ['Potentials', 'SalesOrder', 'Sales Order', 'add', 'get_salesorder', '',],
        ['Potentials', 'Services', 'Services', 'SELECT', 'get_related_list', '',],
        ['Potentials', 'Invoice', 'Invoice', 'ADD', 'get_dependents_list', '',],
        ['Potentials', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list', '',],
        ['HelpDesk', 'Appointments', 'Appointments', '', 'get_related_list', '',],
    ];

    public function addCustomLinks(): void
    {
        $this->updateHistory();
        $this->updateComments();
        $this->updateRelatedList();
        $this->updateMapping();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateHistory(false);
        $this->updateComments(false);
        $this->updateRelatedList(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_OPPORTUNITY_INFORMATION' => [
                'potentialname' => [
                    'name' => 'potentialname',
                    'uitype' => 2,
                    'column' => 'potentialname',
                    'table' => 'vtiger_potential',
                    'label' => 'Potential Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'amount' => [
                    'name' => 'amount',
                    'uitype' => 71,
                    'column' => 'amount',
                    'table' => 'vtiger_potential',
                    'label' => 'Amount',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 6,
                ],
                'related_to' => [
                    'name' => 'related_to',
                    'uitype' => 10,
                    'column' => 'related_to',
                    'table' => 'vtiger_potential',
                    'label' => 'Related To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'Accounts',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'closingdate' => [
                    'name' => 'closingdate',
                    'uitype' => 23,
                    'column' => 'closingdate',
                    'table' => 'vtiger_potential',
                    'label' => 'Expected Close Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~M',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
                ],
                'opportunity_type' => [
                    'name' => 'opportunity_type',
                    'uitype' => 15,
                    'column' => 'potentialtype',
                    'table' => 'vtiger_potential',
                    'label' => 'Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Existing Business',
                        'New Business',
                    ],
                ],
                'nextstep' => [
                    'name' => 'nextstep',
                    'uitype' => 1,
                    'column' => 'nextstep',
                    'table' => 'vtiger_potential',
                    'label' => 'Next Step',
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
                'leadsource' => [
                    'name' => 'leadsource',
                    'uitype' => 15,
                    'column' => 'leadsource',
                    'table' => 'vtiger_potential',
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
                'sales_stage' => [
                    'name' => 'sales_stage',
                    'uitype' => 15,
                    'column' => 'sales_stage',
                    'table' => 'vtiger_potential',
                    'label' => 'Sales Stage',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                    'picklist_values' => [
                        'Prospecting',
                        'Qualification',
                        'Needs Analysis',
                        'Value Proposition',
                        'Id. Decision Makers',
                        'Perception Analysis',
                        'Proposal or Price Quote',
                        'Negotiation or Review',
                        'Closed Won',
                        'Closed Lost',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 9,
                ],
                'probability' => [
                    'name' => 'probability',
                    'uitype' => 9,
                    'column' => 'probability',
                    'table' => 'vtiger_potential',
                    'label' => 'Probability',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'campaignid' => [
                    'name' => 'campaignid',
                    'uitype' => 58,
                    'column' => 'campaignid',
                    'table' => 'vtiger_potential',
                    'label' => 'Campaign Source',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'forecast_amount' => [
                    'name' => 'forecast_amount',
                    'uitype' => 71,
                    'column' => 'forecast_amount',
                    'table' => 'vtiger_potential',
                    'label' => 'Forecast Amount',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'contact_id' => [
                    'name' => 'contact_id',
                    'uitype' => 10,
                    'column' => 'contact_id',
                    'table' => 'vtiger_potential',
                    'label' => 'Contact Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'related_modules' => [
                        'Contacts',
                    ],
                    'filter' => 1,
                    'filter_sequence' => 5,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'potential_no' => [
                    'name' => 'potential_no',
                    'uitype' => 4,
                    'column' => 'potential_no',
                    'table' => 'vtiger_potential',
                    'label' => 'Potential No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 1,
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
                'creator' => [
                    'name' => 'creator',
                    'column' => 'smcreatorid',
                    'label' => 'Creator',
                    'uitype' => 52,
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'table' => 'vtiger_crmentity',
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
                'isconvertedfromlead' => [
                    'name' => 'isconvertedfromlead',
                    'uitype' => 56,
                    'column' => 'isconvertedfromlead',
                    'table' => 'vtiger_potential',
                    'label' => 'Is Converted From Lead',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'mail_manager_id' => [
                    'name' => 'mail_manager_id',
                    'column' => 'mail_manager_id',
                    'uitype' => 32,
                    'table' => 'vtiger_potential',
                    'label' => 'Mail Manager Id',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 0,
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
        return [
            'vtiger_potential',
            'vtiger_opportunity_type',
            'vtiger_sales_stage',
            'vtiger_leadsource',
        ];
    }

    /**
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_potential', 'potentialid')
            ->createTable('potentialid')
            ->createColumn('potential_no', 'varchar(100) NOT NULL')
            ->createColumn('related_to', 'int(19) DEFAULT NULL')
            ->createColumn('potentialname', 'varchar(120) NOT NULL')
            ->createColumn('amount', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('currency', 'varchar(20) DEFAULT NULL')
            ->createColumn('closingdate', 'date DEFAULT NULL')
            ->createColumn('typeofrevenue', 'varchar(50) DEFAULT NULL')
            ->createColumn('nextstep', 'varchar(100) DEFAULT NULL')
            ->createColumn('private', 'int(1) DEFAULT 0')
            ->createColumn('probability', 'decimal(7,3) DEFAULT \'0.000\'')
            ->createColumn('campaignid', 'int(19) DEFAULT NULL')
            ->createColumn('sales_stage', 'varchar(200) DEFAULT NULL')
            ->createColumn('potentialtype', 'varchar(200) DEFAULT NULL')
            ->createColumn('leadsource', 'varchar(200) DEFAULT NULL')
            ->createColumn('productid', 'int(50) DEFAULT NULL')
            ->createColumn('productversion', 'varchar(50) DEFAULT NULL')
            ->createColumn('quotationref', 'varchar(50) DEFAULT NULL')
            ->createColumn('partnercontact', 'varchar(50) DEFAULT NULL')
            ->createColumn('remarks', 'varchar(50) DEFAULT NULL')
            ->createColumn('runtimefee', 'int(19) DEFAULT \'0\'')
            ->createColumn('followupdate', 'date DEFAULT NULL')
            ->createColumn('evaluationstatus', 'varchar(50) DEFAULT NULL')
            ->createColumn('description', 'text DEFAULT NULL')
            ->createColumn('forecastcategory', 'int(19) DEFAULT \'0\'')
            ->createColumn('outcomeanalysis', 'int(19) DEFAULT \'0\'')
            ->createColumn('forecast_amount', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('isconvertedfromlead', 'varchar(3) DEFAULT \'0\'')
            ->createColumn('contact_id', 'int(19) DEFAULT NULL')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('converted', 'int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('currency_id', 'int(19) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createColumn('mail_manager_id', 'INT(19) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`potentialid`)')
            ->createKey('KEY IF NOT EXISTS `potential_relatedto_idx` (`related_to`)')
            ->createKey('KEY IF NOT EXISTS `potentail_sales_stage_idx` (`sales_stage`)')
            ->createKey('KEY IF NOT EXISTS `potentail_sales_stage_amount_idx` (`amount`,`sales_stage`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_potential` FOREIGN KEY IF NOT EXISTS (`potentialid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_opportunity_type', 'opptypeid')
            ->createTable()
            ->createColumn('opportunity_type', 'varchar(200) NOT NULL')
            ->createColumn('presence', 'int(1) NOT NULL DEFAULT \'1\'')
            ->createColumn('picklist_valueid', 'int(11) NOT NULL DEFAULT \'0\'')
            ->createColumn('sortorderid', 'int(11) DEFAULT \'0\'')
            ->createColumn('color', 'varchar(10) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`opptypeid`)');

        $this->getTable('vtiger_sales_stage', 'sales_stage_id')
            ->createTable()
            ->createColumn('sales_stage', 'varchar(200) DEFAULT NULL')
            ->createColumn('presence', 'int(1) NOT NULL DEFAULT 1')
            ->createColumn('picklist_valueid', 'int(19) NOT NULL DEFAULT 0')
            ->createColumn('sortorderid', 'int(11) DEFAULT NULL')
            ->createColumn('color', 'varchar(10) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`sales_stage_id`)');

        $this->getTable('vtiger_leadsource', 'leadsourceid')
            ->createTable()
            ->createColumn('leadsource','varchar(200) NOT NULL')
            ->createColumn('presence','int(1) NOT NULL DEFAULT \'1\'')
            ->createColumn('picklist_valueid','int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('sortorderid','int(11) DEFAULT NULL')
            ->createColumn('color','varchar(10) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`leadsourceid`)');

        $this->getTable('vtiger_convertpotentialmapping', 'cfmid')
            ->createTable()
            ->createColumn('editable', 'INT(1) DEFAULT \'1\'')
            ->createColumn('potential_field', 'VARCHAR(50)')
            ->createColumn('project_field', 'VARCHAR(50)');
    }

    /**
     * @throws AppException
     */
    public function updateMapping(): void
    {
        $table = $this->getTable('vtiger_convertpotentialmapping', null);
        $fieldMap = [
            ['potentialname', 'projectname', 0],
            ['description', 'description', 1],
            ['related_to', 'linktoaccountscontacts', 1],
        ];
        $data = $table->selectData(['cfmid'], []);

        if (!empty($data['cfmid'])) {
            return;
        }

        foreach ($fieldMap as $values) {
            [$potentialField, $projectField, $editable] = $values;

            $table->insertData(['potential_field' => $potentialField, 'project_field' => $projectField, 'editable' => $editable]);
        }
    }
}
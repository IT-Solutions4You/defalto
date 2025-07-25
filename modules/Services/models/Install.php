<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Services_Install_Model extends Core_Install_Model
{
    public string $moduleNumbering = 'SER';

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['HelpDesk', 'Services', 'Services', ['select']],
        ['Leads', 'Services', 'Services', ['select']],
        ['Accounts', 'Services', 'Services', ['select']],
        ['Contacts', 'Services', 'Services', ['select']],
        ['Potentials', 'Services', 'Services', ['select']],
        ['PriceBooks', 'Services', 'Services', ['select'], 'get_pricebook_services'],

        ['Services', 'HelpDesk', 'HelpDesk', ['add', 'select'], 'get_related_list'],
        ['Services', 'Quotes', 'Quotes', ['add'], 'get_quotes'],
        ['Services', 'PurchaseOrder', 'Purchase Order', ['add'], 'get_purchase_orders'],
        ['Services', 'SalesOrder', 'Sales Order', ['add'], 'get_salesorder'],
        ['Services', 'Invoice', 'Invoice', ['add'], 'get_invoices'],
        ['Services', 'PriceBooks', 'PriceBooks', ['add'], 'get_service_pricebooks'],
        ['Services', 'Leads', 'Leads', ['SELECT'], 'get_related_list'],
        ['Services', 'Accounts', 'Accounts', ['SELECT'], 'get_related_list'],
        ['Services', 'Contacts', 'Contacts', ['SELECT'], 'get_related_list'],
        ['Services', 'Potentials', 'Potentials', ['SELECT'], 'get_related_list'],
        ['Services', 'Documents', 'Documents', ['ADD', 'SELECT'], 'get_attachments'],
    ];

    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateRelatedList();
        $this->updateNumbering();
        $this->updateComments();
        $this->updateHistory();
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
            'LBL_SERVICE_INFORMATION' => [
                'servicename' => [
                    'uitype' => 2,
                    'column' => 'servicename',
                    'table' => 'vtiger_service',
                    'label' => 'Service Name',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'quickcreatesequence' => 1,
                    'entity_identifier' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'discontinued' => [
                    'uitype' => 56,
                    'column' => 'discontinued',
                    'table' => 'vtiger_service',
                    'label' => 'Service Active',
                    'quickcreate' => 2,
                    'quickcreatesequence' => 3,
                ],
                'sales_start_date' => [
                    'uitype' => 5,
                    'column' => 'sales_start_date',
                    'table' => 'vtiger_service',
                    'label' => 'Sales Start Date',
                    'typeofdata' => 'D~O',
                ],
                'sales_end_date' => [
                    'uitype' => 5,
                    'column' => 'sales_end_date',
                    'table' => 'vtiger_service',
                    'label' => 'Sales End Date',
                    'typeofdata' => 'D~O~OTH~GE~sales_start_date~Sales Start Date',
                ],
                'start_date' => [
                    'uitype' => 5,
                    'column' => 'start_date',
                    'table' => 'vtiger_service',
                    'label' => 'Support Start Date',
                    'typeofdata' => 'D~O',
                ],
                'expiry_date' => [
                    'uitype' => 5,
                    'column' => 'expiry_date',
                    'table' => 'vtiger_service',
                    'label' => 'Support Expiry Date',
                    'typeofdata' => 'D~O~OTH~GE~start_date~Start Date',
                ],
                'website' => [
                    'uitype' => 17,
                    'column' => 'website',
                    'table' => 'vtiger_service',
                    'label' => 'Website',
                ],
                'service_usageunit' => [
                    'uitype' => 15,
                    'column' => 'service_usageunit',
                    'table' => 'vtiger_service',
                    'label' => 'Usage Unit',
                    'picklist_values' => [
                        'Hours',
                        'Days',
                        'Incidents',
                    ],
                    'filter' => 1,
                ],
                'qty_per_unit' => [
                    'column' => 'qty_per_unit',
                    'table' => 'vtiger_service',
                    'label' => 'No of Units',
                    'typeofdata' => 'N~O',
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Owner',
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'filter' => 1,
                ],
                'servicecategory' => [
                    'uitype' => 15,
                    'column' => 'servicecategory',
                    'table' => 'vtiger_service',
                    'label' => 'Service Category',
                    'picklist_values' => [
                        'Support',
                        'Installation',
                        'Migration',
                        'Customization',
                        'Training',
                    ],
                    'filter' => 1,
                ],
            ],
            'LBL_PRICING_INFORMATION' => [
                'unit_price' => [
                    'uitype' => 72,
                    'column' => 'unit_price',
                    'table' => 'vtiger_service',
                    'label' => 'Price',
                    'typeofdata' => 'N~O',
                    'quickcreate' => 2,
                    'quickcreatesequence' => 2,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'taxclass' => [
                    'uitype' => 83,
                    'column' => 'taxclass',
                    'table' => 'vtiger_service',
                    'label' => 'Taxes',
                ],
                'commissionrate' => [
                    'uitype' => 9,
                    'column' => 'commissionrate',
                    'table' => 'vtiger_service',
                    'label' => 'Commission Rate',
                    'typeofdata' => 'N~O',
                    'summaryfield' => 1,
                ],
                'purchase_cost' => [
                    'name' => 'purchase_cost',
                    'uitype' => 71,
                    'column' => 'purchase_cost',
                    'table' => 'vtiger_service',
                    'label' => 'Purchase Cost',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'service_no' => [
                    'uitype' => 4,
                    'column' => 'service_no',
                    'table' => 'vtiger_service',
                    'label' => 'Service No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
            ]
        ];
    }

    public function getTables(): array
    {
        return [
            'vtiger_service',
            'vtiger_servicecf',
        ];
    }

    /**
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_service', null)
            ->createTable('serviceid')
            ->createColumn('service_no','varchar(100) NOT NULL')
            ->createColumn('servicename','varchar(50) NOT NULL')
            ->createColumn('servicecategory','varchar(200) default NULL')
            ->createColumn('qty_per_unit','decimal(11,2) default \'0.00\'')
            ->createColumn('unit_price',self::$COLUMN_DECIMAL)
            ->createColumn('purchase_cost',self::$COLUMN_DECIMAL)
            ->createColumn('sales_start_date','date default NULL')
            ->createColumn('sales_end_date','date default NULL')
            ->createColumn('start_date','date default NULL')
            ->createColumn('expiry_date','date default NULL')
            ->createColumn('discontinued','int(1) NOT NULL default \'0\'')
            ->createColumn('service_usageunit','varchar(200) default NULL')
            ->createColumn('website','varchar(100) default NULL')
            ->createColumn('taxclass','varchar(200) default NULL')
            ->createColumn('currency_id','int(19) NOT NULL default \'1\'')
            ->createColumn('commissionrate','decimal(7,3) default NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`serviceid`)')
            ->createKey('CONSTRAINT fk_1_vtiger_service FOREIGN KEY IF NOT EXISTS (`serviceid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
            ;

        $this->getTable('vtiger_servicecf', null)
            ->createTable('serviceid')
            ;
    }
}
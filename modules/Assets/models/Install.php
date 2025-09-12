<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Assets_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['Accounts', 'Assets', 'Assets', ['add'], 'get_dependents_list'],
        ['Products', 'Assets', 'Assets', ['add'], 'get_dependents_list'],
        ['Invoice', 'Assets', 'Assets', ['add'], 'get_dependents_list'],
        ['Documents', 'Assets', 'Assets', ['add'], 'get_related_list', '',],
        ['Contacts', 'Assets', 'Assets', ['add'], 'get_dependents_list', '',],

        ['Assets', 'HelpDesk', 'HelpDesk', ['add', 'select'], 'get_related_list'],
        ['Assets', 'Documents', 'Documents', ['add', 'select'], 'get_attachments'],
        ['Assets', 'Appointments', 'Appointments', '', 'get_related_list', '',],
        ['Assets', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list', '',],
    ];

    /**
     * @var string
     */
    protected string $moduleNumbering = 'ASSET';

    /**
     * @var array
     */
    public array $blocksHeaderFields = [
        'assetstatus',
        'product',
        'serialnumber',
        'account',
        'datesold',
    ];

    /**
     * @var array
     */
    public array $blocksSummaryFields = [
        'asset_no',
        'assetname',
        'assetstatus',
        'product',
        'serialnumber',
        'datesold',
        'dateinservice',
        'account',
        'contact',
        'shippingmethod',
        'shippingtrackingnumber',
        'invoiceid',
    ];

    /**
     * @var array
     */
    public array $blocksListFields = [
        'asset_no',
        'assetname',
        'product',
        'serialnumber',
        'assetstatus',
        'account',
        'datesold',
        'invoiceid',
        'assigned_user_id',
    ];

    /**
     * @var array
     */
    public array $blocksQuickCreateFields = [
        'assetname',
        'assetstatus',
        'product',
        'serialnumber',
        'datesold',
        'dateinservice',
        'account',
        'assigned_user_id',
        'description',
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateNumbering();
        $this->updateRelatedList();
        $this->updateComments();
        $this->updateHistory();
        $this->addModuleToCustomerPortal();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
        $this->updateComments(false);
        $this->updateHistory(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_ASSET_INFORMATION' => [
                'assetname' => [
                    'uitype' => 1,
                    'column' => 'assetname',
                    'table' => 'vtiger_assets',
                    'label' => 'Asset Name',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'entity_identifier' => 1,
                    'summaryfield' => 0,
                ],
                'assetstatus' => [
                    'uitype' => 15,
                    'column' => 'assetstatus',
                    'table' => 'vtiger_assets',
                    'label' => 'Status',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'picklist_values' => [
                        'In Service',
                        'Out-of-service',
                    ],
                ],
                'product' => [
                    'uitype' => 10,
                    'column' => 'product',
                    'table' => 'vtiger_assets',
                    'label' => 'Product Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'related_modules' => [
                        'Products',
                    ],
                    'summaryfield' => 0,
                ],
                'serialnumber' => [
                    'uitype' => 2,
                    'column' => 'serialnumber',
                    'table' => 'vtiger_assets',
                    'label' => 'Serial Number',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                ],
                'datesold' => [
                    'uitype' => 5,
                    'column' => 'datesold',
                    'table' => 'vtiger_assets',
                    'label' => 'Date Sold',
                    'typeofdata' => 'D~M~OTH~GE~datesold~Date Sold',
                    'quickcreate' => 2,
                ],
                'dateinservice' => [
                    'uitype' => 5,
                    'column' => 'dateinservice',
                    'table' => 'vtiger_assets',
                    'label' => 'Date in Service',
                    'typeofdata' => 'D~M~OTH~GE~dateinservice~Date in Service',
                    'quickcreate' => 2,
                ],
                'shippingmethod' => [
                    'uitype' => 2,
                    'column' => 'shippingmethod',
                    'table' => 'vtiger_assets',
                    'label' => 'Shipping Method',
                ],
                'shippingtrackingnumber' => [
                    'uitype' => 2,
                    'column' => 'shippingtrackingnumber',
                    'table' => 'vtiger_assets',
                    'label' => 'Shipping Tracking Number',
                ],
                'invoiceid' => [
                    'uitype' => 10,
                    'column' => 'invoiceid',
                    'table' => 'vtiger_assets',
                    'label' => 'Invoice',
                    'related_modules' => [
                        'Invoice',
                    ],
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                ],
                'account' => [
                    'uitype' => 10,
                    'column' => 'account',
                    'table' => 'vtiger_assets',
                    'label' => 'Customer Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'related_modules' => [
                        'Accounts',
                    ],
                    'summaryfield' => 0,
                ],
                'contact' => [
                    'uitype' => 10,
                    'column' => 'contact',
                    'table' => 'vtiger_assets',
                    'label' => 'Contact Name',
                    'quickcreate' => 2,
                    'related_modules' => [
                        'Contacts',
                    ],
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
                'asset_no' => [
                    'uitype' => 4,
                    'column' => 'asset_no',
                    'table' => 'vtiger_assets',
                    'label' => 'Asset No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function getTables(): array
    {
        return [
            'vtiger_assets',
            'vtiger_assetscf',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_assets', null)
            ->createTable('assetsid')
            ->createColumn('asset_no', 'varchar(30) NOT NULL')
            ->createColumn('account', self::$COLUMN_INT)
            ->createColumn('contact', self::$COLUMN_INT)
            ->createColumn('product', self::$COLUMN_INT)
            ->createColumn('serialnumber', 'varchar(200)')
            ->createColumn('datesold', 'date')
            ->createColumn('dateinservice', 'date')
            ->createColumn('assetstatus', 'varchar(200) default \'In Service\'')
            ->createColumn('tagnumber', 'varchar(300) default NULL')
            ->createColumn('invoiceid', self::$COLUMN_INT)
            ->createColumn('shippingmethod', 'varchar(200) default NULL')
            ->createColumn('shippingtrackingnumber', 'varchar(200) default NULL')
            ->createColumn('assetname', 'varchar(100) default NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`assetsid`)');

        $this->getTable('vtiger_assetscf', null)
            ->createTable('assetsid');
    }
}
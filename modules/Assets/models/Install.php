<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Assets_Install_Model extends Vtiger_Install_Model
{
    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = [
        ['Accounts', 'Assets', 'Assets', ['add'], 'get_dependents_list'],
        ['Products', 'Assets', 'Assets', ['add'], 'get_dependents_list'],
        ['Invoice', 'Assets', 'Assets', ['add'], 'get_dependents_list'],

        ['Assets', 'HelpDesk', 'HelpDesk', ['add', 'select'], 'get_related_list'],
        ['Assets', 'Documents', 'Documents', ['add', 'select'], 'get_attachments'],
    ];

    /**
     * @var string
     */
    protected string $moduleNumbering = 'ASSET';

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
                'asset_no' => [
                    'uitype' => 4,
                    'column' => 'asset_no',
                    'table' => 'vtiger_assets',
                    'label' => 'Asset No',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                    'filter' => 1,
                    'summaryfield' => 1,
                ],
                'product' => [
                    'uitype' => 10,
                    'column' => 'product',
                    'table' => 'vtiger_assets',
                    'label' => 'Product Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'related_modules' => [
                        'Products',
                    ],
                    'filter' => 1,
                    'summaryfield' => 1,
                ],
                'serialnumber' => [
                    'uitype' => 2,
                    'column' => 'serialnumber',
                    'table' => 'vtiger_assets',
                    'label' => 'Serial Number',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                ],
                'datesold' => [
                    'uitype' => 5,
                    'column' => 'datesold',
                    'table' => 'vtiger_assets',
                    'label' => 'Date Sold',
                    'typeofdata' => 'D~M~OTH~GE~datesold~Date Sold',
                    'quickcreate' => 0,
                ],
                'dateinservice' => [
                    'uitype' => 5,
                    'column' => 'dateinservice',
                    'table' => 'vtiger_assets',
                    'label' => 'Date in Service',
                    'typeofdata' => 'D~M~OTH~GE~dateinservice~Date in Service',
                    'quickcreate' => 0,
                ],
                'assetstatus' => [
                    'uitype' => 15,
                    'column' => 'assetstatus',
                    'table' => 'vtiger_assets',
                    'label' => 'Status',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'picklist_values' => [
                        'In Service',
                        'Out-of-service',
                    ],
                ],
                'tagnumber' => [
                    'uitype' => 2,
                    'column' => 'tagnumber',
                    'table' => 'vtiger_assets',
                    'label' => 'Tag Number',
                ],
                'invoiceid' => [
                    'uitype' => 10,
                    'column' => 'invoiceid',
                    'table' => 'vtiger_assets',
                    'label' => 'Invoice Name',
                    'related_modules' => [
                        'Invoice',
                    ],
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
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                ],
                'assetname' => [
                    'uitype' => 1,
                    'column' => 'assetname',
                    'table' => 'vtiger_assets',
                    'label' => 'Asset Name',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'filter' => 1,
                    'entity_identifier' => 1,
                    'summaryfield' => 1,
                ],
                'account' => [
                    'uitype' => 10,
                    'column' => 'account',
                    'table' => 'vtiger_assets',
                    'label' => 'Customer Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'filter' => 1,
                    'related_modules' => [
                        'Accounts',
                    ],
                    'summaryfield' => 1,
                ],
                'contact' => [
                    'uitype' => 10,
                    'column' => 'contact',
                    'table' => 'vtiger_assets',
                    'label' => 'Contact Name',
                    'quickcreate' => 0,
                    'related_modules' => [
                        'Contacts',
                    ],
                    'summaryfield' => 0,
                ],
                'createdtime' => [
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'modifiedtime' => [
                    'uitype' => 70,
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Modified Time',
                    'presence' => 0,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 3,
                    'displaytype' => 2,
                    'masseditable' => 0,
                ],
                'modifiedby' => [
                    'uitype' => 52,
                    'column' => 'modifiedby',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Last Modified By',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Notes',
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
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_assets', null)
            ->createTable('assetsid')
            ->createColumn('asset_no', 'varchar(30) NOT NULL')
            ->createColumn('account', 'int(19)')
            ->createColumn('product', 'int(19) NOT NULL')
            ->createColumn('serialnumber', 'varchar(200)')
            ->createColumn('datesold', 'date')
            ->createColumn('dateinservice', 'date')
            ->createColumn('assetstatus', 'varchar(200) default \'In Service\'')
            ->createColumn('tagnumber', 'varchar(300) default NULL')
            ->createColumn('invoiceid', 'int(19) default NULL')
            ->createColumn('shippingmethod', 'varchar(200) default NULL')
            ->createColumn('shippingtrackingnumber', 'varchar(200) default NULL')
            ->createColumn('assetname', 'varchar(100) default NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`assetsid`)');

        $this->getTable('vtiger_assetscf', null)
            ->createTable('assetsid');
    }
}
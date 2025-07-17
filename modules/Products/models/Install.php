<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Products_Install_Model extends Core_Install_Model
{

    public static array $MANUFACTURER_PICKLIST = [
        'AltvetPet Inc.',
        'LexPon Inc.',
        'MetBeat Corp',
    ];

    public static array $USAGE_UNIT_PICKLIST = [
        'Box',
        'Carton',
        'Dozen',
        'Each',
        'Hours',
        'Impressions',
        'Lb',
        'M',
        'Pack',
        'Pages',
        'Pieces',
        'Quantity',
        'Reams',
        'Sheet',
        'Spiral Binder',
        'Sq Ft',
    ];
    public static array $CATEGORY_PICKLIST = [
        'Hardware',
        'Software',
        'CRM Applications',
    ];

    public array $registerRelatedLists = [
        ['Products', 'HelpDesk', 'HelpDesk', 'add', 'get_tickets',],
        ['Products', 'Documents', 'Documents', 'add,select', 'get_attachments',],
        ['Products', 'Quotes', 'Quotes', 'add', 'get_quotes',],
        ['Products', 'PurchaseOrder', 'Purchase Order', 'add', 'get_purchase_orders',],
        ['Products', 'SalesOrder', 'Sales Order', 'add', 'get_salesorder',],
        ['Products', 'Invoice', 'Invoice', 'add', 'get_invoices',],
        ['Products', 'PriceBooks', 'PriceBooks', 'ADD,SELECT', 'get_product_pricebooks',],
        ['Products', 'Leads', 'Leads', 'select', 'get_leads',],
        ['Products', 'Accounts', 'Accounts', 'select', 'get_accounts',],
        ['Products', 'Contacts', 'Contacts', 'select', 'get_contacts',],
        ['Products', 'Potentials', 'Potentials', 'select', 'get_opportunities',],
        ['Products', 'Products', 'Product Bundles', 'add,select', 'get_products',],
        ['Products', 'Products', 'Parent Product', '', 'get_parent_products',],
        ['Products', 'Assets', 'Assets', 'ADD', 'get_dependents_list',],
        ['Products', 'PurchaseOrder', 'PurchaseOrder', 'ADD', 'get_purchase_orders',],
        ['Products', 'Appointments', 'Appointments', '', 'get_related_list',],
        ['Products', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list',],
        ['PriceBooks', 'Products', 'Products', 'select', 'get_pricebook_products',],
        ['Accounts', 'Products', 'Products', 'select', 'get_products',],
        ['Leads', 'Products', 'Products', 'select', 'get_products',],
        ['Contacts', 'Products', 'Products', 'select', 'get_products',],
        ['Potentials', 'Products', 'Products', 'select', 'get_products',],
        ['Vendors', 'Products', 'Products', 'add,select', 'get_products',],
        ['Documents', 'Products', 'Products', '1', 'get_related_list',],
    ];

    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateRelatedList();
        $this->updateComments();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
        $this->updateComments(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_PRODUCT_INFORMATION' => [
                'productname' => [
                    'name' => 'productname',
                    'uitype' => 2,
                    'column' => 'productname',
                    'table' => 'vtiger_products',
                    'label' => 'Product Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'quicksequence' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'serial_no' => [
                    'name' => 'serial_no',
                    'uitype' => 1,
                    'column' => 'serialno',
                    'table' => 'vtiger_products',
                    'label' => 'Serial No',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
                'discontinued' => [
                    'name' => 'discontinued',
                    'uitype' => 56,
                    'column' => 'discontinued',
                    'table' => 'vtiger_products',
                    'label' => 'Product Active',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'defaultvalue' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 2,
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'manufacturer' => [
                    'name' => 'manufacturer',
                    'uitype' => 15,
                    'column' => 'manufacturer',
                    'table' => 'vtiger_products',
                    'label' => 'Manufacturer',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => self::$MANUFACTURER_PICKLIST,
                ],
                'productcategory' => [
                    'name' => 'productcategory',
                    'uitype' => 15,
                    'column' => 'productcategory',
                    'table' => 'vtiger_products',
                    'label' => 'Product Category',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 4,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => self::$CATEGORY_PICKLIST,
                    'headerfield' => 1,
                    'headerfieldsequence' => 3,
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'website' => [
                    'name' => 'website',
                    'uitype' => 17,
                    'column' => 'website',
                    'table' => 'vtiger_products',
                    'label' => 'Website',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'vendor_id' => [
                    'name' => 'vendor_id',
                    'uitype' => 75,
                    'column' => 'vendor_id',
                    'table' => 'vtiger_products',
                    'label' => 'Vendor Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 2,
                    'quicksequence' => 8,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'vendor_part_no' => [
                    'name' => 'vendor_part_no',
                    'uitype' => 1,
                    'column' => 'vendor_part_no',
                    'table' => 'vtiger_products',
                    'label' => 'Vendor PartNo',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 9,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'sales_start_date' => [
                    'name' => 'sales_start_date',
                    'uitype' => 5,
                    'column' => 'sales_start_date',
                    'table' => 'vtiger_products',
                    'label' => 'Sales Start Date',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'sales_end_date' => [
                    'name' => 'sales_end_date',
                    'uitype' => 5,
                    'column' => 'sales_end_date',
                    'table' => 'vtiger_products',
                    'label' => 'Sales End Date',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'D~O~OTH~GE~sales_start_date~Sales Start Date',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'start_date' => [
                    'name' => 'start_date',
                    'uitype' => 5,
                    'column' => 'start_date',
                    'table' => 'vtiger_products',
                    'label' => 'Support Start Date',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'expiry_date' => [
                    'name' => 'expiry_date',
                    'uitype' => 5,
                    'column' => 'expiry_date',
                    'table' => 'vtiger_products',
                    'label' => 'Support Expiry Date',
                    'readonly' => 1,
                    'presence' => 1,
                    'typeofdata' => 'D~O~OTH~GE~start_date~Start Date',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_PRICING_INFORMATION' => [
                'unit_price' => [
                    'name' => 'unit_price',
                    'uitype' => 72,
                    'column' => 'unit_price',
                    'table' => 'vtiger_products',
                    'label' => 'Unit Price',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 2,
                    'quicksequence' => 5,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 5,
                    'headerfield' => 1,
                    'headerfieldsequence' => 5,
                ],
                'purchase_cost' => [
                    'name' => 'purchase_cost',
                    'uitype' => 71,
                    'column' => 'purchase_cost',
                    'table' => 'vtiger_products',
                    'label' => 'Purchase Cost',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'taxclass' => [
                    'name' => 'taxclass',
                    'uitype' => 83,
                    'column' => 'taxclass',
                    'table' => 'vtiger_products',
                    'label' => 'Taxes',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'commissionrate' => [
                    'name' => 'commissionrate',
                    'uitype' => 9,
                    'column' => 'commissionrate',
                    'table' => 'vtiger_products',
                    'label' => 'Commission Rate',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
            ],
            'LBL_STOCK_INFORMATION' => [
                'qtyinstock' => [
                    'name' => 'qtyinstock',
                    'uitype' => 1,
                    'column' => 'qtyinstock',
                    'table' => 'vtiger_products',
                    'label' => 'Qty In Stock',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'NN~O',
                    'quickcreate' => 0,
                    'quicksequence' => 6,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
                    'headerfield' => 1,
                    'headerfieldsequence' => 4,
                ],
                'usageunit' => [
                    'name' => 'usageunit',
                    'uitype' => 15,
                    'column' => 'usageunit',
                    'table' => 'vtiger_products',
                    'label' => 'Usage Unit',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 6,
                    'picklist_values' => self::$USAGE_UNIT_PICKLIST,
                ],
                'qtyindemand' => [
                    'name' => 'qtyindemand',
                    'uitype' => 1,
                    'column' => 'qtyindemand',
                    'table' => 'vtiger_products',
                    'label' => 'Qty In Demand',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 8,
                ],
                'qty_per_unit' => [
                    'name' => 'qty_per_unit',
                    'uitype' => 1,
                    'column' => 'qty_per_unit',
                    'table' => 'vtiger_products',
                    'label' => 'Qty/Unit',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'reorderlevel' => [
                    'name' => 'reorderlevel',
                    'uitype' => 1,
                    'column' => 'reorderlevel',
                    'table' => 'vtiger_products',
                    'label' => 'Reorder Level',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
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
                    'label' => 'Handler',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'quicksequence' => 7,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 9,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_IMAGE_INFORMATION' => [
                'imagename' => [
                    'name' => 'imagename',
                    'uitype' => 69,
                    'column' => 'imagename',
                    'table' => 'vtiger_products',
                    'label' => 'Product Image',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
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
                    'quickcreate' => 2,
                    'quicksequence' => 10,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'product_no' => [
                    'name' => 'product_no',
                    'uitype' => 4,
                    'column' => 'product_no',
                    'table' => 'vtiger_products',
                    'label' => 'Product No',
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

    public function getTables(): array
    {
        return [
            'vtiger_products',
            'vtiger_productcf',
            'vtiger_productcurrencyrel',
            'vtiger_producttaxrel',
        ];
    }

    /**
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->disableForeignKeyCheck();
        $this->getTable('vtiger_products', null)
            ->createTable('productid')
            ->createColumn('product_no', 'varchar(100) NOT NULL')
            ->createColumn('productname', 'varchar(255) DEFAULT NULL')
            ->createColumn('productcode', 'varchar(40) DEFAULT NULL')
            ->createColumn('productcategory', 'varchar(200) DEFAULT NULL')
            ->createColumn('manufacturer', 'varchar(200) DEFAULT NULL')
            ->createColumn('qty_per_unit', 'decimal(11,2) DEFAULT 0.00')
            ->createColumn('unit_price', self::$COLUMN_DECIMAL)
            ->createColumn('weight', 'decimal(11,3) DEFAULT NULL')
            ->createColumn('pack_size', 'int(11) DEFAULT NULL')
            ->createColumn('sales_start_date', 'date DEFAULT NULL')
            ->createColumn('sales_end_date', 'date DEFAULT NULL')
            ->createColumn('start_date', 'date DEFAULT NULL')
            ->createColumn('expiry_date', 'date DEFAULT NULL')
            ->createColumn('cost_factor', 'int(11) DEFAULT NULL')
            ->createColumn('commissionrate', 'decimal(7,3) DEFAULT NULL')
            ->createColumn('commissionmethod', 'varchar(50) DEFAULT NULL')
            ->createColumn('discontinued', 'int(1) NOT NULL DEFAULT 0')
            ->createColumn('usageunit', 'varchar(200) DEFAULT NULL')
            ->createColumn('reorderlevel', 'int(11) DEFAULT NULL')
            ->createColumn('website', 'varchar(100) DEFAULT NULL')
            ->createColumn('taxclass', 'varchar(200) DEFAULT NULL')
            ->createColumn('vendor_part_no', 'varchar(200) DEFAULT NULL')
            ->createColumn('serialno', 'varchar(200) DEFAULT NULL')
            ->createColumn('qtyinstock', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('qtyindemand', 'int(11) DEFAULT NULL')
            ->createColumn('vendor_id', 'int(11) DEFAULT NULL')
            ->createColumn('imagename', 'text DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('is_subproducts_viewable', 'int(1) DEFAULT 1')
            ->createColumn('purchase_cost', self::$COLUMN_DECIMAL)
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`productid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_products` FOREIGN KEY IF NOT EXISTS (`productid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_productcf', null)
            ->createTable('productid')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`productid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_productcf` FOREIGN KEY IF NOT EXISTS (`productid`) REFERENCES `vtiger_products` (`productid`) ON DELETE CASCADE');

        $this->getTable('vtiger_productcurrencyrel', null)
            ->createTable('productid')
            ->createColumn('currencyid', 'int(11) NOT NULL')
            ->createColumn('converted_price', self::$COLUMN_DECIMAL)
            ->createColumn('actual_price', self::$COLUMN_DECIMAL);

        $this->getTable('vtiger_producttaxrel', null)
            ->createTable('productid')
            ->createColumn('taxid', 'int(3) NOT NULL')
            ->createColumn('taxpercentage', 'decimal(7,3) DEFAULT NULL')
            ->createColumn('regions', 'text DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `producttaxrel_productid_idx` (`productid`)')
            ->createKey('KEY IF NOT EXISTS `producttaxrel_taxid_idx` (`taxid`)')
            ->createKey('CONSTRAINT `fk_crmid_vtiger_producttaxrel` FOREIGN KEY IF NOT EXISTS (`productid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');
        
        $this->createPicklistTable('vtiger_manufacturer', '', 'manufacturer');
        $this->createPicklistTable('vtiger_productcategory', '', 'productcategory');
        $this->createPicklistTable('vtiger_usageunit', '', 'usageunit');
    }
}
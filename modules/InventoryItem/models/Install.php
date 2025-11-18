<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_Install_Model extends Core_Install_Model
{
    protected array $defaultSupportedModules = ['Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice'];

    protected array $modifiers = [
        'Quotes' => [
            'DetailView'          => ['InventoryItem_DetailView_Modifier'],
            'EditView'            => ['InventoryItem_EditView_Modifier'],
            'QuickCreateAjaxView' => ['InventoryItem_QuickCreateAjaxView_Modifier'],
        ],
        'PurchaseOrder' => [
            'DetailView'          => ['InventoryItem_DetailView_Modifier'],
            'EditView'            => ['InventoryItem_EditView_Modifier'],
        ],
        'SalesOrder' => [
            'DetailView'          => ['InventoryItem_DetailView_Modifier'],
            'EditView'            => ['InventoryItem_EditView_Modifier'],
            'QuickCreateAjaxView' => ['InventoryItem_QuickCreateAjaxView_Modifier'],
        ],
        'Invoice' => [
            'DetailView'          => ['InventoryItem_DetailView_Modifier'],
            'EditView'            => ['InventoryItem_EditView_Modifier'],
            'QuickCreateAjaxView' => ['InventoryItem_QuickCreateAjaxView_Modifier'],
        ],
    ];

    public function addCustomLinks(): void
    {
        $this->updateHistory();
        $this->updateComments();
        $this->registerModifiers();
    }

    public function deleteCustomLinks(): void
    {
        $this->deregisterModifiers();
    }

    public function getBlocks(): array
    {
        return [
            'LBL_INVENTORYITEM_INFORMATION' => [
                'item_text'                    => [
                    'name'              => 'item_text',
                    'uitype'            => 1,
                    'column'            => 'item_text',
                    'table'             => 'df_inventoryitem',
                    'generatedtype'     => 1,
                    'label'             => 'Item text',
                    'readonly'          => 1,
                    'presence'          => 0,
                    'maximumlength'     => 100,
                    'typeofdata'        => 'V~O',
                    'quickcreate'       => 0,
                    'displaytype'       => 1,
                    'masseditable'      => 0,
                    'summaryfield'      => 1,
                    'entity_identifier' => 1,
                    'filter'            => 1,
                    'filter_sequence'   => 1,
                ],
                'parentid'                     => [
                    'name'            => 'parentid',
                    'uitype'          => 10,
                    'column'          => 'parentid',
                    'table'           => 'df_inventoryitem',
                    'generatedtype'   => 1,
                    'label'           => 'Parent',
                    'readonly'        => 1,
                    'presence'        => 0,
                    'maximumlength'   => 100,
                    'typeofdata'      => 'I~O',
                    'quickcreate'     => 1,
                    'displaytype'     => 1,
                    'masseditable'    => 0,
                    'summaryfield'    => 0,
                    'related_modules' => InventoryItem_Utils_Helper::getInventoryItemModules(),
                    'columntype'      => 'INT(11)',
                    'filter'          => 1,
                    'filter_sequence' => 2,
                ],
                'productid'                    => [
                    'name'            => 'productid',
                    'uitype'          => 10,
                    'column'          => 'productid',
                    'table'           => 'df_inventoryitem',
                    'generatedtype'   => 1,
                    'label'           => 'Product Name',
                    'readonly'        => 1,
                    'presence'        => 0,
                    'maximumlength'   => 100,
                    'typeofdata'      => 'I~O',
                    'quickcreate'     => 1,
                    'displaytype'     => 1,
                    'masseditable'    => 0,
                    'summaryfield'    => 1,
                    'related_modules' => [
                        'Products',
                        'Services'
                    ],
                    'columntype'      => 'INT(11)',
                ],
                'parentitemid'                 => [
                    'name'            => 'parentitemid',
                    'uitype'          => 10,
                    'column'          => 'parentitemid',
                    'table'           => 'df_inventoryitem',
                    'generatedtype'   => 1,
                    'label'           => 'Parent Inventory Item',
                    'readonly'        => 1,
                    'presence'        => 0,
                    'maximumlength'   => 100,
                    'typeofdata'      => 'I~O',
                    'quickcreate'     => 0,
                    'displaytype'     => 1,
                    'masseditable'    => 0,
                    'summaryfield'    => 0,
                    'related_modules' => [
                        'InventoryItem'
                    ],
                    'columntype'      => 'INT(11)',
                ],
                'unit'                         => [
                    'name'          => 'unit',
                    'uitype'        => 1,
                    'column'        => 'unit',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Unit',
                    'readonly'      => 1,
                    'presence'      => 2,
                    'maximumlength' => 100,
                    'typeofdata'    => 'V~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'quantity'                     => [
                    'name'          => 'quantity',
                    'uitype'        => 7,
                    'column'        => 'quantity',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Quantity',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 1,
                ],
                'price'                        => [
                    'name'          => 'price',
                    'uitype'        => 71,
                    'column'        => 'price',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Price',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'NN~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'subtotal'                     => [
                    'name'          => 'subtotal',
                    'uitype'        => 71,
                    'column'        => 'subtotal',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Subtotal',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'discount_type'                => [
                    'name'            => 'discount_type',
                    'uitype'          => 16,
                    'column'          => 'discount_type',
                    'table'           => 'df_inventoryitem',
                    'generatedtype'   => 1,
                    'label'           => 'Discount Type',
                    'readonly'        => 1,
                    'presence'        => 2,
                    'maximumlength'   => 100,
                    'typeofdata'      => 'V~O',
                    'quickcreate'     => 1,
                    'displaytype'     => 1,
                    'masseditable'    => 1,
                    'summaryfield'    => 0,
                    'picklist_values' => [
                        'Percentage',
                        'Direct',
                        'Discount per Unit',
                    ],
                ],
                'discount'                     => [
                    'name'          => 'discount',
                    'uitype'        => 7,
                    'column'        => 'discount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Discount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 0,
                    'summaryfield'  => 0,
                ],
                'overall_discount'             => [
                    'name'          => 'overall_discount',
                    'uitype'        => 7,
                    'column'        => 'overall_discount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Overall Discount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 0,
                    'summaryfield'  => 0,
                ],
                'discount_amount'              => [
                    'name'          => 'discount_amount',
                    'uitype'        => 71,
                    'column'        => 'discount_amount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Discount Amount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'overall_discount_amount'      => [
                    'name'          => 'overall_discount_amount',
                    'uitype'        => 71,
                    'column'        => 'overall_discount_amount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Overall Discount Amount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'price_after_discount'         => [
                    'name'          => 'price_after_discount',
                    'uitype'        => 71,
                    'column'        => 'price_after_discount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Price After Discount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'price_after_overall_discount' => [
                    'name'          => 'price_after_overall_discount',
                    'uitype'        => 71,
                    'column'        => 'price_after_overall_discount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Price After Overall Discount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'discounts_amount'             => [
                    'name'          => 'discounts_amount',
                    'uitype'        => 71,
                    'column'        => 'discounts_amount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Discounts Amount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'tax'                          => [
                    'name'          => 'tax',
                    'uitype'        => 7,
                    'column'        => 'tax',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Tax',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 0,
                    'summaryfield'  => 0,
                ],
                'tax_amount'                   => [
                    'name'          => 'tax_amount',
                    'uitype'        => 71,
                    'column'        => 'tax_amount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Tax Amount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'price_total'                  => [
                    'name'          => 'price_total',
                    'uitype'        => 71,
                    'column'        => 'price_total',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Price Total',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'purchase_cost'                => [
                    'name'          => 'purchase_cost',
                    'uitype'        => 71,
                    'column'        => 'purchase_cost',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Purchase Cost',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'margin'                       => [
                    'name'          => 'margin',
                    'uitype'        => 7,
                    'column'        => 'margin',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Margin',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'margin_amount'                => [
                    'name'          => 'margin_amount',
                    'uitype'        => 71,
                    'column'        => 'margin_amount',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Margin Amount',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
                'sequence'                     => [
                    'name'          => 'sequence',
                    'uitype'        => 7,
                    'column'        => 'sequence',
                    'table'         => 'df_inventoryitem',
                    'generatedtype' => 1,
                    'label'         => 'Sequence',
                    'readonly'      => 1,
                    'presence'      => 0,
                    'maximumlength' => 100,
                    'typeofdata'    => 'N~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 0,
                    'summaryfield'  => 0,
                ],
                'pricebookid'                  => [
                    'name'         => 'pricebookid',
                    'uitype'       => 73,
                    'column'       => 'pricebookid',
                    'table'        => 'df_inventoryitem',
                    'label'        => 'Price Book',
                    'readonly'     => 0,
                    'presence'     => 0,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 1,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'assigned_user_id' => [
                    'name'            => 'assigned_user_id',
                    'uitype'          => 53,
                    'column'          => 'assigned_user_id',
                    'table'           => 'vtiger_crmentity',
                    'generatedtype'   => 1,
                    'label'           => 'Assigned To',
                    'readonly'        => 1,
                    'presence'        => 0,
                    'maximumlength'   => 100,
                    'typeofdata'      => 'V~O',
                    'quickcreate'     => 0,
                    'quicksequence'   => 8,
                    'displaytype'     => 1,
                    'masseditable'    => 1,
                    'summaryfield'    => 1,
                ],
            ],
            'LBL_CUSTOM_INFORMATION'        => [],
            'LBL_DESCRIPTION_INFORMATION'   => [
                'description' => [
                    'name'          => 'description',
                    'uitype'        => '19',
                    'column'        => 'description',
                    'table'         => 'vtiger_crmentity',
                    'generatedtype' => 1,
                    'label'         => 'Description',
                    'readonly'      => 1,
                    'presence'      => 2,
                    'maximumlength' => 100,
                    'typeofdata'    => 'V~O',
                    'quickcreate'   => 1,
                    'displaytype'   => 1,
                    'masseditable'  => 1,
                    'summaryfield'  => 0,
                ],
            ],
        ];
    }

    public function getTables(): array
    {
        return ['df_inventoryitemcolumns', 'df_inventoryitem_itemmodules', 'df_inventoryitem_quantitydecimals', 'df_inventoryitem_modules'];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('df_inventoryitem', null)
            ->createTable('inventoryitemid')
            ->createColumn('item_text', 'varchar(255) DEFAULT NULL')
            ->createColumn('productid', 'int(19) DEFAULT NULL')
            ->createColumn('quantity', self::$COLUMN_DECIMAL)
            ->createColumn('price', self::$COLUMN_DECIMAL)
            ->createColumn('subtotal', self::$COLUMN_DECIMAL)
            ->createColumn('discount', self::$COLUMN_DECIMAL)
            ->createColumn('discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('price_after_discount', self::$COLUMN_DECIMAL)
            ->createColumn('overall_discount', self::$COLUMN_DECIMAL)
            ->createColumn('overall_discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('price_after_overall_discount', self::$COLUMN_DECIMAL)
            ->createColumn('tax_amount', self::$COLUMN_DECIMAL)
            ->createColumn('price_total', self::$COLUMN_DECIMAL)
            ->createColumn('purchase_cost', self::$COLUMN_DECIMAL)
            ->createColumn('margin', self::$COLUMN_DECIMAL)
            ->createColumn('margin_amount', self::$COLUMN_DECIMAL)
            ->createColumn('unit', 'varchar(255) DEFAULT NULL')
            ->createColumn('parentitemid', 'int(19) DEFAULT NULL')
            ->createColumn('sequence', 'int(19) DEFAULT 1')
            ->createColumn('parentid', 'int(19) DEFAULT NULL')
            ->createColumn('tax', self::$COLUMN_DECIMAL)
            ->createColumn('discount_type', 'varchar(255) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`inventoryitemid`)')
            ->createKey('INDEX IF NOT EXISTS `inventoryitem_productid_idx` (`productid`)')
            ->createKey('INDEX IF NOT EXISTS `inventoryitem_parentitemid_idx` (`parentitemid`)')
            ->createKey('INDEX IF NOT EXISTS `inventoryitem_parentid_idx` (`parentid`)')
            ->createKey('CONSTRAINT `fk_1_df_inventoryitem` FOREIGN KEY IF NOT EXISTS (`inventoryitemid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('df_inventoryitemcf', null)
            ->createTable('inventoryitemid')
            ->createKey('CONSTRAINT `fk_1_df_inventoryitemcf` FOREIGN KEY IF NOT EXISTS (`inventoryitemid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('df_inventoryitemcolumns', null)
            ->createTable('tabid')
            ->createColumn('columnslist', 'varchar(500) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tabid`)');

        $this->getTable('df_inventoryitem_itemmodules', null)
            ->createTable('tabid')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tabid`)')
            ->createKey('CONSTRAINT `fk_1_df_inventoryitem_itemmodules` FOREIGN KEY IF NOT EXISTS (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE');

        $this->getTable('df_inventoryitem_quantitydecimals', null)
            ->createTable('field', 'varchar(255) NOT NULL')
            ->createColumn('decimals', 'int(19) NOT NULL DEFAULT 0')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`field`)');

        $this->getTable('df_inventoryitem_modules', null)
            ->createTable('tabid')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tabid`)')
            ->createKey('CONSTRAINT `fk_1_df_inventoryitem_modules` FOREIGN KEY IF NOT EXISTS (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE');
    }

    public function postInstall(): void
    {
        parent::postInstall();
        $this->setupSupportedModules();
    }

    protected function setupSupportedModules()
    {
        $db = PearDatabase::getInstance();
        $res = $db->query('SELECT * FROM df_inventoryitem_modules');

        if ($db->num_rows($res)) {
            return;
        }

        foreach ($this->defaultSupportedModules as $moduleName) {
            InventoryItem_Utils_Helper::registerInventoryModule($moduleName);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function registerModifiers(): void
    {
        foreach ($this->modifiers as $moduleName => $modifiersData) {
            foreach ($modifiersData as $modifiable => $classNames) {
                foreach ($classNames as $className) {
                    Core_Modifiers_Model::registerModifier($moduleName, 'InventoryItem', $modifiable, $className);
                }
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function deregisterModifiers(): void
    {
        foreach ($this->modifiers as $moduleName => $modifiersData) {
            Core_Modifiers_Model::deregisterModifier($moduleName, 'InventoryItem');
        }
    }
}
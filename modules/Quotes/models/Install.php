<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Quotes_Install_Model extends Core_Install_Model
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
            'LBL_QUOTE_INFORMATION' => [

                    'subject' => [
                            'name' => 'subject',
                            'uitype' => 2,
                            'column' => 'subject',
                            'table' => 'vtiger_quotes',
                            'label' => 'Subject',
                            'readonly' => 1,
                            'presence' => 0,
                            'typeofdata' => 'V~M',
                            'quickcreate' => 1,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 1,
                            'entity_identifier' => 1,
                        ],
                    'potential_id' => [
                            'name' => 'potential_id',
                            'uitype' => 76,
                            'column' => 'potentialid',
                            'table' => 'vtiger_quotes',
                            'label' => 'Potential Name',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'I~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 1,
                        ],
                    'quotestage' => [
                            'name' => 'quotestage',
                            'uitype' => 15,
                            'column' => 'quotestage',
                            'table' => 'vtiger_quotes',
                            'label' => 'Quote Stage',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~M',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                            'headerfield' => 1,
                            'picklist_values' => [
                                    'Created',
                                    'Delivered',
                                    'Reviewed',
                                    'Accepted',
                                    'Rejected',
                                ],
                        ],
                    'validtill' => [
                            'name' => 'validtill',
                            'uitype' => 5,
                            'column' => 'validtill',
                            'table' => 'vtiger_quotes',
                            'label' => 'Valid Till',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'D~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'contact_id' => [
                            'name' => 'contact_id',
                            'uitype' => 57,
                            'column' => 'contactid',
                            'table' => 'vtiger_quotes',
                            'label' => 'Contact Name',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
	                        'headerfield' => 1,
                        ],
                    'carrier' => [
                            'name' => 'carrier',
                            'uitype' => 15,
                            'column' => 'carrier',
                            'table' => 'vtiger_quotes',
                            'label' => 'Carrier',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                            'picklist_values' => [
                                    'FedEx',
                                    'UPS',
                                    'USPS',
                                    'DHL',
                                    'BlueDart',
                                ],
                        ],
                    'subtotal' => [
                            'name' => 'subtotal',
                            'uitype' => 72,
                            'column' => 'subtotal',
                            'table' => 'vtiger_quotes',
                            'label' => 'Sub Total',
                            'readonly' => 1,
                            'presence' => 0,
                            'typeofdata' => 'N~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'shipping' => [
                            'name' => 'shipping',
                            'uitype' => 1,
                            'column' => 'shipping',
                            'table' => 'vtiger_quotes',
                            'label' => 'Shipping',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'assigned_user_id1' => [
                            'name' => 'assigned_user_id1',
                            'uitype' => 77,
                            'column' => 'inventorymanager',
                            'table' => 'vtiger_quotes',
                            'label' => 'Inventory Manager',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'I~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'price_total'                  =>
                        [
                            'name'         => 'price_total',
                            'uitype'       => 72,
                            'column'       => 'price_total',
                            'table'        => 'vtiger_quotes',
                            'label'        => 'Total',
                            'readonly'     => 1,
                            'presence'     => 0,
                            'typeofdata'   => 'N~O',
                            'quickcreate'  => 3,
                            'displaytype'  => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                            'headerfield' => 1,
                        ],
                    'hdnTaxType'                   =>
                        [
                            'name'            => 'hdnTaxType',
                            'uitype'          => 16,
                            'column'          => 'taxtype',
                            'table'           => 'vtiger_quotes',
                            'label'           => 'Tax Type',
                            'readonly'        => 1,
                            'presence'        => 2,
                            'typeofdata'      => 'V~O',
                            'quickcreate'     => 3,
                            'displaytype'     => 3,
                            'masseditable'    => 1,
                            'summaryfield'    => 0,
                            'picklist_values' =>
                                [
                                ],
                        ],
                    'account_id' => [
                            'name' => 'account_id',
                            'uitype' => 73,
                            'column' => 'accountid',
                            'table' => 'vtiger_quotes',
                            'label' => 'Account Name',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'I~M',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                            'headerfield' => 1,
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
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 1,
                        ],
                    'conversion_rate' =>
                        [
                            'name' => 'conversion_rate',
                            'uitype' => 1,
                            'column' => 'conversion_rate',
                            'table' => 'vtiger_quotes',
                            'label' => 'Conversion Rate',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'N~O',
                            'quickcreate' => 3,
                            'displaytype' => 3,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                ],
            'LBL_CUSTOM_INFORMATION' => [
                ],
            'LBL_ADDRESS_INFORMATION' => [
                    'bill_street' => [
                            'name' => 'bill_street',
                            'uitype' => 24,
                            'column' => 'bill_street',
                            'table' => 'vtiger_quotesbillads',
                            'label' => 'Billing Street',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~M',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'ship_street' => [
                            'name' => 'ship_street',
                            'uitype' => 24,
                            'column' => 'ship_street',
                            'table' => 'vtiger_quotesshipads',
                            'label' => 'Shipping Street',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'bill_city' => [
                            'name' => 'bill_city',
                            'uitype' => 1,
                            'column' => 'bill_city',
                            'table' => 'vtiger_quotesbillads',
                            'label' => 'Billing City',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'ship_city' => [
                            'name' => 'ship_city',
                            'uitype' => 1,
                            'column' => 'ship_city',
                            'table' => 'vtiger_quotesshipads',
                            'label' => 'Shipping City',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'bill_state' => [
                            'name' => 'bill_state',
                            'uitype' => 1,
                            'column' => 'bill_state',
                            'table' => 'vtiger_quotesbillads',
                            'label' => 'Billing State',
                            'readonly' => 1,
                            'presence' => 2,
                            'typeofdata' => 'V~O',
                            'quickcreate' => 3,
                            'displaytype' => 1,
                            'masseditable' => 1,
                            'summaryfield' => 0,
                        ],
                    'ship_state' => [
                        'name' => 'ship_state',
                        'uitype' => 1,
                        'column' => 'ship_state',
                        'table' => 'vtiger_quotesshipads',
                        'label' => 'Shipping State',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 3,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'bill_code' => [
                        'name' => 'bill_code',
                        'uitype' => 1,
                        'column' => 'bill_code',
                        'table' => 'vtiger_quotesbillads',
                        'label' => 'Billing Code',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 3,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'ship_code' => [
                        'name' => 'ship_code',
                        'uitype' => 1,
                        'column' => 'ship_code',
                        'table' => 'vtiger_quotesshipads',
                        'label' => 'Shipping Code',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 3,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'bill_country_id' => [
                        'name' => 'bill_country_id',
                        'uitype' => 18,
                        'column' => 'bill_country_id',
                        'table' => 'vtiger_quotesbillads',
                        'label' => 'Billing Country',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 3,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'ship_country_id' => [
                        'name' => 'ship_country_id',
                        'uitype' => 18,
                        'column' => 'ship_country_id',
                        'table' => 'vtiger_quotesshipads',
                        'label' => 'Shipping Country',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 3,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'bill_pobox' => [
                        'name' => 'bill_pobox',
                        'uitype' => 1,
                        'column' => 'bill_pobox',
                        'table' => 'vtiger_quotesbillads',
                        'label' => 'Billing Po Box',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 3,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'ship_pobox' => [
                        'name' => 'ship_pobox',
                        'uitype' => 1,
                        'column' => 'ship_pobox',
                        'table' => 'vtiger_quotesshipads',
                        'label' => 'Shipping Po Box',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'V~O',
                        'quickcreate' => 3,
                        'displaytype' => 1,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                ],
            'LBL_TERMS_INFORMATION' => [
                'terms_conditions' => [
                    'name' => 'terms_conditions',
                    'uitype' => 19,
                    'column' => 'terms_conditions',
                    'table' => 'vtiger_quotes',
                    'label' => 'Terms & Conditions',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
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
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_ITEM_DETAILS' => [
                'discount_amount' => [
                    'name' => 'discount_amount',
                    'uitype' => 72,
                    'column' => 'discount_amount',
                    'table' => 'vtiger_quotes',
                    'label' => 'Discount Amount',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'adjustment'         => [
                    'name'         => 'adjustment',
                    'uitype'       => 72,
                    'column'       => 'adjustment',
                    'table'        => 'vtiger_quotes',
                    'label'        => 'Adjustment',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'NN~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'currency_id'        => [
                    'name'         => 'currency_id',
                    'uitype'       => 117,
                    'column'       => 'currency_id',
                    'table'        => 'vtiger_quotes',
                    'label'        => 'Currency',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 1,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'region_id'          => [
                    'name'            => 'region_id',
                    'uitype'          => 29,
                    'column'          => 'region_id',
                    'table'           => 'vtiger_quotes',
                    'label'           => 'Tax Region',
                    'readonly'        => 0,
                    'presence'        => 0,
                    'typeofdata'      => 'N~O',
                    'quickcreate'     => 1,
                    'displaytype'     => 1,
                    'masseditable'    => 0,
                    'summaryfield'    => 0,
                    'picklist_values' => [],
                ],
                'pricebookid'        => [
                    'name'         => 'pricebookid',
                    'uitype'       => 10,
                    'column'       => 'pricebookid',
                    'table'        => 'vtiger_quotes',
                    'label'        => 'Price Book',
                    'readonly'     => 0,
                    'presence'     => 0,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 1,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'related_modules' => [
                        'PriceBooks',
                    ],
                ],
                'grand_total' => [
                    'name' => 'grand_total',
                    'uitype' => 72,
                    'column' => 'grand_total',
                    'table' => 'vtiger_quotes',
                    'label' => 'Grand Total',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'margin_amount' => [
                    'name'          => 'margin_amount',
                    'uitype'        => 71,
                    'column'        => 'margin_amount',
                    'table'         => 'vtiger_quotes',
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
                'overall_discount_amount' => [
                    'name'          => 'overall_discount_amount',
                    'uitype'        => 71,
                    'column'        => 'overall_discount_amount',
                    'table'         => 'vtiger_quotes',
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
                'price_after_discount' => [
                    'name'          => 'price_after_discount',
                    'uitype'        => 71,
                    'column'        => 'price_after_discount',
                    'table'         => 'vtiger_quotes',
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
                    'table'         => 'vtiger_quotes',
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
                'tax_amount' => [
                    'name'          => 'tax_amount',
                    'uitype'        => 71,
                    'column'        => 'tax_amount',
                    'table'         => 'vtiger_quotes',
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
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'quote_no' => [
                    'name' => 'quote_no',
                    'uitype' => 4,
                    'column' => 'quote_no',
                    'table' => 'vtiger_quotes',
                    'label' => 'Quote No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                ],
            ]
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
        $this->getTable('vtiger_quotes', null)
            ->createTable('quoteid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('subject', 'varchar(100) DEFAULT NULL')
            ->createColumn('potentialid', 'int(19) DEFAULT NULL')
            ->createColumn('quotestage', 'varchar(200) DEFAULT NULL')
            ->createColumn('validtill', 'date DEFAULT NULL')
            ->createColumn('contactid', 'int(19) DEFAULT NULL')
            ->createColumn('quote_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('subtotal', self::$COLUMN_DECIMAL)
            ->createColumn('carrier', 'varchar(200) DEFAULT NULL')
            ->createColumn('shipping', 'varchar(100) DEFAULT NULL')
            ->createColumn('inventorymanager', 'int(19) DEFAULT NULL')
            ->createColumn('type', 'varchar(100) DEFAULT NULL')
            ->createColumn('adjustment', self::$COLUMN_DECIMAL)
            ->createColumn('price_total', self::$COLUMN_DECIMAL)
            ->createColumn('taxtype', 'varchar(25) DEFAULT NULL')
            ->createColumn('discount_percent', self::$COLUMN_DECIMAL)
            ->createColumn('discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('s_h_amount', self::$COLUMN_DECIMAL)
            ->createColumn('accountid', 'int(19) DEFAULT NULL')
            ->createColumn('terms_conditions', 'text DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT \'1\'')
            ->createColumn('conversion_rate', 'decimal(10,3) NOT NULL DEFAULT \'1.000\'')
            ->createColumn('compound_taxes_info', 'text DEFAULT NULL')
            ->createColumn('price_after_discount', self::$COLUMN_DECIMAL)
            ->createColumn('overall_discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('price_after_overall_discount', self::$COLUMN_DECIMAL)
            ->createColumn('s_h_percent', self::$COLUMN_DECIMAL)
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('region_id', 'int(19) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createColumn('tax_amount', self::$COLUMN_DECIMAL)
            ->createColumn('grand_total', self::$COLUMN_DECIMAL)
            ->createColumn('margin_amount', self::$COLUMN_DECIMAL)
            ->createKey('PRIMARY KEY IF NOT EXISTS (`quoteid`)')
            ->createKey('KEY IF NOT EXISTS `quote_quotestage_idx` (`quotestage`)')
            ->createKey('KEY IF NOT EXISTS `quotes_potentialid_idx` (`potentialid`)')
            ->createKey('KEY IF NOT EXISTS `quotes_contactid_idx` (`contactid`)')
            ->createKey('CONSTRAINT `fk_3_vtiger_quotes` FOREIGN KEY IF NOT EXISTS (`potentialid`) REFERENCES `vtiger_potential` (`potentialid`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_crmid_vtiger_quotes` FOREIGN KEY IF NOT EXISTS (`quoteid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_quotesbillads', null)
            ->createTable('quotebilladdressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('bill_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('bill_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('bill_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`quotebilladdressid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_quotesbillads` FOREIGN KEY IF NOT EXISTS (`quotebilladdressid`) REFERENCES `vtiger_quotes` (`quoteid`) ON DELETE CASCADE');

        $this->getTable('vtiger_quotesshipads', null)
            ->createTable('quoteshipaddressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('ship_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('ship_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('ship_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`quoteshipaddressid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_quotesshipads` FOREIGN KEY IF NOT EXISTS (`quoteshipaddressid`) REFERENCES `vtiger_quotes` (`quoteid`) ON DELETE CASCADE');

        $this->createPicklistTable('vtiger_quotestage', '', 'quotestage');
        $this->createPicklistTable('vtiger_carrier', '', 'carrier');
    }
}
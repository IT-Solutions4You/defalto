<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class SalesOrder_Install_Model extends Core_Install_Model
{
    public array $registerRelatedLists = [
        ['Accounts', 'SalesOrder', 'Sales Order', 'add', 'get_salesorder',],
        ['Contacts', 'SalesOrder', 'Sales Order', 'add', 'get_salesorder',],
        ['Quotes', 'SalesOrder', 'Sales Order', '', 'get_salesorder',],
        ['SalesOrder', 'Invoice', 'Invoice', '', 'get_invoices',],
        ['Services', 'SalesOrder', 'Sales Order', 'ADD', 'get_salesorder',],
        ['Potentials', 'SalesOrder', 'Sales Order', 'ADD', 'get_salesorder',],
        ['SalesOrder', 'Appointments', 'Appointments', '', 'get_related_list',],
        ['SalesOrder', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list',],
        ['SalesOrder', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments',],
        ['Documents', 'SalesOrder', 'SalesOrder', '', 'get_related_list',],
        ['Products', 'SalesOrder', 'Sales Order', 'ADD', 'get_salesorder',],
        ['Documents', 'SalesOrder', 'SalesOrder', '1', 'get_related_list',],
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateRelatedList();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        $itemDetails = array_merge_recursive(self::$fieldsItemDetails, [
            'grand_total' => [
                'headerfield' => 1,
                'headerfieldsequence' => 5,
                'filter' => 1,
                'filter_sequence' => 6,
            ],
        ]);

        return [
            'LBL_ITEM_DETAILS' => $itemDetails,
            'LBL_SO_INFORMATION' => [
                'subject' => [
                    'name' => 'subject',
                    'uitype' => 2,
                    'column' => 'subject',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Subject',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                    'quickcreate' => 2,
                    'quicksequence' => 1,
                ],
                'potential_id' => [
                    'name' => 'potential_id',
                    'uitype' => 76,
                    'column' => 'potential_id',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Potential Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'account_id' => [
                    'name' => 'account_id',
                    'uitype' => 73,
                    'column' => 'account_id',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~M',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 2,
                    'filter' => 1,
                    'filter_sequence' => 3,
                    'quickcreate' => 2,
                    'quicksequence' => 3,
                    'ajaxeditable' => 0,
                ],
                'contact_id' => [
                    'name' => 'contact_id',
                    'uitype' => 57,
                    'column' => 'contact_id',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Contact Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'quickcreate' => 2,
                    'quicksequence' => 4,
                ],
                'sostatus' => [
                    'name' => 'sostatus',
                    'uitype' => 15,
                    'column' => 'sostatus',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'Created',
                        'Approved',
                        'Delivered',
                        'Cancelled',
                    ],
                    'headerfield' => 1,
                    'headerfieldsequence' => 3,
                    'filter' => 1,
                    'filter_sequence' => 4,
                    'quickcreate' => 2,
                    'quicksequence' => 2,
                ],
                'quote_id' => [
                    'name' => 'quote_id',
                    'uitype' => 78,
                    'column' => 'quote_id',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Quote Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                ],
                'orderdate' => [
                    'name' => 'orderdate',
                    'uitype' => 5,
                    'column' => 'orderdate',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Order Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'quickcreate' => 2,
                    'quicksequence' => 5,
                ],
                'duedate' => [
                    'name' => 'duedate',
                    'uitype' => 5,
                    'column' => 'duedate',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Due Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'headerfieldsequence' => 4,
                    'filter' => 1,
                    'filter_sequence' => 5,
                    'quickcreate' => 2,
                    'quicksequence' => 6,
                ],
                'carrier' => [
                    'name' => 'carrier',
                    'uitype' => 15,
                    'column' => 'carrier',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Carrier',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'quicksequence' => 7,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'picklist_values' => [
                        'FedEx',
                        'UPS',
                        'USPS',
                        'DHL',
                        'BlueDart',
                    ],
                ],
                'purchaseorder_id' => [
                    'name' => 'purchaseorder_id',
                    'uitype' => 1,
                    'column' => 'purchaseorder_id',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Purchase Order',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'related_modules' => [
                        'PurchaseOrder',
                    ],
                ],
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 7,
                    'quickcreate' => 2,
                    'quicksequence' => 8,
                ],
                'inventorymanager' => [
                    'name' => 'inventorymanager',
                    'uitype' => 77,
                    'column' => 'inventorymanager',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Inventory Manager',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'taxtype' => [
                    'name' => 'taxtype',
                    'uitype' => 16,
                    'column' => 'taxtype',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Tax Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [],
                ],
                'conversion_rate' => [
                    'name' => 'conversion_rate',
                    'uitype' => 1,
                    'column' => 'conversion_rate',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Conversion Rate',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 2,
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
                    'table' => 'vtiger_sobillads',
                    'label' => 'Billing Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'quickcreate' => 2,
                    'quicksequence' => 9,
                ],
                'ship_street' => [
                    'name' => 'ship_street',
                    'uitype' => 24,
                    'column' => 'ship_street',
                    'table' => 'vtiger_soshipads',
                    'label' => 'Shipping Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_code' => [
                    'name' => 'bill_code',
                    'uitype' => 1,
                    'column' => 'bill_code',
                    'table' => 'vtiger_sobillads',
                    'label' => 'Billing Code',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_code' => [
                    'name' => 'ship_code',
                    'uitype' => 1,
                    'column' => 'ship_code',
                    'table' => 'vtiger_soshipads',
                    'label' => 'Shipping Code',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_city' => [
                    'name' => 'bill_city',
                    'uitype' => 1,
                    'column' => 'bill_city',
                    'table' => 'vtiger_sobillads',
                    'label' => 'Billing City',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_city' => [
                    'name' => 'ship_city',
                    'uitype' => 1,
                    'column' => 'ship_city',
                    'table' => 'vtiger_soshipads',
                    'label' => 'Shipping City',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_state' => [
                    'name' => 'bill_state',
                    'uitype' => 1,
                    'column' => 'bill_state',
                    'table' => 'vtiger_sobillads',
                    'label' => 'Billing State',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_state' => [
                    'name' => 'ship_state',
                    'uitype' => 1,
                    'column' => 'ship_state',
                    'table' => 'vtiger_soshipads',
                    'label' => 'Shipping State',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_country_id' => [
                    'name' => 'bill_country_id',
                    'uitype' => 18,
                    'column' => 'bill_country_id',
                    'table' => 'vtiger_sobillads',
                    'label' => 'Billing Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_country_id' => [
                    'name' => 'ship_country_id',
                    'uitype' => 18,
                    'column' => 'ship_country_id',
                    'table' => 'vtiger_soshipads',
                    'label' => 'Shipping Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
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
                    'table' => 'vtiger_salesorder',
                    'label' => 'Terms & Conditions',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'quickcreate' => 2,
                    'quicksequence' => 10,
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
                    'summaryfield' => 1,
                ],
            ],
            'Recurring Invoice Information' => [
                'enable_recurring' => [
                    'name' => 'enable_recurring',
                    'uitype' => 56,
                    'column' => 'enable_recurring',
                    'table' => 'vtiger_salesorder',
                    'label' => 'Enable Recurring',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'recurring_frequency' => [
                    'name' => 'recurring_frequency',
                    'uitype' => 16,
                    'column' => 'recurring_frequency',
                    'table' => 'vtiger_invoice_recurring_info',
                    'label' => 'Frequency',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Daily',
                        'Weekly',
                        'Monthly',
                        'Quarterly',
                        'Half-Yearly',
                        'Yearly',
                    ],
                ],
                'start_period' => [
                    'name' => 'start_period',
                    'uitype' => 5,
                    'column' => 'start_period',
                    'table' => 'vtiger_invoice_recurring_info',
                    'label' => 'Start Period',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'end_period' => [
                    'name' => 'end_period',
                    'uitype' => 5,
                    'column' => 'end_period',
                    'table' => 'vtiger_invoice_recurring_info',
                    'label' => 'End Period',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'D~O~OTH~G~start_period~Start Period',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'payment_duration' => [
                    'name' => 'payment_duration',
                    'uitype' => 16,
                    'column' => 'payment_duration',
                    'table' => 'vtiger_invoice_recurring_info',
                    'label' => 'Payment Duration',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Net 7 days',
                        'Net 10 days',
                        'Net 14 days',
                        'Net 15 days',
                        'Net 30 days',
                        'Net 45 days',
                        'Net 60 days',
                        'Net 90 days',
                    ],
                ],
                'next_recurring_date' => [
                    'name' => 'next_recurring_date',
                    'uitype' => 5,
                    'column' => 'next_recurring_date',
                    'table' => 'vtiger_invoice_recurring_info',
                    'label' => 'Next Invoice Date',
                    'typeofdata' => 'D~O',
                    'displaytype' => 2,
                    'columntype' => 'date',
                    'quickcreate' => 3,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'salesorder_no' => [
                    'name' => 'salesorder_no',
                    'uitype' => 4,
                    'column' => 'salesorder_no',
                    'table' => 'vtiger_salesorder',
                    'label' => 'SalesOrder No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                    'headerfieldsequence' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                    'ajaxeditable' => 0,
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
     * @throws Exception
     */
    public function migrate()
    {
        $moduleName = $this->getModuleName();
        $updateFields = [
            'potentialid' => 'potential_id',
            'quoteid' => 'quote_id',
            'purchaseorder' => 'purchaseorder_id',
            'contactid' => 'contact_id',
            'total' => 'grand_total',
            'accountid' => 'account_id',
            'adjustment' => 'adjustment',
            'last_recurring_date' => 'next_recurring_date',
            'subtotal' => 'subtotal',
            'taxtype' => 'taxtype',
            's_h_amount' => 's_h_amount',
            's_h_percent' => 's_h_percent',
        ];

        CustomView_Record_Model::updateColumnNames($moduleName, $updateFields);

        $deleteFields = [
            'vtiger_purchaseorder',
            'hdnS_H_Percent',
            'hdnS_H_Amount',
            'hdnTaxType',
            'hdnSubTotal',
            'hdnGrandTotal',
            'txtAdjustment',
            'hdnDiscountPercent',
            'hdnDiscountAmount',
            'last_recurring_date',
        ];

        Vtiger_Module_Model::deleteFields($moduleName, $deleteFields);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->disableForeignKeyCheck();
        $this->getTable('vtiger_salesorder', null)
            ->createTable('salesorderid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->renameColumn('potentialid','potential_id')
            ->renameColumn('quoteid','quote_id')
            ->renameColumn('purchaseorder','purchaseorder_id')
            ->renameColumn('contactid','contact_id')
            ->renameColumn('total','grand_total')
            ->renameColumn('accountid','account_id')
            ->renameColumn('last_recurring_date','next_recurring_date')
            ->createColumn('subject', 'varchar(100) DEFAULT NULL')
            ->createColumn('potentialid', 'int(19) DEFAULT NULL')
            ->createColumn('salesorder_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('quote_id', 'int(19) DEFAULT NULL')
            ->createColumn('vendorterms', 'varchar(100) DEFAULT NULL')
            ->createColumn('contact_id', 'int(19) DEFAULT NULL')
            ->createColumn('vendorid', 'int(19) DEFAULT NULL')
            ->createColumn('duedate', 'date DEFAULT NULL')
            ->createColumn('orderdate', 'date DEFAULT NULL')
            ->createColumn('carrier', 'varchar(200) DEFAULT NULL')
            ->createColumn('type', 'varchar(100) DEFAULT NULL')
            ->createColumn('adjustment', self::$COLUMN_DECIMAL)
            ->createColumn('price_total', self::$COLUMN_DECIMAL)
            ->createColumn('subtotal', self::$COLUMN_DECIMAL)
            ->createColumn('taxtype', 'varchar(25) DEFAULT NULL')
            ->createColumn('discount_percent', self::$COLUMN_DECIMAL)
            ->createColumn('discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('s_h_amount', self::$COLUMN_DECIMAL)
            ->createColumn('account_id', 'int(19) DEFAULT NULL')
            ->createColumn('terms_conditions', 'text DEFAULT NULL')
            ->createColumn('purchaseorder_id', 'varchar(200) DEFAULT NULL')
            ->createColumn('sostatus', 'varchar(200) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT \'1\'')
            ->createColumn('conversion_rate', 'decimal(10,3) NOT NULL DEFAULT \'1.000\'')
            ->createColumn('enable_recurring', 'int(11) DEFAULT \'0\'')
            ->createColumn('compound_taxes_info', 'text DEFAULT NULL')
            ->createColumn('price_after_discount', self::$COLUMN_DECIMAL)
            ->createColumn('overall_discount', self::$COLUMN_DECIMAL)
            ->createColumn('overall_discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('price_after_overall_discount', self::$COLUMN_DECIMAL)
            ->createColumn('s_h_percent', self::$COLUMN_DECIMAL)
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('region_id', 'int(19) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createColumn('tax_amount', self::$COLUMN_DECIMAL)
            ->createColumn('grand_total', self::$COLUMN_DECIMAL)
            ->createColumn('margin_amount', self::$COLUMN_DECIMAL)
            ->createKey('PRIMARY KEY IF NOT EXISTS (`salesorderid`)')
            ->createKey('KEY IF NOT EXISTS `salesorder_vendorid_idx` (`vendorid`)')
            ->createKey('KEY IF NOT EXISTS `salesorder_contact_id_idx` (`contact_id`)')
            ->createKey('CONSTRAINT `fk_3_vtiger_salesorder` FOREIGN KEY IF NOT EXISTS (`vendorid`) REFERENCES `vtiger_vendor` (`vendorid`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_crmid_vtiger_salesorder` FOREIGN KEY IF NOT EXISTS (`salesorderid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_sobillads', null)
            ->createColumn('sobilladdressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('bill_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('bill_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('bill_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`sobilladdressid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_sobillads` FOREIGN KEY IF NOT EXISTS (`sobilladdressid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE');

        $this->getTable('vtiger_soshipads', null)
            ->createColumn('soshipaddressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('ship_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('ship_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('ship_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`soshipaddressid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_soshipads` FOREIGN KEY IF NOT EXISTS (`soshipaddressid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE');

        $this->getTable('vtiger_invoice_recurring_info', null)
            ->createTable('salesorderid','int(11) NOT NULL')
            ->createColumn('recurring_frequency','varchar(200) DEFAULT NULL')
            ->createColumn('start_period','date DEFAULT NULL')
            ->createColumn('end_period','date DEFAULT NULL')
            ->createColumn('next_recurring_date','date DEFAULT NULL')
            ->createColumn('payment_duration','varchar(200) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`salesorderid`)')
            ->createKey('CONSTRAINT `fk_salesorderid_vtiger_invoice_recurring_info` FOREIGN KEY IF NOT EXISTS (`salesorderid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE');

        $this->createPicklistTable('vtiger_sostatus', 'sostatusid', 'sostatus');
        $this->createPicklistTable('vtiger_recurring_frequency', 'recurring_frequency_id', 'recurring_frequency');
        $this->createPicklistTable('vtiger_payment_duration', 'payment_duration_id', 'payment_duration');
    }
}
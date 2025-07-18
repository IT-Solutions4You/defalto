<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PurchaseOrder_Install_Model extends Core_Install_Model {

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
            'LBL_PO_INFORMATION' => [
                'subject' => [
                    'name' => 'subject',
                    'uitype' => 2,
                    'column' => 'subject',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Subject',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                ],
                'vendor_id' => [
                    'name' => 'vendor_id',
                    'uitype' => 81,
                    'column' => 'vendorid',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Vendor Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'I~M',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'requisition_no' => [
                    'name' => 'requisition_no',
                    'uitype' => 1,
                    'column' => 'requisition_no',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Requisition No',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'tracking_no' => [
                    'name' => 'tracking_no',
                    'uitype' => 1,
                    'column' => 'tracking_no',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Tracking Number',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'contact_id' => [
                    'name' => 'contact_id',
                    'uitype' => 57,
                    'column' => 'contactid',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Contact Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                ],
                'duedate' => [
                    'name' => 'duedate',
                    'uitype' => 5,
                    'column' => 'duedate',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Due Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'carrier' => [
                    'name' => 'carrier',
                    'uitype' => 15,
                    'column' => 'carrier',
                    'table' => 'vtiger_purchaseorder',
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
                'salescommission' => [
                    'name' => 'salescommission',
                    'uitype' => 1,
                    'column' => 'salescommission',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Sales Commission',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'exciseduty' => [
                    'name' => 'exciseduty',
                    'uitype' => 1,
                    'column' => 'exciseduty',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Excise Duty',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'price_total' => [
                    'name' => 'price_total',
                    'uitype' => 72,
                    'column' => 'price_total',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Total',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'subtotal' => [
                    'name' => 'subtotal',
                    'uitype' => 72,
                    'column' => 'subtotal',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Sub Total',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'hdnTaxType' => [
                    'name' => 'hdnTaxType',
                    'uitype' => 16,
                    'column' => 'taxtype',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Tax Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [],
                ],
                'postatus' => [
                    'name' => 'postatus',
                    'uitype' => 15,
                    'column' => 'postatus',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        'Created',
                        'Approved',
                        'Delivered',
                        'Cancelled',
                        'Received Shipment',
                    ],
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
                    'summaryfield' => 0,
                ],
                'conversion_rate' => [
                    'name' => 'conversion_rate',
                    'uitype' => 1,
                    'column' => 'conversion_rate',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Conversion Rate',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'paid' => [
                    'name' => 'paid',
                    'uitype' => 72,
                    'column' => 'paid',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Paid',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'balance' => [
                    'name' => 'balance',
                    'uitype' => 72,
                    'column' => 'balance',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Balance',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
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
                    'table' => 'vtiger_pobillads',
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
                    'table' => 'vtiger_poshipads',
                    'label' => 'Shipping Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_city' => [
                    'name' => 'bill_city',
                    'uitype' => 1,
                    'column' => 'bill_city',
                    'table' => 'vtiger_pobillads',
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
                    'table' => 'vtiger_poshipads',
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
                    'table' => 'vtiger_pobillads',
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
                    'table' => 'vtiger_poshipads',
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
                    'table' => 'vtiger_pobillads',
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
                    'table' => 'vtiger_poshipads',
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
                    'table' => 'vtiger_pobillads',
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
                    'table' => 'vtiger_poshipads',
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
                    'table' => 'vtiger_pobillads',
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
                    'table' => 'vtiger_poshipads',
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
                    'table' => 'vtiger_purchaseorder',
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
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Discount Amount',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'adjustment' => [
                    'name' => 'adjustment',
                    'uitype' => 72,
                    'column' => 'adjustment',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Adjustment',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'NN~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'currency_id' => [
                    'name' => 'currency_id',
                    'uitype' => 117,
                    'column' => 'currency_id',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Currency',
                    'readonly' => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 1,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'region_id' => [
                    'name' => 'region_id',
                    'uitype' => 29,
                    'column' => 'region_id',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Tax Region',
                    'readonly' => 0,
                    'presence' => 0,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'picklist_values' => [],
                ],
                'pricebookid' => [
                    'name' => 'pricebookid',
                    'uitype' => 10,
                    'column' => 'pricebookid',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'Price Book',
                    'readonly' => 0,
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
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
                    'table' => 'vtiger_purchaseorder',
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
                    'table'         => 'vtiger_purchaseorder',
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
                    'table'         => 'vtiger_purchaseorder',
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
                    'table'         => 'vtiger_purchaseorder',
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
                    'table'         => 'vtiger_purchaseorder',
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
                    'table'         => 'vtiger_purchaseorder',
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
                'purchaseorder_no' => [
                    'name' => 'purchaseorder_no',
                    'uitype' => 4,
                    'column' => 'purchaseorder_no',
                    'table' => 'vtiger_purchaseorder',
                    'label' => 'PurchaseOrder No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
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
        $this->getTable('vtiger_pobillads', null)
            ->createTable('pobilladdressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('bill_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('bill_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('bill_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`pobilladdressid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_pobillads` FOREIGN KEY IF NOT EXISTS (`pobilladdressid`) REFERENCES `vtiger_purchaseorder` (`purchaseorderid`) ON DELETE CASCADE')
        ;

        $this->getTable('vtiger_poshipads', null)
            ->createColumn('poshipaddressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('ship_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('ship_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('ship_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`poshipaddressid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_poshipads` FOREIGN KEY IF NOT EXISTS (`poshipaddressid`) REFERENCES `vtiger_purchaseorder` (`purchaseorderid`) ON DELETE CASCADE')
        ;

        $this->getTable('vtiger_purchaseorder', null)
            ->createColumn('purchaseorderid', 'int(19) NOT NULL DEFAULT 0')
            ->createColumn('subject', 'varchar(100) DEFAULT NULL')
            ->createColumn('quoteid', 'int(19) DEFAULT NULL')
            ->createColumn('vendorid', 'int(19) DEFAULT NULL')
            ->createColumn('requisition_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('purchaseorder_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('tracking_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('contactid', 'int(19) DEFAULT NULL')
            ->createColumn('duedate', 'date DEFAULT NULL')
            ->createColumn('carrier', 'varchar(200) DEFAULT NULL')
            ->createColumn('type', 'varchar(100) DEFAULT NULL')
            ->createColumn('adjustment', self::$COLUMN_DECIMAL)
            ->createColumn('salescommission', self::$COLUMN_DECIMAL)
            ->createColumn('exciseduty', self::$COLUMN_DECIMAL)
            ->createColumn('price_total', self::$COLUMN_DECIMAL)
            ->createColumn('subtotal', self::$COLUMN_DECIMAL)
            ->createColumn('taxtype', 'varchar(25) DEFAULT NULL')
            ->createColumn('discount_percent', self::$COLUMN_DECIMAL)
            ->createColumn('discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('s_h_amount', self::$COLUMN_DECIMAL)
            ->createColumn('terms_conditions', 'text DEFAULT NULL')
            ->createColumn('postatus', 'varchar(200) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('conversion_rate', 'decimal(10,3) NOT NULL DEFAULT 1.000')
            ->createColumn('compound_taxes_info', 'text DEFAULT NULL')
            ->createColumn('price_after_discount', self::$COLUMN_DECIMAL)
            ->createColumn('overall_discount_amount', self::$COLUMN_DECIMAL)
            ->createColumn('price_after_overall_discount', self::$COLUMN_DECIMAL)
            ->createColumn('paid', self::$COLUMN_DECIMAL)
            ->createColumn('balance', self::$COLUMN_DECIMAL)
            ->createColumn('s_h_percent', self::$COLUMN_DECIMAL)
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('region_id', 'int(19) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createColumn('tax_amount', self::$COLUMN_DECIMAL)
            ->createColumn('grand_total', self::$COLUMN_DECIMAL)
            ->createColumn('margin_amount', self::$COLUMN_DECIMAL)
            ->createKey('PRIMARY KEY IF NOT EXISTS (`purchaseorderid`)')
            ->createKey('KEY IF NOT EXISTS `purchaseorder_vendorid_idx` (`vendorid`)')
            ->createKey('KEY IF NOT EXISTS `purchaseorder_quoteid_idx` (`quoteid`)')
            ->createKey('KEY IF NOT EXISTS `purchaseorder_contactid_idx` (`contactid`)')
            ->createKey('CONSTRAINT `fk_4_vtiger_purchaseorder` FOREIGN KEY IF NOT EXISTS (`vendorid`) REFERENCES `vtiger_vendor` (`vendorid`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_crmid_vtiger_purchaseorder` FOREIGN KEY IF NOT EXISTS (`purchaseorderid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
        ;

        $this->createPicklistTable('vtiger_postatus', 'postatusid', 'postatus');
    }
}
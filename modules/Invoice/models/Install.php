<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Invoice_Install_Model extends Core_Install_Model
{

    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
    }

    public function getBlocks(): array
    {
        return [
            'LBL_INVOICE_INFORMATION' => [
                'subject' => [
                    'name' => 'subject',
                    'uitype' => 2,
                    'column' => 'subject',
                    'table' => 'vtiger_invoice',
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
                'salesorder_id' => [
                    'name' => 'salesorder_id',
                    'uitype' => 80,
                    'column' => 'salesorderid',
                    'table' => 'vtiger_invoice',
                    'label' => 'Sales Order',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                ],
                'customerno' => [
                    'name' => 'customerno',
                    'uitype' => 1,
                    'column' => 'customerno',
                    'table' => 'vtiger_invoice',
                    'label' => 'Customer No',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'contact_id' => [
                    'name' => 'contact_id',
                    'uitype' => 57,
                    'column' => 'contactid',
                    'table' => 'vtiger_invoice',
                    'label' => 'Contact Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'invoicedate' => [
                    'name' => 'invoicedate',
                    'uitype' => 5,
                    'column' => 'invoicedate',
                    'table' => 'vtiger_invoice',
                    'label' => 'Invoice Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'duedate' => [
                    'name' => 'duedate',
                    'uitype' => 5,
                    'column' => 'duedate',
                    'table' => 'vtiger_invoice',
                    'label' => 'Due Date',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'D~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'vtiger_purchaseorder' => [
                    'name' => 'vtiger_purchaseorder',
                    'uitype' => 1,
                    'column' => 'purchaseorder',
                    'table' => 'vtiger_invoice',
                    'label' => 'Purchase Order',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'salescommission' => [
                    'name' => 'salescommission',
                    'uitype' => 1,
                    'column' => 'salescommission',
                    'table' => 'vtiger_invoice',
                    'label' => 'Sales Commission',
                    'readonly' => 1,
                    'presence' => 2,
                    'maximumlength' => 10,
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
                    'table' => 'vtiger_invoice',
                    'label' => 'Excise Duty',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'hdnSubTotal' => [
                    'name' => 'hdnSubTotal',
                    'uitype' => 72,
                    'column' => 'subtotal',
                    'table' => 'vtiger_invoice',
                    'label' => 'Sub Total',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'hdnGrandTotal' => [
                    'name' => 'hdnGrandTotal',
                    'uitype' => 72,
                    'column' => 'total',
                    'table' => 'vtiger_invoice',
                    'label' => 'Total',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'hdnTaxType' => [
                    'name' => 'hdnTaxType',
                    'uitype' => 16,
                    'column' => 'taxtype',
                    'table' => 'vtiger_invoice',
                    'label' => 'Tax Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' =>
                        [
                        ],
                ],
                'account_id' => [
                    'name' => 'account_id',
                    'uitype' => 73,
                    'column' => 'accountid',
                    'table' => 'vtiger_invoice',
                    'label' => 'Account Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~M',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'invoicestatus' => [
                    'name' => 'invoicestatus',
                    'uitype' => 15,
                    'column' => 'invoicestatus',
                    'table' => 'vtiger_invoice',
                    'label' => 'Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' =>
                        [
                            0 => 'AutoCreated',
                            1 => 'Created',
                            2 => 'Approved',
                            3 => 'Sent',
                            4 => 'Credit Invoice',
                            5 => 'Paid',
                            6 => 'Cancel',
                        ],
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
                'conversion_rate' => [
                    'name' => 'conversion_rate',
                    'uitype' => 1,
                    'column' => 'conversion_rate',
                    'table' => 'vtiger_invoice',
                    'label' => 'Conversion Rate',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'invoice_no' => [
                    'name' => 'invoice_no',
                    'uitype' => 4,
                    'column' => 'invoice_no',
                    'table' => 'vtiger_invoice',
                    'label' => 'Invoice No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                ],
                'pre_tax_total' => [
                    'name' => 'pre_tax_total',
                    'uitype' => 72,
                    'column' => 'pre_tax_total',
                    'table' => 'vtiger_invoice',
                    'label' => 'Pre Tax Total',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'received' => [
                    'name' => 'received',
                    'uitype' => 72,
                    'column' => 'received',
                    'table' => 'vtiger_invoice',
                    'label' => 'Received',
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
                    'table' => 'vtiger_invoice',
                    'label' => 'Balance',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 1,
                    'displaytype' => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'potential_id' => [
                    'name' => 'potential_id',
                    'uitype' => 10,
                    'column' => 'potential_id',
                    'table' => 'vtiger_invoice',
                    'label' => 'Potential Name',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'related_modules' => ['Potentials'],
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
            'LBL_ADDRESS_INFORMATION' =>
                [
                    'hdnS_H_Amount' => [
                        'name' => 'hdnS_H_Amount',
                        'uitype' => 72,
                        'column' => 's_h_amount',
                        'table' => 'vtiger_invoice',
                        'label' => 'S&H Amount',
                        'readonly' => 1,
                        'presence' => 2,
                        'typeofdata' => 'N~O',
                        'quickcreate' => 3,
                        'displaytype' => 3,
                        'masseditable' => 1,
                        'summaryfield' => 0,
                    ],
                    'bill_street' => [
                        'name' => 'bill_street',
                        'uitype' => 24,
                        'column' => 'bill_street',
                        'table' => 'vtiger_invoicebillads',
                        'label' => 'Billing Address',
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
                        'table' => 'vtiger_invoiceshipads',
                        'label' => 'Shipping Address',
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
                        'table' => 'vtiger_invoicebillads',
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
                        'table' => 'vtiger_invoiceshipads',
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
                        'table' => 'vtiger_invoicebillads',
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
                        'table' => 'vtiger_invoiceshipads',
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
                        'table' => 'vtiger_invoicebillads',
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
                        'table' => 'vtiger_invoiceshipads',
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
                        'table' => 'vtiger_invoicebillads',
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
                        'table' => 'vtiger_invoiceshipads',
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
                        'table' => 'vtiger_invoicebillads',
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
                        'table' => 'vtiger_invoiceshipads',
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
                    'table' => 'vtiger_invoice',
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
                'hdnDiscountPercent' => [
                    'name' => 'hdnDiscountPercent',
                    'uitype' => 1,
                    'column' => 'discount_percent',
                    'table' => 'vtiger_invoice',
                    'label' => 'Discount Percent',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 5,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'hdnDiscountAmount' => [
                    'name' => 'hdnDiscountAmount',
                    'uitype' => 72,
                    'column' => 'discount_amount',
                    'table' => 'vtiger_invoice',
                    'label' => 'Discount Amount',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 3,
                    'displaytype' => 5,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'hdnS_H_Percent' => [
                    'name' => 'hdnS_H_Percent',
                    'uitype' => 1,
                    'column' => 's_h_percent',
                    'table' => 'vtiger_invoice',
                    'label' => 'S&H Percent',
                    'readonly' => 0,
                    'presence' => 2,
                    'typeofdata' => 'N~O',
                    'quickcreate' => 0,
                    'displaytype' => 5,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'txtAdjustment' => [
                    'name' => 'txtAdjustment',
                    'uitype' => 72,
                    'column' => 'adjustment',
                    'table' => 'vtiger_invoice',
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
                    'table' => 'vtiger_invoice',
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
                    'table' => 'vtiger_invoice',
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
                    'uitype' => 73,
                    'column' => 'pricebookid',
                    'table' => 'vtiger_invoice',
                    'label' => 'Price Book',
                    'readonly' => 0,
                    'presence' => 0,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 0,
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
        $this->getTable('vtiger_invoice', null)
            ->createTable('invoiceid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('subject', 'varchar(100) DEFAULT NULL')
            ->createColumn('salesorderid', 'int(19) DEFAULT NULL')
            ->createColumn('customerno', 'varchar(100) DEFAULT NULL')
            ->createColumn('contactid', 'int(19) DEFAULT NULL')
            ->createColumn('notes', 'varchar(100) DEFAULT NULL')
            ->createColumn('invoicedate', 'date DEFAULT NULL')
            ->createColumn('duedate', 'date DEFAULT NULL')
            ->createColumn('invoiceterms', 'varchar(100) DEFAULT NULL')
            ->createColumn('type', 'varchar(100) DEFAULT NULL')
            ->createColumn('adjustment', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('salescommission', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('exciseduty', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('subtotal', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('total', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('taxtype', 'varchar(25) DEFAULT NULL')
            ->createColumn('discount_percent', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('discount_amount', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('s_h_amount', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('shipping', 'varchar(100) DEFAULT NULL')
            ->createColumn('accountid', 'int(19) DEFAULT NULL')
            ->createColumn('terms_conditions', 'text DEFAULT NULL')
            ->createColumn('purchaseorder', 'varchar(200) DEFAULT NULL')
            ->createColumn('invoicestatus', 'varchar(200) DEFAULT NULL')
            ->createColumn('invoice_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('conversion_rate', 'decimal(10,3) NOT NULL DEFAULT 1.000')
            ->createColumn('compound_taxes_info', 'text DEFAULT NULL')
            ->createColumn('pre_tax_total', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('received', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('balance', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('s_h_percent', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('potential_id', 'varchar(100) DEFAULT NULL')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('region_id', 'int(19) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (invoiceid)')
            ->createKey('KEY IF NOT EXISTS invoice_purchaseorderid_idx (invoiceid)')
            ->createKey('KEY IF NOT EXISTS fk_2_vtiger_invoice (salesorderid)')
            ->createKey('CONSTRAINT fk_2_vtiger_invoice FOREIGN KEY IF NOT EXISTS (salesorderid) REFERENCES vtiger_salesorder (salesorderid) ON DELETE CASCADE')
            ->createKey('CONSTRAINT fk_crmid_vtiger_invoice FOREIGN KEY IF NOT EXISTS (invoiceid) REFERENCES vtiger_crmentity (crmid) ON DELETE CASCADE');

        $this->getTable('vtiger_invoicebillads', null)
            ->createTable('invoicebilladdressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('bill_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('bill_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('bill_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('bill_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (invoicebilladdressid)')
            ->createKey('CONSTRAINT fk_1_vtiger_invoicebillads FOREIGN KEY IF NOT EXISTS (invoicebilladdressid) REFERENCES vtiger_invoice (invoiceid) ON DELETE CASCADE');

        $this->getTable('vtiger_invoiceshipads', null)
            ->createTable('invoiceshipaddressid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('ship_city', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_code', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_country_id', 'varchar(2) DEFAULT NULL')
            ->createColumn('ship_state', 'varchar(150) DEFAULT NULL')
            ->createColumn('ship_street', 'varchar(250) DEFAULT NULL')
            ->createColumn('ship_pobox', 'varchar(30) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (invoiceshipaddressid)')
            ->createKey('CONSTRAINT fk_1_vtiger_invoiceshipads FOREIGN KEY IF NOT EXISTS (invoiceshipaddressid) REFERENCES vtiger_invoice (invoiceid) ON DELETE CASCADE');
        
        $this->getTable('vtiger_invoice_recurring_info', null)
            ->createTable('salesorderid','int(11) NOT NULL')
            ->createColumn('recurring_frequency','varchar(200) DEFAULT NULL')
            ->createColumn('start_period','date DEFAULT NULL')
            ->createColumn('end_period','date DEFAULT NULL')
            ->createColumn('last_recurring_date','date DEFAULT NULL')
            ->createColumn('payment_duration','varchar(200) DEFAULT NULL')
            ->createColumn('invoice_status','varchar(200) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`salesorderid`)')
            ->createKey('CONSTRAINT `fk_salesorderid_vtiger_invoice_recurring_info` FOREIGN KEY IF NOT EXISTS (`salesorderid`) REFERENCES `vtiger_salesorder` (`salesorderid`) ON DELETE CASCADE')
            ;
    }
}
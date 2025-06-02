<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class SalesOrder_Install_Model extends Core_Install_Model
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
            'LBL_SO_INFORMATION'            => [
                'salesorder_no'                => [
                    'name'         => 'salesorder_no',
                    'uitype'       => 4,
                    'column'       => 'salesorder_no',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'SalesOrder No',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                ],
                'subject'                      => [
                    'name'              => 'subject',
                    'uitype'            => 2,
                    'column'            => 'subject',
                    'table'             => 'vtiger_salesorder',
                    'label'             => 'Subject',
                    'readonly'          => 1,
                    'presence'          => 0,
                    'typeofdata'        => 'V~M',
                    'quickcreate'       => 3,
                    'displaytype'       => 1,
                    'masseditable'      => 1,
                    'summaryfield'      => 1,
                    'entity_identifier' => 1,
                ],
                'potential_id'                 => [
                    'name'         => 'potential_id',
                    'uitype'       => 76,
                    'column'       => 'potentialid',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Potential Name',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'customerno'                   => [
                    'name'         => 'customerno',
                    'uitype'       => 1,
                    'column'       => 'customerno',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Customer No',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'quote_id'                     => [
                    'name'         => 'quote_id',
                    'uitype'       => 78,
                    'column'       => 'quoteid',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Quote Name',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 1,
                ],
                'vtiger_purchaseorder'         => [
                    'name'         => 'vtiger_purchaseorder',
                    'uitype'       => 1,
                    'column'       => 'purchaseorder',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Purchase Order',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'contact_id'                   => [
                    'name'         => 'contact_id',
                    'uitype'       => 57,
                    'column'       => 'contactid',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Contact Name',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'duedate'                      => [
                    'name'         => 'duedate',
                    'uitype'       => 5,
                    'column'       => 'duedate',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Due Date',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'D~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'carrier'                      => [
                    'name'            => 'carrier',
                    'uitype'          => 15,
                    'column'          => 'carrier',
                    'table'           => 'vtiger_salesorder',
                    'label'           => 'Carrier',
                    'readonly'        => 1,
                    'presence'        => 2,
                    'typeofdata'      => 'V~O',
                    'quickcreate'     => 3,
                    'displaytype'     => 1,
                    'masseditable'    => 1,
                    'summaryfield'    => 0,
                    'picklist_values' => [
                        'FedEx',
                        'UPS',
                        'USPS',
                        'DHL',
                        'BlueDart',
                    ],
                ],
                'pending'                      => [
                    'name'         => 'pending',
                    'uitype'       => 1,
                    'column'       => 'pending',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Pending',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'sostatus'                     => [
                    'name'            => 'sostatus',
                    'uitype'          => 15,
                    'column'          => 'sostatus',
                    'table'           => 'vtiger_salesorder',
                    'label'           => 'Status',
                    'readonly'        => 1,
                    'presence'        => 2,
                    'typeofdata'      => 'V~M',
                    'quickcreate'     => 3,
                    'displaytype'     => 1,
                    'masseditable'    => 1,
                    'summaryfield'    => 0,
                    'picklist_values' => [
                        'Created',
                        'Approved',
                        'Delivered',
                        'Cancelled',
                    ],
                ],
                'salescommission'              => [
                    'name'         => 'salescommission',
                    'uitype'       => 1,
                    'column'       => 'salescommission',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Sales Commission',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'exciseduty'                   => [
                    'name'         => 'exciseduty',
                    'uitype'       => 1,
                    'column'       => 'exciseduty',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Excise Duty',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'price_total'                  => [
                    'name'         => 'price_total',
                    'uitype'       => 72,
                    'column'       => 'price_total',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Total',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 3,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'subtotal'                     => [
                    'name'         => 'subtotal',
                    'uitype'       => 72,
                    'column'       => 'subtotal',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Sub Total',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'hdnTaxType'                   => [
                    'name'            => 'hdnTaxType',
                    'uitype'          => 16,
                    'column'          => 'taxtype',
                    'table'           => 'vtiger_salesorder',
                    'label'           => 'Tax Type',
                    'readonly'        => 1,
                    'presence'        => 2,
                    'typeofdata'      => 'V~O',
                    'quickcreate'     => 3,
                    'displaytype'     => 3,
                    'masseditable'    => 1,
                    'summaryfield'    => 0,
                    'picklist_values' => [],
                ],
                'hdnS_H_Amount'                => [
                    'name'         => 'hdnS_H_Amount',
                    'uitype'       => 72,
                    'column'       => 's_h_amount',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'S&H Amount',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'account_id'                   => [
                    'name'         => 'account_id',
                    'uitype'       => 73,
                    'column'       => 'accountid',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Account Name',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'I~M',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'assigned_user_id'             => [
                    'name'         => 'assigned_user_id',
                    'uitype'       => 53,
                    'column'       => 'smownerid',
                    'table'        => 'vtiger_crmentity',
                    'label'        => 'Assigned To',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'V~M',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'createdtime'                  => [
                    'name'         => 'createdtime',
                    'uitype'       => 70,
                    'column'       => 'createdtime',
                    'table'        => 'vtiger_crmentity',
                    'label'        => 'Created Time',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'DT~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'modifiedtime'                 => [
                    'name'         => 'modifiedtime',
                    'uitype'       => 70,
                    'column'       => 'modifiedtime',
                    'table'        => 'vtiger_crmentity',
                    'label'        => 'Modified Time',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'DT~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'modifiedby'                   => [
                    'name'         => 'modifiedby',
                    'uitype'       => 52,
                    'column'       => 'modifiedby',
                    'table'        => 'vtiger_crmentity',
                    'label'        => 'Last Modified By',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 3,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'conversion_rate'              => [
                    'name'         => 'conversion_rate',
                    'uitype'       => 1,
                    'column'       => 'conversion_rate',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Conversion Rate',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'price_after_overall_discount' => [
                    'name'         => 'price_after_overall_discount',
                    'uitype'       => 72,
                    'column'       => 'price_after_overall_discount',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Price After Overall Discount',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 1,
                    'displaytype'  => 3,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'source'                       => [
                    'name'         => 'source',
                    'uitype'       => 1,
                    'column'       => 'source',
                    'table'        => 'vtiger_crmentity',
                    'label'        => 'Source',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 2,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION'        => [
            ],
            'LBL_ADDRESS_INFORMATION'       => [
                'bill_street'     => [
                    'name'         => 'bill_street',
                    'uitype'       => 24,
                    'column'       => 'bill_street',
                    'table'        => 'vtiger_sobillads',
                    'label'        => 'Billing Address',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~M',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_street'     => [
                    'name'         => 'ship_street',
                    'uitype'       => 24,
                    'column'       => 'ship_street',
                    'table'        => 'vtiger_soshipads',
                    'label'        => 'Shipping Address',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~M',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_city'       => [
                    'name'         => 'bill_city',
                    'uitype'       => 1,
                    'column'       => 'bill_city',
                    'table'        => 'vtiger_sobillads',
                    'label'        => 'Billing City',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_city'       => [
                    'name'         => 'ship_city',
                    'uitype'       => 1,
                    'column'       => 'ship_city',
                    'table'        => 'vtiger_soshipads',
                    'label'        => 'Shipping City',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_state'      => [
                    'name'         => 'bill_state',
                    'uitype'       => 1,
                    'column'       => 'bill_state',
                    'table'        => 'vtiger_sobillads',
                    'label'        => 'Billing State',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_state'      => [
                    'name'         => 'ship_state',
                    'uitype'       => 1,
                    'column'       => 'ship_state',
                    'table'        => 'vtiger_soshipads',
                    'label'        => 'Shipping State',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_code'       => [
                    'name'         => 'bill_code',
                    'uitype'       => 1,
                    'column'       => 'bill_code',
                    'table'        => 'vtiger_sobillads',
                    'label'        => 'Billing Code',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_code'       => [
                    'name'         => 'ship_code',
                    'uitype'       => 1,
                    'column'       => 'ship_code',
                    'table'        => 'vtiger_soshipads',
                    'label'        => 'Shipping Code',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_country_id' => [
                    'name'         => 'bill_country_id',
                    'uitype'       => 18,
                    'column'       => 'bill_country_id',
                    'table'        => 'vtiger_sobillads',
                    'label'        => 'Billing Country',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_country_id' => [
                    'name'         => 'ship_country_id',
                    'uitype'       => 18,
                    'column'       => 'ship_country_id',
                    'table'        => 'vtiger_soshipads',
                    'label'        => 'Shipping Country',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'bill_pobox'      => [
                    'name'         => 'bill_pobox',
                    'uitype'       => 1,
                    'column'       => 'bill_pobox',
                    'table'        => 'vtiger_sobillads',
                    'label'        => 'Billing Po Box',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'ship_pobox'      => [
                    'name'         => 'ship_pobox',
                    'uitype'       => 1,
                    'column'       => 'ship_pobox',
                    'table'        => 'vtiger_soshipads',
                    'label'        => 'Shipping Po Box',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_TERMS_INFORMATION'         => [
                'terms_conditions' => [
                    'name'         => 'terms_conditions',
                    'uitype'       => 19,
                    'column'       => 'terms_conditions',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Terms & Conditions',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION'   => [
                'description' => [
                    'name'         => 'description',
                    'uitype'       => 19,
                    'column'       => 'description',
                    'table'        => 'vtiger_crmentity',
                    'label'        => 'Description',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'V~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'Recurring Invoice Information' => [
                'enable_recurring'    => [
                    'name'         => 'enable_recurring',
                    'uitype'       => 56,
                    'column'       => 'enable_recurring',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Enable Recurring',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'C~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'recurring_frequency' => [
                    'name'            => 'recurring_frequency',
                    'uitype'          => 16,
                    'column'          => 'recurring_frequency',
                    'table'           => 'vtiger_invoice_recurring_info',
                    'label'           => 'Frequency',
                    'readonly'        => 1,
                    'presence'        => 0,
                    'typeofdata'      => 'V~O',
                    'quickcreate'     => 3,
                    'displaytype'     => 1,
                    'masseditable'    => 0,
                    'summaryfield'    => 0,
                    'picklist_values' => [
                        'Daily',
                        'Weekly',
                        'Monthly',
                        'Quarterly',
                        'Yearly',
                    ],
                ],
                'start_period'        => [
                    'name'         => 'start_period',
                    'uitype'       => 5,
                    'column'       => 'start_period',
                    'table'        => 'vtiger_invoice_recurring_info',
                    'label'        => 'Start Period',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'D~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'end_period'          => [
                    'name'         => 'end_period',
                    'uitype'       => 5,
                    'column'       => 'end_period',
                    'table'        => 'vtiger_invoice_recurring_info',
                    'label'        => 'End Period',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'D~O~OTH~G~start_period~Start Period',
                    'quickcreate'  => 3,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'payment_duration'    => [
                    'name'            => 'payment_duration',
                    'uitype'          => 16,
                    'column'          => 'payment_duration',
                    'table'           => 'vtiger_invoice_recurring_info',
                    'label'           => 'Payment Duration',
                    'readonly'        => 1,
                    'presence'        => 0,
                    'typeofdata'      => 'V~O',
                    'quickcreate'     => 3,
                    'displaytype'     => 1,
                    'masseditable'    => 0,
                    'summaryfield'    => 0,
                    'picklist_values' => [
                        'Net 30 days',
                        'Net 45 days',
                        'Net 60 days',
                    ],
                ],
                'invoicestatus'       => [
                    'name'            => 'invoicestatus',
                    'uitype'          => 15,
                    'column'          => 'invoice_status',
                    'table'           => 'vtiger_invoice_recurring_info',
                    'label'           => 'Invoice Status',
                    'readonly'        => 1,
                    'presence'        => 0,
                    'typeofdata'      => 'V~M',
                    'quickcreate'     => 3,
                    'displaytype'     => 1,
                    'masseditable'    => 0,
                    'summaryfield'    => 0,
                    'picklist_values' => [
                        'AutoCreated',
                        'Created',
                        'Approved',
                        'Sent',
                        'Credit Invoice',
                        'Paid',
                        'Cancel',
                    ],
                ],
            ],
            'LBL_ITEM_DETAILS'              => [
                'hdnDiscountPercent' => [
                    'name'         => 'hdnDiscountPercent',
                    'uitype'       => 1,
                    'column'       => 'discount_percent',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Discount Percent',
                    'readonly'     => 1,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 5,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'discount_amount'    => [
                    'name'         => 'discount_amount',
                    'uitype'       => 72,
                    'column'       => 'discount_amount',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Discount Amount',
                    'readonly'     => 1,
                    'presence'     => 0,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 3,
                    'displaytype'  => 5,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'hdnS_H_Percent'     => [
                    'name'         => 'hdnS_H_Percent',
                    'uitype'       => 1,
                    'column'       => 's_h_percent',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'S&H Percent',
                    'readonly'     => 0,
                    'presence'     => 2,
                    'typeofdata'   => 'N~O',
                    'quickcreate'  => 0,
                    'displaytype'  => 5,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
                'adjustment'         => [
                    'name'         => 'adjustment',
                    'uitype'       => 72,
                    'column'       => 'adjustment',
                    'table'        => 'vtiger_salesorder',
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
                    'table'        => 'vtiger_salesorder',
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
                    'table'           => 'vtiger_salesorder',
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
                    'uitype'       => 73,
                    'column'       => 'pricebookid',
                    'table'        => 'vtiger_salesorder',
                    'label'        => 'Price Book',
                    'readonly'     => 0,
                    'presence'     => 0,
                    'typeofdata'   => 'I~O',
                    'quickcreate'  => 1,
                    'displaytype'  => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
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
        $this->getTable('vtiger_salesorder', null)
            ->createTable('salesorderid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('subject', 'varchar(100) DEFAULT NULL')
            ->createColumn('potentialid', 'int(19) DEFAULT NULL')
            ->createColumn('customerno', 'varchar(100) DEFAULT NULL')
            ->createColumn('salesorder_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('quoteid', 'int(19) DEFAULT NULL')
            ->createColumn('vendorterms', 'varchar(100) DEFAULT NULL')
            ->createColumn('contactid', 'int(19) DEFAULT NULL')
            ->createColumn('vendorid', 'int(19) DEFAULT NULL')
            ->createColumn('duedate', 'date DEFAULT NULL')
            ->createColumn('carrier', 'varchar(200) DEFAULT NULL')
            ->createColumn('pending', 'varchar(200) DEFAULT NULL')
            ->createColumn('type', 'varchar(100) DEFAULT NULL')
            ->createColumn('adjustment', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('salescommission', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('exciseduty', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('price_total', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('subtotal', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('taxtype', 'varchar(25) DEFAULT NULL')
            ->createColumn('discount_percent', 'decimal(25,3) DEFAULT NULL')
            ->createColumn('discount_amount', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('s_h_amount', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('accountid', 'int(19) DEFAULT NULL')
            ->createColumn('terms_conditions', 'text DEFAULT NULL')
            ->createColumn('purchaseorder', 'varchar(200) DEFAULT NULL')
            ->createColumn('sostatus', 'varchar(200) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT \'1\'')
            ->createColumn('conversion_rate', 'decimal(10,3) NOT NULL DEFAULT \'1.000\'')
            ->createColumn('enable_recurring', 'int(11) DEFAULT \'0\'')
            ->createColumn('compound_taxes_info', 'text DEFAULT NULL')
            ->createColumn('price_after_overall_discount', 'decimal(25,8) DEFAULT NULL')
            ->createColumn('s_h_percent', 'DECIMAL(25,3) DEFAULT NULL')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('region_id', 'int(19) DEFAULT NULL')
            ->createColumn('pricebookid', 'int(19) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`salesorderid`)')
            ->createKey('KEY IF NOT EXISTS `salesorder_vendorid_idx` (`vendorid`)')
            ->createKey('KEY IF NOT EXISTS `salesorder_contactid_idx` (`contactid`)')
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
    }
}
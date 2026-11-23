<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Outgoing payments: tables and list fields. The payment logic (save, related documents, lifecycle events) is in
 * Core_PaymentEntity_Model.
 */
class OutgoingPayment extends Core_PaymentEntity_Model
{
    public string $moduleVersion = '1.0';
    public string $moduleName = 'OutgoingPayment';
    public string $moduleLabel = 'Outgoing Payments';
    public string $parentName = 'SALES';
    public $table_name = 'df_outgoingpayment';
    public $table_index = 'outgoingpaymentid';
    public $customFieldTable = ['df_outgoingpaymentcf', 'outgoingpaymentid'];
    public $tab_name = ['vtiger_crmentity', 'df_outgoingpayment', 'df_outgoingpaymentcf'];
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'df_outgoingpayment' => 'outgoingpaymentid',
        'df_outgoingpaymentcf' => 'outgoingpaymentid',
    ];
    public $list_fields = [
        'Payment No' => ['outgoingpayment' => 'payment_no'],
        'Payment Name' => ['outgoingpayment' => 'payment_name'],
        'Amount' => ['outgoingpayment' => 'payment_amount'],
        'Payment Status' => ['outgoingpayment' => 'payment_status'],
        'Assigned To' => ['crmentity' => 'assigned_user_id'],
    ];
    public $list_fields_name = [
        'Payment No' => 'payment_no',
        'Payment Name' => 'payment_name',
        'Amount' => 'payment_amount',
        'Payment Status' => 'payment_status',
        'Assigned To' => 'assigned_user_id',
    ];
    public $list_link_field = 'payment_name';
    public $def_basicsearch_col = 'payment_name';
    public $def_detailview_recname = 'payment_name';
    public $mandatory_fields = ['payment_name', 'payment_amount', 'due_date', 'assigned_user_id'];
}

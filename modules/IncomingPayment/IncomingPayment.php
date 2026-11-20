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
 * Incoming payments: tables and list fields. The payment logic (save, related documents, lifecycle events) is in
 * Core_PaymentEntity_Model.
 */
class IncomingPayment extends Core_PaymentEntity_Model
{
    public string $moduleVersion = '1.0';
    public string $moduleName = 'IncomingPayment';
    public string $moduleLabel = 'Incoming Payments';
    public string $parentName = 'SALES';
    public $table_name = 'df_incomingpayment';
    public $table_index = 'incomingpaymentid';
    public $customFieldTable = ['df_incomingpaymentcf', 'incomingpaymentid'];
    public $tab_name = ['vtiger_crmentity', 'df_incomingpayment', 'df_incomingpaymentcf'];
    public $tab_name_index = [
        'vtiger_crmentity' => 'crmid',
        'df_incomingpayment' => 'incomingpaymentid',
        'df_incomingpaymentcf' => 'incomingpaymentid',
    ];
    public $list_fields = [
        'Payment No' => ['incomingpayment' => 'payment_no'],
        'Payment Name' => ['incomingpayment' => 'payment_name'],
        'Amount' => ['incomingpayment' => 'payment_amount'],
        'Payment Status' => ['incomingpayment' => 'payment_status'],
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

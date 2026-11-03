<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment extends CRMEntity
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
        'Amount' => ['incomingpayment' => 'paymentamount'],
        'Payment Status' => ['incomingpayment' => 'payment_status'],
        'Assigned To' => ['crmentity' => 'assigned_user_id'],
    ];
    public $list_fields_name = [
        'Payment No' => 'payment_no',
        'Payment Name' => 'payment_name',
        'Amount' => 'paymentamount',
        'Payment Status' => 'payment_status',
        'Assigned To' => 'assigned_user_id',
    ];
    public $list_link_field = 'payment_name';
    public $def_basicsearch_col = 'payment_name';
    public $def_detailview_recname = 'payment_name';
    public $mandatory_fields = ['payment_name', 'paymentamount', 'due_date', 'assigned_user_id'];

    /**
     * Writes the derived payment values and synchronises the documents of the payment.
     * It runs inside saveentity(), so it also covers import (bulk save mode, no events) and workflow field updates,
     * and it runs before the after-save handlers, so workflows and the history see the new values.
     *
     * @param string $module Module name
     * @return void
     * @throws Exception
     */
    public function save_module(string $module)
    {
        $isBulkInsert = self::isBulkSaveMode() && $this->mode !== 'edit';
        IncomingPayment_DocumentSync_Model::getInstance()->handlePaymentSave((int)$this->id, $isBulkInsert);
    }

    /**
     * Related list of the documents of one module that a payment is applied to.
     *
     * @param int $id         Payment record id
     * @param int $curTabId   Tab id of this module
     * @param int $relTabId   Tab id of the document module
     * @param int $relationId Related list id
     * @return array ['query' => related list query]
     */
    public function get_associated_documents($id, $curTabId, $relTabId, $relationId): array
    {
        return ['query' => IncomingPayment_Associated_Model::getInstance()->getDocumentsRelationQuery((int)$id, (string)vtlib_getModuleNameById($relTabId))];
    }

    /**
     * Delegates vtlib lifecycle events (enable, disable, update, uninstall) to the install model.
     *
     * @param string $moduleName Module name
     * @param string $eventType  vtlib event type, e.g. module.enabled
     * @return void
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType): void
    {
        Core_Install_Model::getInstance((string)$eventType, (string)$moduleName)->install();
    }
}

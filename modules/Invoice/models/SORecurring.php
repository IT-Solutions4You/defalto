<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Invoice_SORecurring_Model
{
    /**
     * @param int    $salesOrderId
     * @param string $recurringDate
     * @param int    $paymentDuration
     *
     * @return void
     */
    public static function run(int $salesOrderId, string $recurringDate, int $paymentDuration): void
    {
        global $current_user;

        if (!$recurringDate) {
            $recurringDate = date('Y-m-d');
        }

        if (!$current_user) {
            $current_user = Users::getActiveAdminUser();
        }

        $soFocus = CRMEntity::getInstance('SalesOrder');
        $soFocus->id = $salesOrderId;
        $soFocus->retrieve_entity_info($salesOrderId, 'SalesOrder');

        $focus = CRMEntity::getInstance('Invoice');
        $focus->column_fields['salesorder_id'] = $salesOrderId;

        foreach ($soFocus->column_fields as $fieldName => $fieldValue) {
            if (isset($focus->column_fields[$fieldName])) {
                $focus->column_fields[$fieldName] = decode_html($fieldValue);
            }
        }

        $focus->id = '';
        $focus->mode = '';
        $focus->column_fields['invoicestatus'] = 'AutoCreated';
        $focus->column_fields['invoicedate'] = $recurringDate;

        [$y, $m, $d] = explode('-', $recurringDate);
        $focus->column_fields['duedate'] = date('Y-m-d', mktime(0, 0, 0, (int)$m, (int)$d + $paymentDuration, (int)$y));

        $focus->_salesorderid = $salesOrderId;
        $focus->column_fields['source'] = 'RECURRING INVOICE';

        try {
            $focus->save('Invoice');
        } catch (Exception $e) {
            $log = Logger::getLogger('RecurringInvoice');
            $log->info($e->getMessage());
        }
    }
}
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
 * Writes the paid amount, balance and status of a document (invoice, sales order) after its payments changed.
 * Amounts are read-only fields and are written directly; a status change goes through the record model,
 * so history, workflows and the document's own handlers run.
 */
class IncomingPayment_Document_Helper
{
    /**
     * Updates the amount fields and the status of a document when they differ from the calculated values.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @param array               $config      Integration configuration of the document module
     * @param float               $paid        Paid amount in the document currency
     * @param float               $balance     Open amount in the document currency
     * @param float               $total       Document total in the document currency
     * @return void
     */
    public static function updateDocument(Vtiger_Record_Model $recordModel, array $config, float $paid, float $balance, float $total): void
    {
        $moduleModel = $recordModel->getModule();
        $amounts = [
            (string)($config['paid_field'] ?? '') => $paid,
            (string)($config['balance_field'] ?? '') => $balance,
        ];
        $amountChanges = [];

        foreach ($amounts as $fieldName => $value) {
            if ($fieldName === '' || !$moduleModel->getField($fieldName)) {
                continue;
            }

            if (!IncomingPayment_Calculation_Helper::isSameAmount((float)$recordModel->get($fieldName), $value)) {
                $amountChanges[$fieldName] = $value;
            }
        }

        // The paid and balance fields are read-only (displaytype 2), which the entity save skips,
        // so they are written directly; the status is a normal field and goes through the record save.
        if ($amountChanges) {
            IncomingPayment_DocumentSync_Model::getInstance()->updateRecordColumns($recordModel, $amountChanges);
        }

        $status = self::getTargetStatus($recordModel, $config, $paid, $balance, $total);

        if ($status !== null && $moduleModel->getField((string)$config['status_field'])) {
            $recordModel->set((string)$config['status_field'], $status);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
        }
    }

    /**
     * Tells whether a document counts as paid: something was received and the balance is at most the underpayment
     * threshold. The balance itself keeps the real underpayment. A document without a received payment is never paid,
     * even when its total is below the threshold.
     *
     * @param float $paid      Paid amount in the document currency
     * @param float $balance   Open amount in the document currency
     * @param float $total     Document total in the document currency
     * @param float $threshold Underpayment that still counts as paid, in the document currency
     * @return bool
     */
    public static function isPaid(float $paid, float $balance, float $total, float $threshold): bool
    {
        $epsilon = IncomingPayment_Calculation_Helper::EPSILON;

        return $paid > $epsilon && $balance <= $threshold + $epsilon && $paid + $threshold + $epsilon >= $total;
    }

    /**
     * Returns the status the document should get, or null when the status must stay as it is.
     * Paid documents (isPaid(), with the underpayment threshold) get the configured paid status; a document
     * that is no longer paid and still carries the paid status falls back to the configured previous status.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @param array               $config      Integration configuration
     * @param float               $paid        Paid amount
     * @param float               $balance     Open amount
     * @param float               $total       Document total
     * @return string|null
     */
    public static function getTargetStatus(Vtiger_Record_Model $recordModel, array $config, float $paid, float $balance, float $total): ?string
    {
        $statusField = (string)($config['status_field'] ?? '');
        $paidStatus = (string)($config['status_value'] ?? '');
        $backStatus = (string)($config['status_value_back'] ?? '');

        if ($statusField === '' || $paidStatus === '') {
            return null;
        }

        $currentStatus = (string)$recordModel->get($statusField);
        $threshold = IncomingPayment_Setting_Model::getInstance()->getUnderpaymentThreshold();

        if (self::isPaid($paid, $balance, $total, $threshold)) {
            return $currentStatus === $paidStatus ? null : $paidStatus;
        }

        if ($currentStatus === $paidStatus && $backStatus !== '') {
            return $backStatus;
        }

        return null;
    }
}

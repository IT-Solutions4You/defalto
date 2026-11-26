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
class Core_PaymentDocument_Helper
{
    /**
     * Updates the amount fields and the status of a document when they differ from the calculated values.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param Vtiger_Record_Model       $recordModel   Document record
     * @param array                     $config        Integration configuration of the document module
     * @param float                     $paid          Paid amount in the document currency
     * @param float                     $balance       Open amount in the document currency
     * @param float                     $total         Document total in the document currency
     * @param bool                      $keepStatus    true to write only the amounts and keep the status
     * @param bool                      $isCoveredByAdvance true when paid advances (proforma invoices) cover the document
     * @return void
     */
    public static function updateDocument(Core_PaymentConfig_Helper $paymentConfig, Vtiger_Record_Model $recordModel, array $config, float $paid, float $balance, float $total, bool $keepStatus = false, bool $isCoveredByAdvance = false): void
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

            if (self::isAmountChanged($recordModel->get($fieldName), $value)) {
                $amountChanges[$fieldName] = $value;
            }
        }

        // The paid and balance fields are read-only (displaytype 2), which the entity save skips,
        // so they are written directly; the status is a normal field and goes through the record save.
        if ($amountChanges) {
            $paymentConfig->getDocumentSyncModel()->updateRecordColumns($recordModel, $amountChanges);
        }

        $status = $keepStatus ? null : self::getTargetStatus($paymentConfig, $recordModel, $config, $paid, $balance, $total, $isCoveredByAdvance);

        if ($status !== null && $moduleModel->getField((string)$config['status_field'])) {
            $recordModel->set((string)$config['status_field'], $status);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
        }
    }

    /**
     * Tells whether a stored amount field must be written: it differs from the calculated value or it is empty,
     * so a document without payments shows 0 instead of an empty field.
     *
     * @param mixed $storedValue Value stored on the document (null or '' when empty)
     * @param float $value       Calculated value
     * @return bool
     */
    private static function isAmountChanged(mixed $storedValue, float $value): bool
    {
        return $storedValue === null || $storedValue === '' || !Core_PaymentCalculation_Helper::isSameAmount((float)$storedValue, $value);
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
        $epsilon = Core_PaymentCalculation_Helper::EPSILON;

        return $paid > $epsilon && $balance <= $threshold + $epsilon && $paid + $threshold + $epsilon >= $total;
    }

    /**
     * Tells whether a document counts as paid by its advances: paid proforma invoices cover it (their total is
     * deducted by an item of the document) and the rest of its balance is at most the underpayment threshold.
     *
     * @param bool  $isCoveredByAdvance true when paid advances cover the document
     * @param float $balance            Open amount in the document currency
     * @param float $threshold          Underpayment that still counts as paid, in the document currency
     * @return bool
     */
    public static function isCoveredByAdvance(bool $isCoveredByAdvance, float $balance, float $threshold): bool
    {
        return $isCoveredByAdvance && $balance <= $threshold + Core_PaymentCalculation_Helper::EPSILON;
    }

    /**
     * Returns the status the document should get, or null when the status must stay as it is.
     * Paid documents (isPaid(), with the underpayment threshold) get the configured paid status, and so does a
     * document covered by paid advances whose balance is within the threshold; a document that is no longer paid
     * and still carries the paid status falls back to the configured previous status. Statuses set outside the
     * payments (Core_PaymentConfig_Helper::PROTECTED_STATUSES, e.g. an invoiced proforma) never change.
     *
     * @param Core_PaymentConfig_Helper $paymentConfig Configuration of the payment module
     * @param Vtiger_Record_Model       $recordModel   Document record
     * @param array                     $config        Integration configuration
     * @param float                     $paid          Paid amount
     * @param float                     $balance       Open amount
     * @param float                     $total         Document total
     * @param bool                      $isCoveredByAdvance true when paid advances (proforma invoices) cover the document
     * @return string|null
     */
    public static function getTargetStatus(Core_PaymentConfig_Helper $paymentConfig, Vtiger_Record_Model $recordModel, array $config, float $paid, float $balance, float $total, bool $isCoveredByAdvance = false): ?string
    {
        $statusField = (string)($config['status_field'] ?? '');
        $paidStatus = (string)($config['status_value'] ?? '');
        $backStatus = (string)($config['status_value_back'] ?? '');

        if ($statusField === '' || $paidStatus === '') {
            return null;
        }

        $currentStatus = (string)$recordModel->get($statusField);
        $threshold = $paymentConfig->getSettingModel()->getUnderpaymentThreshold();

        if (in_array($currentStatus, Core_PaymentConfig_Helper::PROTECTED_STATUSES, true)) {
            return null;
        }

        if (self::isPaid($paid, $balance, $total, $threshold) || self::isCoveredByAdvance($isCoveredByAdvance, $balance, $threshold)) {
            return $currentStatus === $paidStatus ? null : $paidStatus;
        }

        if ($currentStatus === $paidStatus && $backStatus !== '') {
            return $backStatus;
        }

        return null;
    }
}

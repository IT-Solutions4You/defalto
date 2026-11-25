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
 * Keeps documents and the payments of one payment module consistent: associations, paid amounts, balances and
 * statuses. The payment module subclass (<Module>_DocumentSync_Model) sets the payment table from its configuration.
 */
abstract class Core_PaymentDocumentSync_Model extends Core_DatabaseData_Model
{

    /**
     * Creates an instance with an initialised database connection.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        return (new static())->retrieveDB();
    }

    /**
     * Returns the configuration of the payment module this model belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Returns a configured table object for the payment table.
     *
     * @return self
     */
    private function getPaymentTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Writes the derived values of a saved payment, applies it to its reference document
     * and recalculates the documents it was or is applied to.
     *
     * @param int $paymentId Payment record id
     * @return void
     * @throws Exception
     */
    public function handlePaymentSave(int $paymentId): void
    {
        $payment = Vtiger_Record_Model::getInstanceById($paymentId, $this->getConfig()::MODULE);
        $this->updateReadonlyValues($payment);
        $associated = $this->getConfig()->getAssociatedModel();
        $previousRecordIds = $associated->fetchRecordIds($paymentId);
        $documentAmounts = $this->getDocumentAmounts($payment);
        $associated->saveForPayment($paymentId, $documentAmounts);
        $this->recalculateDocuments(array_merge($previousRecordIds, array_keys($documentAmounts)));
    }

    /**
     * Returns the amount applied to each document of the payment, split by Core_PaymentAllocation_Model
     * (amounts entered in the form, the stored split, or a split by due date).
     *
     * @param Vtiger_Record_Model $payment Saved payment record
     * @return array [document id => amount applied to the document in the payment currency]
     * @throws Exception
     */
    private function getDocumentAmounts(Vtiger_Record_Model $payment): array
    {
        return Core_PaymentAllocation_Model::getInstance($this->getConfig())->getDocumentAmounts($payment);
    }

    /**
     * Writes the read-only derived columns of a payment (status, reference numbers) directly,
     * because the entity save skips displaytype 2 fields. The record model
     * is updated as well, so later calculations in the same request see the new values.
     *
     * @param Vtiger_Record_Model $payment Saved payment record
     * @return void
     * @throws Exception
     */
    public function updateReadonlyValues(Vtiger_Record_Model $payment): void
    {
        $values = Core_PaymentValues_Helper::getReadonlyValues($payment);
        $this->getPaymentTable()->updateData($values, [$this->tableId => $payment->getId()]);

        foreach ($values as $column => $value) {
            $payment->set($column, $value);
        }
    }

    /**
     * Removes a deleted document from its payments: drops the associations and refreshes the reference numbers.
     * The core already removed the document from the payments' Reference field (CRMEntity::unlinkDependencies()).
     *
     * @param int $recordId Deleted document record id
     * @return void
     * @throws Exception
     */
    public function handleDocumentDelete(int $recordId): void
    {
        $associated = $this->getConfig()->getAssociatedModel();
        $paymentIds = $associated->fetchPaymentIds($recordId);
        $associated->deleteByRecord($recordId);

        foreach ($paymentIds as $paymentId) {
            if (isRecordExists($paymentId)) {
                $this->updateReadonlyValues(Vtiger_Record_Model::getInstanceById($paymentId, $this->getConfig()::MODULE));
            }
        }
    }

    /**
     * Recalculates every document a payment is applied to, after the payment was deleted or restored.
     *
     * @param int $paymentId Payment record id
     * @return void
     * @throws Exception
     */
    public function recalculateForPayment(int $paymentId): void
    {
        $this->recalculateDocuments($this->getConfig()->getAssociatedModel()->fetchRecordIds($paymentId));
    }

    /**
     * Recalculates every document that has a payment, for example after the underpayment threshold changed.
     *
     * @return void
     * @throws Exception
     */
    public function recalculateAllDocuments(): void
    {
        $this->recalculateDocuments($this->getConfig()->getAssociatedModel()->fetchAllRecordIds());
    }

    /**
     * Recalculates the given documents; ids of deleted or unknown records are skipped.
     *
     * @param array $recordIds Document record ids
     * @return void
     * @throws Exception
     */
    public function recalculateDocuments(array $recordIds): void
    {
        foreach (array_unique(array_map('intval', $recordIds)) as $recordId) {
            if ($recordId <= 0 || !isRecordExists($recordId)) {
                continue;
            }

            $module = (string)getSalesEntityType($recordId);

            if ($module !== '') {
                $this->recalculateDocument($module, $recordId);
            }
        }
    }

    /**
     * Recalculates the paid amount, balance and status of one document from the received payments of all payment
     * modules: the paying module minus the refunds (Core_PaymentDocumentBalance_Model).
     *
     * @param string $module     Document module name
     * @param int    $recordId   Document record id
     * @param bool   $keepStatus true to keep the status, e.g. when the document save itself changed it
     * @return void
     * @throws Exception
     */
    public function recalculateDocument(string $module, int $recordId, bool $keepStatus = false): void
    {
        Core_PaymentDocumentBalance_Model::getInstance()->recalculateDocument($module, $recordId, $keepStatus);
    }

    /**
     * Returns the conversion rate stored on a document, or the current rate of its currency when none is stored.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @return float
     */
    public function getDocumentRate(Vtiger_Record_Model $recordModel): float
    {
        return Core_PaymentCalculation_Helper::getRecordRate($recordModel);
    }

    /**
     * Returns the rate for converting a payment into the currency of its document: the rate of the document for the
     * same currency, otherwise the rate stored on the payment.
     *
     * @param array $payment            Row with currency_id and conversion_rate of the payment amount
     * @param int   $documentCurrencyId Currency id of the document
     * @param float $documentRate       Conversion rate of the document currency
     * @return float
     */
    private function getPaymentRate(array $payment, int $documentCurrencyId, float $documentRate): float
    {
        $paymentCurrencyId = (int)$payment['currency_id'];

        if ($paymentCurrencyId === $documentCurrencyId) {
            return $documentRate;
        }

        $paymentRate = (float)$payment['conversion_rate'];

        return $paymentRate > 0 ? $paymentRate : Core_PaymentCalculation_Helper::getConversionRate($paymentCurrencyId);
    }

    /**
     * Writes field values of a document directly into its tables and mirrors them on the record model.
     * Used for read-only amount fields, which CRMEntity::save() does not write.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @param array               $values      [field name => value]
     * @return void
     * @throws Exception
     */
    public function updateRecordColumns(Vtiger_Record_Model $recordModel, array $values): void
    {
        $moduleModel = $recordModel->getModule();
        $tableIndexes = $recordModel->getEntity()->tab_name_index;
        $updates = [];

        foreach ($values as $fieldName => $value) {
            $fieldModel = $moduleModel->getField($fieldName);
            $table = $fieldModel ? (string)$fieldModel->get('table') : '';

            if (empty($tableIndexes[$table])) {
                continue;
            }

            $updates[$table][(string)$fieldModel->get('column')] = $value;
            $recordModel->set($fieldName, $value);
        }

        foreach ($updates as $table => $columns) {
            $this->getTable($table, $tableIndexes[$table])->updateData($columns, [$tableIndexes[$table] => $recordModel->getId()]);
        }
    }

    /**
     * Sums the payments of this module applied to a document, converted into the document currency: received ones,
     * the ones not received yet (created, waiting, pending) or all. A payment in another currency is converted
     * through the base currency with the rate stored on the payment.
     *
     * @param int       $recordId           Document record id
     * @param int       $documentCurrencyId Currency id of the document
     * @param float     $documentRate       Conversion rate of the document currency
     * @param bool|null $isReceived         true for received payments, false for the others, null for all
     * @return float
     */
    public function sumAmount(int $recordId, int $documentCurrencyId, float $documentRate, ?bool $isReceived): float
    {
        $statuses = Core_PaymentConfig_Helper::getPaidStatuses();
        $statusCondition = '';
        $parameters = [$recordId];

        if (null !== $isReceived) {
            $statusCondition = ' AND payment.payment_status ' . ($isReceived ? 'IN' : 'NOT IN') . ' (' . generateQuestionMarks($statuses) . ')';
            $parameters = array_merge($parameters, $statuses);
        }

        $result = $this->getDB()->pquery(
            'SELECT associated.partial_amount, payment.' . Core_PaymentConfig_Helper::FIELD_AMOUNT_CURRENCY . ' AS currency_id,
                payment.' . Core_PaymentConfig_Helper::FIELD_AMOUNT_RATE . ' AS conversion_rate
            FROM ' . $this->getConfig()::TABLE_ASSOCIATED . ' AS associated
            INNER JOIN ' . $this->table . ' AS payment ON payment.' . $this->tableId . ' = associated.payment_id
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = associated.payment_id
            WHERE vtiger_crmentity.deleted = 0 AND associated.record_id = ?' . $statusCondition,
            $parameters
        );
        $sum = 0.0;

        while ($row = $this->getDB()->fetchByAssoc($result)) {
            $paymentRate = $this->getPaymentRate($row, $documentCurrencyId, $documentRate);
            $sum += Core_PaymentCalculation_Helper::convertBetweenCurrencies((float)$row['partial_amount'], $paymentRate, $documentRate);
        }

        return $sum;
    }

    /**
     * Returns the payments applied to a document, newest payment date first.
     *
     * @param int $recordId Document record id
     * @return array rows with id, payment_no, payment_name, payment_date, payment_status, partial_amount, currency_id
     */
    public function fetchDocumentPayments(int $recordId): array
    {
        $paymentDate = Core_PaymentConfig_Helper::FIELD_PAYMENT_DATE;
        $result = $this->getDB()->pquery(
            'SELECT payment.' . $this->tableId . ' AS id, payment.payment_no, payment.payment_name, payment.' . $paymentDate . ' AS payment_date,
                payment.payment_status, associated.partial_amount, payment.' . Core_PaymentConfig_Helper::FIELD_AMOUNT_CURRENCY . ' AS currency_id
            FROM ' . $this->getConfig()::TABLE_ASSOCIATED . ' AS associated
            INNER JOIN ' . $this->table . ' AS payment ON payment.' . $this->tableId . ' = associated.payment_id
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = associated.payment_id
            WHERE vtiger_crmentity.deleted = 0 AND associated.record_id = ?
            ORDER BY payment.' . $paymentDate . ' DESC, payment.' . $this->tableId . ' DESC',
            [$recordId]
        );
        $payments = [];

        while ($row = $this->getDB()->fetchByAssoc($result, -1, false)) {
            $payments[] = $row;
        }

        return $payments;
    }

    /**
     * Returns the payments that are not received yet, for the daily status update.
     *
     * @return array rows with id, payment_date, due_date, payment_status
     */
    public function fetchOpenPayments(): array
    {
        $statuses = Core_PaymentConfig_Helper::getPaidStatuses();
        $result = $this->getDB()->pquery(
            'SELECT payment.' . $this->tableId . ' AS id, payment.' . Core_PaymentConfig_Helper::FIELD_PAYMENT_DATE . ' AS payment_date,
                payment.due_date, payment.payment_status
            FROM ' . $this->table . ' AS payment
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = payment.' . $this->tableId . '
            WHERE vtiger_crmentity.deleted = 0
            AND (payment.payment_status IS NULL OR payment.payment_status NOT IN (' . generateQuestionMarks($statuses) . '))',
            $statuses
        );
        $payments = [];

        while ($row = $this->getDB()->fetchByAssoc($result, -1, false)) {
            $payments[] = $row;
        }

        return $payments;
    }

    /**
     * Re-saves every open payment whose status changed with the calendar, so the save
     * recomputes the status, the history entry and the paid amounts of the documents.
     *
     * @return int number of updated payments
     * @throws Exception
     */
    public function updateOpenStatuses(): int
    {
        $today = Core_PaymentStatus_Helper::getToday();
        $updated = 0;

        foreach ($this->fetchOpenPayments() as $row) {
            $status = Core_PaymentStatus_Helper::getStatus(
                Core_PaymentStatus_Helper::normalizeDate($row['payment_date']),
                Core_PaymentStatus_Helper::normalizeDate($row['due_date']),
                $today
            );

            if ($status === (string)$row['payment_status']) {
                continue;
            }

            $recordModel = Vtiger_Record_Model::getInstanceById((int)$row['id'], $this->getConfig()::MODULE);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
            $updated++;
        }

        return $updated;
    }
}

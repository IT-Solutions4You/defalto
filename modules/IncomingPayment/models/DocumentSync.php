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
 * Keeps documents and their incoming payments consistent: associations, paid amounts, balances and statuses.
 */
class IncomingPayment_DocumentSync_Model extends Core_DatabaseData_Model
{
    protected string $table = IncomingPayment_Config_Helper::TABLE;
    protected string $tableId = IncomingPayment_Config_Helper::TABLE_INDEX;

    /**
     * Documents currently being recalculated, to stop the save handlers from recursing.
     *
     * @var array [record id => true]
     */
    private static array $processing = [];

    /**
     * Creates an instance with an initialised database connection.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return (new self())->retrieveDB();
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
     * Returns the currency stored on a payment, before the running save changes it.
     *
     * @param int $paymentId Payment record id
     * @return int currency id, 0 when the payment has no row yet
     */
    public function fetchStoredCurrencyId(int $paymentId): int
    {
        $row = $this->getPaymentTable()->selectData(['currency_id'], [$this->tableId => $paymentId]);

        return (int)($row['currency_id'] ?? 0);
    }

    /**
     * Writes the derived values of a saved payment, applies it to its reference document
     * and recalculates the documents it was or is applied to.
     *
     * @param int  $paymentId   Payment record id
     * @param bool $refreshRate true to take the current rate of the currency (bulk inserts skip the before-save event)
     * @return void
     * @throws Exception
     */
    public function handlePaymentSave(int $paymentId, bool $refreshRate = false): void
    {
        $payment = Vtiger_Record_Model::getInstanceById($paymentId, IncomingPayment_Config_Helper::MODULE);

        if ($refreshRate) {
            $payment->set('conversion_rate', IncomingPayment_Calculation_Helper::getConversionRate((int)$payment->get('currency_id')));
        }

        $this->updateReadonlyValues($payment);
        $associated = IncomingPayment_Associated_Model::getInstance();
        $previousRecordIds = $associated->fetchRecordIds($paymentId);
        $documentAmounts = $this->getDocumentAmounts($payment);
        $associated->saveForPayment($paymentId, $documentAmounts);
        $this->recalculateDocuments(array_merge($previousRecordIds, array_keys($documentAmounts)));
    }

    /**
     * Returns the amount applied to each document of the payment. The whole amount goes to the first document:
     * the save handler allows one document (IncomingPayment_Config_Helper::REFERENCE_LIMIT); only import, which
     * skips the save events, can store more, and the further documents are not applied.
     *
     * @param Vtiger_Record_Model $payment Saved payment record
     * @return array [document id => amount in the payment currency]
     */
    private function getDocumentAmounts(Vtiger_Record_Model $payment): array
    {
        $documentIds = IncomingPayment_PaymentValues_Helper::getDocumentIds($payment);

        if (!$documentIds) {
            return [];
        }

        return [$documentIds[0] => IncomingPayment_Calculation_Helper::parseAmount($payment->get(IncomingPayment_Config_Helper::FIELD_AMOUNT))];
    }

    /**
     * Writes the read-only derived columns of a payment (rate, status, base currency amount,
     * reference number) directly, because the entity save skips displaytype 2 fields. The record model
     * is updated as well, so later calculations in the same request see the new values.
     *
     * @param Vtiger_Record_Model $payment Saved payment record
     * @return void
     * @throws Exception
     */
    public function updateReadonlyValues(Vtiger_Record_Model $payment): void
    {
        $values = IncomingPayment_PaymentValues_Helper::getReadonlyValues($payment);
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
        $associated = IncomingPayment_Associated_Model::getInstance();
        $paymentIds = $associated->fetchPaymentIds($recordId);
        $associated->deleteByRecord($recordId);

        foreach ($paymentIds as $paymentId) {
            if (isRecordExists($paymentId)) {
                $this->updateReadonlyValues(Vtiger_Record_Model::getInstanceById($paymentId, IncomingPayment_Config_Helper::MODULE));
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
        $this->recalculateDocuments(IncomingPayment_Associated_Model::getInstance()->fetchRecordIds($paymentId));
    }

    /**
     * Recalculates every document that has a payment, for example after the underpayment threshold changed.
     *
     * @return void
     * @throws Exception
     */
    public function recalculateAllDocuments(): void
    {
        $this->recalculateDocuments(IncomingPayment_Associated_Model::getInstance()->fetchAllRecordIds());
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
     * Recalculates the paid amount, balance and status of one document from its received payments.
     *
     * @param string $module   Document module name
     * @param int    $recordId Document record id
     * @return void
     * @throws Exception
     */
    public function recalculateDocument(string $module, int $recordId): void
    {
        if (isset(self::$processing[$recordId])) {
            return;
        }

        $config = IncomingPayment_Integration_Model::getInstance()->retrieveByModule($module);

        if ((int)$config['active'] !== 1 || $config['total_field'] === '') {
            return;
        }

        self::$processing[$recordId] = true;
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
        $currencyId = (int)$recordModel->get('currency_id');
        $documentRate = $this->getDocumentRate($recordModel);
        $total =IncomingPayment_Calculation_Helper::parseAmount($recordModel->get((string)$config['total_field']));
        $paid = IncomingPayment_Calculation_Helper::roundStoredAmount($this->sumPaidAmount($recordId, $currencyId, $documentRate));
        $balance = IncomingPayment_Calculation_Helper::roundStoredAmount($total - $paid);
        IncomingPayment_Document_Helper::updateDocument($recordModel, $config, $paid, $balance, $total);
        unset(self::$processing[$recordId]);
    }

    /**
     * Returns the conversion rate stored on a document, or the current rate of its currency when none is stored.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @return float
     */
    private function getDocumentRate(Vtiger_Record_Model $recordModel): float
    {
        $documentRate = (float)$recordModel->get('conversion_rate');

        return $documentRate > 0 ? $documentRate : IncomingPayment_Calculation_Helper::getConversionRate((int)$recordModel->get('currency_id'));
    }

    /**
     * Returns the rate for converting a payment into the currency of its document: the rate of the document for the
     * same currency, otherwise the rate stored on the payment.
     *
     * @param array $payment            Row with currency_id and conversion_rate
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

        return $paymentRate > 0 ? $paymentRate : IncomingPayment_Calculation_Helper::getConversionRate($paymentCurrencyId);
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
     * Sums the received payments applied to a document, converted into the document currency.
     * A payment in another currency is converted through the base currency with the rate stored on the payment.
     *
     * @param int   $recordId           Document record id
     * @param int   $documentCurrencyId Currency id of the document
     * @param float $documentRate       Conversion rate of the document currency
     * @return float
     */
    public function sumPaidAmount(int $recordId, int $documentCurrencyId, float $documentRate): float
    {
        $statuses = IncomingPayment_Config_Helper::getPaidStatuses();
        $result = $this->getDB()->pquery(
            'SELECT associated.partial_amount, payment.currency_id, payment.conversion_rate
            FROM ' . IncomingPayment_Config_Helper::TABLE_ASSOCIATED . ' AS associated
            INNER JOIN ' . $this->table . ' AS payment ON payment.' . $this->tableId . ' = associated.payment_id
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = associated.payment_id
            WHERE vtiger_crmentity.deleted = 0 AND associated.record_id = ?
            AND payment.payment_status IN (' . generateQuestionMarks($statuses) . ')',
            array_merge([$recordId], $statuses)
        );
        $sum = 0.0;

        while ($row = $this->getDB()->fetchByAssoc($result)) {
            $paymentRate = $this->getPaymentRate($row, $documentCurrencyId, $documentRate);
            $sum += IncomingPayment_Calculation_Helper::convertBetweenCurrencies((float)$row['partial_amount'], $paymentRate, $documentRate);
        }

        return $sum;
    }

    /**
     * Returns the payments applied to a document, newest payment date first.
     *
     * @param int $recordId Document record id
     * @return array rows with id, payment_no, payment_name, paymentdate, payment_status, partial_amount, currency_id
     */
    public function fetchDocumentPayments(int $recordId): array
    {
        $result = $this->getDB()->pquery(
            'SELECT payment.' . $this->tableId . ' AS id, payment.payment_no, payment.payment_name, payment.paymentdate,
                payment.payment_status, associated.partial_amount, payment.currency_id
            FROM ' . IncomingPayment_Config_Helper::TABLE_ASSOCIATED . ' AS associated
            INNER JOIN ' . $this->table . ' AS payment ON payment.' . $this->tableId . ' = associated.payment_id
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = associated.payment_id
            WHERE vtiger_crmentity.deleted = 0 AND associated.record_id = ?
            ORDER BY payment.paymentdate DESC, payment.' . $this->tableId . ' DESC',
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
     * @return array rows with id, paymentdate, due_date, payment_status
     */
    public function fetchOpenPayments(): array
    {
        $statuses = IncomingPayment_Config_Helper::getPaidStatuses();
        $result = $this->getDB()->pquery(
            'SELECT payment.' . $this->tableId . ' AS id, payment.paymentdate, payment.due_date, payment.payment_status
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
        $today = IncomingPayment_Status_Helper::getToday();
        $updated = 0;

        foreach ($this->fetchOpenPayments() as $row) {
            $status = IncomingPayment_Status_Helper::getStatus(
                IncomingPayment_Status_Helper::normalizeDate($row['paymentdate']),
                IncomingPayment_Status_Helper::normalizeDate($row['due_date']),
                $today
            );

            if ($status === (string)$row['payment_status']) {
                continue;
            }

            $recordModel = Vtiger_Record_Model::getInstanceById((int)$row['id'], IncomingPayment_Config_Helper::MODULE);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
            $updated++;
        }

        return $updated;
    }
}

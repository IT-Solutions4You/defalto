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
 * Split of a payment over the documents of its Reference field. The amounts are in the payment currency and are
 * stored per document in the association table of the payment module (partial_amount).
 * - Amounts entered in the payment form (setRequestedAmounts()) win; their difference to the payment amount goes to
 *   the last document.
 * - Otherwise the stored amounts stay while the documents and the amount are unchanged (e.g. the status cron).
 * - Otherwise the amount is split by due date (the earliest first, without a due date last, then by creation): each
 *   document gets at most what it still misses (for a refund: what was paid), the last one gets the rest.
 */
class Core_PaymentAllocation_Model
{
    /**
     * Amounts entered in the payment form for the next save, per payment module: [module => [document id => amount]].
     *
     * @var array<string, array<int, float>>
     */
    private static array $requestedAmounts = [];

    protected Core_PaymentConfig_Helper $config;

    /**
     * Creates the split for a payment module.
     *
     * @param Core_PaymentConfig_Helper $config Configuration of the payment module
     */
    public function __construct(Core_PaymentConfig_Helper $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the split for a payment module.
     *
     * @param Core_PaymentConfig_Helper $config Configuration of the payment module
     * @return self
     */
    public static function getInstance(Core_PaymentConfig_Helper $config): self
    {
        return new self($config);
    }

    /**
     * Keeps the amounts entered in the payment form for the next save of a payment of the module.
     *
     * @param string             $paymentModule Payment module name
     * @param array<int, float>  $amounts       [document id => amount in the payment currency]
     * @return void
     */
    public static function setRequestedAmounts(string $paymentModule, array $amounts): void
    {
        self::$requestedAmounts[$paymentModule] = $amounts;
    }

    /**
     * Returns the amount the saved payment applies to each of its documents.
     *
     * @param Vtiger_Record_Model $payment Saved payment record
     * @return array<int, float> [document id => amount in the payment currency]
     * @throws Exception
     */
    public function getDocumentAmounts(Vtiger_Record_Model $payment): array
    {
        $documentIds = Core_PaymentValues_Helper::getDocumentIds($payment);
        $amount = Core_PaymentCalculation_Helper::parseAmount($payment->get(Core_PaymentConfig_Helper::FIELD_AMOUNT));
        $module = $this->config::MODULE;
        $requested = self::$requestedAmounts[$module] ?? null;
        unset(self::$requestedAmounts[$module]);

        if (!$documentIds) {
            return [];
        }

        if (count($documentIds) === 1) {
            return [$documentIds[0] => $amount];
        }

        $paymentId = (int)$payment->getId();
        $currencyId = (int)$payment->get(Core_PaymentConfig_Helper::FIELD_AMOUNT_CURRENCY);
        $rate = $this->getPaymentRate($currencyId, (float)$payment->get(Core_PaymentConfig_Helper::FIELD_AMOUNT_RATE));

        // an empty list means nothing was entered, the stored or suggested split applies
        if (!empty($requested)) {
            return $this->getRequestedSplit($requested, $documentIds, $amount);
        }

        $stored = $this->config->getAssociatedModel()->fetchAmounts($paymentId);

        if ($this->isSameDocumentSet(array_keys($stored), $documentIds) && Core_PaymentCalculation_Helper::isSameAmount(array_sum($stored), $amount)) {
            return $this->sortByDocuments($stored, $documentIds);
        }

        return $this->getSuggestedAmounts($amount, $currencyId, $rate, $documentIds, $paymentId);
    }

    /**
     * Splits an amount over documents by due date. Each document gets at most what it still misses (a refund at
     * most what was paid on it), converted into the payment currency; the last one gets the rest.
     *
     * @param float $amount      Payment amount in its currency
     * @param int   $currencyId  Currency of the payment
     * @param float $rate        Conversion rate of the payment currency
     * @param int[] $documentIds Document record ids
     * @param int   $paymentId   Payment being edited, whose current amounts do not count as paid; 0 for a new payment
     * @return array<int, float> [document id => amount in the payment currency], in the order of $documentIds
     * @throws Exception
     */
    public function getSuggestedAmounts(float $amount, int $currencyId, float $rate, array $documentIds, int $paymentId = 0): array
    {
        $documents = $this->getOrderedDocuments($documentIds);
        $ownAmounts = $paymentId > 0 ? $this->config->getAssociatedModel()->fetchAmounts($paymentId) : [];
        $remaining = $amount;
        $lastIndex = count($documents) - 1;
        $split = array_fill_keys($documentIds, 0.0);

        foreach ($documents as $index => $document) {
            $documentId = (int)$document->getId();

            if ($index === $lastIndex) {
                $split[$documentId] = Core_PaymentCalculation_Helper::roundStoredAmount(max(0.0, $remaining));
                break;
            }

            $documentRate = Core_PaymentCalculation_Helper::getRecordRate($document);
            $ownAmount = Core_PaymentCalculation_Helper::convertBetweenCurrencies($ownAmounts[$documentId] ?? 0.0, $rate, $documentRate);
            $capacity = Core_PaymentCalculation_Helper::convertBetweenCurrencies($this->getDocumentCapacity($document, $ownAmount), $documentRate, $rate);
            $part = Core_PaymentCalculation_Helper::roundStoredAmount(max(0.0, min($remaining, $capacity)));
            $split[$documentId] = $part;
            $remaining -= $part;
        }

        return $split;
    }

    /**
     * Returns what a document can still take from this payment, in the document currency: the open amount for
     * a payment, the paid amount for a refund. The current amount of the edited payment is added back.
     *
     * @param Vtiger_Record_Model $document  Document record
     * @param float               $ownAmount Amount the edited payment applies to the document now, in the document currency; 0 for a new payment
     * @return float
     * @throws Exception
     */
    public function getDocumentCapacity(Vtiger_Record_Model $document, float $ownAmount = 0.0): float
    {
        $documentModule = $document->getModuleName();
        $balanceModel = Core_PaymentDocumentBalance_Model::getInstance();

        if ($this->config->isRefundFor($documentModule)) {
            return max(0.0, $balanceModel->getNetAmount($document, true) + $ownAmount);
        }

        $payingModule = Core_PaymentDocumentBalance_Model::getPayingModule($documentModule);
        $config = $payingModule !== '' ? Core_PaymentConfig_Helper::getInstance($payingModule)->getIntegrationModel()->retrieveByModule($documentModule) : [];
        $totalField = (string)($config['total_field'] ?? '');
        $total = $totalField !== '' ? Core_PaymentCalculation_Helper::parseAmount($document->get($totalField)) : 0.0;

        return max(0.0, $total - $balanceModel->getNetAmount($document, false) + $ownAmount);
    }

    /**
     * Applies the amounts entered in the form: only the payment's documents, no negative amounts, documents without
     * an amount get 0, and the difference to the payment amount goes to the last document by due date.
     *
     * @param array<int, float> $requested   Entered amounts [document id => amount]
     * @param int[]             $documentIds Documents of the payment
     * @param float             $amount      Payment amount
     * @return array<int, float>
     * @throws Exception
     */
    protected function getRequestedSplit(array $requested, array $documentIds, float $amount): array
    {
        $split = [];

        foreach ($documentIds as $documentId) {
            $split[$documentId] = Core_PaymentCalculation_Helper::roundStoredAmount(max(0.0, (float)($requested[$documentId] ?? 0.0)));
        }

        $documents = $this->getOrderedDocuments($documentIds);
        $lastId = (int)end($documents)->getId();
        $split[$lastId] = Core_PaymentCalculation_Helper::roundStoredAmount(max(0.0, $split[$lastId] + $amount - array_sum($split)));

        return $split;
    }

    /**
     * Returns the documents in split order: due date (the earliest first, without a due date last), creation time, id.
     *
     * @param int[] $documentIds Document record ids
     * @return Vtiger_Record_Model[]
     * @throws Exception
     */
    public function getOrderedDocuments(array $documentIds): array
    {
        $documents = [];

        foreach ($documentIds as $documentId) {
            $module = (string)getSalesEntityType((int)$documentId);

            if ($module !== '') {
                $documents[] = Vtiger_Record_Model::getInstanceById((int)$documentId, $module);
            }
        }

        usort($documents, static function (Vtiger_Record_Model $first, Vtiger_Record_Model $second): int {
            return self::getSortKey($first) <=> self::getSortKey($second);
        });

        return $documents;
    }

    /**
     * Returns the sort key of a document: no due date sorts last.
     *
     * @param Vtiger_Record_Model $document Document record
     * @return array
     */
    private static function getSortKey(Vtiger_Record_Model $document): array
    {
        $dueDateField = Core_PaymentIntegrationFields_Helper::getFirstField($document->getModule(), Core_PaymentConfig_Helper::DOCUMENT_DUE_DATE_FIELDS);
        $dueDate = $dueDateField !== '' ? Core_PaymentStatus_Helper::normalizeDate($document->get($dueDateField)) : '';

        return [$dueDate === '' ? 1 : 0, $dueDate, (string)$document->get('createdtime'), (int)$document->getId()];
    }

    /**
     * Returns the rate of the payment currency: the stored rate, otherwise the current rate of the currency.
     *
     * @param int   $currencyId Currency of the payment
     * @param float $storedRate Rate stored on the payment
     * @return float
     */
    protected function getPaymentRate(int $currencyId, float $storedRate): float
    {
        return $storedRate > 0 ? $storedRate : Core_PaymentCalculation_Helper::getConversionRate($currencyId);
    }

    /**
     * Tells whether two lists hold the same document ids.
     *
     * @param int[] $first  Document ids
     * @param int[] $second Document ids
     * @return bool
     */
    private function isSameDocumentSet(array $first, array $second): bool
    {
        $first = array_map('intval', $first);
        $second = array_map('intval', $second);
        sort($first);
        sort($second);

        return $first === $second;
    }

    /**
     * Returns the amounts in the order of the documents.
     *
     * @param array<int, float> $amounts     [document id => amount]
     * @param int[]             $documentIds Document ids in the wanted order
     * @return array<int, float>
     */
    private function sortByDocuments(array $amounts, array $documentIds): array
    {
        $sorted = [];

        foreach ($documentIds as $documentId) {
            $sorted[(int)$documentId] = (float)($amounts[(int)$documentId] ?? 0.0);
        }

        return $sorted;
    }
}

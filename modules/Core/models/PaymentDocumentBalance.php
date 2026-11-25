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
 * Net paid amount of a document across the payment modules. One payment module pays a document module (integration
 * column is_refund = 0); the payments of the other modules integrated into it are refunds and are subtracted.
 * The paying module writes the paid amount, the balance and the status with its own fields, status values and
 * underpayment threshold, whichever payment module started the recalculation.
 */
class Core_PaymentDocumentBalance_Model
{
    /**
     * Documents being recalculated, shared by all payment modules, so a document save started by the
     * recalculation does not recalculate the document again.
     *
     * @var array<int, true>
     */
    private static array $processing = [];

    /**
     * Returns a new instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Returns the active payment module that pays a document module, or an empty string when none does.
     *
     * @param string   $documentModule  Document module name
     * @param string[] $excludedModules Payment modules treated as inactive, e.g. while their integration is removed
     * @return string
     * @throws Exception
     */
    public static function getPayingModule(string $documentModule, array $excludedModules = []): string
    {
        foreach (Core_PaymentConfig_Helper::getActiveModules($excludedModules) as $paymentModule) {
            if (Core_PaymentConfig_Helper::getInstance($paymentModule)->getIntegrationModel()->isPaying($documentModule)) {
                return $paymentModule;
            }
        }

        return '';
    }

    /**
     * Returns the active payment modules whose payments are refunds of a document module.
     *
     * @param string $documentModule Document module name
     * @return string[]
     * @throws Exception
     */
    public static function getRefundModules(string $documentModule): array
    {
        $modules = [];

        foreach (Core_PaymentConfig_Helper::getActiveModules() as $paymentModule) {
            $paymentConfig = Core_PaymentConfig_Helper::getInstance($paymentModule);

            if ($paymentConfig->getIntegrationModel()->isActive($documentModule) && $paymentConfig->isRefundFor($documentModule)) {
                $modules[] = $paymentModule;
            }
        }

        return $modules;
    }

    /**
     * Recalculates the paid amount, balance and status of a document: received payments of the paying module minus
     * received refunds of the other modules plus the received payments of its source documents that fall on it,
     * each converted into the document currency. Nothing happens when no active payment module pays the document
     * module. A source document (sales order) recalculates its target documents (invoices) afterwards. A final
     * invoice covered by its paid advances counts as paid (Core_PaymentSourceDocument_Model::isCoveredByAdvance()).
     *
     * @param string $documentModule Document module name
     * @param int    $recordId       Document record id
     * @param bool   $keepStatus     true to keep the status, e.g. when the document save itself changed it
     * @return void
     * @throws Exception
     */
    public function recalculateDocument(string $documentModule, int $recordId, bool $keepStatus = false): void
    {
        if (isset(self::$processing[$recordId])) {
            return;
        }

        $payingModule = self::getPayingModule($documentModule);

        if ($payingModule === '') {
            return;
        }

        $payingConfig = Core_PaymentConfig_Helper::getInstance($payingModule);
        $config = $payingConfig->getIntegrationModel()->retrieveByModule($documentModule);
        self::$processing[$recordId] = true;

        try {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $documentModule);
            $total = Core_PaymentCalculation_Helper::parseAmount($recordModel->get((string)$config['total_field']));
            $paid = Core_PaymentCalculation_Helper::roundStoredAmount($this->getNetAmount($recordModel, true));
            $balance = Core_PaymentCalculation_Helper::roundStoredAmount($total - $paid);
            $isCoveredByAdvance = Core_PaymentSourceDocument_Model::getInstance()->isCoveredByAdvance($recordModel);
            Core_PaymentDocument_Helper::updateDocument($payingConfig, $recordModel, $config, $paid, $balance, $total, $keepStatus, $isCoveredByAdvance);
        } finally {
            unset(self::$processing[$recordId]);
        }

        if (Core_PaymentSourceDocument_Model::isSourceModule($documentModule)) {
            Core_PaymentSourceDocument_Model::getInstance()->recalculateTargets($documentModule, $recordId);
        }
    }

    /**
     * Returns the payments of the paying module minus the refunds of the other modules applied to a document,
     * plus the payments of its source documents that fall on it (e.g. the sales order of an invoice), in the
     * document currency: only received ones, or all of them regardless of their status.
     *
     * @param Vtiger_Record_Model $recordModel  Document record
     * @param bool                $onlyReceived true for received payments only, false for all payments
     * @return float
     * @throws Exception
     */
    public function getNetAmount(Vtiger_Record_Model $recordModel, bool $onlyReceived): float
    {
        $isReceived = $onlyReceived ? true : null;

        return $this->getOwnNetAmount($recordModel, $isReceived)
            + Core_PaymentSourceDocument_Model::getInstance()->getAllocatedAmount($recordModel, $isReceived);
    }

    /**
     * Tells whether the current save of a document changed its status (a user or an extension set it), so that the
     * recalculation of the paid amount keeps that status.
     *
     * @param string $documentModule Document module name
     * @param int    $recordId       Document record id
     * @return bool
     * @throws Exception
     */
    public function isStatusChangedBySave(string $documentModule, int $recordId): bool
    {
        $payingModule = self::getPayingModule($documentModule);

        if ($payingModule === '') {
            return false;
        }

        $config = Core_PaymentConfig_Helper::getInstance($payingModule)->getIntegrationModel()->retrieveByModule($documentModule);
        $statusField = (string)($config['status_field'] ?? '');

        if ($statusField === '') {
            return false;
        }

        require_once 'data/VTEntityDelta.php';

        return (bool)(new VTEntityDelta())->hasChanged($documentModule, $recordId, $statusField);
    }

    /**
     * Returns the payments of the paying module minus the refunds that are not received yet, in the document
     * currency: the expected amount of the payments widget.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @return float
     * @throws Exception
     */
    public function getOpenNetAmount(Vtiger_Record_Model $recordModel): float
    {
        return $this->getOwnNetAmount($recordModel, false);
    }

    /**
     * Returns the payments of the paying module minus the refunds of the other modules applied to the document
     * itself, without the payments of its source documents, in the document currency.
     *
     * @param Vtiger_Record_Model $recordModel Document record
     * @param bool|null           $isReceived  true for received payments, false for the others, null for all
     * @return float
     * @throws Exception
     */
    public function getOwnNetAmount(Vtiger_Record_Model $recordModel, ?bool $isReceived): float
    {
        $documentModule = $recordModel->getModuleName();
        $payingModule = self::getPayingModule($documentModule);

        if ($payingModule === '') {
            return 0.0;
        }

        $amount = $this->getModuleAmount($payingModule, $recordModel, $isReceived);

        foreach (self::getRefundModules($documentModule) as $refundModule) {
            $amount -= $this->getModuleAmount($refundModule, $recordModel, $isReceived);
        }

        return $amount;
    }

    /**
     * Returns the payments of one payment module applied to a document, in the document currency.
     *
     * @param string              $paymentModule Payment module name
     * @param Vtiger_Record_Model $recordModel   Document record
     * @param bool|null           $isReceived    true for received payments, false for the others, null for all
     * @return float
     * @throws Exception
     */
    private function getModuleAmount(string $paymentModule, Vtiger_Record_Model $recordModel, ?bool $isReceived): float
    {
        $syncModel = Core_PaymentConfig_Helper::getInstance($paymentModule)->getDocumentSyncModel();

        return $syncModel->sumAmount(
            (int)$recordModel->getId(),
            (int)$recordModel->get('currency_id'),
            $syncModel->getDocumentRate($recordModel),
            $isReceived
        );
    }
}

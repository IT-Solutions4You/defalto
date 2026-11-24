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
 * Suggests the split of a payment over its documents for the payment form
 * (index.php?module=<payment module>&action=PaymentAllocation). Read only; the split is stored on save.
 */
class Core_PaymentAllocation_Action extends Core_Controller_Action
{
    /**
     * Requires access to the payment module.
     *
     * @param Vtiger_Request $request
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    /**
     * Returns one row per document the user may see: number, name, what the document can still take (in its
     * currency) and the suggested or stored amount of this payment (in the payment currency, edit format).
     *
     * @param Vtiger_Request $request module, record (edited payment), amount (user format), currency_id, document_ids
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $paymentModule = $request->getModule();

        if (!in_array($paymentModule, Core_PaymentConfig_Helper::PAYMENT_MODULES, true)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $config = Core_PaymentConfig_Helper::getInstance($paymentModule);
        $paymentId = (int)$request->get('record');
        $amount = (float)CurrencyField::convertToDBFormat((string)$request->get('amount'), null, true);
        $currencyId = (int)$request->get('currency_id') ?: (int)CurrencyField::getDBCurrencyId();
        $documentIds = $this->getDocumentIds($request->get('document_ids'));
        $allocation = Core_PaymentAllocation_Model::getInstance($config);
        $rate = Core_PaymentCalculation_Helper::getConversionRate($currencyId);
        $stored = $paymentId > 0 ? $config->getAssociatedModel()->fetchAmounts($paymentId) : [];
        $amounts = $this->isStoredSplit($stored, $documentIds, $amount)
            ? $stored
            : $allocation->getSuggestedAmounts($amount, $currencyId, $rate, $documentIds, $paymentId);
        $rows = [];

        foreach ($documentIds as $documentId) {
            $module = (string)getSalesEntityType($documentId);
            $document = Vtiger_Record_Model::getInstanceById($documentId, $module);
            $rows[] = [
                'id' => $documentId,
                'number' => Core_PaymentValues_Helper::getRecordNumber($document),
                'name' => decode_html((string)$document->getName()),
                'amount' => Core_PaymentCalculation_Helper::formatEditAmount((float)($amounts[$documentId] ?? 0.0)),
            ];
        }

        $response = new Vtiger_Response();
        $response->setResult(['rows' => $rows]);
        $response->emit();
    }

    /**
     * Returns the existing document ids the current user may see.
     *
     * @param mixed $value Ids as an array or a list separated by ; or ,
     * @return int[]
     */
    protected function getDocumentIds(mixed $value): array
    {
        $ids = [];

        foreach (Core_Multireference_UIType::getRecordIds($value) as $documentId) {
            $module = (string)getSalesEntityType($documentId);

            if ($module !== '' && isRecordExists($documentId) && Users_Privileges_Model::isPermitted($module, 'DetailView', $documentId)) {
                $ids[] = $documentId;
            }
        }

        return $ids;
    }

    /**
     * Tells whether the stored split of the edited payment still fits: same documents, same amount.
     *
     * @param array<int, float> $stored      Stored amounts [document id => amount]
     * @param int[]             $documentIds Documents in the form
     * @param float             $amount      Amount in the form
     * @return bool
     */
    protected function isStoredSplit(array $stored, array $documentIds, float $amount): bool
    {
        $storedIds = array_keys($stored);
        sort($storedIds);
        $formIds = $documentIds;
        sort($formIds);

        return $stored && $storedIds === $formIds && Core_PaymentCalculation_Helper::isSameAmount(array_sum($stored), $amount);
    }
}

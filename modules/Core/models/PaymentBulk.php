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
 * Bulk payment of documents selected in a document list, as in Cashflow: one payment of the module that pays the
 * document module, with all selected documents in its Reference field and the amount split over them. All documents
 * must belong to one organisation; their currencies may differ.
 */
class Core_PaymentBulk_Model
{
    protected Core_PaymentConfig_Helper $config;
    protected string $documentModule;

    /**
     * Creates the bulk payment of a payment module for a document module.
     *
     * @param Core_PaymentConfig_Helper $config         Configuration of the payment module
     * @param string                    $documentModule Document module of the list
     */
    public function __construct(Core_PaymentConfig_Helper $config, string $documentModule)
    {
        $this->config = $config;
        $this->documentModule = $documentModule;
    }

    /**
     * Returns the bulk payment for a request with module (payment module) and source_module (document module).
     *
     * @param Vtiger_Request $request
     * @return self
     * @throws Exception
     */
    public static function getInstanceFromRequest(Vtiger_Request $request): self
    {
        $paymentModule = $request->getModule();
        $documentModule = (string)$request->get('source_module');

        if (!in_array($paymentModule, Core_PaymentConfig_Helper::PAYMENT_MODULES, true) || !Core_PaymentConfig_Helper::isDocumentModule($documentModule)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $config = Core_PaymentConfig_Helper::getInstance($paymentModule);

        if (!$config->getIntegrationModel()->isActive($documentModule)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return new self($config, $documentModule);
    }

    /**
     * Returns the selected documents of the list request: the selected ids, or with selected_ids = all every record
     * of the list filter and search except the excluded ones.
     *
     * @param Vtiger_Request $request List selection (selected_ids, excluded_ids, viewname, search_params, ...)
     * @return int[]
     */
    public function getSelectedIds(Vtiger_Request $request): array
    {
        $selectedIds = $request->get('selected_ids');

        if ('all' !== $selectedIds) {
            return array_values(array_unique(array_filter(array_map('intval', (array)$selectedIds))));
        }

        $customView = CustomView_Record_Model::getInstanceById((int)$request->get('viewname'));

        if (!$customView) {
            return [];
        }

        $searchParams = $request->get('search_params');
        $customView->set('search_params', is_array($searchParams) ? $searchParams : []);

        if ($request->get('operator')) {
            $customView->set('operator', $request->get('operator'));
            $customView->set('search_key', $request->get('search_key'));
            $customView->set('search_value', $request->get('search_value'));
        }

        return array_map('intval', $customView->getRecordIds((array)($request->get('excluded_ids') ?: []), $this->documentModule));
    }

    /**
     * Returns the selected documents the user may see, in split order, with what each still misses in its currency.
     *
     * @param int[] $documentIds Selected document ids
     * @return array<int, array{id: int, number: string, name: string, dueDate: string, currencyId: int, rate: float, open: float, account: int}>
     * @throws Exception when nothing is selected or the documents belong to different organisations
     */
    public function getDocuments(array $documentIds): array
    {
        $documents = [];
        $accountField = (string)($this->config->getIntegrationModel()->retrieveByModule($this->documentModule)['relatedto_field'] ?? '');

        foreach ($documentIds as $documentId) {
            if (!isRecordExists($documentId) || getSalesEntityType($documentId) !== $this->documentModule
                || !Users_Privileges_Model::isPermitted($this->documentModule, 'DetailView', $documentId)) {
                continue;
            }

            $documents[] = Vtiger_Record_Model::getInstanceById($documentId, $this->documentModule);
        }

        if (!$documents) {
            throw new Exception(vtranslate('LBL_BULK_PAYMENT_NO_DOCUMENTS', $this->config::MODULE));
        }

        $accounts = array_unique(array_map(static fn(Vtiger_Record_Model $document): int => $accountField !== '' ? (int)$document->get($accountField) : 0, $documents));

        if (count($accounts) > 1) {
            throw new Exception(vtranslate('LBL_BULK_PAYMENT_ORGANIZATIONS', $this->config::MODULE));
        }

        $allocation = Core_PaymentAllocation_Model::getInstance($this->config);
        $rows = [];

        foreach ($allocation->getOrderedDocuments(array_map(static fn(Vtiger_Record_Model $document): int => (int)$document->getId(), $documents)) as $document) {
            $dueDateField = Core_PaymentIntegrationFields_Helper::getFirstField($document->getModule(), Core_PaymentConfig_Helper::DOCUMENT_DUE_DATE_FIELDS);
            $dueDate = $dueDateField !== '' ? Core_PaymentStatus_Helper::normalizeDate($document->get($dueDateField)) : '';
            $rows[] = [
                'id' => (int)$document->getId(),
                'number' => Core_PaymentValues_Helper::getRecordNumber($document),
                'name' => decode_html((string)$document->getName()),
                'dueDate' => $dueDate !== '' ? DateTimeField::convertToUserFormat($dueDate) : '',
                'currencyId' => (int)$document->get('currency_id') ?: (int)CurrencyField::getDBCurrencyId(),
                'rate' => Core_PaymentCalculation_Helper::getRecordRate($document),
                'open' => Core_PaymentCalculation_Helper::roundStoredAmount($allocation->getDocumentCapacity($document)),
                'account' => $accountField !== '' ? (int)$document->get($accountField) : 0,
            ];
        }

        return $rows;
    }

    /**
     * Creates the payment of the selected documents with the entered split and returns its id.
     *
     * @param int[]             $documentIds Selected document ids
     * @param float             $amount      Payment amount
     * @param int               $currencyId  Payment currency
     * @param string            $paymentDate Payment date in the user format; empty for none
     * @param array<int, float> $amounts     Entered split [document id => amount in the payment currency]
     * @return int
     * @throws Exception
     */
    public function createPayment(array $documentIds, float $amount, int $currencyId, string $paymentDate, array $amounts): int
    {
        $documents = $this->getDocuments($documentIds);
        $paymentModule = $this->config::MODULE;
        $numbers = implode(', ', array_column($documents, 'number'));
        $payment = Vtiger_Record_Model::getCleanInstance($paymentModule);
        $payment->set(Core_PaymentConfig_Helper::FIELD_NAME, trim(vtranslate('SINGLE_' . $paymentModule, $paymentModule) . ' ' . $numbers));
        $payment->set(Core_PaymentConfig_Helper::FIELD_AMOUNT, $amount);
        $payment->set(Core_PaymentConfig_Helper::FIELD_AMOUNT_CURRENCY, $currencyId);
        $payment->set(Core_PaymentConfig_Helper::FIELD_PAYMENT_DATE, $paymentDate !== '' ? DateTimeField::convertToDBFormat($paymentDate) : '');
        $payment->set(Core_PaymentConfig_Helper::FIELD_RELATION, Core_Multireference_UIType::convertToValue(array_column($documents, 'id')));
        $payment->set('assigned_user_id', Users_Record_Model::getCurrentUserModel()->getId());

        if ($documents[0]['account'] > 0) {
            $payment->set(Core_PaymentConfig_Helper::FIELD_RELATED_TO, $documents[0]['account']);
        }

        Core_PaymentAllocation_Model::setRequestedAmounts($paymentModule, $amounts);
        $payment->save();

        return (int)$payment->getId();
    }
}

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
 * Creates the payment of the bulk payment window (Core_PaymentBulk_View): one payment of the payment module with all
 * documents and the entered split.
 */
class Core_PaymentBulkSave_Action extends Core_Controller_Action
{
    /**
     * Requires creating payments and seeing the document module.
     *
     * @param Vtiger_Request $request
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [
            ['module_parameter' => 'module', 'action' => 'CreateView'],
            ['module_parameter' => 'source_module', 'action' => 'DetailView'],
        ];
    }

    /**
     * Creating a payment changes data, so the request must pass the write access check.
     *
     * @param Vtiger_Request $request
     * @return bool
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * Creates the payment and returns its id and detail URL.
     *
     * @param Vtiger_Request $request module, source_module, document_ids (12;15), amount, currency_id, payment_date,
     *                                payment_allocation[document id]
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $bulkModel = Core_PaymentBulk_Model::getInstanceFromRequest($request);
        $amounts = [];

        foreach ((array)$request->get('payment_allocation') as $documentId => $value) {
            if ((int)$documentId > 0 && trim((string)$value) !== '') {
                $amounts[(int)$documentId] = (float)CurrencyField::convertToDBFormat((string)$value, null, true);
            }
        }

        $paymentId = $bulkModel->createPayment(
            Core_Multireference_UIType::getRecordIds($request->get('document_ids')),
            (float)CurrencyField::convertToDBFormat((string)$request->get('amount'), null, true),
            (int)$request->get('currency_id'),
            (string)$request->get('payment_date'),
            $amounts
        );

        $response = new Vtiger_Response();
        $response->setResult([
            'record' => $paymentId,
            'url' => 'index.php?module=' . $request->getModule() . '&view=Detail&record=' . $paymentId,
            'message' => vtranslate('LBL_BULK_PAYMENT_CREATED', $request->getModule()),
        ]);
        $response->emit();
    }
}

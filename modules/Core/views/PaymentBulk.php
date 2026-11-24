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
 * Window of the bulk payment of documents selected in a list
 * (index.php?module=<payment module>&view=PaymentBulk&source_module=<document module> with the list selection).
 * Opened by Core_PaymentBulk_Js; saved by Core_PaymentBulkSave_Action.
 */
class Core_PaymentBulk_View extends Vtiger_IndexAjax_View
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
     * Renders the selected documents with their open amounts, the payment amount, currency and date.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $paymentModule = $request->getModule();
        $documents = [];
        $error = '';

        try {
            $bulkModel = Core_PaymentBulk_Model::getInstanceFromRequest($request);
            $documents = $bulkModel->getDocuments($bulkModel->getSelectedIds($request));
        } catch (Exception $exception) {
            $error = $exception->getMessage();
        }

        $currencies = getAllCurrencies();
        $currencyId = $documents ? $documents[0]['currencyId'] : (int)CurrencyField::getDBCurrencyId();

        $viewer->assign('MODULE', $paymentModule)
            ->assign('SOURCE_MODULE', (string)$request->get('source_module'))
            ->assign('DOCUMENTS', $documents)
            ->assign('CURRENCIES', $currencies)
            ->assign('CURRENCY_ID', $currencyId)
            ->assign('PAYMENT_DATE', DateTimeField::convertToUserFormat(date('Y-m-d')))
            ->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel())
            ->assign('ERROR', $error)
            ->view('PaymentBulk.tpl', 'Core');
    }
}

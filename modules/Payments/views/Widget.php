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
 * Common payments widget on a document: the payments of every payment module integrated on the document module.
 * Core_PaymentWidget_Model registers it instead of the widgets of the payment modules while Payments is active.
 */
class Payments_Widget_View extends Core_Widget_View
{
    /**
     * Exposes the payments widget mode in addition to the core widget modes.
     */
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showPayments');
    }

    /**
     * Renders the payments of a document from all integrated payment modules with the document totals.
     *
     * @param Vtiger_Request $request
     * @return string
     * @throws Exception
     */
    public function showPayments(Vtiger_Request $request): string
    {
        $sourceModule = (string)$request->get('sourceModule');
        $sourceRecord = (int)$request->get('sourceRecord');
        $summary = Core_PaymentWidgetSummary_Helper::getDocumentSummary(
            Core_PaymentWidgetSummary_Helper::getIntegratedModules($sourceModule),
            $sourceModule,
            $sourceRecord
        );

        return $this->getViewer($request)
            ->assign('MODULE_NAME', $request->getModule())
            ->assign('SOURCE_MODULE', $sourceModule)
            ->assign('SOURCE_RECORD', $sourceRecord)
            ->assign('SUMMARY', $summary)
            ->view('PaymentsWidget.tpl', $request->getModule(), true);
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_Widget_View extends Core_Widget_View
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
     * Renders the payments widget of a document: its payments, the total, the paid amount and the balance.
     *
     * @param Vtiger_Request $request
     * @return string
     * @throws Exception
     */
    public function showPayments(Vtiger_Request $request): string
    {
        $sourceModule = (string)$request->get('sourceModule');
        $sourceRecord = (int)$request->get('sourceRecord');
        $summary = IncomingPayment_WidgetSummary_Helper::getSummary($sourceModule, $sourceRecord);

        return $this->getViewer($request)
            ->assign('MODULE_NAME', $request->getModule())
            ->assign('SOURCE_MODULE', $sourceModule)
            ->assign('SOURCE_RECORD', $sourceRecord)
            ->assign('SUMMARY', $summary)
            ->view('PaymentsWidget.tpl', $request->getModule(), true);
    }
}

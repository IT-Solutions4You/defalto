<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_Edit_View extends Vtiger_Edit_View
{
    /**
     * Prefills a new payment created from a document (open amount, currency, due date, organisation, contact)
     * and renders the standard edit view.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        if ($request->isEmpty('record') && !$request->isEmpty('sourceModule') && !$request->isEmpty('sourceRecord')) {
            IncomingPayment_Prefill_Helper::setRequestValues($request, (string)$request->get('sourceModule'), (int)$request->get('sourceRecord'));
        }

        parent::process($request);
    }
}

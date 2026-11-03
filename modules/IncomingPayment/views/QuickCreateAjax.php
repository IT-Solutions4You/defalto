<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
    /**
     * Prefills a payment opened for a document (open amount, currency, due date, organisation)
     * and renders the standard quick create form.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $relationId = (int)$request->get(IncomingPayment_Config_Helper::FIELD_RELATION);

        if ($relationId > 0) {
            IncomingPayment_Prefill_Helper::setRequestValues($request, (string)getSalesEntityType($relationId), $relationId);
        }

        parent::process($request);
    }
}

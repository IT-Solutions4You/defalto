<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class Core_PaymentQuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
    /**
     * Returns the configuration of the payment module this class belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

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
        $relationId = (int)$request->get(Core_PaymentConfig_Helper::FIELD_RELATION);

        if ($relationId > 0) {
            Core_PaymentPrefill_Helper::setRequestValues($this->getConfig(), $request, (string)getSalesEntityType($relationId), $relationId);
        }

        parent::process($request);
    }
}

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
 * Save of the full payment form: passes the amounts entered per document (payment_allocation[document id]) to
 * Core_PaymentAllocation_Model before the record is saved. Each payment module extends it as <Module>_Save_Action.
 */
abstract class Core_PaymentSave_Action extends Vtiger_Save_Action
{
    /**
     * Keeps the entered split for the save and saves the payment.
     *
     * @param Vtiger_Request $request
     * @return Vtiger_Record_Model
     * @throws Exception
     */
    public function saveRecord($request)
    {
        $allocation = $request->get('payment_allocation');

        if (is_array($allocation) && $allocation) {
            Core_PaymentAllocation_Model::setRequestedAmounts($request->getModule(), $this->getAllocationAmounts($allocation));
        }

        return parent::saveRecord($request);
    }

    /**
     * Converts the entered amounts from the user format: [document id => amount].
     *
     * @param array $allocation Submitted amounts per document id
     * @return array<int, float>
     */
    protected function getAllocationAmounts(array $allocation): array
    {
        $amounts = [];

        foreach ($allocation as $documentId => $value) {
            if ((int)$documentId > 0 && trim((string)$value) !== '') {
                $amounts[(int)$documentId] = (float)CurrencyField::convertToDBFormat((string)$value, null, true);
            }
        }

        return $amounts;
    }
}

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
 * Reference field of a payment: limits the edit form to the number of documents a payment may reference.
 * The same limit is enforced on save by IncomingPayment_PaymentValues_Helper::setEditableValues().
 */
class IncomingPayment_Multireference_UIType extends Core_Multireference_UIType
{
    /**
     * @inheritDoc
     */
    public function getMaximumRecords(): int
    {
        return $this->isReferenceField() ? IncomingPayment_Config_Helper::REFERENCE_LIMIT : parent::getMaximumRecords();
    }

    /**
     * @inheritDoc
     */
    public function getMaximumRecordsMessage(): string
    {
        return $this->isReferenceField() ? vtranslate(IncomingPayment_Config_Helper::LBL_REFERENCE_LIMIT, IncomingPayment_Config_Helper::MODULE) : parent::getMaximumRecordsMessage();
    }

    /**
     * Tells whether the field is the payment's Reference field.
     *
     * @return bool
     */
    private function isReferenceField(): bool
    {
        return IncomingPayment_Config_Helper::FIELD_RELATION === $this->get('field')->getName();
    }
}

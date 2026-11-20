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
 * The same limit is enforced on save by Core_PaymentValues_Helper::validateReferences().
 */
abstract class Core_PaymentMultireference_UIType extends Core_Multireference_UIType
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
     * @inheritDoc
     */
    public function getMaximumRecords(): int
    {
        return $this->isReferenceField() ? Core_PaymentConfig_Helper::REFERENCE_LIMIT : parent::getMaximumRecords();
    }

    /**
     * @inheritDoc
     */
    public function getMaximumRecordsMessage(): string
    {
        return $this->isReferenceField() ? vtranslate(Core_PaymentConfig_Helper::LBL_REFERENCE_LIMIT, $this->getConfig()::MODULE) : parent::getMaximumRecordsMessage();
    }

    /**
     * Tells whether the field is the payment's Reference field.
     *
     * @return bool
     */
    private function isReferenceField(): bool
    {
        return Core_PaymentConfig_Helper::FIELD_RELATION === $this->get('field')->getName();
    }
}

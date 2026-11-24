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
 * Reference field of a payment: a multi reference field without a limit, so one payment may pay several documents.
 * When the payment modules are split into a free and a paid edition, the limit goes here (getMaximumRecords(),
 * getMaximumRecordsMessage() and isMaximumRecordsPremium() of Core_Multireference_UIType, which opens the Installer
 * buy window) together with the before-save check in Core_PaymentSave_Handler.
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
}

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
 * Edition of the payment modules. The paid edition is unlocked by a licence of the Payments extension or by an active
 * Membership; this is the only place that asks the Installer about it (stored licence state, no call of the licence
 * server). The free edition allows one document per payment. The paid features (several documents per payment,
 * bulk payment) are not implemented yet, so the limit stays one document for every edition for now.
 */
class Core_PaymentLicense_Helper
{
    /**
     * Extension name of the licence that unlocks the paid edition of all payment modules.
     */
    public const LICENSE_EXTENSION = 'Payments';

    /**
     * Number of documents one payment may reference in the free edition.
     */
    public const FREE_REFERENCE_LIMIT = 1;

    /**
     * Edition state loaded in this request.
     */
    private static ?bool $isProEnabled = null;

    /**
     * Tells whether the paid edition is unlocked: a licence with the Payments entitlement or an active Membership.
     * A Membership licence may carry no extension list, so the extension check alone is not enough.
     *
     * @return bool
     * @throws Exception
     */
    public static function isProEnabled(): bool
    {
        if (null === self::$isProEnabled) {
            self::$isProEnabled = Installer_License_Model::isActiveExtension(self::LICENSE_EXTENSION)
                || Installer_License_Model::isMembershipActive();
        }

        return self::$isProEnabled;
    }

    /**
     * Returns the number of documents one payment may reference. The paid edition will lift the limit once several
     * documents per payment are implemented.
     *
     * @return int
     */
    public static function getReferenceLimit(): int
    {
        return self::FREE_REFERENCE_LIMIT;
    }

    /**
     * Tells whether reaching the document limit should offer the paid edition (buy window) instead of a plain
     * message: only while the paid edition is locked.
     *
     * @return bool
     * @throws Exception
     */
    public static function isLimitPremium(): bool
    {
        return !self::isProEnabled();
    }

    /**
     * Returns the message shown when a payment would reference more documents than allowed.
     *
     * @param string $paymentModule Payment module whose language file translates the message
     * @return string
     * @throws Exception
     */
    public static function getReferenceLimitMessage(string $paymentModule): string
    {
        $label = self::isLimitPremium() ? Core_PaymentConfig_Helper::LBL_REFERENCE_LIMIT_PREMIUM : Core_PaymentConfig_Helper::LBL_REFERENCE_LIMIT;

        return vtranslate($label, $paymentModule);
    }
}

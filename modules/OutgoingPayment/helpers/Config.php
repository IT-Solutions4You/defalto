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
 * Configuration of outgoing payments: what differs from the other payment directions. The shared identifiers
 * (fields, statuses, events) and the payment logic are in Core (Core_PaymentConfig_Helper and Core_Payment… classes).
 */
class OutgoingPayment_Config_Helper extends Core_PaymentConfig_Helper
{
    public const MODULE = 'OutgoingPayment';
    public const TABLE = 'df_outgoingpayment';
    public const TABLE_CF = 'df_outgoingpaymentcf';
    public const TABLE_INDEX = 'outgoingpaymentid';
    public const TABLE_INTEGRATION = 'df_outgoingpayment_integration';
    public const TABLE_ASSOCIATED = 'df_outgoingpayment_associated';
    public const TABLE_SETTING = 'df_outgoingpayment_setting';
    public const NUMBERING_PREFIX = 'PAYOUT';
    public const MAIN_BLOCK = 'LBL_OUTGOING_PAYMENT_INFORMATION';
    public const RELATED_LIST_LABEL = 'Outgoing Payments';
    public const ADD_LINK_LABEL = 'LBL_ADD_OUTGOING_PAYMENT';
    public const BULK_PAYMENT_LABEL = 'LBL_PAY_SELECTED_OUTGOING';
    public const BULK_PAYMENT_SCRIPT_LABEL = 'OutgoingPaymentBulkJs';
    public const INTEGRATION_LABEL = 'LBL_OUTGOING_PAYMENT_INTEGRATION';
    // The incoming payment icon turned upside down: the arrow goes down from left to right
    public const ICON = 'fa-solid fa-money-bill-trend-up fa-flip-vertical';
    public const DIRECTION = self::DIRECTION_OUTGOING;
    public const RELATED_TO_MODULES = ['Vendors', 'Accounts'];
    public const DEFAULT_PAID_FIELDS = ['paid', 'received'];
    public const PAID_FIELD_LABEL = 'Paid';

    /**
     * Purchase orders are integrated by default; the status of a paid purchase order does not change, because the
     * purchase order statuses have no paid value.
     *
     * @return array [document module => integration values]
     */
    public function getIntegrationOverrides(): array
    {
        return [
            'PurchaseOrder' => [
                'active' => 1,
            ],
        ];
    }
}

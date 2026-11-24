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
 * Configuration of incoming payments: what differs from the other payment directions. The shared identifiers
 * (fields, statuses, events) and the payment logic are in Core (Core_PaymentConfig_Helper and Core_Payment… classes).
 */
class IncomingPayment_Config_Helper extends Core_PaymentConfig_Helper
{
    public const MODULE = 'IncomingPayment';
    public const TABLE = 'df_incomingpayment';
    public const TABLE_CF = 'df_incomingpaymentcf';
    public const TABLE_INDEX = 'incomingpaymentid';
    public const TABLE_INTEGRATION = 'df_incomingpayment_integration';
    public const TABLE_ASSOCIATED = 'df_incomingpayment_associated';
    public const TABLE_SETTING = 'df_incomingpayment_setting';
    public const NUMBERING_PREFIX = 'PAYIN';
    public const MAIN_BLOCK = 'LBL_INCOMING_PAYMENT_INFORMATION';
    public const RELATED_LIST_LABEL = 'Incoming Payments';
    public const ADD_LINK_LABEL = 'LBL_ADD_INCOMING_PAYMENT';
    public const BULK_PAYMENT_LABEL = 'LBL_PAY_SELECTED_INCOMING';
    public const BULK_PAYMENT_SCRIPT_LABEL = 'IncomingPaymentBulkJs';
    public const INTEGRATION_LABEL = 'LBL_INCOMING_PAYMENT_INTEGRATION';
    public const ICON = 'fa-solid fa-money-bill-trend-up';
    public const DIRECTION = self::DIRECTION_INCOMING;
    public const RELATED_TO_MODULES = ['Accounts', 'Vendors'];
    public const DEFAULT_PAID_FIELDS = ['received', 'paid'];
    public const PAID_FIELD_LABEL = 'Received';

    /**
     * Invoices are integrated by default: a paid invoice gets the Paid status, an unpaid one goes back to Created.
     *
     * @return array [document module => integration values]
     */
    public function getIntegrationOverrides(): array
    {
        return [
            'Invoice' => [
                'active' => 1,
                'status_value' => 'Paid',
                'status_value_back' => 'Created',
            ],
        ];
    }
}

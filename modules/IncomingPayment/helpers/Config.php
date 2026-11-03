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
 * Shared identifiers of the Incoming Payment module: module name, tables, fields, statuses and integration defaults.
 */
class IncomingPayment_Config_Helper
{
    public const MODULE = 'IncomingPayment';
    public const TABLE = 'df_incomingpayment';
    public const TABLE_CF = 'df_incomingpaymentcf';
    public const TABLE_INDEX = 'incomingpaymentid';
    public const TABLE_INTEGRATION = 'df_incomingpayment_integration';
    public const TABLE_ASSOCIATED = 'df_incomingpayment_associated';

    public const FIELD_RELATION = 'relationid';
    public const FIELD_RELATED_TO = 'relatedto';
    public const FIELD_CONTACT = 'contactid';
    public const FIELD_STATUS = 'payment_status';
    public const FIELD_AMOUNT = 'paymentamount';

    /**
     * Number of documents one payment may reference; payments to several documents come with the paid version.
     */
    public const REFERENCE_LIMIT = 1;
    public const LBL_REFERENCE_LIMIT = 'LBL_ONE_DOCUMENT_PER_PAYMENT';

    public const STATUS_CREATED = 'Created';
    public const STATUS_WAITING = 'Waiting';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_RECEIVED = 'Received';

    public const WIDGET_LABEL = 'IncomingPayment';
    public const RELATED_LIST_LABEL = 'Incoming Payments';
    public const RELATED_DOCUMENTS_FUNCTION = 'get_associated_documents';
    public const ADD_LINK_LABEL = 'LBL_ADD_INCOMING_PAYMENT';
    public const ADD_LINK_TEMPLATE = 'template:AddPaymentButton.tpl:IncomingPayment';
    public const ADD_MORE_LINK_TEMPLATE = 'template:AddPaymentMoreLink.tpl:IncomingPayment';
    /**
     * relatedModule makes the core translate the widget title through this module's language file;
     * relatedField makes its Add action open the quick create form with the document in that field.
     */
    public const WIDGET_URL = 'module=IncomingPayment&view=Widget&mode=showPayments&sourceModule=$MODULE$&sourceRecord=$RECORD$&relatedModule=IncomingPayment&relatedField=relationid';
    public const RELATED_SAVE_HANDLER = 'IncomingPayment_RelatedSave_Handler';
    public const RELATED_SAVE_HANDLER_FILE = 'modules/IncomingPayment/handlers/RelatedSave.php';

    public const EVENT_BEFORE_SAVE = 'vtiger.entity.beforesave';
    public const EVENT_AFTER_SAVE_FINAL = 'vtiger.entity.aftersave.final';
    public const EVENT_AFTER_DELETE = 'vtiger.entity.afterdelete';
    public const EVENT_AFTER_RESTORE = 'vtiger.entity.afterrestore';

    /**
     * Integration columns editable from Settings.
     */
    public const INTEGRATION_COLUMNS = [
        'status_field',
        'status_value',
        'status_value_back',
        'relatedto_field',
        'total_field',
        'paid_field',
        'balance_field',
    ];

    /**
     * Field names every inventory module is expected to use; a module without them gets them chosen in Settings.
     */
    public const DEFAULT_TOTAL_FIELD = 'grand_total';
    public const DEFAULT_BALANCE_FIELD = 'balance';
    public const DEFAULT_PAID_FIELDS = ['received', 'paid'];

    /**
     * Block that renders only the document items; fields placed in it are not displayed.
     */
    public const ITEMS_BLOCK = 'LBL_ITEM_DETAILS';

    /**
     * Modules whose reference field holds the payer of a document, in order of preference.
     */
    public const RELATED_TO_MODULES = ['Accounts', 'Vendors'];

    /**
     * Returns the configuration that differs from the defaults derived from the module fields.
     *
     * @return array [module => integration values]
     */
    public static function getIntegrationOverrides(): array
    {
        return [
            'Invoice' => [
                'active' => 1,
                'status_value' => 'Paid',
                'status_value_back' => 'Created',
            ],
        ];
    }

    /**
     * Returns the statuses that count as a completed incoming payment.
     *
     * @return array
     */
    public static function getPaidStatuses(): array
    {
        return [self::STATUS_RECEIVED];
    }
}

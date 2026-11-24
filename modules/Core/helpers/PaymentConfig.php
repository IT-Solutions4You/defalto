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
 * Configuration of a payment module (IncomingPayment, OutgoingPayment). The shared identifiers (fields, statuses,
 * events, integration columns) are defined here; each payment module extends it as <Module>_Config_Helper with the
 * values of its direction (module name, tables, numbering, labels, defaults). The shared payment classes in Core read
 * the configuration of the module they serve through getForClass(static::class) or getInstance($moduleName).
 */
class Core_PaymentConfig_Helper
{
    /**
     * Values of the payment direction, set by the module configuration.
     */
    public const MODULE = '';
    public const TABLE = '';
    public const TABLE_CF = '';
    public const TABLE_INDEX = '';
    public const TABLE_INTEGRATION = '';
    public const TABLE_ASSOCIATED = '';
    public const TABLE_SETTING = '';
    public const NUMBERING_PREFIX = '';
    public const MAIN_BLOCK = '';
    public const RELATED_LIST_LABEL = '';
    public const ADD_LINK_LABEL = '';
    public const INTEGRATION_LABEL = '';
    public const ICON = 'fa-solid fa-money-bill';

    /**
     * Direction of the payments (DIRECTION_INCOMING or DIRECTION_OUTGOING), stored in the direction field of every
     * payment so the Payments list can show, filter, sort and export it like any other field.
     */
    public const DIRECTION = '';

    /**
     * Modules whose reference field holds the counterparty of a document, in order of preference.
     */
    public const RELATED_TO_MODULES = [];

    /**
     * Paid amount fields of a document, in order of preference; the first one is created when none exists.
     */
    public const DEFAULT_PAID_FIELDS = [];

    /**
     * Label of the paid amount field the integration creates on a document module without one.
     */
    public const PAID_FIELD_LABEL = '';

    /**
     * Payment modules of all directions, in the order the shared screens list them.
     */
    public const PAYMENT_MODULES = ['IncomingPayment', 'OutgoingPayment'];

    public const PAYMENTS_LIST_MODULE = 'Payments';
    public const SETTING_UNDERPAYMENT_THRESHOLD = 'underpayment_threshold';
    public const SETTING_MENU_HIDDEN = 'menu_hidden';

    /**
     * Application whose menu holds the payment modules and the Payments list.
     */
    public const MENU_APP = 'SALES';

    public const DIRECTION_INCOMING = 'Incoming';
    public const DIRECTION_OUTGOING = 'Outgoing';

    public const FIELD_NAME = 'payment_name';
    public const FIELD_NUMBER = 'payment_no';
    public const FIELD_RELATION = 'reference_id';
    public const FIELD_RELATED_TO = 'related_to';
    public const FIELD_CONTACT = 'contact_id';
    public const FIELD_STATUS = 'payment_status';
    public const FIELD_PAYMENT_DATE = 'payment_date';
    public const FIELD_DUE_DATE = 'due_date';
    public const FIELD_REFERENCE_NO = 'reference_no';
    public const FIELD_PAYMENT_METHOD = 'payment_method';
    public const FIELD_TRANSACTION_ID = 'transaction_id';
    public const FIELD_DIRECTION = 'payment_direction';

    /**
     * Amount with its own currency (uitype 35). The core keeps its hidden currency and rate fields and the column
     * with the amount in the base currency (Core_AmountCurrency_Model); the read-only base amount field shows it.
     */
    public const FIELD_AMOUNT = 'payment_amount';
    public const FIELD_AMOUNT_CURRENCY = 'payment_amount_currency_id';
    public const FIELD_AMOUNT_RATE = 'payment_amount_conversion_rate';
    public const FIELD_AMOUNT_BASE = 'payment_amount_base';

    /**
     * Messages when a payment would reference more documents than allowed (Core_PaymentLicense_Helper decides the
     * limit): a plain one, and one offering the paid edition while it is locked.
     */
    public const LBL_REFERENCE_LIMIT = 'LBL_ONE_DOCUMENT_PER_PAYMENT';
    public const LBL_REFERENCE_LIMIT_PREMIUM = 'LBL_SEVERAL_DOCUMENTS_PREMIUM';

    /**
     * Payment statuses, shared by all payment modules (one picklist vtiger_payment_status). Paid is the final status
     * of both directions; every module translates it in its own language file.
     */
    public const STATUS_CREATED = 'Created';
    public const STATUS_WAITING = 'Waiting';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_PAID = 'Paid';

    /**
     * Integration column telling that the payments of the module are refunds of the document module: they are
     * subtracted from the paid amount the opposite payment module writes (Core_PaymentDocumentBalance_Model).
     */
    public const INTEGRATION_REFUND = 'is_refund';
    public const LBL_ADD_REFUND = 'LBL_ADD_REFUND';
    public const LBL_REFUND = 'LBL_REFUND';

    public const RELATED_DOCUMENTS_FUNCTION = 'get_associated_documents';
    public const DELETE_WARNINGS_MODIFIABLE = 'DeleteWarningsAction';

    public const EVENT_BEFORE_SAVE = 'vtiger.entity.beforesave';
    public const EVENT_AFTER_SAVE_FINAL = 'vtiger.entity.aftersave.final';
    public const EVENT_AFTER_DELETE = 'vtiger.entity.afterdelete';
    public const EVENT_AFTER_RESTORE = 'vtiger.entity.afterrestore';

    /**
     * Handlers of a payment module; the class is <Module>_<Handler>_Handler in modules/<Module>/handlers/<Handler>.php.
     */
    public const HANDLER_PAYMENT_SAVE = 'PaymentSave';
    public const HANDLER_PAYMENT_DELETE = 'PaymentDelete';
    public const HANDLER_RELATED_SAVE = 'RelatedSave';
    public const HANDLER_RELATED_DELETE = 'RelatedDelete';

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

    /**
     * Total amount fields of document modules, in order of preference (Potentials keep their total in amount).
     */
    public const DEFAULT_TOTAL_FIELDS = [self::DEFAULT_TOTAL_FIELD, 'amount'];

    /**
     * Due date fields of document modules, in order of preference; the due date of a new payment comes from them.
     */
    public const DOCUMENT_DUE_DATE_FIELDS = ['duedate', 'closingdate'];

    /**
     * Modules that can receive payments besides the inventory modules of the InventoryItem registry.
     */
    public const ADDITIONAL_DOCUMENT_MODULES = ['Potentials'];
    public const DEFAULT_BALANCE_FIELD = 'balance';

    /**
     * Block that renders only the document items; fields placed in it are not displayed.
     */
    public const ITEMS_BLOCK = 'LBL_ITEM_DETAILS';

    /**
     * Configurations loaded in this request, keyed by module name.
     *
     * @var array<string, Core_PaymentConfig_Helper>
     */
    private static array $instances = [];

    /**
     * Returns the configuration of a payment module (<Module>_Config_Helper).
     *
     * @param string $moduleName Payment module name
     * @return self
     * @throws Exception
     */
    public static function getInstance(string $moduleName): self
    {
        if (!isset(self::$instances[$moduleName])) {
            $className = $moduleName . '_Config_Helper';

            if (!class_exists($className) || !is_subclass_of($className, self::class)) {
                throw new Exception('Payment module configuration not found: ' . $moduleName);
            }

            self::$instances[$moduleName] = new $className();
        }

        return self::$instances[$moduleName];
    }

    /**
     * Tells whether a module can be integrated as a document module: an inventory module or one of the
     * additional document modules. Cheap check for handlers that run for every module.
     *
     * @param string $moduleName Module name
     * @return bool
     */
    public static function isDocumentModule(string $moduleName): bool
    {
        return in_array($moduleName, self::ADDITIONAL_DOCUMENT_MODULES, true) || InventoryItem_Utils_Helper::isInventoryModule($moduleName);
    }

    /**
     * Returns the payment modules that are installed and have their configuration class.
     *
     * @param string[] $excludedModules Modules treated as not installed, e.g. a module being uninstalled
     * @return string[]
     */
    public static function getInstalledModules(array $excludedModules = []): array
    {
        $modules = [];

        foreach (self::PAYMENT_MODULES as $moduleName) {
            if (!in_array($moduleName, $excludedModules, true) && getTabid($moduleName) && class_exists($moduleName . '_Config_Helper')) {
                $modules[] = $moduleName;
            }
        }

        return $modules;
    }

    /**
     * Returns the payment modules that are installed and active.
     *
     * @param string[] $excludedModules Modules treated as inactive, e.g. a module being disabled
     * @return string[]
     */
    public static function getActiveModules(array $excludedModules = []): array
    {
        return array_values(array_filter(self::getInstalledModules($excludedModules), 'vtlib_isModuleActive'));
    }

    /**
     * Returns the configuration of the payment module a class belongs to, e.g. IncomingPayment for
     * IncomingPayment_DocumentSync_Model and for Settings_IncomingPayment_Index_View.
     *
     * @param string $className Class name of the payment module
     * @return self
     * @throws Exception
     */
    public static function getForClass(string $className): self
    {
        $parts = explode('_', $className);
        $moduleName = 'Settings' === $parts[0] ? (string)($parts[1] ?? '') : $parts[0];

        return self::getInstance($moduleName);
    }

    /**
     * Returns the payment module name.
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return static::MODULE;
    }

    /**
     * Returns the icon of the payment module, for templates.
     *
     * @return string
     */
    public function getIcon(): string
    {
        return static::ICON;
    }

    /**
     * Returns the label of the Add Payment button on a document, translated by the payment module: Add Refund
     * when the payments of this module are refunds of the document module.
     *
     * @param string $documentModule Document module name; empty for the plain Add Payment label
     * @return string
     * @throws Exception
     */
    public function getAddLinkLabel(string $documentModule = ''): string
    {
        return $documentModule !== '' && $this->isRefundFor($documentModule) ? self::LBL_ADD_REFUND : static::ADD_LINK_LABEL;
    }

    /**
     * Tells whether the payments of this module are refunds of a document module (integration column is_refund).
     *
     * @param string $documentModule Document module name
     * @return bool
     * @throws Exception
     */
    public function isRefundFor(string $documentModule): bool
    {
        return 1 === (int)($this->getIntegrationModel()->retrieveByModule($documentModule)[self::INTEGRATION_REFUND] ?? 0);
    }

    /**
     * Returns the statuses that count as a completed payment.
     *
     * @return array
     */
    public static function getPaidStatuses(): array
    {
        return [self::STATUS_PAID];
    }

    /**
     * Returns the integration configuration that differs from the defaults derived from the document module fields.
     *
     * @return array [document module => integration values]
     */
    public function getIntegrationOverrides(): array
    {
        return [];
    }

    /**
     * Returns the URL of the payments widget on a document. relatedModule makes the core translate the widget
     * title through the payment module's language file; relatedField makes its Add action open the quick create
     * form with the document in that field.
     *
     * @return string
     */
    public function getWidgetUrl(): string
    {
        return 'module=' . static::MODULE . '&view=Widget&mode=showPayments&sourceModule=$MODULE$&sourceRecord=$RECORD$&relatedModule='
            . static::MODULE . '&relatedField=' . self::FIELD_RELATION;
    }

    /**
     * Returns the label of the payments widget on a document.
     *
     * @return string
     */
    public function getWidgetLabel(): string
    {
        return static::MODULE;
    }

    /**
     * Returns the link of the Add Payment button next to the More menu of a document.
     *
     * @return string
     */
    public function getAddLinkTemplate(): string
    {
        return 'template:AddPaymentButton.tpl:' . static::MODULE;
    }

    /**
     * Returns the link of the Add Payment item in the More menu of a document.
     *
     * @return string
     */
    public function getAddMoreLinkTemplate(): string
    {
        return 'template:AddPaymentMoreLink.tpl:' . static::MODULE;
    }

    /**
     * Returns the class name of a handler of the payment module.
     *
     * @param string $handler Handler name, one of the HANDLER_* constants
     * @return string
     */
    public function getHandlerClass(string $handler): string
    {
        return static::MODULE . '_' . $handler . '_Handler';
    }

    /**
     * Returns the file of a handler of the payment module.
     *
     * @param string $handler Handler name, one of the HANDLER_* constants
     * @return string
     */
    public function getHandlerFile(string $handler): string
    {
        return 'modules/' . static::MODULE . '/handlers/' . $handler . '.php';
    }

    /**
     * Returns the class name of the modifier that adds the linked payments to the delete confirmation of a document.
     *
     * @return string
     */
    public function getDeleteWarningsModifier(): string
    {
        return static::MODULE . '_DeleteWarnings_Modifier';
    }

    /**
     * Returns the URL of the integration settings page of the payment module.
     *
     * @return string
     */
    public function getSettingsUrl(): string
    {
        return 'index.php?module=' . static::MODULE . '&parent=Settings&view=Index';
    }

    /**
     * Returns the document synchronisation model of the payment module.
     *
     * @return Core_PaymentDocumentSync_Model
     */
    public function getDocumentSyncModel(): Core_PaymentDocumentSync_Model
    {
        $className = static::MODULE . '_DocumentSync_Model';

        return $className::getInstance();
    }

    /**
     * Returns the payment to document association model of the payment module.
     *
     * @return Core_PaymentAssociated_Model
     */
    public function getAssociatedModel(): Core_PaymentAssociated_Model
    {
        $className = static::MODULE . '_Associated_Model';

        return $className::getInstance();
    }

    /**
     * Returns the integration configuration model of the payment module.
     *
     * @return Core_PaymentIntegration_Model
     */
    public function getIntegrationModel(): Core_PaymentIntegration_Model
    {
        $className = static::MODULE . '_Integration_Model';

        return $className::getInstance();
    }

    /**
     * Returns the settings model of the payment module.
     *
     * @return Core_PaymentSetting_Model
     */
    public function getSettingModel(): Core_PaymentSetting_Model
    {
        $className = static::MODULE . '_Setting_Model';

        return $className::getInstance();
    }
}

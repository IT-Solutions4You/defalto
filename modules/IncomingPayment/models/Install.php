<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_Install_Model extends Core_Install_Model
{
    /**
     * Block holding the payment fields.
     */
    private const MAIN_BLOCK = 'LBL_INCOMING_PAYMENT_INFORMATION';

    /**
     * Block of earlier installations, removed by migrate() once its fields are moved.
     */
    private const LEGACY_ACCOUNTING_BLOCK = 'LBL_ACCOUNTING_INFORMATION';

    /**
     * Fields of earlier installations, removed by migrate().
     */
    private const LEGACY_FIELDS = ['relations', 'accountingdate', 'tax_expense', 'vat_amount', 'total_without_vat', 'category'];

    /**
     * Reference column of earlier installations and its index while it was an int column (uitype 10).
     */
    private const LEGACY_RELATION_COLUMN = 'relationid';
    private const LEGACY_RELATION_INDEX = 'df_incomingpayment_relationid_idx';
    private const LEGACY_RELATED_TO_COLUMN = 'relatedto';
    private const LEGACY_RELATED_TO_INDEX = 'df_incomingpayment_relatedto_idx';
    private const LEGACY_STATUS_CRON = 'IncomingPaymentStatus';

    /**
     * Field names of earlier installations and their current names. renameLegacyFields() renames each field and its
     * column in place, so the field id and everything bound to it (profiles, related lists, references) stay.
     * The former currency and rate fields become the hidden fields of the amount with its own currency.
     */
    private const LEGACY_FIELD_NAMES = [
        'paymentamount' => IncomingPayment_Config_Helper::FIELD_AMOUNT,
        'currency_id' => IncomingPayment_Config_Helper::FIELD_AMOUNT_CURRENCY,
        'conversion_rate' => IncomingPayment_Config_Helper::FIELD_AMOUNT_RATE,
        'payamount_main' => IncomingPayment_Config_Helper::FIELD_AMOUNT_BASE,
        'paymentdate' => IncomingPayment_Config_Helper::FIELD_PAYMENT_DATE,
        'paymethod' => IncomingPayment_Config_Helper::FIELD_PAYMENT_METHOD,
        'transactionid' => IncomingPayment_Config_Helper::FIELD_TRANSACTION_ID,
        self::LEGACY_RELATION_COLUMN => IncomingPayment_Config_Helper::FIELD_RELATION,
        'associated_no' => IncomingPayment_Config_Helper::FIELD_REFERENCE_NO,
        self::LEGACY_RELATED_TO_COLUMN => IncomingPayment_Config_Helper::FIELD_RELATED_TO,
        'contactid' => IncomingPayment_Config_Helper::FIELD_CONTACT,
    ];

    /**
     * Hidden fields of the amount with its own currency; saved filters cannot use them.
     */
    private const AMOUNT_HIDDEN_FIELDS = [
        IncomingPayment_Config_Helper::FIELD_AMOUNT_CURRENCY,
        IncomingPayment_Config_Helper::FIELD_AMOUNT_RATE,
    ];

    /**
     * Tables of saved filters whose column values look like "table:column:field:label:type".
     */
    private const FILTER_TABLES = ['vtiger_cvcolumnlist', 'vtiger_cvadvfilter', 'vtiger_cvstdfilter'];

    protected string $moduleNumbering = 'PAYIN';

    public array $registerRelatedLists = [
        self::DOCUMENTS_RELATED_LIST,
        self::EMAILS_RELATED_LIST,
        ['Accounts', 'IncomingPayment', IncomingPayment_Config_Helper::RELATED_LIST_LABEL, 'add', 'get_dependents_list', IncomingPayment_Config_Helper::FIELD_RELATED_TO],
        ['Vendors', 'IncomingPayment', IncomingPayment_Config_Helper::RELATED_LIST_LABEL, 'add', 'get_dependents_list', IncomingPayment_Config_Helper::FIELD_RELATED_TO],
        ['Contacts', 'IncomingPayment', IncomingPayment_Config_Helper::RELATED_LIST_LABEL, 'add', 'get_dependents_list', IncomingPayment_Config_Helper::FIELD_CONTACT],
    ];

    public array $registerSettingsLinks = [
        ['LBL_INCOMING_PAYMENT_INTEGRATION', 'index.php?module=IncomingPayment&parent=Settings&view=Index', 'LBL_EXTENSIONS'],
    ];


    public array $registerEventHandler = [
        [
            [IncomingPayment_Config_Helper::EVENT_BEFORE_SAVE],
            'modules/IncomingPayment/handlers/PaymentSave.php',
            'IncomingPayment_PaymentSave_Handler',
            '',
            ['IncomingPayment'],
        ],
        [
            [IncomingPayment_Config_Helper::EVENT_AFTER_DELETE, IncomingPayment_Config_Helper::EVENT_AFTER_RESTORE],
            'modules/IncomingPayment/handlers/PaymentDelete.php',
            'IncomingPayment_PaymentDelete_Handler',
            '',
            ['IncomingPayment'],
        ],
    ];

    public array $blocksHeaderFields = ['payment_status', 'payment_amount', 'payment_date', 'reference_id'];
    public array $blocksSummaryFields = [
        'payment_no',
        'payment_name',
        'payment_status',
        'payment_amount',
        'payment_date',
        'due_date',
        'payment_method',
        'reference_id',
        'related_to',
        'assigned_user_id',
    ];
    public array $blocksQuickCreateFields = [
        'payment_name',
        'payment_amount',
        'payment_date',
        'due_date',
        'payment_method',
        'reference_id',
        'related_to',
        'contact_id',
        'assigned_user_id',
    ];
    public array $blocksListFields = [
        'payment_no',
        'payment_name',
        'reference_id',
        'related_to',
        'payment_amount',
        'payment_date',
        'due_date',
        'payment_status',
        'assigned_user_id',
    ];
    public array $relatedListFields = [['payment_no', 'payment_name', 'payment_date', 'payment_amount', 'payment_status'], 'payment_date', 'DESC'];
    public array $popupFields = ['payment_no', 'payment_name', 'payment_amount', 'payment_status', 'related_to'];

    /**
     * Registers numbering, history, comments, related lists, settings link, cron, handlers and document integrations.
     *
     * @return void
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateToCustomModule();
        $this->updateNumbering();
        $this->updateHistory();
        $this->updateComments();
        $this->updateRelatedList();
        $this->updateSettingsLinks();
        $this->updateEventHandler();
        IncomingPayment_Integration_Model::getInstance()->installDefaults();
        $this->updateIntegrations();
    }

    /**
     * Removes everything addCustomLinks() registered, before an update, a disable or an uninstall.
     *
     * @return void
     * @throws Exception
     */
    public function deleteCustomLinks(): void
    {
        $this->updateIntegrations(false);
        $this->updateEventHandler(false);
        $this->updateSettingsLinks(false);
        $this->updateRelatedList(false);
        $this->updateComments(false);
        $this->updateHistory(false);
    }

    /**
     * Keeps the module marked as a custom (installable) module, so the Installer may uninstall it.
     * Core's updateToStandardModule() would clear the source flag the Installer checks.
     *
     * @return void
     */
    public function updateToCustomModule(): void
    {
        $this->db->pquery('UPDATE vtiger_tab SET customized = 1, source = ? WHERE name = ?', ['custom', $this->moduleName]);
    }

    /**
     * Removes the integrations, links, handlers and cron before the core drops the module.
     * The pre-uninstall event fires only later, inside parent::deleteModule(), when the field rows are already
     * gone and the document modules could no longer be removed from the reference field. deleteCustomLinks()
     * therefore runs twice on this path and must stay idempotent.
     *
     * @return void
     * @throws Exception
     */
    public function deleteModule(): void
    {
        $this->updateSummaryWidgets(false);
        $this->deleteCustomLinks();
        parent::deleteModule();
    }

    /**
     * Registers the module's own summary widgets again after the module cache was cleared.
     * On the first install the default widgets registered by install() were missing,
     * because they depend on the module state cached earlier in the same request.
     *
     * @return void
     * @throws Exception
     */
    public function postInstall(): void
    {
        parent::postInstall();
        $this->updateSummaryWidgets();
    }

    /**
     * Registers or removes the links, widgets, related lists and handlers in the integrated document modules.
     * With $register = true only active integrations are registered; with false every available module is cleaned.
     *
     * @param bool $register true to register, false to remove
     * @return void
     * @throws Exception
     */
    public function updateIntegrations(bool $register = true): void
    {
        $integrationModel = IncomingPayment_Integration_Model::getInstance();
        $modules = $register ? $integrationModel->getActiveModules() : $integrationModel->getAvailableModules();
        $paymentModule = IncomingPayment_Config_Helper::MODULE;
        $ownRegistrations = [
            $this->registerRelatedLists,
            $this->registerCustomLinks,
            $this->registerSummaryWidgets,
            $this->registerEventHandler,
        ];
        $this->registerRelatedLists = [];
        $this->registerCustomLinks = [];
        $this->registerSummaryWidgets = [];
        $this->registerEventHandler = [];

        foreach ($modules as $moduleName) {
            $this->deleteLegacyAddLink($moduleName);
            $this->registerRelatedLists[] = [
                $moduleName,
                $paymentModule,
                IncomingPayment_Config_Helper::RELATED_LIST_LABEL,
                'add',
                'get_dependents_list',
                IncomingPayment_Config_Helper::FIELD_RELATION,
            ];
            $this->registerRelatedLists[] = [
                $paymentModule,
                $moduleName,
                $moduleName,
                '',
                IncomingPayment_Config_Helper::RELATED_DOCUMENTS_FUNCTION,
            ];
            $this->registerCustomLinks[] = [
                $moduleName,
                Vtiger_DetailView_Model::LINK_ADVANCED,
                IncomingPayment_Config_Helper::ADD_LINK_LABEL,
                IncomingPayment_Config_Helper::ADD_LINK_TEMPLATE,
            ];

            $this->registerCustomLinks[] = [
                $moduleName,
                'DETAILVIEW',
                IncomingPayment_Config_Helper::ADD_LINK_LABEL,
                IncomingPayment_Config_Helper::ADD_MORE_LINK_TEMPLATE,
            ];
            $this->registerSummaryWidgets[] = [
                $moduleName,
                IncomingPayment_Config_Helper::WIDGET_LABEL,
                IncomingPayment_Config_Helper::WIDGET_URL,
                '',
                6,
            ];
        }

        if (!empty($modules)) {
            $this->registerEventHandler[] = [
                [IncomingPayment_Config_Helper::EVENT_AFTER_SAVE_FINAL],
                IncomingPayment_Config_Helper::RELATED_SAVE_HANDLER_FILE,
                IncomingPayment_Config_Helper::RELATED_SAVE_HANDLER,
                '',
                $modules,
            ];
            $this->registerEventHandler[] = [
                [IncomingPayment_Config_Helper::EVENT_AFTER_DELETE, IncomingPayment_Config_Helper::EVENT_AFTER_RESTORE],
                IncomingPayment_Config_Helper::RELATED_DELETE_HANDLER_FILE,
                IncomingPayment_Config_Helper::RELATED_DELETE_HANDLER,
                '',
                $modules,
            ];
        }

        $this->updateDeleteWarnings($modules, $register);
        $this->updateRelatedList($register);
        $this->updateCustomLinks($register);
        $this->updateEventHandler($register);
        $this->registerDefaultSummaryWidgets = false;
        $this->updateSummaryWidgets($register);
        $this->registerDefaultSummaryWidgets = true;
        $integrationModel->updateModules($modules, $register);
        [
            $this->registerRelatedLists,
            $this->registerCustomLinks,
            $this->registerSummaryWidgets,
            $this->registerEventHandler,
        ] = $ownRegistrations;
    }

    /**
     * Registers or removes the warning about linked payments in the delete confirmation of document modules.
     *
     * @param string[] $modules  Document module names
     * @param bool     $register true to register, false to remove
     * @return void
     * @throws Exception
     */
    private function updateDeleteWarnings(array $modules, bool $register): void
    {
        foreach ($modules as $moduleName) {
            if ($register) {
                Core_Modifiers_Model::registerModifier(
                    $moduleName,
                    IncomingPayment_Config_Helper::MODULE,
                    IncomingPayment_Config_Helper::DELETE_WARNINGS_MODIFIABLE,
                    IncomingPayment_Config_Helper::DELETE_WARNINGS_MODIFIER
                );
            } else {
                Core_Modifiers_Model::deregisterModifier(
                    $moduleName,
                    IncomingPayment_Config_Helper::MODULE,
                    IncomingPayment_Config_Helper::DELETE_WARNINGS_MODIFIABLE,
                    IncomingPayment_Config_Helper::DELETE_WARNINGS_MODIFIER
                );
            }
        }
    }

    /**
     * Removes the Add Payment button from the left side of the toolbar, where earlier versions registered it.
     * A module update only registers the current links, so the older row would show a second button.
     *
     * @param string $moduleName Document module name
     * @return void
     */
    private function deleteLegacyAddLink(string $moduleName): void
    {
        $module = Vtiger_Module::getInstance($moduleName);

        if ($module) {
            $module->deleteLink(Vtiger_DetailView_Model::LINK_BASIC, IncomingPayment_Config_Helper::ADD_LINK_LABEL);
        }
    }

    /**
     * Returns the block and field definitions of the module layout.
     *
     * @return array [BLOCK_LABEL => [fieldname => field parameters]]
     */
    public function getBlocks(): array
    {
        $table = IncomingPayment_Config_Helper::TABLE;
        $amountType = 'decimal(25,8)';

        return [
            self::MAIN_BLOCK => [
                'payment_name' => [
                    'uitype' => 2,
                    'column' => 'payment_name',
                    'table' => $table,
                    'columntype' => 'varchar(255)',
                    'label' => 'Payment Name',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'entity_identifier' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'payment_status' => [
                    'uitype' => 15,
                    'column' => 'payment_status',
                    'table' => $table,
                    'columntype' => 'varchar(255)',
                    'label' => 'Payment Status',
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 6,
                    'picklist_values' => [
                        [IncomingPayment_Config_Helper::STATUS_CREATED, '#D1D5DB'],
                        [IncomingPayment_Config_Helper::STATUS_WAITING, '#F0D98B'],
                        [IncomingPayment_Config_Helper::STATUS_PENDING, '#F5587D'],
                        [IncomingPayment_Config_Helper::STATUS_RECEIVED, '#5CE573'],
                    ],
                ],
                // The core adds the hidden currency and rate fields and the base amount column (uitype 35)
                IncomingPayment_Config_Helper::FIELD_AMOUNT => [
                    'uitype' => Vtiger_Field_Model::UITYPE_AMOUNT_CURRENCY,
                    'column' => IncomingPayment_Config_Helper::FIELD_AMOUNT,
                    'table' => $table,
                    'columntype' => $amountType,
                    'label' => 'Amount',
                    'typeofdata' => 'N~M',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                IncomingPayment_Config_Helper::FIELD_AMOUNT_BASE => [
                    'uitype' => 72,
                    'column' => IncomingPayment_Config_Helper::FIELD_AMOUNT_BASE,
                    'table' => $table,
                    'columntype' => $amountType,
                    'label' => 'Amount in Base Currency',
                    'typeofdata' => 'N~O',
                    'displaytype' => 2,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
                ],
                IncomingPayment_Config_Helper::FIELD_PAYMENT_DATE => [
                    'uitype' => 5,
                    'column' => IncomingPayment_Config_Helper::FIELD_PAYMENT_DATE,
                    'table' => $table,
                    'columntype' => 'date',
                    'label' => 'Payment Date',
                    'typeofdata' => 'D~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
                'due_date' => [
                    'uitype' => 5,
                    'column' => 'due_date',
                    'table' => $table,
                    'columntype' => 'date',
                    'label' => 'Due Date',
                    'typeofdata' => 'D~M',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 5,
                ],
                IncomingPayment_Config_Helper::FIELD_PAYMENT_METHOD => [
                    'uitype' => 15,
                    'column' => IncomingPayment_Config_Helper::FIELD_PAYMENT_METHOD,
                    'table' => $table,
                    'columntype' => 'varchar(255)',
                    'label' => 'Payment Method',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'picklist_values' => ['Bank Transfer', 'Cash', 'Credit card', 'Paypal', 'Wire transfer', 'Other'],
                ],
                IncomingPayment_Config_Helper::FIELD_TRANSACTION_ID => [
                    'uitype' => 1,
                    'column' => IncomingPayment_Config_Helper::FIELD_TRANSACTION_ID,
                    'table' => $table,
                    'columntype' => 'varchar(100)',
                    'label' => 'Transaction ID',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                ],
                IncomingPayment_Config_Helper::FIELD_RELATION => [
                    'uitype' => Vtiger_Field_Model::UITYPE_MULTI_REFERENCE,
                    'column' => IncomingPayment_Config_Helper::FIELD_RELATION,
                    'table' => $table,
                    'columntype' => 'text',
                    'label' => 'Reference',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
                    'related_modules' => ['Invoice'],
                ],
                IncomingPayment_Config_Helper::FIELD_RELATED_TO => [
                    'uitype' => 10,
                    'column' => IncomingPayment_Config_Helper::FIELD_RELATED_TO,
                    'table' => $table,
                    'columntype' => 'int(19)',
                    'label' => 'Related To',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'related_modules' => IncomingPayment_Config_Helper::RELATED_TO_MODULES,
                ],
                IncomingPayment_Config_Helper::FIELD_CONTACT => [
                    'uitype' => 10,
                    'column' => IncomingPayment_Config_Helper::FIELD_CONTACT,
                    'table' => $table,
                    'columntype' => 'int(19)',
                    'label' => 'Contact Name',
                    'typeofdata' => 'V~O',
                    'related_modules' => ['Contacts'],
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 8,
                ],
                IncomingPayment_Config_Helper::FIELD_REFERENCE_NO => [
                    'uitype' => 1,
                    'column' => IncomingPayment_Config_Helper::FIELD_REFERENCE_NO,
                    'table' => $table,
                    'columntype' => 'varchar(255)',
                    'label' => 'Reference No',
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'payment_no' => [
                    'uitype' => 4,
                    'column' => 'payment_no',
                    'table' => $table,
                    'columntype' => 'varchar(100)',
                    'label' => 'Payment No',
                    'typeofdata' => 'V~O',
                    'presence' => 0,
                    'quickcreate' => 3,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
            ],
        ];
    }

    /**
     * Lists every table this module owns; deleteModule() drops them.
     *
     * @return array
     */
    public function getTables(): array
    {
        return [
            IncomingPayment_Config_Helper::TABLE,
            IncomingPayment_Config_Helper::TABLE_CF,
            IncomingPayment_Config_Helper::TABLE_INTEGRATION,
            IncomingPayment_Config_Helper::TABLE_ASSOCIATED,
            IncomingPayment_Config_Helper::TABLE_SETTING,
        ];
    }

    /**
     * Creates or updates the module tables; safe to run repeatedly.
     *
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $index = IncomingPayment_Config_Helper::TABLE_INDEX;
        $amountType = 'decimal(25,8) DEFAULT NULL';

        $this->dropLegacyRelationIndex();
        $this->renameLegacyFields();
        // The hidden currency and rate columns of the amount are created with its field (Core_Install_Model::createField())
        $this->getTable(IncomingPayment_Config_Helper::TABLE, null)
            ->createTable($index)
            ->createColumn('payment_name', 'varchar(255) DEFAULT NULL')
            ->createColumn('payment_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('payment_status', 'varchar(255) DEFAULT NULL')
            ->createColumn(IncomingPayment_Config_Helper::FIELD_AMOUNT, $amountType)
            ->createColumn(IncomingPayment_Config_Helper::FIELD_AMOUNT_BASE, $amountType)
            ->createColumn(IncomingPayment_Config_Helper::FIELD_PAYMENT_DATE, 'date DEFAULT NULL')
            ->createColumn(IncomingPayment_Config_Helper::FIELD_DUE_DATE, 'date DEFAULT NULL')
            ->createColumn(IncomingPayment_Config_Helper::FIELD_PAYMENT_METHOD, 'varchar(255) DEFAULT NULL')
            ->createColumn(IncomingPayment_Config_Helper::FIELD_TRANSACTION_ID, 'varchar(100) DEFAULT NULL')
            ->createColumn(IncomingPayment_Config_Helper::FIELD_RELATION, 'text DEFAULT NULL')
            ->createColumn(IncomingPayment_Config_Helper::FIELD_RELATED_TO, self::$COLUMN_INT)
            ->createColumn(IncomingPayment_Config_Helper::FIELD_CONTACT, self::$COLUMN_INT)
            ->createColumn(IncomingPayment_Config_Helper::FIELD_REFERENCE_NO, 'varchar(255) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`' . $index . '`)')
            ->createKey('KEY IF NOT EXISTS `df_incomingpayment_related_to_idx` (`' . IncomingPayment_Config_Helper::FIELD_RELATED_TO . '`)')
            ->createKey('KEY IF NOT EXISTS `df_incomingpayment_status_idx` (`payment_status`)')
            ->createKey('CONSTRAINT `fk_crmid_df_incomingpayment` FOREIGN KEY IF NOT EXISTS (`' . $index . '`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable(IncomingPayment_Config_Helper::TABLE_CF, null)
            ->createTable($index)
            ->createKey('PRIMARY KEY IF NOT EXISTS (`' . $index . '`)');

        $this->getTable(IncomingPayment_Config_Helper::TABLE_INTEGRATION, 'id')
            ->createTable()
            ->createColumn('module', 'varchar(100) NOT NULL')
            ->createColumn('active', 'tinyint(1) NOT NULL DEFAULT 0')
            ->createColumn('status_field', 'varchar(100) DEFAULT NULL')
            ->createColumn('status_value', 'varchar(255) DEFAULT NULL')
            ->createColumn('status_value_back', 'varchar(255) DEFAULT NULL')
            ->createColumn('relatedto_field', 'varchar(100) DEFAULT NULL')
            ->createColumn('total_field', 'varchar(100) DEFAULT NULL')
            ->createColumn('paid_field', 'varchar(100) DEFAULT NULL')
            ->createColumn('balance_field', 'varchar(100) DEFAULT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `df_incomingpayment_integration_module` (`module`)');

        $this->getTable(IncomingPayment_Config_Helper::TABLE_ASSOCIATED, 'id')
            ->createTable()
            ->createColumn('payment_id', 'int(19) NOT NULL')
            ->createColumn('record_id', 'int(19) NOT NULL')
            ->createColumn('partial_amount', $amountType)
            ->createKey('KEY IF NOT EXISTS `df_incomingpayment_associated_payment_idx` (`payment_id`)')
            ->createKey('KEY IF NOT EXISTS `df_incomingpayment_associated_record_idx` (`record_id`)');

        $this->getTable(IncomingPayment_Config_Helper::TABLE_SETTING, 'id')
            ->createTable()
            ->createColumn('name', 'varchar(100) NOT NULL')
            ->createColumn('value', 'text DEFAULT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `df_incomingpayment_setting_name` (`name`)');
    }

    /**
     * Migrates an existing installation: removes the legacy fields and the legacy accounting block.
     * The core FieldEventHandler drops the column and the filter columns of a deleted field.
     *
     * @return void
     * @throws Exception
     */
    public function migrate(): void
    {
        foreach (self::LEGACY_FIELDS as $fieldName) {
            $this->deleteField($fieldName);
        }

        $this->renameLegacyFields();
        $this->migrateAccountingBlock();
        $this->migrateRelationField();
        $this->migrateStatusCron();
    }

    /**
     * Renames the fields of earlier installations to their current names (LEGACY_FIELD_NAMES) together with their
     * columns, the picklist table of the payment method and the saved filters, workflow conditions, picklist
     * dependencies and history that refer to them. Then the amount becomes the amount with its own currency
     * (uitype 35) and the former currency and rate fields its hidden fields, so createField() finds them with
     * their values. It runs before the fields are created (installTables(), also on a module update, which does
     * not call migrate()) and again in migrate(); a field already renamed is skipped.
     *
     * @return void
     * @throws Exception
     */
    private function renameLegacyFields(): void
    {
        $tabId = (int)getTabid($this->getModuleName());

        if (!$tabId) {
            return;
        }

        $isRenamed = false;

        foreach (self::LEGACY_FIELD_NAMES as $legacyName => $fieldName) {
            $legacyField = $this->fetchFieldRow($tabId, $legacyName);

            if (!$legacyField || $this->fetchFieldRow($tabId, $fieldName)) {
                continue;
            }

            $this->renameLegacyField($tabId, $legacyField, $fieldName);
            $isRenamed = true;
        }

        $this->convertAmountField($tabId);

        if ($isRenamed) {
            Vtiger_Cache::flush();
            Core_AmountCurrency_Model::clearCache();
        }
    }

    /**
     * Renames one field of an earlier installation and its column, and every stored reference to it.
     *
     * @param int    $tabId       Tab id of the module
     * @param array  $legacyField Row of vtiger_field with fieldid, fieldname and columnname
     * @param string $fieldName   Current field and column name
     * @return void
     * @throws Exception
     */
    private function renameLegacyField(int $tabId, array $legacyField, string $fieldName): void
    {
        $legacyName = (string)$legacyField['fieldname'];
        $legacyColumn = (string)$legacyField['columnname'];
        $table = $this->getTable(IncomingPayment_Config_Helper::TABLE, null);

        if (self::LEGACY_RELATED_TO_COLUMN === $legacyName && $table->isKeyExists(self::LEGACY_RELATED_TO_INDEX)) {
            $table->getDB()->pquery(sprintf('ALTER TABLE %s DROP INDEX %s', IncomingPayment_Config_Helper::TABLE, self::LEGACY_RELATED_TO_INDEX), []);
        }

        $table->renameColumn($legacyColumn, $fieldName);
        $this->db->pquery('UPDATE vtiger_field SET fieldname = ?, columnname = ? WHERE fieldid = ?', [$fieldName, $fieldName, (int)$legacyField['fieldid']]);

        if (IncomingPayment_Config_Helper::FIELD_PAYMENT_METHOD === $fieldName) {
            $this->renamePicklistTable($tabId, $legacyName, $fieldName);
        }

        $this->renameFilterColumns($legacyColumn, $legacyName, $fieldName);
        $this->renameWorkflowConditions($legacyName, $fieldName);
        $this->db->pquery('UPDATE vtiger_picklist_dependency SET sourcefield = ? WHERE tabid = ? AND sourcefield = ?', [$fieldName, $tabId, $legacyName]);
        $this->db->pquery('UPDATE vtiger_picklist_dependency SET targetfield = ? WHERE tabid = ? AND targetfield = ?', [$fieldName, $tabId, $legacyName]);
        $this->db->pquery(
            'UPDATE vtiger_modtracker_detail
            INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.id = vtiger_modtracker_detail.id
            SET vtiger_modtracker_detail.fieldname = ?
            WHERE vtiger_modtracker_basic.module = ? AND vtiger_modtracker_detail.fieldname = ?',
            [$fieldName, $this->getModuleName(), $legacyName]
        );
    }

    /**
     * Makes the amount the amount with its own currency (uitype 35) and the former currency and rate fields its
     * hidden fields. A module update keeps the uitype and the layout flags of existing fields, so this is done here.
     * Saved filters lose the hidden fields, because the filter editor cannot offer them. Safe to run again.
     *
     * @param int $tabId Tab id of the module
     * @return void
     */
    private function convertAmountField(int $tabId): void
    {
        $this->db->pquery(
            'UPDATE vtiger_field SET uitype = ? WHERE tabid = ? AND fieldname = ?',
            [Vtiger_Field_Model::UITYPE_AMOUNT_CURRENCY, $tabId, IncomingPayment_Config_Helper::FIELD_AMOUNT]
        );

        foreach (self::AMOUNT_HIDDEN_FIELDS as $fieldName) {
            $this->db->pquery(
                'UPDATE vtiger_field SET displaytype = 3, quickcreate = 1, masseditable = 0, summaryfield = 0, headerfield = 0 WHERE tabid = ? AND fieldname = ?',
                [$tabId, $fieldName]
            );

            foreach (self::FILTER_TABLES as $filterTable) {
                $this->db->pquery(
                    sprintf('DELETE FROM %s WHERE columnname LIKE ?', $filterTable),
                    [$this->getFilterColumnPattern($fieldName, $fieldName)]
                );
            }
        }
    }

    /**
     * Renames the field in the columns and conditions of saved filters, also of the Payments list, whose filters
     * use the columns of this module.
     *
     * @param string $legacyColumn Former column name
     * @param string $legacyName   Former field name
     * @param string $fieldName    Current field and column name
     * @return void
     */
    private function renameFilterColumns(string $legacyColumn, string $legacyName, string $fieldName): void
    {
        $legacyPrefix = IncomingPayment_Config_Helper::TABLE . ':' . $legacyColumn . ':' . $legacyName . ':';
        $prefix = IncomingPayment_Config_Helper::TABLE . ':' . $fieldName . ':' . $fieldName . ':';

        foreach (self::FILTER_TABLES as $filterTable) {
            $this->db->pquery(
                sprintf('UPDATE %s SET columnname = CONCAT(?, SUBSTRING(columnname, ?)) WHERE columnname LIKE ?', $filterTable),
                [$prefix, strlen($legacyPrefix) + 1, $this->getFilterColumnPattern($legacyColumn, $legacyName)]
            );
        }
    }

    /**
     * Returns the LIKE pattern of the saved filter columns of a field of this module.
     *
     * @param string $columnName Column name
     * @param string $fieldName  Field name
     * @return string
     */
    private function getFilterColumnPattern(string $columnName, string $fieldName): string
    {
        $prefix = IncomingPayment_Config_Helper::TABLE . ':' . $columnName . ':' . $fieldName . ':';

        return addcslashes($prefix, '\\%_') . '%';
    }

    /**
     * Renames the field in the conditions of the workflows of this module, including conditions on a field of a
     * referenced record, e.g. "(relatedto : (Accounts) accountname)".
     *
     * @param string $legacyName Former field name
     * @param string $fieldName  Current field name
     * @return void
     */
    private function renameWorkflowConditions(string $legacyName, string $fieldName): void
    {
        $result = $this->db->pquery('SELECT workflow_id, test FROM com_vtiger_workflows WHERE module_name = ?', [$this->getModuleName()]);
        $referencePrefix = '(' . $legacyName . ' : (';

        while ($row = $this->db->fetchByAssoc($result, -1, false)) {
            $conditions = json_decode(decode_html((string)$row['test']), true);
            $isChanged = false;

            if (!is_array($conditions)) {
                continue;
            }

            foreach ($conditions as $index => $condition) {
                $conditionField = (string)($condition['fieldname'] ?? '');

                if ($legacyName === $conditionField) {
                    $conditions[$index]['fieldname'] = $fieldName;
                    $isChanged = true;
                } elseif (str_starts_with($conditionField, $referencePrefix)) {
                    $conditions[$index]['fieldname'] = '(' . $fieldName . substr($conditionField, strlen($legacyName) + 1);
                    $isChanged = true;
                }
            }

            if ($isChanged) {
                $this->db->pquery('UPDATE com_vtiger_workflows SET test = ? WHERE workflow_id = ?', [json_encode($conditions), (int)$row['workflow_id']]);
            }
        }
    }

    /**
     * Renames the picklist table of a renamed picklist field with its sequence table and picklist name, so the values,
     * their order, colors and role assignments stay. A picklist also used by a field of another module stays as it is.
     *
     * @param int    $tabId      Tab id of the module
     * @param string $legacyName Former field name
     * @param string $fieldName  Current field name
     * @return void
     * @throws Exception
     */
    private function renamePicklistTable(int $tabId, string $legacyName, string $fieldName): void
    {
        $legacyTable = 'vtiger_' . $legacyName;
        $tableName = 'vtiger_' . $fieldName;

        if (!Vtiger_Utils::CheckTable($legacyTable) || Vtiger_Utils::CheckTable($tableName) || $this->isPicklistUsedByOtherModule($tabId, $legacyName)) {
            self::logError('Picklist table not renamed: ' . $legacyTable);

            return;
        }

        $this->db->pquery(sprintf('RENAME TABLE %s TO %s', $legacyTable, $tableName), []);
        $this->getTable($tableName, null)
            ->renameColumn($legacyName . 'id', $fieldName . 'id')
            ->renameColumn($legacyName, $fieldName);

        if (Vtiger_Utils::CheckTable($legacyTable . '_seq') && !Vtiger_Utils::CheckTable($tableName . '_seq')) {
            $this->db->pquery(sprintf('RENAME TABLE %s_seq TO %s_seq', $legacyTable, $tableName), []);
        }

        $this->db->pquery('UPDATE vtiger_picklist SET name = ? WHERE name = ?', [$fieldName, $legacyName]);
    }

    /**
     * Tells whether a field of another module uses the picklist of the same name.
     *
     * @param int    $tabId     Tab id of this module
     * @param string $fieldName Picklist field name
     * @return bool
     */
    private function isPicklistUsedByOtherModule(int $tabId, string $fieldName): bool
    {
        $result = $this->db->pquery('SELECT 1 FROM vtiger_field WHERE fieldname = ? AND tabid != ? LIMIT 1', [$fieldName, $tabId]);

        return 0 < $this->db->num_rows($result);
    }

    /**
     * Returns the id, name and column of a field of this module, or an empty array.
     *
     * @param int    $tabId     Tab id of the module
     * @param string $fieldName Field name
     * @return array
     */
    private function fetchFieldRow(int $tabId, string $fieldName): array
    {
        $result = $this->db->pquery('SELECT fieldid, fieldname, columnname FROM vtiger_field WHERE tabid = ? AND fieldname = ?', [$tabId, $fieldName]);

        return $this->db->num_rows($result) ? (array)$this->db->fetchByAssoc($result) : [];
    }

    /**
     * Removes the former module cron; statuses are updated by the core cron PaymentStatus (Core_PaymentStatus_Model).
     *
     * @return void
     */
    private function migrateStatusCron(): void
    {
        Vtiger_Cron::deregister(self::LEGACY_STATUS_CRON);
    }

    /**
     * Drops the index of the former int reference column, because a text column cannot keep an index without a length.
     * Runs before installTables() changes the column type.
     *
     * @return void
     */
    private function dropLegacyRelationIndex(): void
    {
        $table = $this->getTable(IncomingPayment_Config_Helper::TABLE, null);

        if ($table->checkColumn(self::LEGACY_RELATION_COLUMN, IncomingPayment_Config_Helper::TABLE) && $table->isKeyExists(self::LEGACY_RELATION_INDEX)) {
            $table->getDB()->pquery(sprintf('ALTER TABLE %s DROP INDEX %s', IncomingPayment_Config_Helper::TABLE, self::LEGACY_RELATION_INDEX), []);
        }
    }

    /**
     * Turns the former single reference field (uitype 10) into the multi reference field (uitype 34) and stores
     * empty references kept as 0 by the former int column as NULL. Both steps are idempotent.
     *
     * @return void
     * @throws Exception
     */
    private function migrateRelationField(): void
    {
        $moduleInstance = Vtiger_Module::getInstance($this->getModuleName());
        $field = $moduleInstance ? Vtiger_Field::getInstance(IncomingPayment_Config_Helper::FIELD_RELATION, $moduleInstance) : false;

        if (!$field) {
            return;
        }

        if (Vtiger_Field_Model::UITYPE_MULTI_REFERENCE !== (int)$field->uitype) {
            $field->getFieldTable()->updateData(['uitype' => Vtiger_Field_Model::UITYPE_MULTI_REFERENCE], ['fieldid' => $field->id]);
        }

        $this->getTable(IncomingPayment_Config_Helper::TABLE, null)
            ->updateData([IncomingPayment_Config_Helper::FIELD_RELATION => null], [IncomingPayment_Config_Helper::FIELD_RELATION => '0']);
    }

    /**
     * Moves the fields out of the legacy accounting block and deletes the block. A module update keeps the block
     * and sequence of existing fields, so this is done here. It runs only while the legacy block exists, so later
     * migrations do not overwrite a field order set in the Layout Editor.
     *
     * @return void
     * @throws Exception
     */
    private function migrateAccountingBlock(): void
    {
        $moduleInstance = Vtiger_Module::getInstance($this->getModuleName());

        // The system migration calls migrate() of every module on disk, also of one that is not installed.
        if (!$moduleInstance) {
            return;
        }

        $legacyBlock = Vtiger_Block::getInstance(self::LEGACY_ACCOUNTING_BLOCK, $moduleInstance);
        $mainBlock = Vtiger_Block::getInstance(self::MAIN_BLOCK, $moduleInstance);

        if (!$legacyBlock || !$mainBlock) {
            return;
        }

        $sequence = 0;

        foreach ($this->getBlocks()[self::MAIN_BLOCK] as $fieldName => $fieldParams) {
            $sequence++;
            $field = Vtiger_Field::getInstance($fieldName, $moduleInstance);

            if ($field) {
                $field->getFieldTable()->updateData(
                    [
                        'block' => $mainBlock->id,
                        'sequence' => $sequence,
                        'displaytype' => $fieldParams['displaytype'] ?? 1,
                        'masseditable' => $fieldParams['masseditable'] ?? 1,
                    ],
                    [
                        'fieldid' => $field->id,
                    ],
                );
            }
        }

        $legacyBlock->delete(false);
    }
}

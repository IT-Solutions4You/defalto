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
     * Index of the reference column while it was an int column (uitype 10).
     */
    private const LEGACY_RELATION_INDEX = 'df_incomingpayment_relationid_idx';

    protected string $moduleNumbering = 'PAYIN';

    public array $registerRelatedLists = [
        self::DOCUMENTS_RELATED_LIST,
        self::EMAILS_RELATED_LIST,
    ];

    public array $registerSettingsLinks = [
        ['LBL_INCOMING_PAYMENT_INTEGRATION', 'index.php?module=IncomingPayment&parent=Settings&view=Index', 'LBL_EXTENSIONS'],
    ];

    public array $registerCron = [
        ['IncomingPaymentStatus', 'modules/IncomingPayment/cron/Status.php', 86400, 'IncomingPayment', 0, 'Updates incoming payment statuses by payment date and due date'],
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

    public array $blocksHeaderFields = ['payment_status', 'paymentamount', 'paymentdate', 'relationid'];
    public array $blocksSummaryFields = [
        'payment_no',
        'payment_name',
        'payment_status',
        'paymentamount',
        'currency_id',
        'paymentdate',
        'due_date',
        'paymethod',
        'relationid',
        'relatedto',
        'assigned_user_id',
    ];
    public array $blocksQuickCreateFields = [
        'payment_name',
        'paymentamount',
        'currency_id',
        'paymentdate',
        'due_date',
        'paymethod',
        'relationid',
        'relatedto',
        'assigned_user_id',
    ];
    public array $blocksListFields = [
        'payment_no',
        'payment_name',
        'relationid',
        'relatedto',
        'paymentamount',
        'paymentdate',
        'due_date',
        'payment_status',
        'assigned_user_id',
    ];
    public array $relatedListFields = [['payment_no', 'payment_name', 'paymentdate', 'paymentamount', 'payment_status'], 'paymentdate', 'DESC'];
    public array $popupFields = ['payment_no', 'payment_name', 'paymentamount', 'payment_status', 'relatedto'];

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
        $this->updateCron();
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
        $this->updateCron(false);
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
                [IncomingPayment_Config_Helper::EVENT_AFTER_DELETE],
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
                'paymentamount' => [
                    'uitype' => 72,
                    'column' => 'paymentamount',
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
                'currency_id' => [
                    'uitype' => 117,
                    'column' => 'currency_id',
                    'table' => $table,
                    'columntype' => 'int(19)',
                    'label' => 'Currency',
                    'typeofdata' => 'I~O',
                    'defaultvalue' => 1,
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                ],
                'conversion_rate' => [
                    'uitype' => 1,
                    'column' => 'conversion_rate',
                    'table' => $table,
                    'columntype' => 'decimal(12,5)',
                    'label' => 'Conversion Rate',
                    'typeofdata' => 'N~O',
                    'displaytype' => 3,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                ],
                'payamount_main' => [
                    'uitype' => 72,
                    'column' => 'payamount_main',
                    'table' => $table,
                    'columntype' => $amountType,
                    'label' => 'Amount in Base Currency',
                    'typeofdata' => 'N~O',
                    'displaytype' => 2,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
                ],
                'paymentdate' => [
                    'uitype' => 5,
                    'column' => 'paymentdate',
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
                'paymethod' => [
                    'uitype' => 15,
                    'column' => 'paymethod',
                    'table' => $table,
                    'columntype' => 'varchar(255)',
                    'label' => 'Payment Method',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'picklist_values' => ['Bank Transfer', 'Cash', 'Credit card', 'Paypal', 'Wire transfer', 'Other'],
                ],
                'transactionid' => [
                    'uitype' => 1,
                    'column' => 'transactionid',
                    'table' => $table,
                    'columntype' => 'varchar(100)',
                    'label' => 'Transaction ID',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                ],
                'relationid' => [
                    'uitype' => Vtiger_Field_Model::UITYPE_MULTI_REFERENCE,
                    'column' => 'relationid',
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
                'relatedto' => [
                    'uitype' => 10,
                    'column' => 'relatedto',
                    'table' => $table,
                    'columntype' => 'int(19)',
                    'label' => 'Related To',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'related_modules' => IncomingPayment_Config_Helper::RELATED_TO_MODULES,
                ],
                'contactid' => [
                    'uitype' => 10,
                    'column' => 'contactid',
                    'table' => $table,
                    'columntype' => 'int(19)',
                    'label' => 'Contact Name',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
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
                'associated_no' => [
                    'uitype' => 1,
                    'column' => 'associated_no',
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
        $this->getTable(IncomingPayment_Config_Helper::TABLE, null)
            ->createTable($index)
            ->createColumn('payment_name', 'varchar(255) DEFAULT NULL')
            ->createColumn('payment_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('payment_status', 'varchar(255) DEFAULT NULL')
            ->createColumn('paymentamount', $amountType)
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('conversion_rate', 'decimal(12,5) NOT NULL DEFAULT 1.00000')
            ->createColumn('payamount_main', $amountType)
            ->createColumn('paymentdate', 'date DEFAULT NULL')
            ->createColumn('due_date', 'date DEFAULT NULL')
            ->createColumn('paymethod', 'varchar(255) DEFAULT NULL')
            ->createColumn('transactionid', 'varchar(100) DEFAULT NULL')
            ->createColumn('relationid', 'text DEFAULT NULL')
            ->createColumn('relatedto', self::$COLUMN_INT)
            ->createColumn('contactid', self::$COLUMN_INT)
            ->createColumn('associated_no', 'varchar(255) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`' . $index . '`)')
            ->createKey('KEY IF NOT EXISTS `df_incomingpayment_relatedto_idx` (`relatedto`)')
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

        $this->migrateAccountingBlock();
        $this->migrateRelationField();
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

        if ($table->checkColumn(IncomingPayment_Config_Helper::FIELD_RELATION, IncomingPayment_Config_Helper::TABLE) && $table->isKeyExists(self::LEGACY_RELATION_INDEX)) {
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

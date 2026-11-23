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
 * Installation shared by the payment modules: fields, tables, numbering, related lists, handlers, the settings link
 * and the registrations on the integrated document modules. Each payment module extends it as <Module>_Install_Model
 * and adds the migrations of its earlier installations; the values of the direction come from its configuration.
 */
abstract class Core_PaymentInstall_Model extends Core_Install_Model
{
    /**
     * True while deleteModule() runs, so getBlocks() leaves out the values of picklists shared with another
     * payment module and the core keeps their tables.
     */
    protected bool $isKeepingSharedPicklists = false;

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
     * Sets the registrations that depend on the payment module: numbering, related lists of organisations,
     * vendors and contacts, the settings link and the payment handlers.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $config = $this->getConfig();
        $module = $config::MODULE;
        $this->moduleNumbering = $config::NUMBERING_PREFIX;
        $this->registerRelatedLists = [
            self::DOCUMENTS_RELATED_LIST,
            self::EMAILS_RELATED_LIST,
            ['Accounts', $module, $config::RELATED_LIST_LABEL, 'add', 'get_dependents_list', $config::FIELD_RELATED_TO],
            ['Vendors', $module, $config::RELATED_LIST_LABEL, 'add', 'get_dependents_list', $config::FIELD_RELATED_TO],
            ['Contacts', $module, $config::RELATED_LIST_LABEL, 'add', 'get_dependents_list', $config::FIELD_CONTACT],
        ];
        $this->registerSettingsLinks = [
            [$config::INTEGRATION_LABEL, $config->getSettingsUrl(), 'LBL_EXTENSIONS'],
        ];
        $this->registerEventHandler = [
            [
                [$config::EVENT_BEFORE_SAVE],
                $config->getHandlerFile($config::HANDLER_PAYMENT_SAVE),
                $config->getHandlerClass($config::HANDLER_PAYMENT_SAVE),
                '',
                [$module],
            ],
            [
                [$config::EVENT_AFTER_DELETE, $config::EVENT_AFTER_RESTORE],
                $config->getHandlerFile($config::HANDLER_PAYMENT_DELETE),
                $config->getHandlerClass($config::HANDLER_PAYMENT_DELETE),
                '',
                [$module],
            ],
        ];
    }

    /**
     * Returns the configuration of the payment module this install model belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Registers numbering, history, comments, related lists, settings link, cron, handlers and document integrations,
     * and hides the module in the menu while the Payments list is there (once, see Core_PaymentMenu_Model).
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
        $this->getConfig()->getIntegrationModel()->installDefaults();
        $this->updateIntegrations();
        Core_PaymentMenu_Model::getInstance()->hideModules([$this->moduleName]);
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
     * therefore runs twice on this path and must stay idempotent. Picklists shared with another payment module
     * (payment_status, payment_method) keep their tables and values (getBlocks(), removeSharedPicklistValues()).
     *
     * @return void
     * @throws Exception
     */
    public function deleteModule(): void
    {
        // deleteCustomLinks() removes comments and history; install() loads their classes, this path does not
        require_once 'vtlib/Vtiger/Cron.php';
        require_once 'modules/ModComments/ModComments.php';
        require_once 'modules/ModTracker/ModTracker.php';

        $this->updateSummaryWidgets(false);
        $this->deleteCustomLinks();
        $this->isKeepingSharedPicklists = true;
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
     * Registers or removes the links, related lists and handlers in the integrated document modules and updates the
     * payments widgets of the documents (Core_PaymentWidget_Model). With $register = true only active integrations
     * are registered; with false every available module is cleaned and this module counts as inactive for the widgets.
     *
     * @param bool $register true to register, false to remove
     * @return void
     * @throws Exception
     */
    public function updateIntegrations(bool $register = true): void
    {
        $config = $this->getConfig();
        $integrationModel = $config->getIntegrationModel();
        $modules = $register ? $integrationModel->getActiveModules() : $integrationModel->getAvailableModules();
        $paymentModule = $config::MODULE;
        $ownRegistrations = [
            $this->registerRelatedLists,
            $this->registerCustomLinks,
            $this->registerEventHandler,
        ];
        $this->registerRelatedLists = [];
        $this->registerCustomLinks = [];
        $this->registerEventHandler = [];

        foreach ($modules as $moduleName) {
            $this->deleteLegacyAddLink($moduleName);
            $this->registerRelatedLists[] = [
                $moduleName,
                $paymentModule,
                $config::RELATED_LIST_LABEL,
                'add',
                'get_dependents_list',
                $config::FIELD_RELATION,
            ];
            $this->registerRelatedLists[] = [
                $paymentModule,
                $moduleName,
                $moduleName,
                '',
                $config::RELATED_DOCUMENTS_FUNCTION,
            ];
            $this->registerCustomLinks[] = [
                $moduleName,
                Vtiger_DetailView_Model::LINK_ADVANCED,
                $config::ADD_LINK_LABEL,
                $this->getConfig()->getAddLinkTemplate(),
            ];

            $this->registerCustomLinks[] = [
                $moduleName,
                'DETAILVIEW',
                $config::ADD_LINK_LABEL,
                $this->getConfig()->getAddMoreLinkTemplate(),
            ];
        }

        if (!empty($modules)) {
            $this->registerEventHandler[] = [
                [$config::EVENT_AFTER_SAVE_FINAL],
                $this->getConfig()->getHandlerFile(Core_PaymentConfig_Helper::HANDLER_RELATED_SAVE),
                $this->getConfig()->getHandlerClass(Core_PaymentConfig_Helper::HANDLER_RELATED_SAVE),
                '',
                $modules,
            ];
            $this->registerEventHandler[] = [
                [$config::EVENT_AFTER_DELETE, $config::EVENT_AFTER_RESTORE],
                $this->getConfig()->getHandlerFile(Core_PaymentConfig_Helper::HANDLER_RELATED_DELETE),
                $this->getConfig()->getHandlerClass(Core_PaymentConfig_Helper::HANDLER_RELATED_DELETE),
                '',
                $modules,
            ];
        }

        $this->updateDeleteWarnings($modules, $register);
        $this->updateRelatedList($register);
        $this->updateCustomLinks($register);
        $this->updateEventHandler($register);
        $integrationModel->updateModules($modules, $register);
        Core_PaymentWidget_Model::getInstance()->updateDocumentWidgets($register ? [] : [$paymentModule]);
        [
            $this->registerRelatedLists,
            $this->registerCustomLinks,
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
    protected function updateDeleteWarnings(array $modules, bool $register): void
    {
        $config = $this->getConfig();

        foreach ($modules as $moduleName) {
            if ($register) {
                Core_Modifiers_Model::registerModifier(
                    $moduleName,
                    $config::MODULE,
                    $config::DELETE_WARNINGS_MODIFIABLE,
                    $this->getConfig()->getDeleteWarningsModifier()
                );
            } else {
                Core_Modifiers_Model::deregisterModifier(
                    $moduleName,
                    $config::MODULE,
                    $config::DELETE_WARNINGS_MODIFIABLE,
                    $this->getConfig()->getDeleteWarningsModifier()
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
    protected function deleteLegacyAddLink(string $moduleName): void
    {
        $config = $this->getConfig();
        $module = Vtiger_Module::getInstance($moduleName);

        if ($module) {
            $module->deleteLink(Vtiger_DetailView_Model::LINK_BASIC, $config::ADD_LINK_LABEL);
        }
    }

    /**
     * Returns the block and field definitions of the module layout.
     *
     * @return array [BLOCK_LABEL => [fieldname => field parameters]]
     */
    public function getBlocks(): array
    {
        $config = $this->getConfig();
        $table = $config::TABLE;
        $amountType = 'decimal(25,8)';

        $blocks = [
            $config::MAIN_BLOCK => [
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
                        [$config::STATUS_CREATED, '#D1D5DB'],
                        [$config::STATUS_WAITING, '#F0D98B'],
                        [$config::STATUS_PENDING, '#F5587D'],
                        [Core_PaymentConfig_Helper::STATUS_PAID, '#5CE573'],
                    ],
                ],
                // Fixed per module: the column default fills it for new and existing rows, displaytype 2 is never saved
                $config::FIELD_DIRECTION => [
                    'uitype' => 15,
                    'column' => $config::FIELD_DIRECTION,
                    'table' => $table,
                    'columntype' => "varchar(100) NOT NULL DEFAULT '" . $config::DIRECTION . "'",
                    'label' => 'Direction',
                    'typeofdata' => 'V~O',
                    'displaytype' => 2,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
                    'picklist_values' => [
                        [Core_PaymentConfig_Helper::DIRECTION_INCOMING, '#5CE573'],
                        [Core_PaymentConfig_Helper::DIRECTION_OUTGOING, '#F5587D'],
                    ],
                ],
                // The core adds the hidden currency and rate fields and the base amount column (uitype 35)
                $config::FIELD_AMOUNT => [
                    'uitype' => Vtiger_Field_Model::UITYPE_AMOUNT_CURRENCY,
                    'column' => $config::FIELD_AMOUNT,
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
                $config::FIELD_AMOUNT_BASE => [
                    'uitype' => 72,
                    'column' => $config::FIELD_AMOUNT_BASE,
                    'table' => $table,
                    'columntype' => $amountType,
                    'label' => 'Amount in Base Currency',
                    'typeofdata' => 'N~O',
                    'displaytype' => 2,
                    'quickcreate' => 1,
                    'masseditable' => 0,
                    'ajaxeditable' => 0,
                ],
                $config::FIELD_PAYMENT_DATE => [
                    'uitype' => 5,
                    'column' => $config::FIELD_PAYMENT_DATE,
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
                $config::FIELD_PAYMENT_METHOD => [
                    'uitype' => 15,
                    'column' => $config::FIELD_PAYMENT_METHOD,
                    'table' => $table,
                    'columntype' => 'varchar(255)',
                    'label' => 'Payment Method',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'picklist_values' => ['Bank Transfer', 'Cash', 'Credit card', 'Paypal', 'Wire transfer', 'Other'],
                ],
                $config::FIELD_TRANSACTION_ID => [
                    'uitype' => 1,
                    'column' => $config::FIELD_TRANSACTION_ID,
                    'table' => $table,
                    'columntype' => 'varchar(100)',
                    'label' => 'Transaction ID',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                ],
                $config::FIELD_RELATION => [
                    'uitype' => Vtiger_Field_Model::UITYPE_MULTI_REFERENCE,
                    'column' => $config::FIELD_RELATION,
                    'table' => $table,
                    'columntype' => 'text',
                    'label' => 'Reference',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'headerfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 7,
                    'related_modules' => array_keys($config->getIntegrationOverrides()),
                ],
                $config::FIELD_RELATED_TO => [
                    'uitype' => 10,
                    'column' => $config::FIELD_RELATED_TO,
                    'table' => $table,
                    'columntype' => 'int(19)',
                    'label' => 'Related To',
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'summaryfield' => 1,
                    'related_modules' => $config::RELATED_TO_MODULES,
                ],
                $config::FIELD_CONTACT => [
                    'uitype' => 10,
                    'column' => $config::FIELD_CONTACT,
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
                $config::FIELD_REFERENCE_NO => [
                    'uitype' => 1,
                    'column' => $config::FIELD_REFERENCE_NO,
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

        return $this->isKeepingSharedPicklists ? $this->removeSharedPicklistValues($blocks) : $blocks;
    }

    /**
     * Lists every table this module owns; deleteModule() drops them.
     *
     * @return array
     */
    public function getTables(): array
    {
        $config = $this->getConfig();

        return [
            $config::TABLE,
            $config::TABLE_CF,
            $config::TABLE_INTEGRATION,
            $config::TABLE_ASSOCIATED,
            $config::TABLE_SETTING,
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
        $config = $this->getConfig();
        $index = $config::TABLE_INDEX;
        $amountType = 'decimal(25,8) DEFAULT NULL';

        $this->preInstallTables();
        // The hidden currency and rate columns of the amount are created with its field (Core_Install_Model::createField())
        $this->getTable($config::TABLE, null)
            ->createTable($index)
            ->createColumn('payment_name', 'varchar(255) DEFAULT NULL')
            ->createColumn('payment_no', 'varchar(100) DEFAULT NULL')
            ->createColumn('payment_status', 'varchar(255) DEFAULT NULL')
            ->createColumn($config::FIELD_AMOUNT, $amountType)
            ->createColumn($config::FIELD_AMOUNT_BASE, $amountType)
            ->createColumn($config::FIELD_PAYMENT_DATE, 'date DEFAULT NULL')
            ->createColumn($config::FIELD_DUE_DATE, 'date DEFAULT NULL')
            ->createColumn($config::FIELD_PAYMENT_METHOD, 'varchar(255) DEFAULT NULL')
            ->createColumn($config::FIELD_TRANSACTION_ID, 'varchar(100) DEFAULT NULL')
            ->createColumn($config::FIELD_RELATION, 'text DEFAULT NULL')
            ->createColumn($config::FIELD_RELATED_TO, self::$COLUMN_INT)
            ->createColumn($config::FIELD_CONTACT, self::$COLUMN_INT)
            ->createColumn($config::FIELD_REFERENCE_NO, 'varchar(255) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`' . $index . '`)')
            ->createKey('KEY IF NOT EXISTS `' . $config::TABLE . '_related_to_idx` (`' . $config::FIELD_RELATED_TO . '`)')
            ->createKey('KEY IF NOT EXISTS `' . $config::TABLE . '_status_idx` (`' . $config::FIELD_STATUS . '`)')
            ->createKey('CONSTRAINT `fk_crmid_' . $config::TABLE . '` FOREIGN KEY IF NOT EXISTS (`' . $index . '`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable($config::TABLE_CF, null)
            ->createTable($index)
            ->createKey('PRIMARY KEY IF NOT EXISTS (`' . $index . '`)');

        $this->getTable($config::TABLE_INTEGRATION, 'id')
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
            ->createKey('UNIQUE KEY IF NOT EXISTS `' . $config::TABLE_INTEGRATION . '_module` (`module`)');

        $this->getTable($config::TABLE_ASSOCIATED, 'id')
            ->createTable()
            ->createColumn('payment_id', 'int(19) NOT NULL')
            ->createColumn('record_id', 'int(19) NOT NULL')
            ->createColumn('partial_amount', $amountType)
            ->createKey('KEY IF NOT EXISTS `' . $config::TABLE_ASSOCIATED . '_payment_idx` (`payment_id`)')
            ->createKey('KEY IF NOT EXISTS `' . $config::TABLE_ASSOCIATED . '_record_idx` (`record_id`)');

        $this->getTable($config::TABLE_SETTING, 'id')
            ->createTable()
            ->createColumn('name', 'varchar(100) NOT NULL')
            ->createColumn('value', 'text DEFAULT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `' . $config::TABLE_SETTING . '_name` (`name`)');
    }

    /**
     * Runs before the module tables are created or updated, e.g. to rename the fields of earlier installations
     * of a payment module. Empty by default.
     *
     * @return void
     * @throws Exception
     */
    protected function preInstallTables(): void
    {
    }

    /**
     * Removes the picklist values of fields whose picklist another module also uses, so deleteModule() keeps
     * the picklist table, its values and their role assignments for that module.
     *
     * @param array $blocks [block label => [field name => field parameters]]
     * @return array
     */
    protected function removeSharedPicklistValues(array $blocks): array
    {
        $tabId = (int)getTabid($this->getModuleName());

        foreach ($blocks as $blockLabel => $fields) {
            foreach ($fields as $fieldName => $fieldParams) {
                if (!empty($fieldParams['picklist_values']) && $this->isPicklistUsedByOtherModule($tabId, (string)$fieldName)) {
                    unset($blocks[$blockLabel][$fieldName]['picklist_values']);
                }
            }
        }

        return $blocks;
    }

    /**
     * Tells whether a field of another module uses the picklist of the same name.
     *
     * @param int    $tabId     Tab id of this module
     * @param string $fieldName Picklist field name
     * @return bool
     */
    protected function isPicklistUsedByOtherModule(int $tabId, string $fieldName): bool
    {
        $result = $this->db->pquery('SELECT 1 FROM vtiger_field WHERE fieldname = ? AND tabid != ? LIMIT 1', [$fieldName, $tabId]);

        return 0 < $this->db->num_rows($result);
    }
}

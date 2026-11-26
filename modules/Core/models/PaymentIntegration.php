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
 * Integration configuration of a payment module per document module (<payment table>_integration). The payment
 * module subclass (<Module>_Integration_Model) sets the table from its configuration.
 */
abstract class Core_PaymentIntegration_Model extends Core_DatabaseData_Model
{
    protected string $tableId = 'id';

    /**
     * Configuration rows loaded in this request per payment module class, keyed by document module.
     *
     * @var array [model class => [document module => row]]
     */
    private static array $rows = [];

    /**
     * Pattern a configured field name must match before it is stored or used in DDL.
     */
    private const FIELD_NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    /**
     * Returns a configured table object for the integration table.
     *
     * @return self
     */
    private function getIntegrationTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Creates an instance with an initialised database connection.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        return (new static())->retrieveDB();
    }

    /**
     * Returns the configuration of the payment module this model belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Returns the active inventory modules that can receive payments.
     * The list comes from the InventoryItem registry, so a newly registered inventory module appears automatically.
     *
     * @return array module names
     */
    public function getAvailableModules(): array
    {
        $modules = [];

        foreach (array_merge(InventoryItem_Utils_Helper::getInventoryItemModules(), Core_PaymentConfig_Helper::ADDITIONAL_DOCUMENT_MODULES) as $moduleName) {
            if (!vtlib_isModuleActive($moduleName)) {
                continue;
            }

            $modules[] = $moduleName;
        }

        return $modules;
    }

    /**
     * Returns an empty configuration row for a module.
     *
     * @param string $module Document module name
     * @return array
     */
    public function getEmptyRow(string $module): array
    {
        $row = ['id' => 0, 'module' => $module, 'active' => 0, Core_PaymentConfig_Helper::INTEGRATION_REFUND => 0];

        foreach (Core_PaymentConfig_Helper::INTEGRATION_COLUMNS as $column) {
            $row[$column] = '';
        }

        return $row;
    }

    /**
     * Returns the configuration of every available module; modules without a stored row get the defaults.
     *
     * @return array [module => configuration row]
     */
    public function retrieveAll(): array
    {
        if (isset(self::$rows[static::class])) {
            return self::$rows[static::class];
        }

        $table = $this->getIntegrationTable();
        $result = $table->selectResult(array_merge(['id', 'module', 'active', Core_PaymentConfig_Helper::INTEGRATION_REFUND], Core_PaymentConfig_Helper::INTEGRATION_COLUMNS), []);
        $stored = [];

        while ($row = $table->getDB()->fetchByAssoc($result, -1, false)) {
            $stored[$row['module']] = $row;
        }

        $rows = [];

        foreach ($this->getAvailableModules() as $module) {
            $rows[$module] = $stored[$module] ?? array_merge($this->getEmptyRow($module), Core_PaymentIntegrationFields_Helper::getDefaultConfig($this->getConfig(), $module));
            $rows[$module]['active'] = (int)$rows[$module]['active'];
            $rows[$module][Core_PaymentConfig_Helper::INTEGRATION_REFUND] = (int)($rows[$module][Core_PaymentConfig_Helper::INTEGRATION_REFUND] ?? 0);
        }

        self::$rows[static::class] = $rows;

        return $rows;
    }

    /**
     * Forgets the rows loaded in this request, after the configuration changed.
     *
     * @return void
     */
    public function clearCache(): void
    {
        unset(self::$rows[static::class]);
    }

    /**
     * Returns the configuration of one module, or an empty inactive row when the module is not available.
     *
     * @param string $module Document module name
     * @return array
     */
    public function retrieveByModule(string $module): array
    {
        $rows = $this->retrieveAll();

        return $rows[$module] ?? $this->getEmptyRow($module);
    }

    /**
     * Tells whether the payments of this module are integrated into the given document module.
     * A paying integration needs a total field; a refund integration uses the fields of the paying module.
     *
     * @param string $module Document module name
     * @return bool
     */
    public function isActive(string $module): bool
    {
        return $this->isActiveRow($this->retrieveByModule($module));
    }

    /**
     * Tells whether a configuration row is an active integration.
     *
     * @param array $row Integration configuration
     * @return bool
     */
    private function isActiveRow(array $row): bool
    {
        return (int)$row['active'] === 1 && ($row['total_field'] !== '' || $this->isRefundRow($row));
    }

    /**
     * Tells whether a configuration row integrates refunds, not payments.
     *
     * @param array $row Integration configuration
     * @return bool
     */
    private function isRefundRow(array $row): bool
    {
        return 1 === (int)($row[Core_PaymentConfig_Helper::INTEGRATION_REFUND] ?? 0);
    }

    /**
     * Tells whether the payments of this module pay the given document module: the integration is active and
     * its payments are not refunds.
     *
     * @param string $module Document module name
     * @return bool
     */
    public function isPaying(string $module): bool
    {
        $row = $this->retrieveByModule($module);

        return $this->isActiveRow($row) && !$this->isRefundRow($row);
    }

    /**
     * Returns the modules with an active integration.
     *
     * @return array module names
     */
    public function getActiveModules(): array
    {
        $modules = [];

        foreach ($this->retrieveAll() as $module => $row) {
            if ($this->isActiveRow($row)) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * Stores the default configuration rows that do not exist yet; safe to run repeatedly.
     *
     * @return void
     */
    public function installDefaults(): void
    {
        $table = $this->getIntegrationTable();

        foreach ($this->getAvailableModules() as $module) {
            $defaults = Core_PaymentIntegrationFields_Helper::getDefaultConfig($this->getConfig(), $module);
            $existing = $table->selectData(['id', 'total_field'], ['module' => $module]);

            if (!$existing) {
                $table->insertData(array_merge(['module' => $module], $defaults));
                continue;
            }

            $moduleModel = Vtiger_Module_Model::getInstance($module);

            if ($moduleModel && $existing['total_field'] !== '' && !$moduleModel->getField((string)$existing['total_field'])) {
                $table->updateData(['total_field' => $defaults['total_field']], ['id' => (int)$existing['id']]);
            }
        }

        $this->clearCache();
    }

    /**
     * Returns the field name when it is a valid identifier, an empty string otherwise.
     *
     * @param mixed $value Submitted field name
     * @return string
     */
    public function getValidFieldName(mixed $value): string
    {
        $value = trim((string)$value);

        return preg_match(self::FIELD_NAME_PATTERN, $value) ? $value : '';
    }

    /**
     * Stores the configuration submitted from the Settings page for every available module.
     *
     * @param array $integration [module => [active, status_field, ...]] as submitted
     * @return void
     */
    public function saveFromRequest(array $integration): void
    {
        $table = $this->getIntegrationTable();

        foreach ($this->getAvailableModules() as $module) {
            $values = is_array($integration[$module] ?? null) ? $integration[$module] : [];
            $data = [
                'active' => !empty($values['active']) ? 1 : 0,
                Core_PaymentConfig_Helper::INTEGRATION_REFUND => !empty($values[Core_PaymentConfig_Helper::INTEGRATION_REFUND]) ? 1 : 0,
            ];

            foreach (Core_PaymentConfig_Helper::INTEGRATION_COLUMNS as $column) {
                $value = $values[$column] ?? '';
                $data[$column] = str_ends_with($column, '_field') ? $this->getValidFieldName($value) : trim((string)$value);
            }

            $existing = $table->selectData(['id'], ['module' => $module]);

            if ($existing) {
                $table->updateData($data, ['id' => (int)$existing['id']]);
            } else {
                $table->insertData(array_merge(['module' => $module], $data));
            }
        }

        $this->clearCache();
    }

    /**
     * Applies or removes the integration side effects on the document modules:
     * the reference field of the payment, the paid amount fields and their read-only state.
     *
     * @param array $modules  Document module names
     * @param bool  $register true to apply, false to remove
     * @return void
     * @throws Exception
     */
    public function updateModules(array $modules, bool $register): void
    {
        $this->updateRelationModules($modules, $register);

        foreach ($modules as $module) {
            $row = $this->retrieveByModule($module);

            if ($register && !$this->isRefundRow($row)) {
                $this->createAmountFields($module, $row);
                $this->fillEmptyAmounts($module, $row);
            }

            $this->updatePaidFieldAccess($module, $row, $register);
        }
    }

    /**
     * Adds or removes the document modules from the reference field of the payment.
     *
     * @param array $modules  Document module names
     * @param bool  $register true to add, false to remove
     * @return void
     * @throws Exception
     */
    private function updateRelationModules(array $modules, bool $register): void
    {
        if (empty($modules)) {
            return;
        }

        $moduleInstance = Vtiger_Module::getInstance($this->getConfig()::MODULE);
        $field = $moduleInstance ? Vtiger_Field::getInstance(Core_PaymentConfig_Helper::FIELD_RELATION, $moduleInstance) : false;

        if (!$field) {
            return;
        }

        $field->unsetRelatedModules($modules);

        if ($register) {
            $field->setRelatedModules($modules);
        }
    }

    /**
     * Makes the paid amount field of a document read-only while a payment module pays the document, editable when
     * none does. The paid field of the paying module decides, so removing a refund integration, or the integration
     * of a module that does not pay the document, keeps the field locked.
     *
     * @param string $module   Document module name
     * @param array  $row      Integration configuration
     * @param bool   $register true when the integration is active, false when it is being removed
     * @return void
     * @throws Exception
     */
    private function updatePaidFieldAccess(string $module, array $row, bool $register): void
    {
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $payingModule = Core_PaymentDocumentBalance_Model::getPayingModule($module, $register ? [] : [$this->getConfig()::MODULE]);
        $fieldName = (string)($row['paid_field'] ?? '');

        if ($payingModule !== '') {
            $fieldName = (string)(Core_PaymentConfig_Helper::getInstance($payingModule)->getIntegrationModel()->retrieveByModule($module)['paid_field'] ?? '');
        }

        $field = $moduleModel && $fieldName !== '' ? $moduleModel->getField($fieldName) : false;

        if (!$field) {
            return;
        }

        $field->getFieldTable()->updateData(['displaytype' => $payingModule !== '' ? 2 : 1], ['fieldid' => (int)$field->getId()]);
        Vtiger_Cache::flushModuleandBlockFieldsCache($moduleModel);
    }

    /**
     * Creates the paid and balance amount fields of a document module when they are configured but missing.
     * They are placed into the first block outside the items block as read-only unit price fields.
     *
     * @param string $module Document module name
     * @param array  $row    Integration configuration
     * @return void
     * @throws Exception
     */
    private function createAmountFields(string $module, array $row): void
    {
        $moduleInstance = Vtiger_Module::getInstance($module);
        $block = $moduleInstance ? $this->getAmountFieldsBlock($moduleInstance) : null;

        if (!$block) {
            return;
        }

        $labels = ['paid_field' => $this->getConfig()::PAID_FIELD_LABEL, 'balance_field' => 'Balance'];

        foreach ($labels as $configKey => $label) {
            $fieldName = $this->getValidFieldName($row[$configKey] ?? '');

            if ($fieldName === '' || Vtiger_Field::getInstance($fieldName, $moduleInstance)) {
                continue;
            }

            $field = new Vtiger_Field();
            $field->name = $fieldName;
            $field->label = $label;
            $field->table = $moduleInstance->basetable;
            $field->column = $fieldName;
            $field->columntype = 'decimal(25,8)';
            $field->uitype = 72;
            $field->typeofdata = 'N~O';
            $field->displaytype = 2;
            $field->presence = 2;
            $field->quickcreate = 1;
            $field->masseditable = 0;
            $block->addField($field);
        }
    }

    /**
     * Writes 0 into the empty paid amounts and the total minus the paid amount into the empty balances of a document
     * module, e.g. documents that existed before the amount fields were created. The recalculation writes only
     * amounts that changed and an empty amount counts as 0, so documents without payments would keep empty fields.
     * The balance is filled only when the total is in the same table.
     *
     * @param string $module Document module name
     * @param array  $row    Integration configuration
     * @return void
     * @throws Exception
     */
    private function fillEmptyAmounts(string $module, array $row): void
    {
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $paidField = $moduleModel ? $moduleModel->getField($this->getValidFieldName($row['paid_field'] ?? '')) : false;

        if (!$paidField) {
            return;
        }

        $table = (string)$paidField->get('table');
        $paidColumn = (string)$paidField->get('column');
        $db = $this->getDB();
        $db->pquery('UPDATE ' . $table . ' SET ' . $paidColumn . ' = 0 WHERE ' . $paidColumn . ' IS NULL', []);

        $balanceField = $moduleModel->getField($this->getValidFieldName($row['balance_field'] ?? ''));
        $totalField = $moduleModel->getField($this->getValidFieldName($row['total_field'] ?? ''));

        if (!$this->isFieldInTable($balanceField, $table) || !$this->isFieldInTable($totalField, $table)) {
            return;
        }

        $balanceColumn = (string)$balanceField->get('column');
        $db->pquery(
            'UPDATE ' . $table . ' SET ' . $balanceColumn . ' = COALESCE(' . $totalField->get('column') . ', 0) - ' . $paidColumn . ' WHERE ' . $balanceColumn . ' IS NULL',
            []
        );
    }

    /**
     * Tells whether a field exists and is stored in the given table.
     *
     * @param mixed  $field Field model, false or null when the field is missing
     * @param string $table Table name
     * @return bool
     */
    private function isFieldInTable(mixed $field, string $table): bool
    {
        return $field instanceof Vtiger_Field_Model && (string)$field->get('table') === $table;
    }

    /**
     * Returns the block for newly created amount fields: the first one that is not the items block.
     *
     * @param Vtiger_Module $moduleInstance Document module
     * @return Vtiger_Block|null
     */
    private function getAmountFieldsBlock(Vtiger_Module $moduleInstance): ?Vtiger_Block
    {
        foreach (Vtiger_Block::getAllForModule($moduleInstance) as $block) {
            if ($block->label !== Core_PaymentConfig_Helper::ITEMS_BLOCK) {
                return $block;
            }
        }

        return null;
    }
}

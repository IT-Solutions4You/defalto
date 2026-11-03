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
 * Integration configuration of incoming payments per document module (df_incomingpayment_integration).
 */
class IncomingPayment_Integration_Model extends Core_DatabaseData_Model
{
    protected string $table = IncomingPayment_Config_Helper::TABLE_INTEGRATION;
    protected string $tableId = 'id';

    /**
     * Configuration rows loaded in this request, keyed by module; null until loaded.
     *
     * @var array|null
     */
    private static ?array $rows = null;

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
     * @return self
     */
    public static function getInstance(): self
    {
        return (new self())->retrieveDB();
    }

    /**
     * Returns the active inventory modules that can receive incoming payments.
     * The list comes from the InventoryItem registry, so a newly registered inventory module appears automatically.
     *
     * @return array module names
     */
    public function getAvailableModules(): array
    {
        $modules = [];

        foreach (InventoryItem_Utils_Helper::getInventoryItemModules() as $moduleName) {
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
        $row = ['id' => 0, 'module' => $module, 'active' => 0];

        foreach (IncomingPayment_Config_Helper::INTEGRATION_COLUMNS as $column) {
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
        if (self::$rows !== null) {
            return self::$rows;
        }

        $table = $this->getIntegrationTable();
        $result = $table->selectResult(array_merge(['id', 'module', 'active'], IncomingPayment_Config_Helper::INTEGRATION_COLUMNS), []);
        $stored = [];

        while ($row = $table->getDB()->fetchByAssoc($result, -1, false)) {
            $stored[$row['module']] = $row;
        }

        $rows = [];

        foreach ($this->getAvailableModules() as $module) {
            $rows[$module] = $stored[$module] ?? array_merge($this->getEmptyRow($module), IncomingPayment_IntegrationFields_Helper::getDefaultConfig($module));
            $rows[$module]['active'] = (int)$rows[$module]['active'];
        }

        self::$rows = $rows;

        return $rows;
    }

    /**
     * Forgets the rows loaded in this request, after the configuration changed.
     *
     * @return void
     */
    public function clearCache(): void
    {
        self::$rows = null;
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
     * Tells whether incoming payments are integrated into the given module.
     *
     * @param string $module Document module name
     * @return bool
     */
    public function isActive(string $module): bool
    {
        $row = $this->retrieveByModule($module);

        return (int)$row['active'] === 1 && $row['total_field'] !== '';
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
            if ((int)$row['active'] === 1 && $row['total_field'] !== '') {
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
            $defaults = IncomingPayment_IntegrationFields_Helper::getDefaultConfig($module);
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
            $data = ['active' => !empty($values['active']) ? 1 : 0];

            foreach (IncomingPayment_Config_Helper::INTEGRATION_COLUMNS as $column) {
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

            if ($register) {
                $this->createAmountFields($module, $row);
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

        $moduleInstance = Vtiger_Module::getInstance(IncomingPayment_Config_Helper::MODULE);
        $field = $moduleInstance ? Vtiger_Field::getInstance(IncomingPayment_Config_Helper::FIELD_RELATION, $moduleInstance) : false;

        if (!$field) {
            return;
        }

        $field->unsetRelatedModules($modules);

        if ($register) {
            $field->setRelatedModules($modules);
        }
    }

    /**
     * Makes the paid amount field of a document read-only while the integration is active, editable when not.
     *
     * @param string $module   Document module name
     * @param array  $row      Integration configuration
     * @param bool   $register true when the integration is active
     * @return void
     */
    private function updatePaidFieldAccess(string $module, array $row, bool $register): void
    {
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        $fieldName = (string)($row['paid_field'] ?? '');

        if (!$moduleModel || $fieldName === '') {
            return;
        }

        $field = $moduleModel->getField($fieldName);

        if (!$field) {
            return;
        }

        $field->getFieldTable()->updateData(['displaytype' => $register ? 2 : 1], ['fieldid' => (int)$field->getId()]);
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

        $labels = ['paid_field' => 'Received', 'balance_field' => 'Balance'];

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
     * Returns the block for newly created amount fields: the first one that is not the items block.
     *
     * @param Vtiger_Module $moduleInstance Document module
     * @return Vtiger_Block|null
     */
    private function getAmountFieldsBlock(Vtiger_Module $moduleInstance): ?Vtiger_Block
    {
        foreach (Vtiger_Block::getAllForModule($moduleInstance) as $block) {
            if ($block->label !== IncomingPayment_Config_Helper::ITEMS_BLOCK) {
                return $block;
            }
        }

        return null;
    }
}

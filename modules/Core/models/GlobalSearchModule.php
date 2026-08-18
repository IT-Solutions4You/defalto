<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_GlobalSearchModule_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_global_search_module';
    protected string $tableId = 'tab_id';
    protected static array|null $configuration = null;

    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @return array<int, array{tab_id:int,is_active:int,sequence:int}>
     * @throws Exception
     */
    public function getConfiguration(): array
    {
        if (self::$configuration !== null) {
            return self::$configuration;
        }

        $this->retrieveDB();

        if (!$this->db->tableExists($this->table)) {
            return self::$configuration = [];
        }

        $result = $this->db->pquery(
            'SELECT tab_id, is_active, sequence FROM ' . $this->table . ' ORDER BY sequence, tab_id'
        );
        self::$configuration = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            $tabId = (int)$row['tab_id'];
            self::$configuration[$tabId] = [
                'tab_id' => $tabId,
                'is_active' => (int)$row['is_active'],
                'sequence' => (int)$row['sequence'],
            ];
        }

        return self::$configuration;
    }

    /**
     * Returns configured and permitted modules. Before the first configuration save,
     * all currently searchable modules remain enabled for backward compatibility.
     *
     * @return array<string, Vtiger_Module_Model>
     * @throws Exception
     */
    public function getActiveModuleModels(): array
    {
        $searchableModules = Vtiger_Module_Model::getSearchableModules();
        $configuration = $this->getConfiguration();

        if (!$configuration) {
            return $searchableModules;
        }

        uasort($configuration, static fn(array $left, array $right): int => $left['sequence'] <=> $right['sequence']);
        $activeModules = [];

        foreach ($configuration as $tabId => $moduleConfiguration) {
            if (!$moduleConfiguration['is_active']) {
                continue;
            }

            $moduleName = getTabModuleName($tabId);

            if ($moduleName && isset($searchableModules[$moduleName])) {
                $activeModules[$moduleName] = $searchableModules[$moduleName];
            }
        }

        return $activeModules;
    }

    /**
     * @param array<array{tab_id:mixed,is_active:mixed,field_ids:mixed}> $configuration
     * @throws Exception
     */
    public function saveConfiguration(array $configuration): void
    {
        $searchableById = [];

        foreach (Vtiger_Module_Model::getSearchableModules() as $moduleModel) {
            $searchableById[(int)$moduleModel->getId()] = $moduleModel;
        }

        $fieldModel = Core_GlobalSearchField_Model::getInstance();
        $validatedConfiguration = [];

        foreach ($configuration as $sequence => $moduleConfiguration) {
            $tabId = (int)($moduleConfiguration['tab_id'] ?? 0);
            $isActive = (int)($moduleConfiguration['is_active'] ?? 0) === 1 ? 1 : 0;
            $moduleModel = $searchableById[$tabId] ?? null;

            if (!$moduleModel) {
                throw new InvalidArgumentException(vtranslate('LBL_GLOBAL_SEARCH_INVALID_MODULE', 'Settings:GlobalSearch'));
            }

            $fieldIds = $moduleConfiguration['field_ids'] ?? [];
            $fieldIds = is_array($fieldIds) ? $fieldModel->getValidFieldIds($moduleModel, $fieldIds) : [];

            if ($isActive && !$fieldIds) {
                throw new InvalidArgumentException(vtranslate('LBL_GLOBAL_SEARCH_FIELDS_REQUIRED', 'Settings:GlobalSearch'));
            }

            $validatedConfiguration[] = [
                'tab_id' => $tabId,
                'is_active' => $isActive,
                'sequence' => $sequence + 1,
                'field_ids' => $fieldIds,
            ];
        }

        $table = $this->getTable($this->table, $this->tableId);

        foreach ($validatedConfiguration as $moduleConfiguration) {
            $tabId = $moduleConfiguration['tab_id'];
            $data = [
                'is_active' => $moduleConfiguration['is_active'],
                'sequence' => $moduleConfiguration['sequence'],
            ];

            if ($table->selectData(['tab_id'], ['tab_id' => $tabId])) {
                $table->updateData($data, ['tab_id' => $tabId]);
            } else {
                $table->insertData(array_merge(['tab_id' => $tabId], $data));
            }

            $fieldModel->replaceForModule($tabId, $moduleConfiguration['field_ids']);
        }

        self::$configuration = null;
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTable($this->table, null)
            ->createTable('tab_id', 'int(19) NOT NULL')
            ->createColumn('is_active', 'int(1) NOT NULL DEFAULT 0')
            ->createColumn('sequence', 'int(19) NOT NULL DEFAULT 0')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tab_id`)')
            ->createKey('CONSTRAINT `fk_df_global_search_module_tab` FOREIGN KEY IF NOT EXISTS (`tab_id`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE');
        self::$configuration = null;
    }

    /**
     * @throws Exception
     */
    public function createLinks(): void
    {
        $menu = Settings_Vtiger_Menu_Model::createMenu('LBL_CONFIGURATION');
        Settings_Vtiger_MenuItem_Model::createItem(
            'LBL_GLOBAL_SEARCH_SETTINGS',
            'index.php?module=GlobalSearch&parent=Settings&view=List',
            $menu,
            'LBL_GLOBAL_SEARCH_SETTINGS_DESCRIPTION'
        );
    }
}

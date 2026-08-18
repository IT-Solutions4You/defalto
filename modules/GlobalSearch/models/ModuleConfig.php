<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class GlobalSearch_ModuleConfig_Model extends Core_DatabaseData_Model
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
     * @throws Exception
     */
    public function saveModuleConfig(int $tabId, bool $isActive, int $sequence): void
    {
        $table = $this->getTable($this->table, $this->tableId);
        $data = [
            'is_active' => $isActive ? 1 : 0,
            'sequence' => $sequence,
        ];

        if ($table->selectData(['tab_id'], ['tab_id' => $tabId])) {
            $table->updateData($data, ['tab_id' => $tabId]);
        } else {
            $table->insertData(array_merge(['tab_id' => $tabId], $data));
        }

        self::$configuration = null;
    }
}

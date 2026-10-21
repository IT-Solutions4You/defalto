<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class MailManager_ProConfig_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_mailmanager_config';
    protected string $tableId = 'name';

    /**
     * Returns the configured table accessor for df_mailmanager_config.
     *
     * @return self
     */
    public function getConfigTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Returns a Pro setting value, or null when it is not stored.
     *
     * @param string $name setting name
     *
     * @return string|null
     */
    public function getValue(string $name): ?string
    {
        $row = $this->getConfigTable()->selectData(['value'], ['name' => $name]);

        return $row['value'] ?? null;
    }

    /**
     * Stores a Pro setting value as one atomic upsert (safe for concurrent requests).
     * Use this instead of the inherited save(): the table key is the text setting name.
     *
     * @param string      $name  setting name
     * @param string|null $value setting value
     *
     * @return void
     */
    public function saveValue(string $name, ?string $value): void
    {
        $this->retrieveDB()->getDB()->pquery(
            'INSERT INTO ' . $this->table . ' (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)',
            [$name, $value]
        );
    }
}

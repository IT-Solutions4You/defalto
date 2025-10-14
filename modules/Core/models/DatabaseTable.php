<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_DatabaseTable_Model extends Vtiger_Base_Model
{
    public static array $tableColumns = [];
    public static string $COLUMN_DECIMAL = 'decimal(25,4) DEFAULT NULL';
    public static string $COLUMN_INT = 'int(19) DEFAULT NULL';

    /**
     * @var PearDatabase
     */
    protected PearDatabase $db;

    /**
     * @param $column
     * @param $type
     *
     * @return $this
     * @throws Exception
     */
    public function createColumn($column, $type): self
    {
        $this->requireTable('Table is empty for create column');

        if (!$this->checkColumn($column, $this->get('table'), true)) {
            $sql = sprintf('ALTER TABLE %s ADD %s %s', $this->get('table'), $column, $type);
        } else {
            $sql = sprintf('ALTER TABLE %s CHANGE %s %s %s', $this->get('table'), $column, $column, $type);
        }

        $this->db->query($sql);
        $this->addTableColumns([$column]);

        return $this;
    }

    /**
     * @param string $fromColumn
     * @param string $toColumn
     *
     * @return $this
     */
    public function renameColumn(string $fromColumn, string $toColumn): static
    {
        $table = $this->get('table');
        $sql = sprintf('ALTER TABLE %s RENAME COLUMN %s to %s', $table, $fromColumn, $toColumn);

        if ($this->checkColumn($fromColumn, $table, true) && !$this->checkColumn($toColumn, $table, true)) {
            $this->db->query($sql);
            $this->removeTableColumns([$fromColumn]);
            $this->addTableColumns([$toColumn]);
        }

        return $this;
    }

    /**
     * @param string $columnName
     * @param string $tableName
     * @param bool   $cache
     *
     * @return bool
     */
    public function checkColumn(string $columnName, string $tableName, bool $cache = false): bool
    {
        if (!$cache) {
            $this->setTableColumns([]);
        }

        if ($this->hasTableColumns()) {
            $this->setTableColumns($this->getDB()->getColumnNames($tableName));
        }

        if (in_array($columnName, $this->getTableColumns())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $columns
     *
     * @return void
     */
    public function setTableColumns($columns): void
    {
        self::$tableColumns[$this->get('table')] = $columns;
    }

    public function addTableColumns($columns): void
    {
        $table = $this->get('table');

        foreach ($columns as $column) {
            if (!in_array($column, self::$tableColumns[$table])) {
                self::$tableColumns[$table][] = $column;
            }
        }
    }

    public function removeTableColumns($columns): void
    {
        $table = $this->get('table');
        $columnKeys = array_flip(self::$tableColumns[$table]);

        foreach ($columns as $column) {
            if (isset($columnKeys[$column])) {
                unset(self::$tableColumns[$table][$columnKeys[$column]]);
            }
        }
    }

    /**
     * @return self
     */
    public function clearTableColumns(): static
    {
        $this->setTableColumns([]);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasTableColumns(): bool
    {
        return empty($this->getTableColumns());
    }

    /**
     * @return array
     */
    public function getTableColumns(): array
    {
        return self::$tableColumns[$this->get('table')] ?? [];
    }

    /**
     * @param $criteria
     *
     * @return $this
     */
    public function createKey($criteria)
    {
        $lowerCriteria = strtolower($criteria);

        if (str_contains($lowerCriteria, 'key ') && !str_contains($lowerCriteria, 'key if not exists')) {
            Core_Install_Model::logError('Added to key "IF NOT EXISTS" to: ' . $criteria);

            $criteria = str_replace('KEY', 'KEY IF NOT EXISTS', $criteria);
        }

        if (str_contains($lowerCriteria, 'index ') && !str_contains($lowerCriteria, 'index if not exists')) {
            Core_Install_Model::logError('Added to index "IF NOT EXISTS" to: ' . $criteria);

            $criteria = str_replace('INDEX', 'INDEX IF NOT EXISTS', $criteria);
        }

        $this->db->pquery(
            sprintf(
                'ALTER TABLE %s ADD %s',
                $this->get('table'),
                $criteria
            )
        );

        return $this;
    }

    /**
     * @throws Exception
     */
    public function createTable($firstColumn = '', $firstType = 'int(19)'): self
    {
        $this->requireTable('Table is empty for create table');

        if (!empty($firstColumn)) {
            $criteria = sprintf(' (%s %s)', $firstColumn, $firstType);
        }

        if (!$this->isEmpty('table_id')) {
            $criteria = sprintf(' (%s int(19) AUTO_INCREMENT,PRIMARY KEY (%s))', $this->get('table_id'), $this->get('table_id'));
        }

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->get('table') . $criteria;

        $this->db->pquery($sql);

        return $this;
    }

    /**
     * @param string      $table
     * @param string|null $tableId
     *
     * @return self
     */
    public function getTable(string $table, string|null $tableId): self
    {
        $clone = clone $this;
        $clone->retrieveDB();
        $clone->set('table', $table);
        $clone->set('table_id', $tableId);

        return $clone;
    }

    /**
     * @param string $message
     * @return void
     * @throws Exception
     */
    public function requireTable(string $message): void
    {
        if ($this->isEmpty('table')) {
            throw new Exception($message);
        }
    }

    /**
     * @param string $message
     * @return void
     * @throws Exception
     */
    public function requireTableId(string $message): void
    {
        if ($this->isEmpty('table_id')) {
            throw new Exception($message);
        }
    }

    public function retrieveDB()
    {
        if (empty($this->db)) {
            $this->db = PearDatabase::getInstance();
        }
    }

    public function getDB()
    {
        return $this->db;
    }

    public function disableForeignKeyCheck(): void
    {
        $this->getDB()->pquery('SET FOREIGN_KEY_CHECKS = 0');
    }

    public function enableForeignKeyCheck(): void
    {
        $this->getDB()->pquery('SET FOREIGN_KEY_CHECKS = 0');
    }
}
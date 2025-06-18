<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_DatabaseTable_Model extends Vtiger_Base_Model
{
    public static string $COLUMN_DECIMAL = 'decimal(25,4) DEFAULT NULL';

    /**
     * @var PearDatabase
     */
    protected PearDatabase $db;

    /**
     * @param $column
     * @param $type
     * @return $this
     * @throws AppException
     */
    public function createColumn($column, $type): self
    {
        $this->requireTable('Table is empty for create column');

        if (!columnExists($column, $this->get('table'))) {
            $sql = sprintf('ALTER TABLE %s ADD %s %s', $this->get('table'), $column, $type);
        } else {
            $sql = sprintf('ALTER TABLE %s CHANGE %s %s %s', $this->get('table'), $column, $column, $type);
        }

        $this->db->query($sql);

        return $this;
    }

    /**
     * @param $criteria
     * @return $this
     */
    public function createKey($criteria)
    {
        $lowerCriteria = strtolower($criteria);

        if (str_contains($lowerCriteria, 'key') && !str_contains($lowerCriteria, 'key if not exists')) {
            Core_Install_Model::logError('Added to key "IF NOT EXISTS" to: ' . $criteria);

            $criteria = str_replace('KEY', 'KEY IF NOT EXISTS', $criteria);
        }

        if (str_contains($lowerCriteria, 'index') && !str_contains($lowerCriteria, 'index if not exists')) {
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
     * @throws AppException
     */
    public function createTable($firstColumn = '', $firstType = 'int(19)'): self
    {
        $this->requireTable('Table is empty for create table');

        if (!empty($firstColumn)) {
            $criteria = sprintf('(%s %s)', $firstColumn, $firstType);
        }

        if (!$this->isEmpty('table_id')) {
            $criteria = sprintf(' (%s int(19) AUTO_INCREMENT,PRIMARY KEY (%s))', $this->get('table_id'), $this->get('table_id'));
        }

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $this->get('table') . $criteria;

        $this->db->pquery($sql);

        return $this;
    }

    /**
     * @param string $table
     * @param string|null $tableId
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

    public function requireTable($message)
    {
        if ($this->isEmpty('table')) {
            throw new AppException($message);
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
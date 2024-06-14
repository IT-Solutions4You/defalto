<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_DatabaseData_Model extends Vtiger_DatabaseTable_Model
{
    /**
     * @param string $table
     * @param array $data
     * @return string
     */
    public function getInsertQuery(string $table, array $data): string
    {
        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(',', array_keys($data)),
            generateQuestionMarks($data),
        );
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $search
     * @return string
     */
    public function getSelectQuery(string $table, array $data = [], array $search = []): string
    {
        return sprintf(
            'SELECT %s FROM %s  %s',
            empty($data) ? '*' : array_keys($data),
            $table,
            empty($search) ? '' : 'WHERE ' . implode('=? AND ', array_keys($search)) . '=?'
        );
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $search
     * @return string
     */
    public function getUpdateQuery(string $table, array $data, array $search): string
    {
        return sprintf(
            'UPDATE %s SET %s=? WHERE %s=?',
            $table,
            implode('=?,', array_keys($data)),
            implode('=? AND ', array_keys($search)),
        );
    }

    /**
     * @param $data
     * @return mixed
     */
    public function insertData($data): mixed
    {
        $this->requireTable('Table is empty for insert data');

        $query = $this->getInsertQuery($this->get('table'), $data);
        $params = $data;

        $this->retrieveDB();

        return $this->db->pquery($query, $params);
    }

    /**
     * @param array $data
     * @param array $search
     * @return array|null
     */
    public function selectData(array $data, array $search): array|null
    {
        $this->requireTable('Table is empty for select data');

        $query = $this->getSelectQuery($this->get('table'), $data, $search);
        $params = [];

        if (!empty($data)) {
            $params = array_merge($params, $data);
        }

        if (!empty($search)) {
            $params = array_merge($params, $search);
        }

        $this->retrieveDB();
        $result = $this->db->pquery($query, $params);

        return $this->db->fetchByAssoc($result);
    }

    /**
     * @param array $data
     * @param array $search
     * @return bool
     */
    public function updateData(array $data, array $search): bool
    {
        $this->requireTable('Table is empty for update data');

        $query = $this->getUpdateQuery($this->get('table'), $data, $search);
        $params = array_merge($data, $search);

        $this->retrieveDB();

        return (bool)$this->db->pquery($query, $params);
    }
}
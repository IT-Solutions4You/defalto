<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_DatabaseData_Model extends Core_DatabaseTable_Model
{
    /**
     * @var string
     */
    protected string $table = '';
    /**
     * @var string
     */
    protected string $tableId = '';

    protected string $tableName = 'name';
    protected array $columns = [];

    /**
     * @param $search
     * @return mixed
     * @throws AppException
     */
    public function deleteData($search): mixed
    {
        $this->requireTable('Table is empty for delete data');

        $query = $this->getDeleteQuery($this->get('table'), $search);
        $params = $search;

        $this->retrieveDB();

        return $this->db->pquery($query, $params);
    }

    /**
     * @param $table
     * @param $search
     * @return string
     */
    public function getDeleteQuery($table, $search): string
    {
        return sprintf(
            'DELETE FROM %s WHERE %s=?',
            $table,
            implode('=? AND ', array_keys($search)),
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->get($this->tableId);
    }

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
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->get($this->tableName);
    }

    public function getSaveParams()
    {
        throw new AppException('Create getSaveParams before use save function');
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
            empty($data) ? '*' : implode(',', $data),
            $table,
            empty($search) ? '' : 'WHERE ' . implode('=? AND ', array_keys($search)) . '=?',
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
     * @throws AppException
     */
    public function insertData($data): mixed
    {
        $this->requireTable('Table is empty for insert data');

        $query = $this->getInsertQuery($this->get('table'), $data);
        $params = $data;

        $this->retrieveDB();
        $this->db->pquery($query, $params);

        return $this->db->getLastInsertID();
    }

    /**
     * @return void
     * @throws AppException
     */
    public function save(): void
    {
        $recordId = $this->get($this->tableId);
        $params = $this->getSaveParams();

        if (empty($recordId)) {
            $recordId = $this->getTable($this->table, $this->tableId)->insertData($params);

            if (!empty($recordId)) {
                $this->set($this->tableId, $recordId);
            }
        } else {
            $this->getTable($this->table, $this->tableId)->updateData($params, [$this->tableId => $recordId]);
        }
    }

    /**
     * @param array $data
     * @param array $search
     * @return array|null
     * @throws AppException
     */
    public function selectData(array $data, array $search): array|null
    {
        $this->requireTable('Table is empty for select data');

        $query = $this->getSelectQuery($this->get('table'), $data, $search);
        $params = [];

        if (!empty($search)) {
            $params = array_merge($params, $search);
        }

        $this->retrieveDB();
        $result = $this->db->pquery($query, $params);
        $data = $this->db->fetchByAssoc($result);

        if (empty($data)) {
            return null;
        }

        return array_map(function ($value) {
            return is_string($value) ? decode_html($value) : $value;
        }, $data);
    }

    /**
     * @param array $data
     * @param array $search
     * @return bool
     * @throws AppException
     */
    public function updateData(array $data, array $search): bool
    {
        $this->requireTable('Table is empty for update data');

        $query = $this->getUpdateQuery($this->get('table'), $data, $search);
        $params = array_merge($data, $search);

        $this->retrieveDB();

        return (bool)$this->db->pquery($query, $params);
    }

    /**
     * @throws AppException
     */
    public function retrieveDataByName(): void
    {
        $data = $this->getTable($this->table, $this->tableId)->selectData([], [$this->tableName => $this->get($this->tableName)]);
        
        if ($data) {
            $this->setData(array_merge($this->getData(), $data));
        }
    }

    /**
     * @param string $value
     * @return void
     */
    public function setName(string $value): void
    {
        $this->set($this->tableName, $value);
    }

    /**
     * @throws AppException
     */
    public function retrieveDataById(): void
    {
        $data = $this->getTable($this->table, $this->tableId)->selectData([], [$this->tableId => $this->get($this->tableId)]);

        if ($data) {
            $this->setData(array_merge($this->getData(), $data));
        }
    }

    public function setId(int $id): void
    {
        $this->set($this->tableId, $id);
    }

    public function retrieveFromRequest($request): void
    {
        foreach ($request->getAll() as $key => $value) {
            if (!in_array($key, $this->columns)) {
                continue;
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $this->set($key, $value);
        }
    }

    /**
     * @throws AppException
     */
    public function delete(): void
    {
        $recordId = $this->get($this->tableId);

        if (!empty($recordId)) {
            $this->getTable($this->table, $this->tableId)->deleteData([$this->tableId => $recordId]);
        }
    }


    /**
     * @return bool
     * @throws AppException
     */
    public function isDuplicateName(): bool
    {
        $data = $this->getTable($this->table, $this->tableId)->selectData([$this->tableId], [$this->tableName => $this->get($this->tableName)]);

        if (empty($data)) {
            return false;
        }

        if (!empty($data[$this->tableId]) && $this->isEmpty($this->tableId)) {
            return true;
        }

        return (int)$data[$this->tableId] !== (int)$this->get($this->tableId);
    }

    /**
     * @throws AppException
     */
    public function retrieveIdByParams($params = []): void
    {
        $data = $this->getTable($this->table, $this->tableId)->selectData([$this->tableId], $params);

        $this->setId((int)$data[$this->tableId]);
    }
}
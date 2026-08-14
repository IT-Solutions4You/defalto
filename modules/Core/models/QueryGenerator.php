<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_QueryGenerator_Model extends EnhancedQueryGenerator
{
    protected bool $groupByClauseRequired = false;
    protected array $groupByColumns = [];
    protected array $structuredWhereConditions = [];

    public const NOT_EMPTY = 'ny';
    public const EMPTY = 'y';

    public int $limit = 0;

    public static function getInstance($module, $user = false): self
    {
        if (!$user) {
            $user = Users_Record_Model::getCurrentUserModel();
        }

        $query = new self($module, $user);
        $query->setFields(['id']);

        return $query;
    }

    public function setLimit($value): void
    {
        $this->limit = $value;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        $records = [];
        $adb = PearDatabase::getInstance();
        $query = $this->getQuery();

        if (0 < $this->getLimit()) {
            $query .= sprintf(' LIMIT %d', $this->getLimit());
        }

        $result = $adb->pquery($query, $this->getQueryParameters());
        $index = $this->getBaseTableIndex();

        while ($row = $adb->fetchByAssoc($result)) {
            $recordId = (int)$row[$index];
            $records[$recordId] = Vtiger_Record_Model::getInstanceById($recordId);
        }

        return $records;
    }

    public function addColumnCondition(
        string $tableName,
        string $columnName,
        string $operator,
        mixed $value
    ): self {
        $qualifiedColumn = $this->getQualifiedColumn($tableName, $columnName);
        $operator = strtoupper(trim($operator));

        if (!in_array($operator, ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'], true)) {
            throw new InvalidArgumentException('Unsupported SQL column operator.');
        }

        if ($value === null) {
            if ($operator === '=') {
                return $this->addStructuredWhereCondition($qualifiedColumn . ' IS NULL');
            }

            if (in_array($operator, ['!=', '<>'], true)) {
                return $this->addStructuredWhereCondition($qualifiedColumn . ' IS NOT NULL');
            }

            throw new InvalidArgumentException('NULL supports only equality operators.');
        }

        return $this->addStructuredWhereCondition(
            $qualifiedColumn . ' ' . $operator . ' ?',
            [$value]
        );
    }

    public function addFieldColumnCondition(string $fieldName, string $operator, mixed $value): self
    {
        $field = $this->getModuleFields()[$fieldName] ?? null;

        if (!$field) {
            throw new InvalidArgumentException('Unknown module field.');
        }

        $this->addWhereField($fieldName);

        return $this->addColumnCondition(
            $field->getTableName(),
            $field->getColumnName(),
            $operator,
            $value
        );
    }

    public function addColumnValuesCondition(
        string $tableName,
        string $columnName,
        array $values,
        bool $exclude = false
    ): self {
        $values = array_values($values);

        if (!$values) {
            return $this->addStructuredWhereCondition($exclude ? '1=1' : '1=0');
        }

        return $this->addStructuredWhereCondition(
            $this->getQualifiedColumn($tableName, $columnName)
            . ($exclude ? ' NOT IN (' : ' IN (')
            . generateQuestionMarks($values)
            . ')',
            $values
        );
    }

    public function addRecordIdsCondition(array $recordIds, bool $exclude = false): self
    {
        return $this->addColumnValuesCondition(
            $this->meta->getEntityBaseTable(),
            $this->getBaseTableIndex(),
            $recordIds,
            $exclude
        );
    }

    public function getQueryParameters(): array
    {
        $parameters = [];

        foreach ($this->structuredWhereConditions as $condition) {
            array_push($parameters, ...$condition['parameters']);
        }

        return $parameters;
    }

    public function getQueryData(bool $sortClause = false): array
    {
        return [
            'query' => $this->getQuery($sortClause),
            'parameters' => $this->getQueryParameters(),
        ];
    }

    public function clearConditionals(): void
    {
        parent::clearConditionals();
        $this->structuredWhereConditions = [];
        $this->reset();
    }

    public function getWhereClause(): string
    {
        if ($this->query || $this->whereClause) {
            return $this->whereClause;
        }

        $whereClause = parent::getWhereClause();

        foreach ($this->structuredWhereConditions as $condition) {
            $whereClause .= ' AND (' . $condition['expression'] . ')';
        }

        $this->whereClause = $whereClause;

        return $whereClause;
    }

    protected function addStructuredWhereCondition(string $expression, array $parameters = []): self
    {
        $this->structuredWhereConditions[] = [
            'expression' => $expression,
            'parameters' => array_values($parameters),
        ];
        $this->reset();

        return $this;
    }

    protected function getQualifiedColumn(string $tableName, string $columnName): string
    {
        if (!$this->isSqlIdentifier($tableName) || !$this->isSqlIdentifier($columnName)) {
            throw new InvalidArgumentException('Invalid SQL identifier.');
        }

        return $tableName . '.' . $columnName;
    }

    protected function isSqlIdentifier(string $value): bool
    {
        return $value !== '' && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $value) === 1;
    }

    public function getBaseTableIndex()
    {
        $baseTable = $this->meta->getEntityBaseTable();
        $moduleTableIndexList = $this->meta->getEntityTableIndexList();

        return $moduleTableIndexList[$baseTable];
    }

    /**
     * @return string
     */
    public function getGroupByClause(): string
    {
        return ' GROUP BY ' . implode(', ', $this->getGroupByColumns());
    }

    /**
     * @param array $columns
     * @return void
     */
    public function setGroupByColumns(array $columns): self
    {
        $this->groupByColumns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroupByColumns(): array
    {
        return $this->groupByColumns;
    }

    /**
     * @return bool
     */
    public function isGroupByClauseRequired(): bool
    {
        return $this->groupByClauseRequired;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setGroupByClauseRequired(bool $value): self
    {
        $this->groupByClauseRequired = $value;

        return $this;
    }

    public function getQuery(bool $sortClause = false): string
    {
        if (empty($this->query)) {
            $conditionedReferenceFields = [];
            $allFields = array_merge($this->fields, (array)$this->whereFields);
            foreach ($allFields as $fieldName) {
                if (in_array($fieldName, $this->referenceFieldList)) {
                    $moduleList = $this->referenceFieldInfoList[$fieldName];
                    foreach ($moduleList as $module) {
                        if (empty($this->moduleNameFields[$module])) {
                            $meta = $this->getMeta($module);
                        }
                    }
                } elseif (in_array($fieldName, $this->ownerFields)) {
                    $meta = $this->getMeta('Users');
                    $meta = $this->getMeta('Groups');
                }
            }

            $query = "SELECT ";
            $query .= $this->getSelectClauseColumnSQL();
            $query .= $this->getFromClause();
            $query .= $this->getWhereClause();

            if ($this->isGroupByClauseRequired()) {
                $query .= $this->getGroupByClause();
            }

            if ($this->isOrderByClauseRequired()) {
                $query .= $this->getOrderByClause();
            }

            $this->query = $query;

            return $query;
        } else {
            return $this->query;
        }
    }
}

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
    protected ?array $projectedColumns = null;

    public const NOT_EMPTY = 'ny';
    public const EMPTY = 'y';

    public int $limit = 0;

    public function parseAdvFilterList($advFilterList, $glue = '')
    {
        $filterModel = Core_Filter_Model::getInstance($this->module);

        foreach ($advFilterList as $groupIndex => $group) {
            if (empty($group['columns'])) {
                unset($advFilterList[$groupIndex]);
                continue;
            }

            foreach ($group['columns'] as $columnIndex => $condition) {
                $advFilterList[$groupIndex]['columns'][$columnIndex] = $filterModel->getQueryCondition($condition);
            }

            $lastColumn = array_key_last($group['columns']);
            $advFilterList[$groupIndex]['columns'][$lastColumn]['column_condition'] = '';
        }

        if (!$advFilterList) {
            return;
        }

        $lastGroup = array_key_last($advFilterList);
        $advFilterList[$lastGroup]['condition'] = '';

        // Keep OR groups inside the surrounding deleted/access/search constraints.
        $this->startGroup($glue);
        parent::parseAdvFilterList($advFilterList);
        $this->endGroup();
    }

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

    /**
     * Return selected values keyed by record ID and query field name, without loading record models.
     * Additional columns must belong to the base table or the entity table.
     */
    public function getProjectedRows(array $additionalColumns = []): array
    {
        $query = clone $this;
        $query->reset();
        $query->projectedColumns = [];
        $moduleFields = $query->getModuleFields() ?? [];
        $query->fields = array_intersect($query->fields, ['id', ...array_keys($moduleFields)]);
        $aliases = [];

        foreach ($query->fields as $field) {
            $definition = $moduleFields[$field] ?? false;
            $query->projectedColumns[$field] = $query->getSQLColumn($field, $definition);
        }

        foreach ($additionalColumns as $name => [$table, $column]) {
            $query->projectedColumns[$name] = $query->getQualifiedColumn($table, $column);
        }

        foreach (array_keys($query->projectedColumns) as $name) {
            $aliases['df_projection_' . count($aliases)] = $name;
        }

        $sql = $query->getQuery();

        if ($query->getLimit() > 0) {
            $sql .= sprintf(' LIMIT %d', $query->getLimit());
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery($sql, $query->getQueryParameters());
        $rows = [];

        while ($row = $db->fetchByAssoc($result)) {
            $values = [];

            foreach ($aliases as $alias => $name) {
                $values[$name] = decode_html($row[$alias] ?? '');
            }

            $rows[(int)$values['id']] = $values;
        }

        return $rows;
    }

    public function getSelectClauseColumnSQL()
    {
        if (null === $this->projectedColumns) {
            return parent::getSelectClauseColumnSQL() . $this->getAmountCurrencySelectColumns();
        }

        $columns = [];

        foreach ($this->projectedColumns as $column) {
            $columns[] = $column . ' AS df_projection_' . count($columns);
        }

        return implode(', ', $columns);
    }

    /**
     * Returns the currency columns of the selected amounts with their own currency (uitype 35), prefixed with
     * a comma, so list rows know the currency of each amount (Vtiger_Record_Model::getAmountCurrencyId()).
     *
     * @return string
     */
    protected function getAmountCurrencySelectColumns(): string
    {
        $moduleFields = $this->getModuleFields() ?? [];
        $columns = [];

        foreach ($this->fields as $fieldName) {
            $field = $moduleFields[$fieldName] ?? null;

            // Fields of a referenced module ("a ; (Module) b") keep the record currency of their own row
            if ($field && empty($field->parentReferenceField) && Core_AmountCurrency_Helper::isAmountCurrencyUIType($field->getUIType())) {
                $columns[] = $this->getQualifiedColumn(
                    $field->getTableName(),
                    Core_AmountCurrency_Helper::getCurrencyName($field->getColumnName())
                );
            }
        }

        return $columns ? ', ' . implode(', ', $columns) : '';
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

    /**
     * Adds one parameterized contains condition across several module fields.
     * Percent and underscore in the user value are treated as literal characters.
     *
     * @param array<string> $fieldNames
     */
    public function addAnyFieldContainsCondition(array $fieldNames, string $value): self
    {
        $expressions = $parameters = [];
        $escapedValue = str_replace(['=', '%', '_'], ['==', '=%', '=_'], $value);
        $moduleFields = $this->getModuleFields();

        foreach (array_unique($fieldNames) as $fieldName) {
            $field = $moduleFields[$fieldName] ?? null;

            if (!$field) {
                continue;
            }

            $this->addWhereField($fieldName);
            $expressions[] = $this->getQualifiedColumn($field->getTableName(), $field->getColumnName())
                . " LIKE ? ESCAPE '='";
            $parameters[] = '%' . $escapedValue . '%';
        }

        if (!$expressions) {
            return $this->addStructuredWhereCondition('1=0');
        }

        return $this->addStructuredWhereCondition(
            '(' . implode(' OR ', $expressions) . ')',
            $parameters
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

    /**
     * Multi reference fields (uitype 34) store record ids (12;15), so their conditions compare the name and
     * the record number of the referenced records. The parent query prefixes each returned fragment with the column.
     *
     * @inheritDoc
     */
    protected function getConditionValue($value, $operator, $field)
    {
        if (Core_AmountCurrency_Helper::isAmountCurrencyUIType($field->getUIType())) {
            $fragments = $this->getAmountCurrencyConditionValues($value, strtolower((string)$operator), $field);

            if (null !== $fragments) {
                return $fragments;
            }
        }

        if (Vtiger_Field_Model::MULTI_REFERENCE_TYPE !== $field->getFieldDataType()) {
            return parent::getConditionValue($value, $operator, $field);
        }

        return $this->getMultiReferenceConditionValues($value, strtolower((string)$operator), $field);
    }

    /**
     * Returns the condition fragments of an amount with its own currency (uitype 35), each following "table.column".
     * Comparisons use the column with the amount in the base currency; the searched value is entered in the user's
     * currency and converted to the base currency. "Equals" compares values rounded to the user's currency decimals,
     * because amounts converted with stored rates rarely match exactly. Returns null for operators that keep the
     * default handling (text operators such as contains).
     *
     * @param mixed           $value    Searched value, "from,to" for between
     * @param string          $operator Filter operator
     * @param WebserviceField $field    Amount field, a clone with parentReferenceField when it comes through a reference
     * @return array|null
     */
    protected function getAmountCurrencyConditionValues(mixed $value, string $operator, WebserviceField $field): ?array
    {
        if (self::EMPTY === $operator) {
            return ['IS NULL'];
        }

        if (self::NOT_EMPTY === $operator) {
            return ['IS NOT NULL'];
        }

        // "Equals" or "not equals" an empty value mean "is empty" or "is not empty", as for other fields
        if (in_array($operator, ['e', 'n'], true) && !is_array($value) && '' === trim((string)$value)) {
            return ['e' === $operator ? 'IS NULL' : 'IS NOT NULL'];
        }

        $comparisons = ['e' => '=', 'n' => '<>', 'l' => '<', 'g' => '>', 'm' => '<=', 'h' => '>=', 'bw' => 'BETWEEN'];

        if (!isset($comparisons[$operator])) {
            return null;
        }

        $baseColumn = $this->getQualifiedColumn(
            $this->getAmountCurrencyTableAlias($field),
            Core_AmountCurrency_Helper::getBaseColumnName($field->getColumnName())
        );
        $values = is_array($value) ? array_values($value) : ('bw' === $operator ? explode(',', (string)$value) : [(string)$value]);
        $numbers = array_map(
            static fn($searchValue): string => sprintf('%.8F', (float)CurrencyField::convertToDBFormat(trim((string)$searchValue), null, false)),
            $values
        );

        if ('bw' === $operator) {
            return [sprintf('IS NOT NULL AND %s BETWEEN %s AND %s', $baseColumn, $numbers[0], $numbers[1] ?? $numbers[0])];
        }

        $fragments = [];

        foreach ($numbers as $number) {
            if (in_array($operator, ['e', 'n'], true)) {
                $decimals = max(0, (int)($this->user->no_of_currency_decimals ?? 2));
                $fragments[] = sprintf(
                    '%s ROUND(%s, %d) %s ROUND(%s, %d)',
                    'e' === $operator ? 'IS NOT NULL AND' : 'IS NULL OR',
                    $baseColumn,
                    $decimals,
                    $comparisons[$operator],
                    $number,
                    $decimals
                );
            } else {
                $fragments[] = sprintf('IS NOT NULL AND %s %s %s', $baseColumn, $comparisons[$operator], $number);
            }
        }

        return $fragments;
    }

    /**
     * Orders an amount with its own currency (uitype 35) by its amount in the base currency, so amounts in different
     * currencies compare correctly; every other field keeps the default ordering column.
     *
     * @param string $fieldName Field name
     * @return string
     */
    public function getOrderByColumn($fieldName)
    {
        $field = $this->getModuleFields()[$fieldName] ?? null;

        if ($field && Core_AmountCurrency_Helper::isAmountCurrencyUIType($field->getUIType())) {
            return $this->getQualifiedColumn(
                $this->getAmountCurrencyTableAlias($field),
                Core_AmountCurrency_Helper::getBaseColumnName($field->getColumnName())
            );
        }

        return parent::getOrderByColumn($fieldName);
    }

    /**
     * Returns the table alias of an amount field in the query: a field of a referenced module is joined under
     * <table><reference field name>, as the parent query generator joins it.
     *
     * @param WebserviceField $field Amount field, a clone with parentReferenceField when it comes through a reference
     * @return string
     */
    protected function getAmountCurrencyTableAlias(WebserviceField $field): string
    {
        $tableName = $field->getTableName();

        if (!empty($field->parentReferenceField)) {
            $tableName .= $field->parentReferenceField->getFieldName();
        }

        return $tableName;
    }

    /**
     * Returns the condition fragments of a multi reference field, each one following "table.column".
     *
     * @param mixed           $value    Searched value, comma separated values or an array
     * @param string          $operator Filter operator (e, n, c, k, s, ew, y, ny)
     * @param WebserviceField $field    Multi reference field
     * @return array
     */
    protected function getMultiReferenceConditionValues(mixed $value, string $operator, WebserviceField $field): array
    {
        $column = $this->getQualifiedColumn($field->getTableName(), $field->getColumnName());

        if (self::EMPTY === $operator) {
            return ["IS NULL OR $column = ''"];
        }

        if (self::NOT_EMPTY === $operator) {
            return ["IS NOT NULL AND $column <> ''"];
        }

        $values = is_array($value) ? $value : explode(',', (string)$value);
        $isNegated = in_array($operator, ['n', 'k'], true);
        $fragments = [];

        foreach ($values as $searchValue) {
            $searchValue = trim(decode_html((string)$searchValue));
            $condition = $this->getMultiReferenceExistsSql($column, $this->getMultiReferencePattern($searchValue, $operator), $field);
            $fragments[] = $isNegated ? "IS NULL OR NOT $condition" : "IS NOT NULL AND $condition";
        }

        return $fragments;
    }

    /**
     * Returns the LIKE pattern for a searched value and operator, with the LIKE wildcards of the value escaped.
     *
     * @param string $value    Searched value
     * @param string $operator Filter operator
     * @return string
     */
    protected function getMultiReferencePattern(string $value, string $operator): string
    {
        $value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return match ($operator) {
            'e', 'n' => $value,
            's' => $value . '%',
            'ew' => '%' . $value,
            default => '%' . $value . '%',
        };
    }

    /**
     * Returns an EXISTS condition matching a referenced record of the column by its name or record number.
     *
     * @param string          $column  Qualified multi reference column
     * @param string          $pattern LIKE pattern
     * @param WebserviceField $field   Multi reference field
     * @return string
     */
    protected function getMultiReferenceExistsSql(string $column, string $pattern, WebserviceField $field): string
    {
        $db = PearDatabase::getInstance();
        $quotedPattern = $db->sql_escape_string($pattern);
        $matches = ["multiref.label LIKE '$quotedPattern'"];

        foreach ($this->getMultiReferenceNumberColumns($field) as [$tableName, $indexColumn, $numberColumn]) {
            $matches[] = "multiref.crmid IN (SELECT $tableName.$indexColumn FROM $tableName WHERE $tableName.$numberColumn LIKE '$quotedPattern')";
        }

        return "EXISTS (SELECT 1 FROM vtiger_crmentity AS multiref WHERE multiref.deleted = 0"
            . " AND FIND_IN_SET(multiref.crmid, REPLACE($column, ';', ','))"
            . ' AND (' . implode(' OR ', $matches) . '))';
    }

    /**
     * Returns the record number columns (uitype 4) of the modules a multi reference field points to.
     *
     * @param WebserviceField $field Multi reference field
     * @return array [[table, index column, number column], ...]
     */
    protected function getMultiReferenceNumberColumns(WebserviceField $field): array
    {
        $numberColumns = [];

        foreach ($field->getReferenceList(false) as $moduleName) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $entity = $moduleModel ? CRMEntity::getInstance($moduleName) : null;

            if (!$entity) {
                continue;
            }

            foreach ($moduleModel->getFields() as $fieldModel) {
                $tableName = (string)$fieldModel->get('table');
                $indexColumn = (string)($entity->tab_name_index[$tableName] ?? '');
                $numberColumn = (string)$fieldModel->get('column');

                if (Vtiger_Field_Model::UITYPE_RECORD_NO === (int)$fieldModel->get('uitype')
                    && $this->isSqlIdentifier($tableName) && $this->isSqlIdentifier($indexColumn) && $this->isSqlIdentifier($numberColumn)) {
                    $numberColumns[] = [$tableName, $indexColumn, $numberColumn];
                }
            }
        }

        return $numberColumns;
    }
    public function getBaseTableIndex()
    {
        $baseTable = $this->meta->getEntityBaseTable();
        $moduleTableIndexList = $this->meta->getEntityTableIndexList();

        return $moduleTableIndexList[$baseTable];
    }

    public function getBaseTableIndexColumn(): string
    {
        return $this->getQualifiedColumn(
            $this->meta->getEntityBaseTable(),
            $this->getBaseTableIndex()
        );
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

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
 * Stored values of amounts with their own currency (uitype 35): the currency, the conversion rate (taken when the
 * currency is set or changed, kept while it stays) and the amount in the base currency used by searching,
 * ordering, reports and workflow conditions.
 * CRMEntity::saveentity() calls prepareRecordValues() before it writes the tables and saveBaseValues() after,
 * so every save path (form, inline and mass edit, import, workflow, webservice) keeps the values consistent.
 */
class Core_AmountCurrency_Model
{
    /** @var array<string, array<string, array{column: string, table: string}>> */
    protected static array $moduleFields = [];

    /**
     * Returns a new instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Returns the active amount fields with their own currency of a module, keyed by field name.
     *
     * @param string $moduleName Module name
     * @return array<string, array{column: string, table: string}>
     */
    public function getModuleFields(string $moduleName): array
    {
        if (isset(self::$moduleFields[$moduleName])) {
            return self::$moduleFields[$moduleName];
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT fieldname, columnname, tablename FROM vtiger_field WHERE tabid = ? AND uitype = ? AND presence IN (0, 2)',
            [getTabid($moduleName), Core_AmountCurrency_Helper::UITYPE]
        );
        $fields = [];

        while ($row = $db->fetchByAssoc($result)) {
            $fields[$row['fieldname']] = ['column' => $row['columnname'], 'table' => $row['tablename']];
        }

        self::$moduleFields[$moduleName] = $fields;

        return $fields;
    }

    /**
     * Sets the currency and the conversion rate of every amount field before the record is written.
     * A missing currency becomes the user's currency. The stored rate is kept while the currency stays the same;
     * a new record, another currency or a missing rate takes the current rate of the currency.
     *
     * @param CRMEntity $focus      Record being saved
     * @param string    $moduleName Module name
     * @return void
     */
    public function prepareRecordValues(CRMEntity $focus, string $moduleName): void
    {
        $fields = $this->getModuleFields($moduleName);

        if (!$fields) {
            return;
        }

        $storedValues = 'edit' === $focus->mode && $focus->id ? $this->fetchStoredCurrencies($focus, $fields) : [];

        foreach ($fields as $fieldName => $field) {
            $currencyName = Core_AmountCurrency_Helper::getCurrencyName($fieldName);
            $rateName = Core_AmountCurrency_Helper::getRateName($fieldName);
            $currencyId = (int)($focus->column_fields[$currencyName] ?? 0) ?: $this->getDefaultCurrencyId();
            $rate = (float)($focus->column_fields[$rateName] ?? 0);
            $storedCurrencyId = (int)($storedValues[$fieldName] ?? 0);

            if (0 >= $rate || $currencyId !== $storedCurrencyId) {
                $rate = $this->getCurrencyRate($currencyId);
            }

            $focus->column_fields[$currencyName] = $currencyId;
            $focus->column_fields[$rateName] = $rate;
        }
    }

    /**
     * Writes the amount in the base currency (amount / conversion rate) of every amount field after the record
     * was written, from the stored amount and rate, so the value never depends on the input format.
     *
     * @param CRMEntity $focus      Saved record
     * @param string    $moduleName Module name
     * @return void
     */
    public function saveBaseValues(CRMEntity $focus, string $moduleName): void
    {
        $db = PearDatabase::getInstance();

        foreach ($this->getFieldsByTable($this->getModuleFields($moduleName)) as $tableName => $columns) {
            $indexColumn = $focus->tab_name_index[$tableName] ?? '';

            if ('' === $indexColumn) {
                continue;
            }

            $assignments = [];

            foreach ($columns as $column) {
                $rateColumn = Core_AmountCurrency_Helper::getRateName($column);
                $assignments[] = sprintf(
                    '%s = CASE WHEN %s > 0 THEN %s / %s ELSE %s END',
                    Core_AmountCurrency_Helper::getBaseColumnName($column),
                    $rateColumn,
                    $column,
                    $rateColumn,
                    $column
                );
            }

            $db->pquery(
                sprintf('UPDATE %s SET %s WHERE %s = ?', $tableName, implode(', ', $assignments), $indexColumn),
                [$focus->id]
            );
        }
    }

    /**
     * Returns the currency columns of the amounts with their own currency among the given fields, prefixed with
     * a comma, for queries built without Core_QueryGenerator_Model (e.g. related lists), so each row shows its
     * own currency (Vtiger_Record_Model::getAmountCurrencyId()).
     *
     * @param string        $moduleName Module of the fields
     * @param array<string> $fieldNames Selected field names
     * @return string
     */
    public function getCurrencySelectColumns(string $moduleName, array $fieldNames): string
    {
        $columns = [];

        foreach (array_intersect_key($this->getModuleFields($moduleName), array_flip($fieldNames)) as $field) {
            $columns[] = $field['table'] . '.' . Core_AmountCurrency_Helper::getCurrencyName($field['column']);
        }

        return $columns ? ', ' . implode(', ', $columns) : '';
    }

    /**
     * Returns the stored amount in the base currency of an amount field, e.g. for workflow conditions.
     *
     * @param string $moduleName Module name
     * @param int    $recordId   Record id
     * @param string $fieldName  Amount field name
     * @return float|null
     */
    public function getBaseValue(string $moduleName, int $recordId, string $fieldName): ?float
    {
        $field = $this->getModuleFields($moduleName)[$fieldName] ?? null;
        $focus = CRMEntity::getInstance($moduleName);
        $indexColumn = $field ? ($focus->tab_name_index[$field['table']] ?? '') : '';

        if (!$field || '' === $indexColumn) {
            return null;
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            sprintf(
                'SELECT %s AS base_value FROM %s WHERE %s = ?',
                Core_AmountCurrency_Helper::getBaseColumnName($field['column']),
                $field['table'],
                $indexColumn
            ),
            [$recordId]
        );
        $value = $db->num_rows($result) ? $db->query_result($result, 0, 'base_value') : null;

        return null === $value || '' === $value ? null : (float)$value;
    }

    /**
     * Returns the currency of the current user, or the base currency.
     *
     * @return int
     */
    public function getDefaultCurrencyId(): int
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $currencyId = $user ? (int)$user->get('currency_id') : 0;

        return $currencyId ?: (int)CurrencyField::getDBCurrencyId();
    }

    /**
     * Returns the current conversion rate of a currency (units per one unit of the base currency).
     *
     * @param int $currencyId Currency id
     * @return float
     */
    public function getCurrencyRate(int $currencyId): float
    {
        $rate = (float)(getCurrencySymbolandCRate($currencyId)['rate'] ?? 0);

        return 0 < $rate ? $rate : 1.0;
    }

    /**
     * Returns the stored currencies of the amount fields of a record, keyed by field name.
     *
     * @param CRMEntity $focus  Record being saved
     * @param array     $fields Amount fields of the module
     * @return array<string, int>
     */
    protected function fetchStoredCurrencies(CRMEntity $focus, array $fields): array
    {
        $db = PearDatabase::getInstance();
        $values = [];

        foreach ($this->getFieldsByTable($fields) as $tableName => $columns) {
            $indexColumn = $focus->tab_name_index[$tableName] ?? '';

            if ('' === $indexColumn) {
                continue;
            }

            $selects = [];

            foreach ($columns as $fieldName => $column) {
                $selects[] = Core_AmountCurrency_Helper::getCurrencyName($column) . ' AS ' . Core_AmountCurrency_Helper::getCurrencyName($fieldName);
            }

            $result = $db->pquery(
                sprintf('SELECT %s FROM %s WHERE %s = ?', implode(', ', $selects), $tableName, $indexColumn),
                [$focus->id]
            );
            $row = $db->num_rows($result) ? $db->fetchByAssoc($result) : [];

            foreach ($columns as $fieldName => $column) {
                $values[$fieldName] = (int)($row[Core_AmountCurrency_Helper::getCurrencyName($fieldName)] ?? 0);
            }
        }

        return $values;
    }

    /**
     * Groups amount fields by table: [table => [field name => column]].
     *
     * @param array $fields Amount fields of the module
     * @return array<string, array<string, string>>
     */
    protected function getFieldsByTable(array $fields): array
    {
        $tables = [];

        foreach ($fields as $fieldName => $field) {
            $tables[$field['table']][$fieldName] = $field['column'];
        }

        return $tables;
    }

    /**
     * Clears the cached field lists, e.g. after a field was created.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$moduleFields = [];
    }
}

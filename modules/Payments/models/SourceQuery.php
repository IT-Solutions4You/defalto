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
 * Query of one Payments source: the payment module's own query generator, so its permissions and sharing apply,
 * with the Payments filter, the list search and the source condition. It yields the part of the Payments union.
 */
class Payments_SourceQuery_Model extends Core_QueryGenerator_Model
{
    /**
     * Field used to replace a condition on a field the source does not have; every record has a creation time.
     */
    public const ALWAYS_FILLED_FIELD = 'createdtime';

    protected string $sourceKey = '';
    protected array $source = [];

    /**
     * Creates the query of one source with its source condition.
     *
     * @param string $sourceKey Source key from Payments_Sources_Helper::getSources()
     * @param array  $source    Source configuration
     * @param object $user      Current user
     * @return self
     */
    public static function getSourceInstance(string $sourceKey, array $source, object $user): self
    {
        $query = new self($source['module'], $user);
        $query->sourceKey = $sourceKey;
        $query->source = $source;
        $query->addSourceCondition();

        return $query;
    }

    /**
     * Returns the source key.
     *
     * @return string
     */
    public function getSourceKey(): string
    {
        return $this->sourceKey;
    }

    /**
     * Returns the source configuration.
     *
     * @return array
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * Tells whether the source has a field, i.e. the module has it and the source does not treat it as missing.
     *
     * @param string $fieldName Field name
     * @return bool
     */
    public function hasSourceField(string $fieldName): bool
    {
        return isset($this->getModuleFields()[$fieldName]) && !Payments_Sources_Helper::isMissingField($this->source, $fieldName);
    }

    /**
     * Applies filter and search conditions; a condition on a field the source does not have is evaluated as if the
     * field were empty, because the parent would silently drop it and the source would then match too many records.
     *
     * @param array  $advFilterList Filter groups with their conditions
     * @param string $glue          Glue to the conditions added before
     * @return void
     */
    public function parseAdvFilterList($advFilterList, $glue = '')
    {
        parent::parseAdvFilterList($this->convertMissingFieldConditions((array)$advFilterList), $glue);
    }

    /**
     * Applies the simple list search; a search on a field the source does not have matches no record of the source.
     *
     * @param array $input Search input (search_field, search_text, operator)
     * @return void
     */
    public function addUserSearchConditions($input)
    {
        $fieldName = (string)($input['search_field'] ?? '');

        if ($fieldName !== '' && !$this->hasSourceField($fieldName)) {
            $this->addStructuredWhereCondition('1=0');

            return;
        }

        parent::addUserSearchConditions($input);
    }

    /**
     * Returns the query of the union part: the record id and the value the list is ordered by.
     *
     * @param string $orderFieldName Field the list is ordered by, or an empty string
     * @return array{query: string, parameters: array}
     */
    public function getUnionPartQueryData(string $orderFieldName): array
    {
        $this->projectedColumns = [
            'id' => $this->getBaseTableIndexColumn(),
            'sort' => $this->getUnionSortColumn($orderFieldName),
        ];
        $this->reset();

        return $this->getQueryData();
    }

    /**
     * Returns the SQL column the source is ordered by, or NULL when the source does not have the field.
     *
     * @param string $orderFieldName Field the list is ordered by
     * @return string
     */
    protected function getUnionSortColumn(string $orderFieldName): string
    {
        if ($orderFieldName === '' || !$this->hasSourceField($orderFieldName)) {
            return 'NULL';
        }

        $sortFieldName = Payments_Sources_Helper::getSortFieldName($orderFieldName);

        if (!$this->hasSourceField($sortFieldName)) {
            $sortFieldName = $orderFieldName;
        }

        $sortField = $this->getModuleFields()[$sortFieldName];

        if (in_array($sortField->getFieldDataType(), [Vtiger_Field_Model::REFERENCE_TYPE, Vtiger_Field_Model::OWNER_TYPE], true)) {
            $this->addWhereField($sortFieldName);
        }

        return (string)$this->getOrderByColumn($sortFieldName);
    }

    /**
     * Replaces conditions on fields the source does not have by an always true or always false condition,
     * keeping the AND/OR structure of the filter.
     *
     * @param array $advFilterList Filter groups with their conditions
     * @return array
     */
    protected function convertMissingFieldConditions(array $advFilterList): array
    {
        foreach ($advFilterList as $groupIndex => $group) {
            foreach ((array)($group['columns'] ?? []) as $columnIndex => $condition) {
                $fieldName = explode(':', (string)($condition['columnname'] ?? ''))[2] ?? '';

                if ($fieldName === '' || $this->hasSourceField($fieldName)) {
                    continue;
                }

                $advFilterList[$groupIndex]['columns'][$columnIndex] = $this->getConstantCondition(
                    $this->isSatisfiedByEmptyValue($condition),
                    (string)($condition['column_condition'] ?? '')
                );
            }
        }

        return $advFilterList;
    }

    /**
     * Tells whether a condition holds for an empty field value.
     *
     * @param array $condition Filter condition (comparator, value)
     * @return bool
     */
    protected function isSatisfiedByEmptyValue(array $condition): bool
    {
        $value = trim((string)($condition['value'] ?? ''));

        return match ((string)($condition['comparator'] ?? '')) {
            self::EMPTY, 'k' => true,
            'e' => $value === '',
            'n' => $value !== '',
            default => false,
        };
    }

    /**
     * Returns a condition on the creation time, which every record has: "is not empty" is always true,
     * "is empty" always false.
     *
     * @param bool   $result          Required result of the condition
     * @param string $columnCondition Glue to the next condition (and, or)
     * @return array
     */
    protected function getConstantCondition(bool $result, string $columnCondition): array
    {
        $field = $this->getModuleFields()[self::ALWAYS_FILLED_FIELD];

        return [
            'columnname' => implode(':', [
                $field->getTableName(),
                $field->getColumnName(),
                self::ALWAYS_FILLED_FIELD,
                $this->getModule() . '_Created_Time',
                'DT',
            ]),
            'comparator' => $result ? self::NOT_EMPTY : self::EMPTY,
            'value' => '',
            'column_condition' => $columnCondition,
        ];
    }

    /**
     * Limits the source to its records, e.g. the prototype sources split IncomingPayment by having a document.
     *
     * @return void
     */
    protected function addSourceCondition(): void
    {
        $condition = $this->source['condition'] ?? null;
        $field = $this->getModuleFields()[Payments_Sources_Helper::DOCUMENT_FIELD] ?? null;

        if (!$condition || !$field) {
            return;
        }

        $column = $this->getQualifiedColumn($field->getTableName(), $field->getColumnName());

        if (Payments_Sources_Helper::CONDITION_WITH_DOCUMENT === $condition) {
            $this->addStructuredWhereCondition($column . " IS NOT NULL AND $column <> ''");
        } elseif (Payments_Sources_Helper::CONDITION_WITHOUT_DOCUMENT === $condition) {
            $this->addStructuredWhereCondition($column . " IS NULL OR $column = ''");
        }
    }
}

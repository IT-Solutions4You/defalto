<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** Module-extensible filter contract shared by quick and saved list conditions. */
class Core_Filter_Model extends Vtiger_Base_Model
{
    protected array $fieldOperators = [];

    public static function getInstance(string $moduleName): self
    {
        $class = Vtiger_Loader::getComponentClassName('Model', 'Filter', $moduleName);
        $instance = new $class();

        if (!$instance instanceof self) {
            throw new Exception('Module filter models must extend Core_Filter_Model.');
        }

        $instance->set('module', $moduleName);
        $instance->initialize();
        return $instance;
    }

    /** Called once for each new object after its module context is assigned. */
    protected function initialize(): void
    {
    }

    public function setFieldOperators(string $fieldName, array $operators): self
    {
        $this->fieldOperators[$fieldName] = $operators;
        return $this;
    }

    public function getOperators(Vtiger_Field_Model $field): array
    {
        return $this->fieldOperators[$field->getName()] ?? Core_FilterOperator_Model::getDefaultForField($field);
    }

    /**
     * Input is an advanced-filter condition in the user's display format.
     * Both saved-view readback and quick requests arrive here before SQL parsing.
     * Keep currency strings, symbolic periods and group connectors unchanged.
     */
    public function getQueryCondition(array $condition): array
    {
        $column = explode(':', $condition['columnname']);
        $type = $column[4] ?? '';
        $operator = $condition['comparator'];

        if (in_array($operator, ['y', 'ny'], true) || Core_FilterOperator_Model::hasRelativeDateValue($operator)) {
            return $condition;
        }

        if ($type === 'T') {
            $values = is_array($condition['value']) ? $condition['value'] : explode(',', (string)$condition['value']);
            $values = array_map(
                [Vtiger_Time_UIType::class, 'getTimeValueWithSeconds'],
                $values
            );
            // Keep range endpoints separate all the way to SQL, independently
            // of the query generator's comma handling for other conditions.
            $condition['value'] = $this->isStandaloneTimeRange($column, $operator) ? $values : implode(',', $values);
        } elseif ($type === 'DT' && in_array($operator, ['bw', 'custom'], true)) {
            $values = explode(',', (string)$condition['value']);

            foreach ($values as $index => &$value) {
                $value = trim($value);

                if ($value !== '' && !str_contains($value, ' ')) {
                    $value .= $index === 0 ? ' 00:00:00' : ' 23:59:59';
                }
            }

            unset($value);
            $condition['value'] = implode(',', $values);
        }

        return $condition;
    }

    protected function isStandaloneTimeRange(array $column, string $operator): bool
    {
        return ($column[4] ?? '') === 'T'
            && $operator === 'bw'
            && !in_array($column[2] ?? '', ['time_start', 'time_end'], true);
    }
}

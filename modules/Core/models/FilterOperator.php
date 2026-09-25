<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** Shared operator presentation for saved views and the quick list filter. */
class Core_FilterOperator_Model extends Vtiger_Base_Model
{
    public static function getForField(Vtiger_Field_Model $field, ?string $moduleName = null): array
    {
        return Core_Filter_Model::getInstance($moduleName ?: $field->getModuleName())->getOperators($field);
    }

    public static function getDefaultForField(Vtiger_Field_Model $field): array
    {
        $type = $field->getFieldDataType();
        $fieldType = in_array($type, ['reference', 'multireference'], true) ? 'V' : $field->getFieldType();
        $mapping = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
        $labels = Vtiger_Field_Model::getAdvancedFilterOptions();
        $operators = $mapping[$fieldType] ?? $mapping['V'];
        $dates = [];

        if (in_array($fieldType, ['D', 'DT'], true)) {
            $dates = Vtiger_Field_Model::getDisplayDateFilterTypes();
            $operators = array_merge($operators, array_keys($dates));
        }

        if ($type === 'multipicklist') {
            $operators = array_diff($operators, ['e', 'n']);
        } elseif ($type === 'boolean') {
            // The saved-view editor expresses checkbox state as equals Yes/No.
            $operators = ['e'];
        } elseif ($type === 'currencyList') {
            $operators = $mapping['C'];
        }

        $result = [];

        foreach (array_unique($operators) as $operator) {
            $result[$operator] = $dates[$operator]['label'] ?? vtranslate($labels[$operator] ?? $operator);
        }

        return $result;
    }

    public static function hasRelativeDateValue(string $operator): bool
    {
        return $operator === 'lastperiod' || in_array($operator, Vtiger_Functions::getSpecialDateTimeCondtions(), true);
    }

    public static function isCalendarValue(string $type, string $name, string $operator): bool
    {
        return (in_array($type, ['D', 'DT', 'date', 'datetime'], true)
                || (in_array($type, ['T', 'time'], true) && !in_array($name, ['time_start', 'time_end'], true)))
            && !self::hasRelativeDateValue($operator);
    }
}

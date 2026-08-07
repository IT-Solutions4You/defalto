<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Grouping_UIType extends Vtiger_Base_UIType
{
    public function getTemplateName(): string
    {
        return 'uitypes/Grouping.tpl';
    }

    /**
     * @throws Exception
     */
    public function getFieldOptions(string $moduleName): array
    {
        return (new Reporting_Fields_UIType())->getFields($moduleName);
    }

    /**
     * @throws Exception
     */
    public function getSelectedFieldOptions(string $moduleName, array $selectedFields, array $labels = []): array
    {
        $fieldOptions = $this->getFieldOptions($moduleName);
        $selectedOptions = [];

        foreach ($selectedFields as $fieldName) {
            $fieldName = (string)$fieldName;

            if (isset($fieldOptions[$fieldName])) {
                $selectedOptions[$fieldName] = $labels[$fieldName] ?? $fieldOptions[$fieldName];
            }
        }

        return $selectedOptions;
    }

    public function getRequestValue(mixed $fieldValue): mixed
    {
        if (null === $fieldValue) {
            return null;
        }

        $values = is_array($fieldValue) ? $fieldValue : [$fieldValue];
        $values = array_values(array_unique(array_filter(array_map('trim', $values))));

        return json_encode($values);
    }

    public function getSelectedValues(mixed $fieldValue): array
    {
        if (empty($fieldValue)) {
            return [];
        }

        $fieldValue = decode_html($fieldValue);
        $selectedValues = json_decode($fieldValue, true);

        if (!is_array($selectedValues)) {
            return [(string)$fieldValue];
        }

        return array_values(array_unique(array_filter(array_map('strval', $selectedValues))));
    }

    /**
     * @throws Exception
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        if (empty($value) || !$recordInstance) {
            return $value;
        }

        $options = $this->getFieldOptions($recordInstance->getPrimaryModule());
        $labels = [];

        foreach ($this->getSelectedValues($value) as $fieldName) {
            $labels[] = $options[$fieldName] ?? $fieldName;
        }

        return implode(', ', $labels);
    }
}

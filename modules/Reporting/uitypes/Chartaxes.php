<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Chartaxes_UIType extends Core_Data_UIType
{
    public function getConfiguration(mixed $value, ?Reporting_Record_Model $record = null): array
    {
        return $record ? $record->getChartConfiguration() : Reporting_Chart_Model::getConfiguration($value);
    }

    public function getXAxisOptions(string $moduleName): array
    {
        return Reporting_Fields_Model::getFieldLabels($moduleName);
    }

    public function getXAxisDisplayValue(Reporting_Record_Model $record): string
    {
        $fieldLabels = $this->getXAxisOptions($record->getPrimaryModule());
        $fieldLabels = array_replace(
            $fieldLabels,
            array_intersect_key(array_filter($record->getLabels()), $fieldLabels),
        );
        $intervalLabels = $this->getDateGroupingIntervals();
        $labels = [];

        foreach ($record->getGroupByConfigurations() as $configuration) {
            $label = $fieldLabels[$configuration['field']] ?? $configuration['field'];

            if ('' !== $configuration['interval']) {
                $label .= ' (' . ($intervalLabels[$configuration['interval']] ?? $configuration['interval']) . ')';
            }

            $labels[] = $label;
        }

        return implode(' / ', $labels);
    }

    public function getYAxisOptions(string $moduleName): array
    {
        $labels = Reporting_Fields_Model::getFieldLabels($moduleName);
        $options = [];

        foreach ((new Reporting_Calculations_UIType())->getNumberFields($moduleName) as $fieldName) {
            if (isset($labels[$fieldName])) {
                $options[$fieldName] = $labels[$fieldName];
            }
        }

        return $options;
    }

    public function getDateFieldNames(string $moduleName): array
    {
        return Reporting_Fields_Model::getFieldsByDataTypes($moduleName, ['date', 'datetime']);
    }

    public function getDateGroupingIntervals(): array
    {
        return (new Reporting_Grouping_UIType())->getDateGroupingIntervals();
    }

    public function getAggregations(): array
    {
        return [
            'count' => vtranslate('LBL_COUNT', 'Reporting'),
            'sum' => vtranslate('LBL_SUM'),
            'avg' => vtranslate('LBL_AVG'),
            'min' => vtranslate('LBL_MIN'),
            'max' => vtranslate('LBL_MAX'),
        ];
    }

    public function getRequestValue(mixed $fieldValue): mixed
    {
        if (is_array($fieldValue)) {
            return json_encode(Reporting_Chart_Model::getConfiguration($fieldValue));
        }

        return is_string($fieldValue)
            ? json_encode(Reporting_Chart_Model::getConfiguration($fieldValue))
            : null;
    }

    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $configuration = $this->getConfiguration(
            $value,
            $recordInstance instanceof Reporting_Record_Model ? $recordInstance : null
        );

        if ('' === $configuration['x']['field']) {
            return '';
        }

        return sprintf(
            '%s: %s; %s: %d',
            vtranslate('LBL_CHART_X_AXIS', 'Reporting'),
            $configuration['x']['field'],
            vtranslate('LBL_CHART_Y_AXIS', 'Reporting'),
            count($configuration['series'])
        );
    }
}

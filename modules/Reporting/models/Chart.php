<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Chart_Model
{
    public const AGGREGATIONS = ['count', 'sum', 'avg', 'min', 'max'];
    public const NUMERIC_DATA_TYPES = ['double', 'currency', 'percentage', 'integer'];

    public static function getConfiguration(mixed $value): array
    {
        if (is_string($value)) {
            $decodedValue = decode_html($value);
            $decoded = json_decode($decodedValue, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($value)) {
            $value = [];
        }

        $xAxis = is_array($value['x'] ?? null) ? $value['x'] : [];
        $xField = trim((string)($xAxis['field'] ?? ''));
        $xInterval = trim((string)($xAxis['interval'] ?? ''));

        if (!in_array($xInterval, Reporting_Grouping_Model::DATE_INTERVALS, true)) {
            $xInterval = '';
        }

        $series = [];

        foreach ((array)($value['series'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $aggregation = strtolower(trim((string)($item['aggregation'] ?? '')));
            $field = trim((string)($item['field'] ?? ''));

            if (!in_array($aggregation, self::AGGREGATIONS, true)) {
                continue;
            }

            if ('count' === $aggregation) {
                $field = '';
            } elseif ('' === $field) {
                continue;
            }

            $series[$aggregation . ':' . $field] = [
                'field' => $field,
                'aggregation' => $aggregation,
            ];
        }

        return [
            'x' => [
                'field' => $xField,
                'interval' => $xInterval,
            ],
            'series' => array_values($series),
        ];
    }

    public static function getFields(mixed $value): array
    {
        $configuration = self::getConfiguration($value);
        $fields = [$configuration['x']['field']];

        foreach ($configuration['series'] as $series) {
            $fields[] = $series['field'];
        }

        return array_values(array_unique(array_filter($fields)));
    }
}

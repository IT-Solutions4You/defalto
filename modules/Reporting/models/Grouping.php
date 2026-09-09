<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Grouping_Model
{
    public const DATE_INTERVALS = ['day', 'week', 'month', 'quarter', 'year'];

    public static function getConfigurations(mixed $value): array
    {
        if (is_string($value)) {
            $decodedValue = decode_html($value);
            $decoded = json_decode($decodedValue, true);
            $value = is_array($decoded) ? $decoded : ('' === trim($decodedValue) ? [] : [$decodedValue]);
        } elseif (!is_array($value)) {
            $value = [];
        }

        $configurations = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $decodedItem = json_decode(decode_html($item), true);
                $item = is_array($decodedItem) ? $decodedItem : ['field' => $item];
            }

            if (!is_array($item)) {
                continue;
            }

            $field = trim((string)($item['field'] ?? ''));
            $interval = trim((string)($item['interval'] ?? ''));

            if ('' === $field) {
                continue;
            }

            if (!in_array($interval, self::DATE_INTERVALS, true)) {
                $interval = '';
            }

            $configurations[$field] = [
                'field' => $field,
                'interval' => $interval,
            ];
        }

        return array_values($configurations);
    }

    public static function getFields(mixed $value): array
    {
        return array_column(self::getConfigurations($value), 'field');
    }

    public static function getIntervals(mixed $value): array
    {
        $intervals = [];

        foreach (self::getConfigurations($value) as $configuration) {
            if ('' !== $configuration['interval']) {
                $intervals[$configuration['field']] = $configuration['interval'];
            }
        }

        return $intervals;
    }
}

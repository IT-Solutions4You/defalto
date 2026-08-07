<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_PDFChart_Helper
{
    protected const COLORS = [
        '#0d6efd',
        '#6f42c1',
        '#198754',
        '#fd7e14',
        '#dc3545',
        '#0dcaf0',
        '#ffc107',
        '#6c757d',
    ];

    public static function getPDFData(Reporting_Record_Model $recordModel, array $chartData): array
    {
        $labels = array_map([self::class, 'getPlainText'], $chartData['data']['labels'] ?? []);
        $datasets = self::getDatasets($chartData['data']['datasets'] ?? [], count($labels));

        if (empty($labels) || empty($datasets)) {
            return [];
        }

        $recordName = self::getPlainText($recordModel->getName());
        $chartLabel = vtranslate('LBL_CHART', 'Reporting');

        return [
            'title' => trim($recordName . ' - ' . $chartLabel, ' -'),
            'description' => sprintf(
                vtranslate('LBL_PDF_CHART_DESCRIPTION', 'Reporting'),
                count($labels),
                count($datasets)
            ),
            'image' => self::getChartImage($chartData['type'] ?? 'bar', $labels, $datasets),
            'insights' => self::getInsights($labels, $datasets),
            'filters' => self::getFilters($recordModel),
        ];
    }

    protected static function getDatasets(array $datasets, int $labelCount): array
    {
        $normalizedDatasets = [];

        foreach ($datasets as $dataset) {
            $values = array_slice(array_map('floatval', (array)($dataset['data'] ?? [])), 0, $labelCount);

            if (count($values) < $labelCount) {
                $values = array_pad($values, $labelCount, 0.0);
            }

            $normalizedDatasets[] = [
                'label' => self::getPlainText($dataset['label'] ?? vtranslate('LBL_CHART', 'Reporting')),
                'data' => $values,
            ];
        }

        return $normalizedDatasets;
    }

    protected static function getChartImage(string $type, array $labels, array $datasets): string
    {
        $svg = in_array($type, ['pie', 'doughnut'], true)
            ? self::getCircularChartSvg($type, $labels, $datasets)
            : self::getCartesianChartSvg($type, $labels, $datasets);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    protected static function getCartesianChartSvg(string $type, array $labels, array $datasets): string
    {
        $width = 1000;
        $height = 480;
        $left = 76;
        $right = 28;
        $top = 58;
        $bottom = 92;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $values = [];

        foreach ($datasets as $dataset) {
            $values = array_merge($values, $dataset['data']);
        }

        $minimum = min(0.0, min($values));
        $maximum = max(0.0, max($values));

        if ($minimum === $maximum) {
            $maximum = $minimum + 1.0;
        }

        $range = $maximum - $minimum;
        $zeroY = $top + (($maximum / $range) * $plotHeight);
        $svg = self::getSvgStart($width, $height);

        for ($step = 0; $step <= 5; $step++) {
            $ratio = $step / 5;
            $y = $top + ($plotHeight * $ratio);
            $value = $maximum - ($range * $ratio);
            $svg .= sprintf(
                '<line x1="%1$.2f" y1="%2$.2f" x2="%3$.2f" y2="%2$.2f" stroke="#dee2e6" stroke-width="1"/>',
                $left,
                $y,
                $left + $plotWidth
            );
            $svg .= self::getSvgText($left - 10, $y + 4, self::getFormattedNumber($value), 12, '#6c757d', 'end');
        }

        $categoryCount = max(1, count($labels));
        $categoryWidth = $plotWidth / $categoryCount;

        if ('line' === $type) {
            foreach ($datasets as $datasetIndex => $dataset) {
                $points = [];
                $color = self::COLORS[$datasetIndex % count(self::COLORS)];

                foreach ($dataset['data'] as $valueIndex => $value) {
                    $x = $left + ($categoryWidth * $valueIndex) + ($categoryWidth / 2);
                    $y = $top + ((($maximum - $value) / $range) * $plotHeight);
                    $points[] = sprintf('%1$.2f,%2$.2f', $x, $y);
                }

                $svg .= sprintf(
                    '<polyline points="%s" fill="none" stroke="%s" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>',
                    implode(' ', $points),
                    $color
                );

                foreach ($points as $point) {
                    [$x, $y] = explode(',', $point);
                    $svg .= sprintf('<circle cx="%s" cy="%s" r="4" fill="%s"/>', $x, $y, $color);
                }
            }
        } else {
            $datasetCount = max(1, count($datasets));
            $barWidth = max(3.0, min(34.0, ($categoryWidth * 0.72) / $datasetCount));

            foreach ($datasets as $datasetIndex => $dataset) {
                $color = self::COLORS[$datasetIndex % count(self::COLORS)];

                foreach ($dataset['data'] as $valueIndex => $value) {
                    $groupWidth = $barWidth * $datasetCount;
                    $x = $left + ($categoryWidth * $valueIndex) + (($categoryWidth - $groupWidth) / 2)
                        + ($barWidth * $datasetIndex);
                    $valueY = $top + ((($maximum - $value) / $range) * $plotHeight);
                    $y = min($zeroY, $valueY);
                    $barHeight = max(1.0, abs($zeroY - $valueY));
                    $svg .= sprintf(
                        '<rect x="%1$.2f" y="%2$.2f" width="%3$.2f" height="%4$.2f" rx="2" fill="%5$s"/>',
                        $x,
                        $y,
                        max(2.0, $barWidth - 2),
                        $barHeight,
                        $color
                    );
                }
            }
        }

        $labelStep = max(1, (int)ceil(count($labels) / 12));

        foreach ($labels as $labelIndex => $label) {
            if (0 !== $labelIndex % $labelStep) {
                continue;
            }

            $x = $left + ($categoryWidth * $labelIndex) + ($categoryWidth / 2);
            $svg .= self::getSvgText(
                $x,
                $top + $plotHeight + 24,
                self::getTruncatedText($label, 18),
                11,
                '#495057',
                'middle'
            );
        }

        $svg .= self::getDatasetLegend($datasets, $left, 24);

        return $svg . '</svg>';
    }

    protected static function getCircularChartSvg(string $type, array $labels, array $datasets): string
    {
        $width = 1000;
        $height = 480;
        $centerX = 310;
        $centerY = 245;
        $radius = 165;
        $values = array_map(static fn(float $value): float => max(0.0, $value), $datasets[0]['data']);
        $total = array_sum($values);

        if ($total <= 0) {
            return self::getCartesianChartSvg('bar', $labels, $datasets);
        }

        $svg = self::getSvgStart($width, $height);
        $angle = -M_PI / 2;

        foreach ($values as $index => $value) {
            if ($value <= 0) {
                continue;
            }

            $color = self::COLORS[$index % count(self::COLORS)];
            $sliceAngle = ($value / $total) * M_PI * 2;

            if ($sliceAngle >= (M_PI * 2) - 0.0001) {
                $svg .= sprintf(
                    '<circle cx="%d" cy="%d" r="%d" fill="%s"/>',
                    $centerX,
                    $centerY,
                    $radius,
                    $color
                );
            } else {
                $endAngle = $angle + $sliceAngle;
                [$startX, $startY] = self::getCirclePoint($centerX, $centerY, $radius, $angle);
                [$endX, $endY] = self::getCirclePoint($centerX, $centerY, $radius, $endAngle);
                $largeArc = $sliceAngle > M_PI ? 1 : 0;
                $svg .= sprintf(
                    '<path d="M %1$d %2$d L %3$.2f %4$.2f A %5$d %5$d 0 %6$d 1 %7$.2f %8$.2f Z" fill="%9$s"/>',
                    $centerX,
                    $centerY,
                    $startX,
                    $startY,
                    $radius,
                    $largeArc,
                    $endX,
                    $endY,
                    $color
                );
                $angle = $endAngle;
            }
        }

        if ('doughnut' === $type) {
            $svg .= sprintf(
                '<circle cx="%d" cy="%d" r="%d" fill="#ffffff"/>',
                $centerX,
                $centerY,
                (int)round($radius * 0.56)
            );
        }

        $legendX = 555;
        $legendY = 68;

        foreach (array_slice($labels, 0, 14, true) as $index => $label) {
            $y = $legendY + ($index * 27);
            $color = self::COLORS[$index % count(self::COLORS)];
            $svg .= sprintf('<rect x="%d" y="%d" width="14" height="14" rx="2" fill="%s"/>', $legendX, $y, $color);
            $svg .= self::getSvgText(
                $legendX + 23,
                $y + 12,
                self::getTruncatedText($label, 26) . ' (' . self::getFormattedNumber($values[$index] ?? 0) . ')',
                12,
                '#343a40'
            );
        }

        return $svg . '</svg>';
    }

    protected static function getDatasetLegend(array $datasets, int $startX, int $y): string
    {
        $svg = '';
        $x = $startX;

        foreach ($datasets as $index => $dataset) {
            $color = self::COLORS[$index % count(self::COLORS)];
            $label = self::getTruncatedText($dataset['label'], 28);
            $svg .= sprintf('<rect x="%d" y="%d" width="14" height="14" rx="2" fill="%s"/>', $x, $y, $color);
            $svg .= self::getSvgText($x + 22, $y + 12, $label, 12, '#343a40');
            $x += max(150, (strlen($label) * 7) + 52);
        }

        return $svg;
    }

    protected static function getSvgStart(int $width, int $height): string
    {
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d">'
            . '<rect width="100%%" height="100%%" rx="8" fill="#ffffff"/>',
            $width,
            $height
        );
    }

    protected static function getSvgText(
        float $x,
        float $y,
        string $text,
        int $size,
        string $color,
        string $anchor = 'start'
    ): string {
        return sprintf(
            '<text x="%1$.2f" y="%2$.2f" font-family="Arial, sans-serif" font-size="%3$d" fill="%4$s" text-anchor="%5$s">%6$s</text>',
            $x,
            $y,
            $size,
            $color,
            $anchor,
            htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8')
        );
    }

    protected static function getCirclePoint(int $centerX, int $centerY, int $radius, float $angle): array
    {
        return [
            $centerX + ($radius * cos($angle)),
            $centerY + ($radius * sin($angle)),
        ];
    }

    protected static function getInsights(array $labels, array $datasets): array
    {
        $insights = [];

        foreach ($datasets as $dataset) {
            if (empty($dataset['data'])) {
                continue;
            }

            $maximum = max($dataset['data']);
            $minimum = min($dataset['data']);
            $maximumIndex = array_search($maximum, $dataset['data'], true);
            $minimumIndex = array_search($minimum, $dataset['data'], true);
            $insights[] = sprintf(
                vtranslate('LBL_PDF_CHART_INSIGHT', 'Reporting'),
                $dataset['label'],
                $labels[$maximumIndex] ?? '',
                self::getFormattedNumber($maximum),
                $labels[$minimumIndex] ?? '',
                self::getFormattedNumber($minimum)
            );
        }

        return $insights;
    }

    protected static function getFilters(Reporting_Record_Model $recordModel): array
    {
        $filters = [];
        $operatorLabels = Vtiger_Field_Model::getAdvancedFilterOptions();

        foreach ($recordModel->getFilter() as $group) {
            foreach ((array)($group['columns'] ?? []) as $condition) {
                $columnName = decode_html((string)($condition['columnname'] ?? ''));

                if ('' === $columnName || 'none' === $columnName) {
                    continue;
                }

                $comparator = (string)($condition['comparator'] ?? '');
                $operatorLabel = $operatorLabels[$comparator] ?? self::getHumanizedText($comparator);
                $value = self::getPlainText($condition['value'] ?? '');
                $parts = array_filter([
                    self::getFilterFieldLabel($columnName),
                    vtranslate($operatorLabel, 'Vtiger'),
                    $value,
                ], static fn(string $part): bool => '' !== trim($part));
                $filters[] = implode(' ', $parts);
            }
        }

        return array_values(array_unique($filters));
    }

    protected static function getFilterFieldLabel(string $columnName): string
    {
        $parts = explode(':', $columnName);
        $moduleFieldLabel = $parts[3] ?? '';

        if ('' !== $moduleFieldLabel && str_contains($moduleFieldLabel, '_')) {
            [$moduleName, $fieldLabel] = explode('_', $moduleFieldLabel, 2);

            return self::getPlainText(vtranslate(str_replace('_', ' ', $fieldLabel), $moduleName));
        }

        return self::getHumanizedText($parts[2] ?? $columnName);
    }

    protected static function getFormattedNumber(float $value): string
    {
        $precision = abs($value - round($value)) < 0.00001 ? 0 : 2;

        return number_format($value, $precision, '.', ' ');
    }

    protected static function getTruncatedText(string $value, int $length): string
    {
        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

        if (false === $characters) {
            return strlen($value) > $length ? substr($value, 0, $length - 3) . '...' : $value;
        }

        return count($characters) > $length
            ? implode('', array_slice($characters, 0, $length - 3)) . '...'
            : $value;
    }

    protected static function getHumanizedText(string $value): string
    {
        return ucfirst(trim(str_replace(['_', '-'], ' ', $value)));
    }

    protected static function getPlainText(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([self::class, 'getPlainText'], $value));
        }

        return trim(html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5));
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Table_Model extends Vtiger_Base_Model
{
    public const GROUP_SUMMARY_COLUMN = '__group_summary';
    public const RECORD_COUNT_COLUMN = '__record_count';

    public static array $extensionModules = ['Users'];
    public array $fieldNames = [];
    public array $fields = [];
    public array $modules = [];
    public false|int $recordId = false;
    public false|string $moduleName = false;
    public array $records = [];
    public array $tableColumns = [];
    public array $tableRecords = [];
    public array $tableLabels = [];
    public array $tableCalculations = [];
    public array $tableAlignments = [];
    public array $groupBy = [];
    protected array $groupByIntervals = [];
    protected array $groupBySortDirections = [];
    protected ?array $groupedData = null;
    protected ?array $grandTotalRow = null;
    protected ?array $tableTotalRow = null;
    protected ?array $reportingCurrency = null;
    protected ?int $reportingCurrencyId = null;
    protected bool $groupByCurrency = false;
    protected bool $recordCount = false;
    protected array $recordCurrencies = [];

    /**
     * @return array
     */
    public function getFieldNames(): array
    {
        return $this->fieldNames;
    }

    /**
     * @param array $value
     *
     * @return void
     */
    public function setFieldNames(array $value): void
    {
        $this->fieldNames = $value;
    }

    /**
     * @param $moduleName
     *
     * @return self
     */
    public static function getInstance($moduleName): self
    {
        $instance = new self();
        $instance->moduleName = $moduleName;

        return $instance;
    }

    /**
     * @param int    $recordId
     * @param string $moduleName
     *
     * @return object
     */
    public function getRecord(int $recordId, string $moduleName): object
    {
        $recordKey = implode(':', array_filter([$recordId, $moduleName]));

        if (empty($this->records[$recordKey])) {
            if ($this->isRecordExists($recordId, $moduleName)) {
                $record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            } else {
                $record = Vtiger_Record_Model::getCleanInstance($moduleName);
            }

            $this->records[$recordKey] = $record;
        }

        return $this->records[$recordKey];
    }

    public function isRecordExists(int $recordId, string $moduleName): bool
    {
        if (empty($recordId)) {
            return false;
        }

        if (empty(getTabid($moduleName))) {
            return false;
        }

        if (!in_array($moduleName, self::$extensionModules) && isRecordExists($recordId)) {
            return true;
        }

        return in_array($moduleName, self::$extensionModules);
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @param array $records
     *
     * @return void
     */
    public function setRecords(array $records): void
    {
        $this->records = array_merge($this->records, $records);
    }

    /**
     * @throws Exception
     */
    public function getTable(): array
    {
        $labels = $this->getTableLabels();
        $fieldNames = $this->getTableColumnsWithCalculations();
        $table = [[]];

        foreach ($fieldNames as $fieldName) {
            $table[0][] = $labels[$fieldName]
                ?? $this->tableCalculations[$fieldName]['label']
                ?? (self::RECORD_COUNT_COLUMN === $fieldName ? vtranslate('LBL_COUNT', 'Reporting') : null)
                ?? $fieldName;
        }

        foreach ($this->getTableRecords() as $tableRecord) {
            $table[] = $this->getTableRecordRow($tableRecord, $fieldNames);
        }

        $tableTotalRow = $this->getTableTotalRow();

        if (!empty($tableTotalRow)) {
            $table[] = $tableTotalRow;
        }

        return $table;
    }

    public function getExportTable(): array
    {
        $labels = $this->getTableLabels();
        $fieldNames = $this->getTableColumnsWithCalculations();
        $table = [[]];

        foreach ($fieldNames as $fieldName) {
            $table[0][] = $labels[$fieldName]
                ?? $this->tableCalculations[$fieldName]['label']
                ?? (self::RECORD_COUNT_COLUMN === $fieldName ? vtranslate('LBL_COUNT', 'Reporting') : null)
                ?? $fieldName;
        }

        foreach ($this->getTableRecords() as $tableRecord) {
            $table[] = $this->getTableRecordRow($tableRecord, $fieldNames);
        }

        $metrics = $this->getCalculationMetrics($this->getTableRecords());

        if (!empty($metrics)) {
            $table = array_merge(
                $table,
                $this->getExportCalculationRows($metrics, $fieldNames, $this->getTotalLabel($metrics)),
            );
        }

        return $table;
    }

    public function getTableRowTypes(): array
    {
        if (empty($this->getTableColumnsWithCalculations())) {
            return [];
        }

        $rowTypes = array_merge(
            ['header'],
            array_fill(0, count($this->getTableRecords()), 'table_record'),
        );

        if (!empty($this->getTableTotalRow())) {
            $rowTypes[] = 'grand_total';
        }

        return $rowTypes;
    }

    /**
     * @throws Exception
     */
    public function getTableCalculations(): array
    {
        if (empty($this->tableCalculations) || empty($this->getTableRecords())) {
            return [];
        }

        $fieldNames = $this->getTableColumns();
        $formatRecord = array_values($this->getTableRecords())[0];
        $data = [];
        $table = [
            [
                '',
                vtranslate('LBL_SUM', 'Reporting'),
                vtranslate('LBL_AVG', 'Reporting'),
                vtranslate('LBL_MIN', 'Reporting'),
                vtranslate('LBL_MAX', 'Reporting'),
            ]
        ];

        foreach ($this->getTableRecords() as $tableRecord) {
            foreach ($fieldNames as $fieldName) {
                $data[$fieldName][] = $this->getCalculationFieldValue($fieldName, $tableRecord);
            }
        }

        foreach ($this->tableCalculations as $fieldName => $value) {
            $values = $data[$fieldName];
            $row = [
                $value['label'],
            ];

            $row[] = 'Yes' === $value['sum']
                ? $this->getFormattedCalculationValue($fieldName, $formatRecord, $this->sum($values))
                : '-';
            $row[] = 'Yes' === $value['avg']
                ? $this->getFormattedCalculationValue($fieldName, $formatRecord, $this->avg($values))
                : '-';
            $row[] = 'Yes' === $value['min']
                ? $this->getFormattedCalculationValue($fieldName, $formatRecord, $this->min($values))
                : '-';
            $row[] = 'Yes' === $value['max']
                ? $this->getFormattedCalculationValue($fieldName, $formatRecord, $this->max($values))
                : '-';

            $table[] = $row;
        }

        return $table;
    }

    /**
     * @throws Exception
     */
    public function getGroupedTable(): array
    {
        $fieldNames = $this->getGroupedTableColumns();

        if (empty($this->getGroupBy()) || empty($fieldNames)) {
            return [];
        }

        $labels = $this->getTableLabels();
        $table = [[]];

        foreach ($fieldNames as $fieldName) {
            $table[0][] = $labels[$fieldName]
                ?? $this->tableCalculations[$fieldName]['label']
                ?? (self::GROUP_SUMMARY_COLUMN === $fieldName ? vtranslate('LBL_GROUP_BY', 'Reporting') : null)
                ?? (self::RECORD_COUNT_COLUMN === $fieldName ? vtranslate('LBL_COUNT', 'Reporting') : null)
                ?? $fieldName;
        }

        foreach ($this->getGroupedData() as $group) {
            $table[] = $this->getGroupedSummaryRow($group);

            foreach ($group['rows'] as $row) {
                $table[] = $row;
            }
        }

        $grandTotalRow = $this->getGrandTotalRow();

        if (!empty($grandTotalRow)) {
            $table[] = $grandTotalRow;
        }

        return $table;
    }

    public function getGroupedExportTable(): array
    {
        $fieldNames = $this->getGroupedTableColumns();

        if (empty($this->getGroupBy()) || empty($fieldNames)) {
            return [];
        }

        $labels = $this->getTableLabels();
        $table = [[]];

        foreach ($fieldNames as $fieldName) {
            $table[0][] = $labels[$fieldName]
                ?? $this->tableCalculations[$fieldName]['label']
                ?? (self::GROUP_SUMMARY_COLUMN === $fieldName ? vtranslate('LBL_GROUP_BY', 'Reporting') : null)
                ?? (self::RECORD_COUNT_COLUMN === $fieldName ? vtranslate('LBL_COUNT', 'Reporting') : null)
                ?? $fieldName;
        }

        foreach ($this->getGroupedData() as $group) {
            $table = array_merge(
                $table,
                $this->getExportCalculationRows(
                    $group['metrics'],
                    $fieldNames,
                    $this->getGroupedSummaryLabel($group),
                ),
                $group['rows'],
            );
        }

        $metrics = $this->getCalculationMetrics($this->getTableRecords());

        if (!empty($metrics)) {
            $table = array_merge(
                $table,
                $this->getExportCalculationRows($metrics, $fieldNames, $this->getTotalLabel($metrics)),
            );
        }

        return $table;
    }

    public function getGroupedTableRowTypes(): array
    {
        if (empty($this->getGroupBy()) || empty($this->getGroupedTableColumns())) {
            return [];
        }

        $rowTypes = ['header'];

        foreach ($this->getGroupedData() as $group) {
            $rowTypes[] = 'group';
            $rowTypes = array_merge($rowTypes, array_fill(0, count($group['rows']), 'record'));
        }

        if (!empty($this->getGrandTotalRow())) {
            $rowTypes[] = 'grand_total';
        }

        return $rowTypes;
    }

    /**
     * @throws Exception
     */
    public function getGroupedData(): array
    {
        if (null !== $this->groupedData) {
            return $this->groupedData;
        }

        $groups = [];
        $groupByFields = $this->getGroupBy();

        if (empty($groupByFields)) {
            return $this->groupedData = [];
        }

        foreach ($this->getTableRecords() as $tableRecord) {
            $rawValues = [];
            $displayValues = [];
            $sortValues = [];

            foreach ($groupByFields as $groupByField) {
                $rawValue = $this->getRawFieldValue($groupByField, $tableRecord);
                $displayValue = $this->getFieldValue($groupByField, $tableRecord);
                $groupingInterval = $this->groupByIntervals[$groupByField] ?? '';
                [$rawValue, $displayValue] = $this->getDateGroupingValue(
                    $groupByField,
                    $rawValue,
                    $displayValue,
                    $groupingInterval
                );

                if ('' !== $groupingInterval) {
                    $sortValues[] = [
                        'value' => $this->getDateGroupingSortValue($rawValue, $groupingInterval),
                        'direction' => $this->groupBySortDirections[$groupByField] ?? 'ASC',
                    ];
                }

                $rawValues[] = [
                    'type' => get_debug_type($rawValue),
                    'value' => $rawValue,
                ];
                $displayValues[] = '' === trim(strip_tags((string)$displayValue))
                    ? vtranslate('LBL_EMPTY_GROUP', 'Reporting')
                    : $displayValue;
            }

            $groupKey = json_encode($rawValues);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'label' => implode(' / ', $displayValues),
                    'labels' => $displayValues,
                    'count' => 0,
                    'records' => [],
                    'rows' => [],
                    'metrics' => [],
                    '_sort_values' => $sortValues,
                    '_sequence' => count($groups),
                ];
            }

            $groups[$groupKey]['count']++;
            $groups[$groupKey]['records'][] = $tableRecord;
        }

        foreach ($groups as &$group) {
            $group['metrics'] = $this->getCalculationMetrics($group['records']);

            if ($this->hasGroupedDetailColumns()) {
                foreach ($group['records'] as $record) {
                    $group['rows'][] = $this->getGroupedTableRecordRow($record);
                }
            }

            unset($group['records']);
        }
        unset($group);

        return $this->groupedData = $this->sortGroupedData($groups);
    }

    protected function sortGroupedData(array $groups): array
    {
        if (!empty($this->groupByIntervals)) {
            uasort($groups, static function (array $left, array $right): int {
                $leftValues = $left['_sort_values'] ?? [];
                $rightValues = $right['_sort_values'] ?? [];
                $valueCount = max(count($leftValues), count($rightValues));

                for ($index = 0; $index < $valueCount; $index++) {
                    $leftSort = $leftValues[$index] ?? [];
                    $rightSort = $rightValues[$index] ?? [];
                    $leftValue = $leftSort['value'] ?? null;
                    $rightValue = $rightSort['value'] ?? null;
                    $direction = 'DESC' === ($leftSort['direction'] ?? 'ASC') ? -1 : 1;

                    if ($leftValue === $rightValue) {
                        continue;
                    }

                    if (null === $leftValue) {
                        return 1;
                    }

                    if (null === $rightValue) {
                        return -1;
                    }

                    return $direction * ($leftValue <=> $rightValue);
                }

                return ($left['_sequence'] ?? 0) <=> ($right['_sequence'] ?? 0);
            });
        }

        foreach ($groups as &$group) {
            unset($group['_sort_values'], $group['_sequence']);
        }
        unset($group);

        return $groups;
    }

    /**
     * @throws Exception
     */
    protected function getGrandTotalRow(): array
    {
        if (null !== $this->grandTotalRow) {
            return $this->grandTotalRow;
        }

        return $this->grandTotalRow = $this->getTotalRow($this->getGroupedTableColumns());
    }

    protected function getTableTotalRow(): array
    {
        if (null !== $this->tableTotalRow) {
            return $this->tableTotalRow;
        }

        return $this->tableTotalRow = $this->getTotalRow($this->getTableColumnsWithCalculations());
    }

    protected function getTotalRow(array $fieldNames): array
    {
        $records = $this->getTableRecords();

        if (empty($fieldNames) || empty($records)) {
            return [];
        }

        $metrics = $this->getCalculationMetrics($records);

        if (empty($metrics)) {
            return [];
        }

        $row = array_fill(0, count($fieldNames), '');
        $row[0] = $this->getTotalLabel($metrics);

        return $this->setCalculationMetricsInRow($row, $metrics, $fieldNames);
    }

    protected function getGroupedSummaryRow(array $group): array
    {
        $fieldNames = $this->getGroupedTableColumns();
        $row = array_fill(0, count($fieldNames), '');

        $row[0] = $this->getGroupedSummaryLabel($group);

        return $this->setCalculationMetricsInRow($row, $group['metrics'], $fieldNames);
    }

    protected function getGroupedSummaryLabel(array $group): string
    {
        $label = implode(', ', $group['labels']);

        return $this->recordCount ? $label : sprintf('%s (%d)', $label, $group['count']);
    }

    protected function getTotalLabel(array $metrics): string
    {
        $calculationFields = array_values(array_unique(array_column($metrics, 'field')));

        return vtranslate(
            1 < count($calculationFields) ? 'LBL_CALCULATION_SUMMARY' : 'LBL_GRAND_TOTAL',
            'Reporting',
        );
    }

    protected function getExportCalculationRows(array $metrics, array $fieldNames, string $label): array
    {
        $columnMetrics = [];

        foreach ($metrics as $metric) {
            $columnIndex = array_search($metric['field'], $fieldNames, true);

            if (false !== $columnIndex) {
                $columnMetrics[$columnIndex][] = $metric;
            }
        }

        if (empty($columnMetrics)) {
            $row = array_fill(0, count($fieldNames), '');
            $row[0] = $label;

            return [$row];
        }

        $columnValues = [];
        $rowCount = 1;

        foreach ($columnMetrics as $columnIndex => $metricsForColumn) {
            $columnValues[$columnIndex] = $this->getExportMetricValues($metricsForColumn);
            $rowCount = max($rowCount, count($columnValues[$columnIndex]));
        }

        $rows = [];

        for ($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++) {
            $row = array_fill(0, count($fieldNames), '');

            if (0 === $rowIndex) {
                $row[0] = $label;
            }

            foreach ($columnValues as $columnIndex => $values) {
                if (!isset($values[$rowIndex])) {
                    continue;
                }

                if (0 === $columnIndex && '' !== $row[$columnIndex]) {
                    $row[$columnIndex] .= ' — ' . $values[$rowIndex];
                } else {
                    $row[$columnIndex] = $values[$rowIndex];
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    protected function getExportMetricValues(array $metrics): array
    {
        $operations = [];
        $values = [];

        foreach ($metrics as $metric) {
            $operation = $metric['operation'];
            $operations[$operation]['label'] = $metric['operation_label'];
            $operations[$operation]['values'][] = html_entity_decode(
                strip_tags((string)$metric['display']),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8',
            );
        }

        $showOperationLabels = 1 < count($operations);

        foreach ($operations as $operation) {
            foreach ($operation['values'] as $valueIndex => $displayValue) {
                $values[] = $showOperationLabels && 0 === $valueIndex
                    ? sprintf('%s: %s', $operation['label'], $displayValue)
                    : $displayValue;
            }
        }

        return $values;
    }

    protected function setCalculationMetricsInRow(array $row, array $metrics, array $fieldNames): array
    {
        $columnMetrics = [];

        foreach ($metrics as $metric) {
            $columnIndex = array_search($metric['field'], $fieldNames, true);

            if (false === $columnIndex) {
                continue;
            }

            $columnMetrics[$columnIndex][] = $metric;
        }

        foreach ($columnMetrics as $columnIndex => $metricsForColumn) {
            $fieldName = $metricsForColumn[0]['field'];
            $metricsContainer = $this->getMetricsDisplay(
                $metricsForColumn,
                $this->getMetricsAlignmentClasses($fieldName),
            );
            $row[$columnIndex] .= empty($row[$columnIndex]) ? $metricsContainer : ' ' . $metricsContainer;
        }

        return $row;
    }

    protected function getMetricsDisplay(array $metrics, array $alignmentClasses): string
    {
        $operations = [];

        foreach ($metrics as $metric) {
            $operation = $metric['operation'];
            $operations[$operation]['label'] = $metric['operation_label'];
            $operations[$operation]['values'][] = $metric['display'];
        }

        $showOperationLabels = 1 < count($operations);
        $operationDisplays = [];

        foreach ($operations as $operation) {
            $values = [];

            foreach ($operation['values'] as $displayValue) {
                $values[] = sprintf('<span class="text-nowrap">%s</span>', $displayValue);
            }

            $valuesDisplay = sprintf(
                '<span class="d-flex flex-column %s">%s</span>',
                $alignmentClasses['items'],
                implode('', $values),
            );

            if (!$showOperationLabels) {
                $operationDisplays[] = $valuesDisplay;
                continue;
            }

            $operationDisplays[] = sprintf(
                '<span class="reportingMetric d-flex align-items-start %s gap-1"><span class="fw-normal text-nowrap">%s:</span>%s</span>',
                $alignmentClasses['content'],
                htmlspecialchars($operation['label'], ENT_QUOTES, 'UTF-8'),
                $valuesDisplay,
            );
        }

        return sprintf(
            '<span class="reportingMetrics d-flex flex-column %s %s gap-1">%s</span>',
            $alignmentClasses['items'],
            $alignmentClasses['content'],
            implode('', $operationDisplays),
        );
    }

    protected function getMetricsAlignmentClasses(string $fieldName): array
    {
        return match (strtolower(trim((string)($this->tableAlignments[$fieldName] ?? '')))) {
            'left', 'start' => [
                'items' => 'align-items-start',
                'content' => 'justify-content-start',
            ],
            'center' => [
                'items' => 'align-items-center',
                'content' => 'justify-content-center',
            ],
            default => [
                'items' => 'align-items-end',
                'content' => 'justify-content-end',
            ],
        };
    }

    /**
     * @throws Exception
     */
    protected function getCalculationMetrics(array $records): array
    {
        if (empty($records)) {
            return [];
        }

        $formatRecord = reset($records);
        $metrics = [];

        if ($this->recordCount) {
            $metrics[self::RECORD_COUNT_COLUMN . ':count'] = [
                'label' => vtranslate('LBL_COUNT_RECORDS', 'Reporting'),
                'field' => self::RECORD_COUNT_COLUMN,
                'operation' => 'count',
                'operation_label' => vtranslate('LBL_COUNT', 'Reporting'),
                'value' => (float)count($records),
                'display' => (string)count($records),
            ];
        }

        foreach ($this->tableCalculations as $fieldName => $calculation) {
            if ($this->groupByCurrency && $this->isCurrencyField($fieldName)) {
                $metrics = array_merge(
                    $metrics,
                    $this->getCurrencyCalculationMetrics($fieldName, $calculation, $records),
                );
                continue;
            }

            $values = [];

            foreach ($records as $record) {
                $values[] = $this->getCalculationFieldValue($fieldName, $record);
            }

            foreach ($this->getCalculationOperations() as $operation => $operationLabel) {
                if ('Yes' !== ($calculation[$operation] ?? '')) {
                    continue;
                }

                $calculatedValue = $this->$operation($values);
                $metricKey = $fieldName . ':' . $operation;
                $metrics[$metricKey] = [
                    'label' => sprintf('%s (%s)', $calculation['label'] ?? $fieldName, $operationLabel),
                    'field' => $fieldName,
                    'operation' => $operation,
                    'operation_label' => $operationLabel,
                    'value' => $calculatedValue,
                    'display' => 'count' === $operation
                        ? (string)(int)$calculatedValue
                        : $this->getFormattedCalculationValue($fieldName, $formatRecord, $calculatedValue),
                ];
            }
        }

        return $metrics;
    }

    protected function getCurrencyCalculationMetrics(string $fieldName, array $calculation, array $records): array
    {
        $buckets = [];
        $metrics = [];

        foreach ($records as $record) {
            $fieldRecord = $this->getFieldRecord($fieldName, $record);
            $value = $this->getCalculationFieldValue($fieldName, $record);

            if (null === $value) {
                continue;
            }

            $currency = $this->getRecordCurrency($fieldRecord);
            $currencyId = $currency['id'];
            $buckets[$currencyId]['currency'] = $currency;
            $buckets[$currencyId]['record'] = $fieldRecord;
            $buckets[$currencyId]['values'][] = $value;
        }

        foreach ($buckets as $currencyId => $bucket) {
            foreach ($this->getCalculationOperations() as $operation => $operationLabel) {
                if ('Yes' !== ($calculation[$operation] ?? '')) {
                    continue;
                }

                $calculatedValue = $this->$operation($bucket['values']);
                $currencyCode = $bucket['currency']['code'];
                $metricLabel = '' === $currencyCode
                    ? $operationLabel
                    : sprintf('%s, %s', $operationLabel, $currencyCode);
                $metricKey = implode(':', [$fieldName, $operation, $currencyId]);
                $metrics[$metricKey] = [
                    'label' => sprintf(
                        '%s (%s)',
                        $calculation['label'] ?? $fieldName,
                        $metricLabel,
                    ),
                    'field' => $fieldName,
                    'operation' => $operation,
                    'operation_label' => $operationLabel,
                    'currency_id' => $currencyId,
                    'value' => $calculatedValue,
                    'display' => 'count' === $operation
                        ? (string)(int)$calculatedValue
                        : $this->getFormattedCalculationValue(
                            $fieldName,
                            $bucket['record'],
                            $calculatedValue,
                            $bucket['currency'],
                        ),
                ];
            }
        }

        return $metrics;
    }

    /**
     * @throws Exception
     */
    protected function getTableRecordRow(object $record, ?array $fieldNames = null): array
    {
        $row = [];
        $fieldNames ??= $this->getTableColumns();

        foreach ($fieldNames as $fieldName) {
            $row[] = self::RECORD_COUNT_COLUMN === $fieldName
                ? ''
                : $this->getFieldValue($fieldName, $record);
        }

        return $row;
    }

    /**
     * @throws Exception
     */
    protected function getGroupedTableRecordRow(object $record): array
    {
        $row = [];

        foreach ($this->getGroupedTableColumns() as $fieldName) {
            $row[] = self::RECORD_COUNT_COLUMN === $fieldName
                ? ''
                : $this->getFieldValue($fieldName, $record);
        }

        return $row;
    }

    protected function getGroupedTableColumns(): array
    {
        $fieldNames = array_values(array_unique(array_merge(
            $this->getTableColumns(),
            array_keys($this->tableCalculations),
        )));

        if (empty($fieldNames) && !empty($this->getGroupBy())) {
            $fieldNames[] = self::GROUP_SUMMARY_COLUMN;
        }

        if ($this->recordCount) {
            $fieldNames[] = self::RECORD_COUNT_COLUMN;
        }

        return array_values(array_unique($fieldNames));
    }

    protected function hasGroupedDetailColumns(): bool
    {
        return !empty($this->getTableColumns()) || !empty($this->tableCalculations);
    }

    protected function getTableColumnsWithCalculations(): array
    {
        $fieldNames = array_values(array_unique(array_merge(
            $this->getTableColumns(),
            array_keys($this->tableCalculations),
        )));

        if ($this->recordCount) {
            $fieldNames[] = self::RECORD_COUNT_COLUMN;
        }

        return array_values(array_unique($fieldNames));
    }

    protected function getCalculationOperations(): array
    {
        return [
            'sum' => vtranslate('LBL_SUM', 'Reporting'),
            'avg' => vtranslate('LBL_AVG', 'Reporting'),
            'min' => vtranslate('LBL_MIN', 'Reporting'),
            'max' => vtranslate('LBL_MAX', 'Reporting'),
        ];
    }

    /**
     * @throws Exception
     */
    protected function getFormattedCalculationValue(
        string $fieldName,
        object $record,
        float $value,
        ?array $currency = null,
    ): string
    {
        if ($this->isCurrencyField($fieldName)) {
            return null === $currency
                ? $this->getFormattedReportingCurrencyValue($value)
                : $this->getFormattedCurrencyValue($value, $currency);
        }

        $fieldInfo = $this->getFieldInfo($fieldName);
        $formatRecord = clone $this->getFieldRecord($fieldName, $record);
        $formatFieldName = $fieldInfo['reference_field'] ?: $fieldInfo['field'];
        $formatRecord->set($formatFieldName, $value);
        $displayValue = $formatRecord->getReportDisplayValue($formatFieldName);

        if (Reporting_Fields_Model::isCustomField($formatFieldName)) {
            return (string)Reporting_Fields_Model::getCustomFieldValue($formatRecord, $formatFieldName, $displayValue);
        }

        return (string)$displayValue;
    }

    public function setFieldValue($fieldName, $formatRecord, $value): void
    {
        $formatRecord->set($fieldName, $value);
    }

    /**
     * @param array $numbers
     *
     * @return float
     */
    public function avg(array $numbers): float
    {
        $numbers = $this->getNumericValues($numbers);

        if (empty($numbers)) {
            return 0.0;
        }

        return array_sum($numbers) / count($numbers);
    }

    /**
     * @param array $numbers
     *
     * @return float
     */
    public function min(array $numbers): float
    {
        $numbers = $this->getNumericValues($numbers);

        return empty($numbers) ? 0.0 : min($numbers);
    }

    /**
     * @param array $numbers
     *
     * @return float
     */
    public function max(array $numbers): float
    {
        $numbers = $this->getNumericValues($numbers);

        return empty($numbers) ? 0.0 : max($numbers);
    }

    /**
     * @param array $numbers
     *
     * @return float
     */
    public function sum(array $numbers): float
    {
        return array_sum($this->getNumericValues($numbers));
    }

    protected function getCalculationFieldValue(string $fieldName, object $record): ?float
    {
        if ($this->isCurrencyField($fieldName)) {
            $fieldInfo = $this->getFieldInfo($fieldName);
            $record = $this->getFieldRecord($fieldName, $record);
            $recordFieldName = $fieldInfo['reference_field'] ?: $fieldInfo['field'];

            $value = $record->get($recordFieldName);

            return $this->groupByCurrency
                ? $this->getNumericValue($value)
                : $this->getConvertedCurrencyValue($record, $value);
        }

        $displayValue = $this->getNumericValue($this->getFieldValue($fieldName, $record));

        if (null !== $displayValue) {
            return $displayValue;
        }

        return $this->getNumericValue($this->getRawFieldValue($fieldName, $record));
    }

    protected function isCurrencyField(string $fieldName): bool
    {
        $field = $this->getField($fieldName);

        return $field && 'currency' === $field->getFieldDataType();
    }

    protected function getConvertedCurrencyValue(object $record, mixed $value): ?float
    {
        $value = $this->getNumericValue($value);

        if (null === $value) {
            return null;
        }

        $reportingCurrency = $this->getReportingCurrency();
        $recordCurrency = $this->getRecordCurrency($record);
        $recordCurrencyId = $recordCurrency['id'];

        if ($recordCurrencyId === $reportingCurrency['id']) {
            return $value;
        }

        $recordRate = $recordCurrency['rate'];

        return ($value / $recordRate) * $reportingCurrency['rate'];
    }

    protected function getRecordCurrencyId(object $record): int
    {
        return $this->getRecordCurrency($record)['id'];
    }

    protected function getRecordCurrencyRate(object $record, int $currencyId): float
    {
        return $this->getRecordCurrency($record)['rate'];
    }

    protected function getRecordCurrency(object $record): array
    {
        $record = $this->getCurrencyOwnerRecord($record);
        $recordId = method_exists($record, 'getId') ? (int)$record->getId() : 0;
        $recordKey = 0 < $recordId && method_exists($record, 'getModuleName')
            ? $record->getModuleName() . ':' . $recordId
            : (string)spl_object_id($record);

        if (isset($this->recordCurrencies[$recordKey])) {
            return $this->recordCurrencies[$recordKey];
        }

        $currencyId = $this->getNumericValue($record->get('currency_id'));
        $conversionRate = $this->getNumericValue($record->get('conversion_rate'));
        $storedCurrency = (empty($currencyId) || 0 >= $currencyId || null === $conversionRate || 0 >= $conversionRate)
            ? $this->fetchStoredCurrency($record)
            : [];

        if (empty($currencyId) || 0 >= $currencyId) {
            $currencyId = $this->getNumericValue($storedCurrency['currency_id'] ?? null);
        }
        if (null === $conversionRate || 0 >= $conversionRate) {
            $conversionRate = $this->getNumericValue($storedCurrency['conversion_rate'] ?? null);
        }
        if ((empty($currencyId) || 0 >= $currencyId) && method_exists($record, 'getCurrencyId')) {
            $currencyId = $record->getCurrencyId();
        }

        $currencyId = 0 < (int)$currencyId ? (int)$currencyId : 1;
        $currencyInfo = Vtiger_Functions::getCurrencyInfo($currencyId) ?: [];

        if (null === $conversionRate || 0 >= $conversionRate) {
            $conversionRate = $this->getNumericValue($currencyInfo['conversion_rate'] ?? null);
        }

        return $this->recordCurrencies[$recordKey] = [
            'id' => $currencyId,
            'rate' => null !== $conversionRate && 0 < $conversionRate ? $conversionRate : 1.0,
            'code' => (string)($currencyInfo['currency_code'] ?? ''),
            'symbol' => (string)($currencyInfo['currency_symbol'] ?? $currencyInfo['currency_code'] ?? ''),
            'user' => Users_Record_Model::getCurrentUserModel(),
        ];
    }

    protected function getCurrencyOwnerRecord(object $record): object
    {
        if (!method_exists($record, 'getModuleName') || 'InventoryItem' !== $record->getModuleName()) {
            return $record;
        }

        $parentId = (int)$record->get('parentid');

        if (empty($parentId) || !function_exists('getSalesEntityType')) {
            return $record;
        }

        $parentModule = getSalesEntityType($parentId);

        return empty($parentModule) ? $record : $this->getRecord($parentId, $parentModule);
    }

    protected function fetchStoredCurrency(object $record): array
    {
        if (
            !method_exists($record, 'getId')
            || !method_exists($record, 'getModuleName')
            || empty($record->getId())
            || !class_exists('CRMEntity')
            || !function_exists('columnExists')
        ) {
            return [];
        }

        $focus = CRMEntity::getInstance($record->getModuleName());
        $columns = [];

        foreach (['currency_id', 'conversion_rate'] as $column) {
            if (columnExists($column, $focus->table_name)) {
                $columns[] = $column;
            }
        }

        if (empty($columns)) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            sprintf(
                'SELECT %s FROM %s WHERE %s = ?',
                implode(', ', $columns),
                $focus->table_name,
                $focus->table_index,
            ),
            [$record->getId()],
        );

        return $db->num_rows($result) ? $db->query_result_rowdata($result, 0) : [];
    }

    protected function getReportingCurrency(): array
    {
        if (null !== $this->reportingCurrency) {
            return $this->reportingCurrency;
        }

        $user = Users_Record_Model::getCurrentUserModel();
        $currencyId = $this->reportingCurrencyId;

        if (empty($currencyId)) {
            $currencyId = method_exists($user, 'getCurrencyId')
                ? $user->getCurrencyId()
                : (int)$user->get('currency_id');
        }

        $currencyInfo = Vtiger_Functions::getCurrencyInfo($currencyId) ?: [];
        $conversionRate = $this->getNumericValue($currencyInfo['conversion_rate'] ?? null);

        return $this->reportingCurrency = [
            'id' => $currencyId,
            'rate' => null !== $conversionRate && 0 < $conversionRate ? $conversionRate : 1.0,
            'code' => (string)($currencyInfo['currency_code'] ?? ''),
            'symbol' => (string)($currencyInfo['currency_symbol'] ?? $currencyInfo['currency_code'] ?? ''),
            'user' => $user,
        ];
    }

    public function setReportingCurrencyId(int $currencyId): void
    {
        $this->reportingCurrencyId = 0 < $currencyId ? $currencyId : null;
        $this->reportingCurrency = null;
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    public function setGroupByCurrency(bool $groupByCurrency): void
    {
        $this->groupByCurrency = $groupByCurrency;
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    protected function getFormattedReportingCurrencyValue(float $value): string
    {
        $currency = $this->getReportingCurrency();

        return $this->getFormattedCurrencyValue($value, $currency);
    }

    protected function getFormattedCurrencyValue(float $value, array $currency): string
    {
        $displayValue = CurrencyField::convertToUserFormat($value, $currency['user'], true, false, true);
        $currencySymbol = html_entity_decode(
            (string)($currency['symbol'] ?? $currency['code'] ?? ''),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        if ('' === $currencySymbol) {
            return (string)$displayValue;
        }

        return sprintf(
            "%s\u{00A0}%s",
            $displayValue,
            htmlspecialchars($currencySymbol, ENT_QUOTES, 'UTF-8'),
        );
    }

    protected function getNumericValues(array $values): array
    {
        $numbers = [];

        foreach ($values as $value) {
            $number = $this->getNumericValue($value);

            if (null !== $number) {
                $numbers[] = $number;
            }
        }

        return $numbers;
    }

    protected function getNumericValue(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return is_finite((float)$value) ? (float)$value : null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim(html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ('' === $value) {
            return null;
        }

        $isNegative = preg_match('/^\s*\(.*\)\s*$/u', $value) === 1;
        $value = preg_replace('/[^0-9,.+\-]/u', '', $value);

        if ('' === $value) {
            return null;
        }

        $commaPosition = strrpos($value, ',');
        $dotPosition = strrpos($value, '.');

        if (false !== $commaPosition && false !== $dotPosition) {
            if ($commaPosition > $dotPosition) {
                $value = str_replace('.', '', $value);
                $value = $this->replaceLastSeparator($value, ',');
            } else {
                $value = str_replace(',', '', $value);
                $value = $this->replaceLastSeparator($value, '.');
            }
        } elseif (false !== $commaPosition) {
            $value = 1 < substr_count($value, ',') && $this->hasThousandsSeparatorFormat($value, ',')
                ? str_replace(',', '', $value)
                : $this->replaceLastSeparator($value, ',');
        } elseif (false !== $dotPosition && 1 < substr_count($value, '.')) {
            $value = $this->hasThousandsSeparatorFormat($value, '.')
                ? str_replace('.', '', $value)
                : $this->replaceLastSeparator($value, '.');
        }

        if (!preg_match('/^[+\-]?(?:\d+(?:\.\d*)?|\.\d+)$/', $value)) {
            return null;
        }

        $number = (float)$value;

        if ($isNegative && 0 < $number) {
            $number *= -1;
        }

        return is_finite($number) ? $number : null;
    }

    protected function hasThousandsSeparatorFormat(string $value, string $separator): bool
    {
        $pattern = '/^[+\-]?\d{1,3}(?:' . preg_quote($separator, '/') . '\d{3}){2,}$/';

        return preg_match($pattern, $value) === 1;
    }

    protected function replaceLastSeparator(string $value, string $separator): string
    {
        $position = strrpos($value, $separator);

        if (false === $position) {
            return $value;
        }

        $integerPart = str_replace($separator, '', substr($value, 0, $position));

        return $integerPart . '.' . substr($value, $position + 1);
    }

    public function setTableCalculations(array $value): void
    {
        $calculations = [];
        $this->recordCount = false;

        foreach ($value as $fieldName => $calculation) {
            $fieldName = (string)($calculation['name'] ?? $fieldName);

            if ('' === $fieldName) {
                continue;
            }

            if ('Yes' === ($calculation['count'] ?? '')) {
                $this->recordCount = true;
            }

            unset($calculation['count']);

            if (self::RECORD_COUNT_COLUMN === $fieldName) {
                continue;
            }

            $calculation['name'] = $fieldName;
            $calculations[$fieldName] = $calculation;
        }

        $this->tableCalculations = $calculations;
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    public function setTableAlignments(array $tableAlignments): void
    {
        $this->tableAlignments = $tableAlignments;
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    public function setGroupBy(array|string $value): void
    {
        $value = is_array($value) ? $value : [$value];
        $this->groupBy = array_values(array_unique(array_filter($value)));
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    public function setGroupByIntervals(array $value): void
    {
        $this->groupByIntervals = array_filter(
            $value,
            static fn(string $interval): bool => in_array(
                $interval,
                Reporting_Grouping_Model::DATE_INTERVALS,
                true
            )
        );
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    public function setGroupBySortDirections(array $value): void
    {
        $this->groupBySortDirections = array_map(
            static fn(mixed $direction): string => 'DESC' === strtoupper((string)$direction) ? 'DESC' : 'ASC',
            $value,
        );
        $this->groupedData = null;
    }

    protected function getDateGroupingValue(
        string $fieldName,
        mixed $rawValue,
        mixed $displayValue,
        string $interval
    ): array {
        if ('' === $interval || null === $rawValue || '' === trim((string)$rawValue)) {
            return [$rawValue, $displayValue];
        }

        $field = $this->getField($fieldName);

        if (!$field || !in_array($field->getFieldDataType(), ['date', 'datetime'], true)) {
            return [$rawValue, $displayValue];
        }

        try {
            $date = 'datetime' === $field->getFieldDataType()
                ? DateTimeField::convertToUserTimeZone((string)$rawValue)
                : new DateTime((string)$rawValue);
        } catch (Throwable) {
            return [$rawValue, $displayValue];
        }

        $currentUser = Users_Record_Model::getCurrentUserModel();
        [$startDate, $endDate] = Core_DateFilter_Helper::getGroupingPeriod(
            $date,
            $interval,
            $currentUser->get('dayoftheweek')
        );
        $groupValue = $interval . ':' . $startDate;

        if ('day' === $interval) {
            $groupLabel = DateTimeField::convertToUserFormat($startDate);
        } elseif ('month' === $interval) {
            $groupLabel = sprintf(
                '%s %s',
                $this->getTranslatedMonthName((int)substr($startDate, 5, 2)),
                substr($startDate, 0, 4),
            );
        } elseif ('year' === $interval) {
            $groupLabel = substr($startDate, 0, 4);
        } else {
            $groupLabel = DateTimeField::convertToUserFormat($startDate)
                . ' – '
                . DateTimeField::convertToUserFormat($endDate);
        }

        return [$groupValue, $groupLabel];
    }

    protected function getDateGroupingSortValue(mixed $rawValue, string $interval): ?string
    {
        $prefix = $interval . ':';

        if (!is_string($rawValue) || !str_starts_with($rawValue, $prefix)) {
            return null;
        }

        $startDate = substr($rawValue, strlen($prefix));

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ? $startDate : null;
    }

    protected function getTranslatedMonthName(int $month): string
    {
        $monthLabels = [
            1 => 'LBL_MONTH_NAME_JANUARY',
            2 => 'LBL_MONTH_NAME_FEBRUARY',
            3 => 'LBL_MONTH_NAME_MARCH',
            4 => 'LBL_MONTH_NAME_APRIL',
            5 => 'LBL_MONTH_NAME_MAY',
            6 => 'LBL_MONTH_NAME_JUNE',
            7 => 'LBL_MONTH_NAME_JULY',
            8 => 'LBL_MONTH_NAME_AUGUST',
            9 => 'LBL_MONTH_NAME_SEPTEMBER',
            10 => 'LBL_MONTH_NAME_OCTOBER',
            11 => 'LBL_MONTH_NAME_NOVEMBER',
            12 => 'LBL_MONTH_NAME_DECEMBER',
        ];

        return vtranslate($monthLabels[$month] ?? '', 'Core');
    }

    public function getRawFieldValue($value, $record)
    {
        $fieldInfo = $this->getFieldInfo($value);
        $record = $this->getFieldRecord($value, $record);
        $fieldName = $fieldInfo['reference_field'] ?: $fieldInfo['field'];
        $fieldValue = $record->get($fieldName);

        if (Reporting_Fields_Model::isCustomField($fieldName)) {
            return Reporting_Fields_Model::getCustomFieldValue($record, $fieldName, $fieldValue);
        }

        return $fieldValue;
    }

    public function getFieldRecord($value, $record)
    {
        $fieldInfo = $this->getFieldInfo($value);
        $field = $fieldInfo['field'];
        $referenceField = $fieldInfo['reference_field'];

        if (!empty($referenceField) && !$record->isEmpty($field)) {
            $referenceId = $record->get($field);
            $referenceRecord = $this->getRecord($referenceId, $fieldInfo['reference_module']);

            if (!empty($referenceRecord) && $fieldInfo['reference_module'] === $referenceRecord->getModuleName()) {
                $record = $referenceRecord;
            }
        }

        return $record;
    }

    /**
     * @param string $value
     *
     * @return array
     */
    public function getFieldInfo(string $value): array
    {
        [$fieldName, $referenceModule, $referenceField] = array_pad(explode(':', $value), 3, null);

        return [
            'module'           => $this->moduleName,
            'field'            => $fieldName,
            'reference_module' => $referenceModule,
            'reference_field'  => $referenceField,
        ];
    }

    /**
     * @throws Exception
     */
    public function getFieldValue($field, $record)
    {
        $record = $this->getFieldRecord($field, $record);
        $fieldInfo = $this->getFieldInfo($field);
        $fieldName = $fieldInfo['reference_field'] ?: $fieldInfo['field'];

        if ($this->isCurrencyField($field)) {
            if ($this->groupByCurrency) {
                $currencyValue = $this->getNumericValue($record->get($fieldName));

                return null === $currencyValue
                    ? ''
                    : $this->getFormattedCurrencyValue($currencyValue, $this->getRecordCurrency($record));
            }

            $currencyValue = $this->getConvertedCurrencyValue($record, $record->get($fieldName));

            return null === $currencyValue ? '' : $this->getFormattedReportingCurrencyValue($currencyValue);
        }

        $fieldValue = $record->getReportDisplayValue($fieldName);

        if (Reporting_Fields_Model::isCustomField($fieldName)) {
            return Reporting_Fields_Model::getCustomFieldValue($record, $fieldName, $fieldValue);
        }

        return $fieldValue;
    }

    /**
     * @param string $value
     *
     * @return Vtiger_Field_Model|null
     * @throws Exception
     */
    public function getField(string $value): Vtiger_Field_Model|bool
    {
        if (!empty($this->fields[$value])) {
            return $this->fields[$value];
        }

        $moduleName = $this->moduleName;
        [$fieldName, $referenceModule, $referenceField] = array_pad(explode(':', $value), 3, null);

        if (!empty($referenceField)) {
            $moduleName = $referenceModule;
            $fieldName = $referenceField;
        }

        $module = $this->getModule($moduleName);

        if ($module) {
            $field = $module->getField($fieldName);

            if (!empty($referenceField)) {
                $field->set('label', sprintf('(%s) %s', vtranslate($moduleName, $moduleName), vtranslate($field->get('label'), $moduleName)));
            }

            $this->fields[$value] = $field;
        }

        return $this->fields[$value];
    }

    /**
     * @param string $value
     *
     * @return bool|Vtiger_Module_Model
     */
    public function getModule(string $value): Vtiger_Module_Model|bool
    {
        if (empty($this->modules[$value])) {
            $this->modules[$value] = Vtiger_Module_Model::getInstance($value);
        }

        return $this->modules[$value];
    }

    /**
     * @return array
     */
    public function getTableColumns(): array
    {
        return $this->tableColumns;
    }

    public function getTableLabels(): array
    {
        return $this->tableLabels;
    }

    /**
     * @return array
     */
    public function getTableRecords(): array
    {
        return $this->tableRecords;
    }

    /**
     * @param $value
     *
     * @return void
     */
    public function setTableRecords($value): void
    {
        $this->tableRecords = $value;
        $this->setRecords($value);
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    /**
     * @param array $tableColumns
     *
     * @return void
     */
    public function setTableColumns(array $tableColumns): void
    {
        $this->tableColumns = $tableColumns;
        $this->groupedData = null;
        $this->grandTotalRow = null;
        $this->tableTotalRow = null;
    }

    public function setTableLabels(array $tableLabels): void
    {
        $this->tableLabels = $tableLabels;
    }
}

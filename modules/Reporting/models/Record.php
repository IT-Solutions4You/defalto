<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Record_Model extends Vtiger_Record_Model
{
    public const CHART_NUMERIC_TYPES = Reporting_Chart_Model::NUMERIC_DATA_TYPES;

    public bool|Core_QueryGenerator_Model $query = false;
    public bool|Reporting_Table_Model $tableModel = false;

    /**
     * @throws Exception
     */
    public function getTableData(): array
    {
        if (empty($this->getPrimaryModule())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        return $this->getTableModel()->getTable();
    }

    public function getExportTableData(): array
    {
        if (empty($this->getPrimaryModule())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        return $this->getTableModel()->getExportTable();
    }

    public function getTableStyle(): array
    {
        $widths = $this->getWidth();
        $alignments = $this->getAlign();
        $fields = $this->getFields();
        $normalizedWidths = [];
        $style = [];

        $fields = array_values(array_unique(array_merge($fields, $this->getCalculationFields())));

        if (empty($fields) && $this->isSummaryReport() && !empty($this->getGroupByFields())) {
            $fields = [Reporting_Table_Model::GROUP_SUMMARY_COLUMN];
        }

        if ($this->hasRecordCountCalculation()) {
            $fields[] = Reporting_Table_Model::RECORD_COUNT_COLUMN;
            $fields = array_values(array_unique($fields));
        }

        foreach ($fields as $columnIndex => $field) {
            $normalizedWidths[$columnIndex] = $this->getTableColumnWidth($widths[$field] ?? '');
        }

        $hasConfiguredWidths = !empty(array_filter(
            $normalizedWidths,
            static fn(string $width): bool => '' !== $width && 'auto' !== $width
        ));
        $tableWidths = [];

        foreach ($fields as $columnIndex => $field) {
            $width = $normalizedWidths[$columnIndex];
            $webWidth = $hasConfiguredWidths && ('' === $width || 'auto' === $width) ? '100px' : $width;
            $align = $this->getTableColumnAlign($alignments[$field] ?? '');
            $widthStyle = $width ? 'width:' . $width . ';' : '';
            $alignStyle = $align ? 'text-align:' . $align . ';' : '';

            $style['col'][$columnIndex] = $widthStyle;
            $style['web_col'][$columnIndex] = $webWidth ? 'width:' . $webWidth . ';' : '';
            $style['min'][$columnIndex] = $webWidth ? 'min-width:' . $webWidth . ';' : '';
            $style['th'][$columnIndex] = $widthStyle . $alignStyle;
            $style['td'][$columnIndex] = $widthStyle . $alignStyle;

            if ($hasConfiguredWidths) {
                $tableWidths[] = $webWidth;
            }
        }

        if ($hasConfiguredWidths) {
            $tableWidth = 'calc(' . implode(' + ', $tableWidths) . ')';
            $style['table'] = 'table-layout:fixed;width:' . $tableWidth . ';min-width:' . $tableWidth . ';';
        }

        return $style;
    }

    protected function getTableColumnWidth(mixed $value): string
    {
        $value = trim(rtrim(trim((string)$value), ';'));

        if ('' === $value) {
            return '';
        }

        if (is_numeric($value)) {
            $number = (float)$value;

            return max(100, $number) . 'px';
        }

        if ('auto' === strtolower($value)) {
            return 'auto';
        }

        if (preg_match('/^(\d+(?:\.\d+)?)px$/i', $value, $matches)) {
            return max(100, (float)$matches[1]) . 'px';
        }

        return preg_match('/^\d+(?:\.\d+)?(?:px|%|em|rem|ch|vw|cm|mm|in|pt|pc)$/i', $value)
            ? $value
            : '';
    }

    protected function getTableColumnAlign(mixed $value): string
    {
        return match (strtolower(trim((string)$value))) {
            'left', 'start' => 'left',
            'center' => 'center',
            'right', 'end' => 'right',
            default => '',
        };
    }

    /**
     * @throws Exception
     */
    public function getTableCalculations(): array
    {
        if (empty($this->getPrimaryModule())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        return $this->getTableModel()->getTableCalculations();
    }

    /**
     * @throws Exception
     */
    public function getTableRowTypes(): array
    {
        if (empty($this->getPrimaryModule())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        return $this->getTableModel()->getTableRowTypes();
    }

    /**
     * @throws Exception
     */
    public function getGroupedTableData(): array
    {
        if (!$this->isSummaryReport() || empty($this->getGroupByFields()) || empty($this->getPrimaryModule())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        return $this->getTableModel()->getGroupedTable();
    }

    public function getGroupedExportTableData(): array
    {
        if (!$this->isSummaryReport() || empty($this->getGroupByFields()) || empty($this->getPrimaryModule())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        return $this->getTableModel()->getGroupedExportTable();
    }

    /**
     * @throws Exception
     */
    public function getGroupedTableRowTypes(): array
    {
        if (!$this->isSummaryReport() || empty($this->getGroupByFields()) || empty($this->getPrimaryModule())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        return $this->getTableModel()->getGroupedTableRowTypes();
    }

    /**
     * @throws Exception
     */
    public function getChartData(): array
    {
        if (!$this->isSummaryReport()) {
            return [];
        }

        $configuration = $this->getChartConfiguration();

        if ('' === $configuration['x']['field'] || empty($configuration['series'])) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        $chartTable = clone $this->getTableModel();
        $chartTable->setGroupBy($this->getGroupByFields());
        $chartTable->setGroupByIntervals($this->getGroupByIntervals());
        $chartTable->setTableCalculations($this->getChartCalculations($configuration['series']));
        $groupedData = $chartTable->getGroupedData();

        if (empty($groupedData)) {
            return [];
        }

        $labels = [];
        $datasets = [];

        foreach ($groupedData as $group) {
            $labels[] = html_entity_decode(strip_tags((string)$group['label']), ENT_QUOTES | ENT_HTML5);
        }

        $datasets = $this->getChartDatasets($groupedData, $configuration['series']);

        if (empty($datasets)) {
            return [];
        }

        $chartType = $this->getChartType();
        $options = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];

        if (in_array($chartType, ['bar', 'line'], true)) {
            $options['scales'] = [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => $this->getChartXAxisLabel(),
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => vtranslate('LBL_CHART_Y_AXIS', 'Reporting'),
                    ],
                ],
            ];
        }

        return [
            'type' => $chartType,
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => $options,
        ];
    }

    protected function getChartXAxisLabel(): string
    {
        $fieldLabels = Reporting_Fields_Model::getFieldLabels($this->getPrimaryModule());
        $fieldLabels = array_replace(
            $fieldLabels,
            array_intersect_key(array_filter($this->getLabels()), $fieldLabels),
        );
        $intervalLabels = [
            'day' => vtranslate('LBL_DATE_UNIT_DAY'),
            'week' => vtranslate('LBL_DATE_UNIT_WEEK'),
            'month' => vtranslate('LBL_DATE_UNIT_MONTH'),
            'quarter' => vtranslate('LBL_DATE_UNIT_QUARTER'),
            'year' => vtranslate('LBL_DATE_UNIT_YEAR'),
        ];
        $labels = [];

        foreach ($this->getGroupByConfigurations() as $configuration) {
            $label = $fieldLabels[$configuration['field']] ?? $configuration['field'];

            if ('' !== $configuration['interval']) {
                $label .= ' (' . ($intervalLabels[$configuration['interval']] ?? $configuration['interval']) . ')';
            }

            $labels[] = $label;
        }

        return implode(' / ', $labels);
    }

    protected function getChartCalculations(array $series): array
    {
        $fieldLabels = Reporting_Fields_Model::getFieldLabels($this->getPrimaryModule());
        $calculations = [];

        foreach ($series as $item) {
            $fieldName = $item['field'];
            $operation = $item['aggregation'];

            if ('' === $fieldName) {
                continue;
            }

            $calculations[$fieldName] ??= [
                'name' => $fieldName,
                'label' => $fieldLabels[$fieldName] ?? $fieldName,
                'sum' => '',
                'avg' => '',
                'min' => '',
                'max' => '',
            ];
            $calculations[$fieldName][$operation] = 'Yes';
        }

        return $calculations;
    }

    protected function getChartDatasets(array $groupedData, array $series): array
    {
        $datasets = [];

        foreach ($series as $item) {
            if ('count' === $item['aggregation'] && '' === $item['field']) {
                $datasets[] = [
                    'label' => vtranslate('LBL_CHART_COUNT_RECORDS', 'Reporting'),
                    'data' => array_column($groupedData, 'count'),
                ];
                continue;
            }

            $availableMetrics = [];

            foreach ($groupedData as $group) {
                foreach ($group['metrics'] as $metricKey => $metric) {
                    if ($item['field'] === ($metric['field'] ?? '') && $item['aggregation'] === ($metric['operation'] ?? '')) {
                        $availableMetrics[$metricKey] ??= $metric;
                    }
                }
            }

            foreach ($availableMetrics as $metricKey => $metric) {
                $values = [];

                foreach ($groupedData as $group) {
                    $values[] = $group['metrics'][$metricKey]['value'] ?? 0;
                }

                $datasets[] = [
                    'label' => html_entity_decode(strip_tags((string)$metric['label']), ENT_QUOTES | ENT_HTML5),
                    'data' => $values,
                ];
            }
        }

        return $datasets;
    }

    public function getTableModel(): Reporting_Table_Model|bool
    {
        return $this->tableModel;
    }

    public function retrieveTable(): void
    {
        if ($this->tableModel) {
            return;
        }

        $table = Reporting_Table_Model::getInstance($this->getPrimaryModule());
        $table->setTableRecords($this->query->getRecords());
        $table->setTableColumns($this->getFields());
        $table->setTableLabels($this->getLabels());
        $table->setTableCalculations($this->getCalculations());
        $table->setTableAlignments($this->getAlign());
        $table->setGroupBy($this->getGroupByFields());
        $table->setGroupByIntervals($this->getGroupByIntervals());
        $table->setGroupBySortDirections($this->getGroupBySortDirections());
        $table->setGroupByCurrency($this->isGroupByCurrencyEnabled());
        $table->setReportingCurrencyId($this->getReportingCurrencyId());

        $this->tableModel = $table;
    }

    public function retrieveQueryGenerator(): void
    {
        if ($this->query) {
            return;
        }

        $query = Core_QueryGenerator_Model::getInstance($this->getPrimaryModule());
        $query->setFields($this->getQueryFields());
        $query->setLimit(0);
        $query->setOrderByClauseRequired(true);
        $query->setOrderByColumns($this->getOrderByColumns());
        $query->parseAdvFilterList($this->getFormatedFilters());
        $this->query = $query;
    }

    public function getQueryFields(): array
    {
        $fields = array_merge(['id'], $this->getFields(), $this->getCalculationFields());

        if ($this->isSummaryReport()) {
            $fields = array_merge(
                $fields,
                $this->getGroupByFields(),
                Reporting_Chart_Model::getFields($this->getChartConfiguration())
            );
        }

        $queryFields = [];

        foreach (array_unique($fields) as $fieldName) {
            $fieldName = (string)$fieldName;
            [$fieldName, $referenceModule, $referenceField] = array_pad(explode(':', $fieldName), 3, null);

            if ($referenceModule && $referenceField) {
                $fieldName = sprintf('(%s ; (%s) %s)', $fieldName, $referenceModule, $referenceField);
            }

            $queryFields[] = $fieldName;
        }

        return array_values(array_unique(array_filter($queryFields)));
    }

    public function getMaxEntries(): int
    {
        global $list_max_entries_per_page;

        return (int)$this->get('max_entries') ?: (int)$list_max_entries_per_page;
    }

    public function getReportingCurrencyId(): int
    {
        $currencyId = (int)$this->get('currency_id');

        if (0 < $currencyId) {
            return $currencyId;
        }

        return Users_Record_Model::getCurrentUserModel()->getCurrencyId();
    }

    public function isGroupByCurrencyEnabled(): bool
    {
        return (bool)$this->get('group_by_currency');
    }

    public function getCalculations(): array
    {
        return $this->getArrayFromJson('calculation');
    }

    public function getCalculationFields(): array
    {
        $fields = [];

        foreach ($this->getCalculations() as $fieldName => $calculation) {
            $fieldName = (string)($calculation['name'] ?? $fieldName);

            if ('' !== $fieldName && Reporting_Table_Model::RECORD_COUNT_COLUMN !== $fieldName) {
                $fields[] = $fieldName;
            }
        }

        return array_values(array_unique($fields));
    }

    public function isSummaryReport(): bool
    {
        return 'summary' === $this->get('report_type');
    }

    public function getGroupBy(): string
    {
        return (string)$this->get('group_by');
    }

    public function getGroupByFields(): array
    {
        return array_column($this->getGroupByConfigurations(), 'field');
    }

    public function getGroupByIntervals(): array
    {
        $intervals = [];

        foreach ($this->getGroupByConfigurations() as $configuration) {
            if ('' !== $configuration['interval']) {
                $intervals[$configuration['field']] = $configuration['interval'];
            }
        }

        return $intervals;
    }

    public function getGroupByConfigurations(): array
    {
        if (empty($this->getPrimaryModule())) {
            return [];
        }

        $availableFields = array_keys(Reporting_Fields_Model::getFieldLabels($this->getPrimaryModule()));
        $configurations = [];

        foreach (Reporting_Grouping_Model::getConfigurations($this->getGroupBy()) as $configuration) {
            if (in_array($configuration['field'], $availableFields, true)) {
                $configurations[] = $configuration;
            }
        }

        return $configurations;
    }

    public function getChartConfiguration(): array
    {
        $storedValue = trim((string)$this->get('chart_config'));
        $configuration = Reporting_Chart_Model::getConfiguration($storedValue);
        $groupingConfigurations = [];

        foreach ($this->getGroupByConfigurations() as $groupingConfiguration) {
            $groupingConfigurations[$groupingConfiguration['field']] = $groupingConfiguration;
        }

        $configuration['x'] = empty($groupingConfigurations)
            ? ['field' => '', 'interval' => '']
            : reset($groupingConfigurations);

        $availableSeries = $this->getCalculationChartSeries();
        $selectedSeries = [];
        $series = [];

        foreach ($availableSeries as $item) {
            $series[$item['aggregation'] . ':' . $item['field']] = $item;
        }

        foreach ($configuration['series'] as $item) {
            $seriesKey = $item['aggregation'] . ':' . $item['field'];

            if (isset($series[$seriesKey])) {
                $selectedSeries[$seriesKey] = $series[$seriesKey];
            }
        }

        $configuration['series'] = !empty($selectedSeries)
            ? array_values($selectedSeries)
            : (empty($series) ? [] : [reset($series)]);

        if (empty($this->getPrimaryModule())) {
            return $configuration;
        }

        $fieldDataTypes = Reporting_Fields_Model::getFieldDataTypes($this->getPrimaryModule());
        $xField = $configuration['x']['field'];

        if (!isset($fieldDataTypes[$xField]) || !isset($groupingConfigurations[$xField])) {
            $configuration['x'] = ['field' => '', 'interval' => ''];
        } elseif (!in_array($fieldDataTypes[$xField], ['date', 'datetime'], true)) {
            $configuration['x']['interval'] = '';
        }

        $validSeries = [];

        foreach ($configuration['series'] as $item) {
            if ('count' === $item['aggregation'] && '' === $item['field']) {
                $validSeries['count:'] = ['field' => '', 'aggregation' => 'count'];
                continue;
            }

            if (in_array($fieldDataTypes[$item['field']] ?? '', self::CHART_NUMERIC_TYPES, true)) {
                $validSeries[$item['aggregation'] . ':' . $item['field']] = $item;
            }
        }

        $configuration['series'] = array_values($validSeries);

        return $configuration;
    }

    protected function getCalculationChartSeries(): array
    {
        $series = [];
        $recordCount = $this->hasRecordCountCalculation();

        foreach ($this->getCalculations() as $fieldName => $calculation) {
            $fieldName = (string)($calculation['name'] ?? $fieldName);

            if ('' === $fieldName) {
                continue;
            }

            if (Reporting_Table_Model::RECORD_COUNT_COLUMN === $fieldName) {
                continue;
            }

            foreach (['sum', 'avg', 'min', 'max'] as $operation) {
                if ('Yes' === ($calculation[$operation] ?? '')) {
                    $series[] = ['field' => $fieldName, 'aggregation' => $operation];
                }
            }
        }

        if ($recordCount) {
            array_unshift($series, ['field' => '', 'aggregation' => 'count']);
        }

        return $series;
    }

    public function hasRecordCountCalculation(): bool
    {
        foreach ($this->getCalculations() as $calculation) {
            if ('Yes' === ($calculation['count'] ?? '')) {
                return true;
            }
        }

        return false;
    }

    public function save(): void
    {
        $this->set('group_by', json_encode($this->getGroupByConfigurations()));
        $this->set('chart_config', json_encode($this->getChartConfiguration()));

        parent::save();
    }

    public function getChartType(): string
    {
        $chartType = (string)$this->get('chart_type');
        $allowedTypes = ['bar', 'line', 'pie', 'doughnut'];

        return in_array($chartType, $allowedTypes, true) ? $chartType : 'bar';
    }

    public function getChartPosition(): string
    {
        return 'below' === (string)$this->get('chart_position') ? 'below' : 'above';
    }

    /**
     * @return bool
     */
    public function hasCalculations(): bool
    {
        $calculations = $this->getCalculations();

        foreach ($calculations as $calculation) {
            foreach (['count', 'sum', 'avg', 'min', 'max'] as $operation) {
                if ('Yes' === ($calculation[$operation] ?? '')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getEditViewTabUrl(string $value): string
    {
        return parent::getEditViewUrl() . '&tab=' . $value;
    }

    /**
     * @throws Exception
     */
    public function getOrderByColumns(): array
    {
        $primaryModule = Vtiger_Module_Model::getInstance($this->getPrimaryModule());
        $data = [];

        foreach ($this->getSortConfigurations() as $sortConfiguration) {
            $orderBy = $sortConfiguration['field'];
            $sortOrder = $sortConfiguration['direction'];
            [$orderBy] = explode(':', $orderBy);

            $field = $primaryModule->getField($orderBy);

            if ($field) {
                $data[$field->get('table') . '.' . $field->get('column')] = $sortOrder;
            }
        }

        return $data;
    }

    public function getGroupBySortDirections(): array
    {
        $groupByFields = $this->getGroupByFields();
        $directions = [];

        foreach ($this->getSortConfigurations() as $sortConfiguration) {
            if (in_array($sortConfiguration['field'], $groupByFields, true)) {
                $directions[$sortConfiguration['field']] = $sortConfiguration['direction'];
            }
        }

        return $directions;
    }

    protected function getSortConfigurations(): array
    {
        $configurations = [];

        foreach ($this->getArrayFromJson('sort_by') as $sortField) {
            [$fieldName, $direction] = array_pad(preg_split('/\s+/', trim((string)$sortField), 2), 2, '');
            $direction = strtoupper($direction);

            if ('' === $fieldName || !in_array($direction, ['ASC', 'DESC'], true)) {
                continue;
            }

            $configurations[] = [
                'field' => $fieldName,
                'direction' => $direction,
            ];
        }

        return $configurations;
    }

    /**
     * @return array
     */
    public function getFormatedFilters(): array
    {
        $filters = $this->getFilter();

        if (empty($filters[2]['columns'])) {
            $filters[1]['condition'] = '';
        }

        return $filters;
    }

    public function getPrimaryModule(): string
    {
        return (string)$this->get('primary_module');
    }

    public function getFields(): array
    {
        return $this->getArrayFromJson('fields');
    }

    public function getLabels(): array
    {
        return $this->getArrayFromJson('labels');
    }

    public function getFilter(): array
    {
        return $this->getArrayFromJson('filter');
    }

    public function getWidth(): array
    {
        return $this->getArrayFromJson('width');
    }

    public function getAlign(): array
    {
        return $this->getArrayFromJson('align');
    }

    public function getArrayFromJson($field): array
    {
        return (array)json_decode(decode_html($this->get($field)), true);
    }
}

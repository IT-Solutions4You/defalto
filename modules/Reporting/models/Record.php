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

        if ($this->isSummaryReport()) {
            $fields = array_values(array_unique(array_merge(
                $fields,
                $this->getGroupByFields(),
                $this->getCalculationFields(),
            )));
        } else {
            $fields = array_values(array_unique(array_merge($fields, $this->getCalculationFields())));
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
        if (!$this->isSummaryReport() || empty($this->getGroupByFields())) {
            return [];
        }

        $this->retrieveQueryGenerator();
        $this->retrieveTable();

        $groupedData = $this->getTableModel()->getGroupedData();

        if (empty($groupedData)) {
            return [];
        }

        $labels = [];
        $datasets = [];

        foreach ($groupedData as $group) {
            $labels[] = html_entity_decode(strip_tags((string)$group['label']), ENT_QUOTES | ENT_HTML5);
        }

        $availableMetrics = [];

        foreach ($groupedData as $group) {
            foreach ($group['metrics'] as $metricKey => $metric) {
                $availableMetrics[$metricKey] ??= $metric;
            }
        }

        if (empty($availableMetrics)) {
            $datasets[] = [
                'label' => vtranslate('LBL_COUNT', 'Reporting'),
                'data' => array_column($groupedData, 'count'),
            ];
        } else {
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

        return [
            'type' => $this->getChartType(),
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
            ],
        ];
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
        $query->setLimit(0);
        $query->setOrderByClauseRequired(true);
        $query->setOrderByColumns($this->getOrderByColumns());
        $query->parseAdvFilterList($this->getFormatedFilters());
        $this->query = $query;
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

            if ('' !== $fieldName) {
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
        $groupBy = decode_html($this->getGroupBy());
        $groupByFields = json_decode($groupBy, true);

        if (!is_array($groupByFields)) {
            $groupByFields = empty($groupBy) ? [] : [$groupBy];
        }

        $selectedFields = $this->getFields();
        $groupByFields = array_values(array_unique(array_filter(array_map('strval', $groupByFields))));

        return array_values(array_intersect($groupByFields, $selectedFields));
    }

    public function save(): void
    {
        $this->set('group_by', json_encode($this->getGroupByFields()));

        parent::save();
    }

    public function getChartType(): string
    {
        $chartType = (string)$this->get('chart_type');
        $allowedTypes = ['bar', 'line', 'pie', 'doughnut'];

        return in_array($chartType, $allowedTypes, true) ? $chartType : 'bar';
    }

    /**
     * @return bool
     */
    public function hasCalculations(): bool
    {
        $calculations = $this->getCalculations();

        foreach ($calculations as $calculation) {
            if ('Yes' === $calculation['sum'] || 'Yes' === $calculation['avg'] || 'Yes' === $calculation['min'] || 'Yes' === $calculation['max']) {
                return true;
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
        $sortFields = json_decode(decode_html($this->get('sort_by')), true);
        $primaryModule = Vtiger_Module_Model::getInstance($this->getPrimaryModule());
        $data = [];

        foreach ($sortFields as $sortField) {
            [$orderBy, $sortOrder] = explode(' ', $sortField);
            [$orderBy] = explode(':', $orderBy);

            if (empty($orderBy) || empty($sortOrder)) {
                continue;
            }

            $field = $primaryModule->getField($orderBy);

            if ($field) {
                $data[$field->get('table') . '.' . $field->get('column')] = $sortOrder;
            }
        }

        return $data;
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

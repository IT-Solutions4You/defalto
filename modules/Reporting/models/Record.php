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
    public bool|Reporting_Table_Model $table = false;

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

        return $this->table->getTable();
    }

    public function getTableStyle(): array
    {
        $width = $this->getWidth();
        $align = $this->getAlign();
        $row = 0;
        $style = [];

        foreach ($this->getFields() as $field) {
            $style['th'][$row] = 'width:' . $width[$field] . '; text-align:' . $align[$field] . ';';
            $style['td'][$row] = 'text-align:' . $align[$field] . ';';
            $row++;
        }

        return $style;
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

        return $this->table->getTableCalculations();
    }

    public function retrieveTable(): void
    {
        if ($this->table) {
            return;
        }

        $table = Reporting_Table_Model::getInstance($this->getPrimaryModule());
        $table->setTableRecords($this->query->getRecords());
        $table->setTableColumns($this->getFields());
        $table->setTableLabels($this->getLabels());
        $table->setTableCalculations($this->getCalculations());

        $this->table = $table;
    }

    public function retrieveQueryGenerator(): void
    {
        if ($this->query) {
            return;
        }

        $query = Core_QueryGenerator_Model::getInstance($this->getPrimaryModule());
        $query->setLimit($this->getMaxEntries());
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

    public function getCalculations(): array
    {
        return $this->getArrayFromJson('calculation');
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
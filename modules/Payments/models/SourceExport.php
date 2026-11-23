<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Export of the records of one payment module for the Payments export. It reuses the core export of the module
 * (field choice, permissions, value formatting) for given record ids and returns the values instead of a file.
 * Payments_ExportData_Action joins the results of all payment modules.
 */
class Payments_SourceExport_Model extends Vtiger_ExportData_Action
{
    /**
     * Key of the record id in a captured row.
     */
    public const RECORD_ID = 'payments_record_id';

    /** @var int[] */
    protected array $recordIds = [];
    protected string $recordIdColumn = '';
    protected array $exportedFields = [];
    protected array $capturedHeaders = [];
    protected array $capturedEntries = [];

    /**
     * Returns the exported columns and rows of records of one payment module.
     *
     * @param Vtiger_Request $request    Export request of Payments
     * @param string         $moduleName Payment module name
     * @param int[]          $recordIds  Record ids of the module
     * @return array{headers: array<string, string>, rows: array<int, array<string, mixed>>} headers [field name =>
     *               translated label], rows [record id => [field name => value]]
     */
    public function getSourceData(Vtiger_Request $request, string $moduleName, array $recordIds): array
    {
        $sourceRequest = clone $request;
        $sourceRequest->set('source_module', $moduleName);
        $this->recordIds = array_values(array_filter(array_map('intval', $recordIds)));
        $this->moduleCall = true;

        if (!$this->recordIds) {
            return ['headers' => [], 'rows' => []];
        }

        $this->ExportData($sourceRequest);
        $headers = array_combine($this->exportedFields, $this->capturedHeaders) ?: [];
        $columnFields = [];

        foreach ($this->moduleFieldInstances as $fieldName => $fieldModel) {
            $columnFields[(string)$fieldModel->get('column')] = $fieldName;
        }

        $rows = [];

        foreach ($this->capturedEntries as $entry) {
            $values = [];

            foreach ($entry['values'] as $column => $value) {
                $values[$columnFields[$column] ?? $column] = $value;
            }

            $rows[$entry[self::RECORD_ID]] = $values;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Returns the export query of the module limited to the record ids, with the record id as an extra column.
     *
     * @param Vtiger_Request $request Export request with source_module
     * @return string
     */
    public function getExportQuery(Vtiger_Request $request)
    {
        $moduleName = (string)$request->get('source_module');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $queryGenerator = new Core_QueryGenerator_Model($moduleName, Users_Record_Model::getCurrentUserModel());
        $fields = [];

        foreach ($this->moduleFieldInstances as $field) {
            if (in_array($field->get('presence'), [0, 2]) && '6' !== (string)$field->get('displaytype')) {
                $fields[] = $field->getName();
            }
        }

        $queryGenerator->setFields(array_merge($fields, ['id']));
        $query = $queryGenerator->getQuery();
        $this->exportedFields = array_values(array_diff($queryGenerator->getFields(), ['id']));
        $this->accessibleFields = $this->exportedFields;
        $this->recordIdColumn = (string)$moduleModel->get('basetableid');

        return $query . ' AND ' . $moduleModel->get('basetable') . '.' . $this->recordIdColumn . ' IN (' . implode(',', $this->recordIds) . ')';
    }

    /**
     * Formats the values of a row like the core export and keeps the record id beside them.
     *
     * @param array $arr Row of the export query
     * @return array{payments_record_id: int, values: array}
     */
    public function sanitizeValues($arr)
    {
        return [
            self::RECORD_ID => (int)($arr[$this->recordIdColumn] ?? 0),
            'values' => parent::sanitizeValues($arr),
        ];
    }

    /**
     * Keeps the headers and rows instead of sending a file.
     *
     * @param Vtiger_Request $request Export request
     * @param array          $headers Translated headers
     * @param array          $entries Rows
     * @return void
     */
    public function output($request, $headers, $entries)
    {
        $this->capturedHeaders = $headers;
        $this->capturedEntries = $entries;
    }
}

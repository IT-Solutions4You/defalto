<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporting_Table_Model extends Vtiger_Base_Model
{
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

    /**
     * @return array
     */
    public function getFieldNames(): array
    {
        return $this->fieldNames;
    }

    /**
     * @param array $value
     * @return void
     */
    public function setFieldNames(array $value): void
    {
        $this->fieldNames = $value;
    }

    /**
     * @param $moduleName
     * @return self
     */
    public static function getInstance($moduleName): self
    {
        $instance = new self();
        $instance->moduleName = $moduleName;

        return $instance;
    }

    /**
     * @param int $recordId
     * @param string $moduleName
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
        $fieldNames = $this->getTableColumns();
        $table = [];
        $tr = [];

        foreach ($fieldNames as $fieldName) {
            $tr[] = $labels[$fieldName];
        }

        $table[] = $tr;

        foreach ($this->getTableRecords() as $tableRecord) {
            $tr = [];

            foreach ($fieldNames as $fieldName) {
                $tr[] = $this->getFieldValue($fieldName, $tableRecord);
            }

            $table[] = $tr;
        }

        return $table;
    }

    /**
     * @throws AppException
     */
    public function getTableCalculations(): array
    {
        if (empty($this->tableCalculations)) {
            return [];
        }

        $fieldNames = $this->getTableColumns();
        $formatRecord = clone array_values($this->getTableRecords())[0];
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
                $data[$fieldName][] = $this->getRawFieldValue($fieldName, $tableRecord);
            }
        }

        foreach ($this->tableCalculations as $fieldName => $value) {
            $values = $data[$fieldName];
            $row = [
                $value['label'],
            ];

            $this->setFieldValue($fieldName, $formatRecord, $this->sum($values));
            $row[] = 'Yes' === $value['sum'] ? $this->getFieldValue($fieldName, $formatRecord) : '-';

            $this->setFieldValue($fieldName, $formatRecord, $this->avg($values));
            $row[] = 'Yes' === $value['avg'] ? $this->getFieldValue($fieldName, $formatRecord) : '-';

            $this->setFieldValue($fieldName, $formatRecord, $this->min($values));
            $row[] = 'Yes' === $value['min'] ? $this->getFieldValue($fieldName, $formatRecord) : '-';

            $this->setFieldValue($fieldName, $formatRecord, $this->max($values));
            $row[] = 'Yes' === $value['max'] ? $this->getFieldValue($fieldName, $formatRecord) : '-';

            $table[] = $row;
        }

        return $table;
    }

    public function setFieldValue($fieldName, $formatRecord, $value): void
    {
        $formatRecord->set($fieldName, $value);
    }

    /**
     * @param array $numbers
     * @return float
     */
    public function avg(array $numbers): float
    {
        if (empty($numbers)) {
            return 0.0;
        }

        $numbers = array_map('floatval', $numbers);

        return array_sum($numbers) / count($numbers);
    }

    /**
     * @param array $numbers
     * @return float
     */
    public function min(array $numbers): float
    {
        $numbers = array_map('floatval', $numbers);

        return min($numbers);
    }

    /**
     * @param array $numbers
     * @return float
     */
    public function max(array $numbers): float
    {
        $numbers = array_map('floatval', $numbers);

        return max($numbers);
    }


    /**
     * @param array $numbers
     * @return float
     */
    public function sum(array $numbers): float
    {
        $numbers = array_map('floatval', $numbers);

        return array_sum($numbers);
    }

    public function setTableCalculations(array $value): void
    {
        $this->tableCalculations = $value;
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
     * @return array
     */
    public function getFieldInfo(string $value): array
    {
        [$fieldName, $referenceModule, $referenceField] = explode(':', $value);

        return [
            'module' => $this->moduleName,
            'field' => $fieldName,
            'reference_module' => $referenceModule,
            'reference_field' => $referenceField,
        ];
    }

    /**
     * @throws AppException
     */
    public function getFieldValue($field, $record)
    {
        $record = $this->getFieldRecord($field, $record);
        $fieldInfo = $this->getFieldInfo($field);
        $fieldName = $fieldInfo['reference_field'] ?: $fieldInfo['field'];
        $fieldValue = $record->getReportDisplayValue($fieldName);

        if (Reporting_Fields_Model::isCustomField($fieldName)) {
            return Reporting_Fields_Model::getCustomFieldValue($record, $fieldName, $fieldValue);
        }

        return $fieldValue;
    }

    /**
     * @param string $value
     * @return Vtiger_Field_Model|null
     * @throws Exception
     */
    public function getField(string $value): Vtiger_Field_Model|bool
    {
        if (!empty($this->fields[$value])) {
            return $this->fields[$value];
        }

        $moduleName = $this->moduleName;
        [$fieldName, $referenceModule, $referenceField] = explode(':', $value);

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
     * @return void
     */
    public function setTableRecords($value): void
    {
        $this->tableRecords = $value;
        $this->setRecords($value);
    }

    /**
     * @param array $tableColumns
     * @return void
     */
    public function setTableColumns(array $tableColumns): void
    {
        $this->tableColumns = $tableColumns;
    }

    public function setTableLabels(array $tableLabels): void
    {
        $this->tableLabels = $tableLabels;
    }
}
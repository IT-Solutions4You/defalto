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
 * Uitype 34: a reference to several records of the modules set on the field (vtiger_fieldmodulerel).
 * The column holds the record ids separated by a semicolon, for example 12;15.
 */
class Core_Multireference_UIType extends Vtiger_Reference_UIType
{
    public const SEPARATOR = ';';

    /**
     * Returns the unique positive record ids of a stored or submitted value.
     *
     * @param mixed $value Stored value (12;15), comma separated value or array of ids
     * @return int[]
     */
    public static function getRecordIds(mixed $value): array
    {
        if (is_array($value)) {
            $value = implode(self::SEPARATOR, $value);
        }

        $recordIds = [];

        foreach (preg_split('/[;,]/', (string)$value) as $recordId) {
            $recordId = (int)trim($recordId);

            if ($recordId > 0) {
                $recordIds[$recordId] = $recordId;
            }
        }

        return array_values($recordIds);
    }

    /**
     * Converts a list of record ids into the stored value.
     *
     * @param int[] $recordIds Record ids
     * @return string
     */
    public static function convertToValue(array $recordIds): string
    {
        return implode(self::SEPARATOR, $recordIds);
    }

    /**
     * Returns the ids of the value that belong to one of the modules of the field, grouped by module.
     *
     * @param mixed $value Stored or submitted value
     * @return array [module name => [record id, ...]]
     * @throws Exception
     */
    public function getRecordIdsByModule(mixed $value): array
    {
        $referenceModules = $this->get('field')->getReferenceList(false);
        $recordIdsByModule = [];

        foreach (self::getRecordIds($value) as $recordId) {
            $moduleName = (string)getSalesEntityType($recordId);

            if (in_array($moduleName, $referenceModules, true)) {
                $recordIdsByModule[$moduleName][] = $recordId;
            }
        }

        return $recordIdsByModule;
    }

    /**
     * Returns the records of the value with their names, in the stored order.
     *
     * @param mixed $value Stored or submitted value
     * @return array [record id => ['module' => module name, 'name' => record name]]
     * @throws Exception
     */
    public function getReferenceRecords(mixed $value): array
    {
        $recordsByModule = $this->getRecordIdsByModule($value);
        $names = [];

        foreach ($recordsByModule as $moduleName => $recordIds) {
            foreach ((array)getEntityName($moduleName, $recordIds) as $recordId => $recordName) {
                $names[(int)$recordId] = ['module' => $moduleName, 'name' => decode_html((string)$recordName)];
            }
        }

        $records = [];

        foreach (self::getRecordIds($value) as $recordId) {
            if (isset($names[$recordId])) {
                $records[$recordId] = $names[$recordId];
            }
        }

        return $records;
    }

    /**
     * @inheritDoc
     */
    public function getRequestValue(mixed $fieldValue): mixed
    {
        if (null === $fieldValue) {
            return null;
        }

        return parent::getRequestValue($this->getDBInsertValue($fieldValue));
    }

    /**
     * Keeps only the ids of records of the field's modules, in the submitted order.
     *
     * @param mixed $value Submitted value (12;15, 12,15 or an array of ids)
     * @return string
     * @throws Exception
     */
    public function getDBInsertValue($value)
    {
        return self::convertToValue(array_keys($this->getReferenceRecords($value)));
    }

    /**
     * Returns the module of the first record of the value, used to preselect the module in the edit view.
     *
     * @param mixed $value Stored value
     * @return Vtiger_Module_Model|null
     * @throws Exception
     */
    public function getReferenceModule($value)
    {
        $recordIds = self::getRecordIds($value);

        return $recordIds ? parent::getReferenceModule($recordIds[0]) : null;
    }

    /**
     * Returns links to the records of the value.
     *
     * @inheritDoc
     * @throws Exception
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        $displayValues = [];

        foreach ($this->getReferenceRecords($value) as $recordId => $referenceRecord) {
            $displayValues[] = self::transformToDisplayValue($recordId, $referenceRecord['module']);
        }

        return implode(', ', $displayValues);
    }

    /**
     * Returns the names of the records of the value.
     *
     * @param mixed $value Stored value
     * @return string
     * @throws Exception
     */
    public function getEditViewDisplayValue($value): string
    {
        return implode(', ', array_column($this->getReferenceRecords($value), 'name'));
    }

    /**
     * Returns how many records the field accepts in the edit form, 0 for no limit. Modules override it in
     * <Module>_Multireference_UIType and must enforce the same limit on save.
     *
     * @return int
     */
    public function getMaximumRecords(): int
    {
        return 0;
    }

    /**
     * Returns the message shown when more records are chosen in the popup than getMaximumRecords() allows.
     *
     * @return string
     */
    public function getMaximumRecordsMessage(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTemplateName()
    {
        return 'uitypes/Multireference.tpl';
    }

    /**
     * @inheritDoc
     */
    public function getListSearchTemplateName()
    {
        return 'uitypes/FieldSearchView.tpl';
    }
}

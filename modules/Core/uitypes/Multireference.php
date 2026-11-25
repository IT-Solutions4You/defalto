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
    public const EXPORT_SEPARATOR = ' |##| ';
    public const EXPORT_MODULE_SEPARATOR = '::::';

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
     * Returns the value written to an export file: each record as Module::::record number (the name when the module
     * has no record number), joined like multi select picklist values. getImportValue() reads it back.
     *
     * @param mixed $value Stored value (12;15)
     * @return string For example Invoice::::INV17 |##| Invoice::::INV18
     * @throws Exception
     */
    public function getExportValue(mixed $value): string
    {
        $records = $this->getReferenceRecords($value);
        $numbers = [];

        foreach (array_unique(array_column($records, 'module')) as $moduleName) {
            $searchModel = Core_MultireferenceSearch_Model::getInstance($moduleName);
            $numbers += $searchModel ? $searchModel->fetchRecordNumbers(array_keys($records)) : [];
        }

        $exportValues = [];

        foreach ($records as $recordId => $referenceRecord) {
            $label = '' !== (string)($numbers[$recordId] ?? '') ? decode_html($numbers[$recordId]) : $referenceRecord['name'];
            $exportValues[] = $referenceRecord['module'] . self::EXPORT_MODULE_SEPARATOR . $label;
        }

        return implode(self::EXPORT_SEPARATOR, $exportValues);
    }

    /**
     * Converts a value of an import file into the stored value. Accepts the export format (Module::::number or name,
     * joined by |##|), values without the module (number or name searched in every module of the field) and plain
     * record ids (12;15). A record is found by its number first, then by its name; missing records are skipped,
     * the import does not create documents.
     *
     * @param string $value Value from the import file
     * @return string Stored value (12;15)
     * @throws Exception
     */
    public function getImportValue(string $value): string
    {
        $value = trim($value);

        if ('' === $value) {
            return '';
        }

        if (1 === preg_match('/^\d+(\s*[;,]\s*\d+)*$/', $value)) {
            return $this->getDBInsertValue($value);
        }

        $referenceModules = $this->get('field')->getReferenceList(false);
        $recordIds = [];

        foreach (explode(trim(self::EXPORT_SEPARATOR), $value) as $part) {
            $part = trim($part);
            $modules = $referenceModules;

            if (str_contains($part, ':::')) {
                [$moduleName, $part] = array_map('trim', explode(':::', str_replace('::::', ':::', $part), 2));
                $modules = array_intersect([$moduleName], $referenceModules);
            }

            if ('' === $part) {
                continue;
            }

            foreach ($modules as $moduleName) {
                $searchModel = Core_MultireferenceSearch_Model::getInstance($moduleName);
                $recordId = $searchModel ? $searchModel->fetchRecordIdByNumber($part) : 0;

                if (!$recordId) {
                    $recordId = (int)getEntityId($moduleName, decode_html($part));
                }

                if ($recordId) {
                    $recordIds[] = $recordId;
                    break;
                }
            }
        }

        return $this->getDBInsertValue($recordIds);
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
     * Tells whether reaching the maximum number of records should open the buy window of the paid edition
     * (window.DefaltoPremiumModal of the Installer) instead of showing the message. The page must load the Installer
     * script PremiumModal.js; without it the message is shown.
     *
     * @return bool
     */
    public function isMaximumRecordsPremium(): bool
    {
        return false;
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

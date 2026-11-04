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
 * Searches the records offered by the edit form of a multi reference field (uitype 34): by the record name through
 * the module's own searchRecord() and by the record number (uitype 4 field) of the module.
 */
class Core_MultireferenceSearch_Model
{
    public const SEARCH_LIMIT = 100;

    protected Vtiger_Module_Model $moduleModel;

    /**
     * @param Vtiger_Module_Model $moduleModel Module whose records are searched
     */
    public function __construct(Vtiger_Module_Model $moduleModel)
    {
        $this->moduleModel = $moduleModel;
    }

    /**
     * Returns the search model for a module, or null when the module does not exist.
     *
     * @param string $moduleName Module whose records are searched
     * @return self|null
     */
    public static function getInstance(string $moduleName): ?self
    {
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        return $moduleModel ? new self($moduleModel) : null;
    }

    /**
     * Returns the records whose name or record number contains the search value, name matches first.
     * A parent record (parent_id/parent_module, e.g. the organization of an invoice for its contacts) limits the
     * search to the records related to it; such a search matches only names, as the parent filter of searchRecord().
     *
     * @param string $searchValue     Text typed by the user
     * @param int    $excludeRecordId Record being edited, left out of the result
     * @param int    $parentId        Parent record limiting the search, 0 for none
     * @param string $parentModule    Module of the parent record
     * @return array [['id' => int, 'label' => string, 'number' => string], ...]
     * @throws Exception
     */
    public function getRecords(string $searchValue, int $excludeRecordId = 0, int $parentId = 0, string $parentModule = ''): array
    {
        if ($parentId > 0 && '' !== $parentModule) {
            $labels = $this->getRecordLabelsByName($searchValue, $parentId, $parentModule);
        } else {
            $labels = $this->getRecordLabelsByName($searchValue) + $this->fetchRecordLabelsByNumber($searchValue);
        }

        unset($labels[$excludeRecordId]);
        $labels = array_slice($labels, 0, self::SEARCH_LIMIT, true);
        $numbers = $this->fetchRecordNumbers(array_keys($labels));
        $records = [];

        foreach ($labels as $recordId => $label) {
            $records[] = [
                'id' => $recordId,
                'label' => decode_html($label),
                'number' => decode_html((string)($numbers[$recordId] ?? '')),
            ];
        }

        return $records;
    }

    /**
     * Returns the records whose name contains the search value, through the module's own search (permissions and
     * module rules such as converted leads apply).
     *
     * @param string $searchValue  Text typed by the user
     * @param int    $parentId     Parent record limiting the search, 0 for none
     * @param string $parentModule Module of the parent record
     * @return array record id => label
     */
    protected function getRecordLabelsByName(string $searchValue, int $parentId = 0, string $parentModule = ''): array
    {
        $labels = [];
        $records = $parentId > 0
            ? $this->moduleModel->searchRecord($searchValue, $parentId, $parentModule)
            : $this->moduleModel->searchRecord($searchValue);

        foreach ($records as $recordModels) {
            foreach ($recordModels as $recordModel) {
                $labels[(int)$recordModel->getId()] = (string)$recordModel->getName();
            }
        }

        return $labels;
    }

    /**
     * Loads the records the user may view whose record number contains the search value. The permission is checked
     * per row, so the rows are read until the limit of permitted records is reached; converted leads are skipped
     * like in Vtiger_Record_Model::getSearchResult().
     *
     * @param string $searchValue Text typed by the user
     * @return array record id => label
     */
    protected function fetchRecordLabelsByNumber(string $searchValue): array
    {
        $numberColumn = $this->getNumberColumn();

        if (empty($numberColumn)) {
            return [];
        }

        [$tableName, $indexColumn, $column] = $numberColumn;
        $moduleName = $this->moduleModel->getName();
        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            sprintf(
                'SELECT vtiger_crmentity.crmid, vtiger_crmentity.label FROM vtiger_crmentity INNER JOIN %1$s ON %1$s.%2$s = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = 0 AND vtiger_crmentity.setype = ? AND %1$s.%3$s LIKE ?',
                $tableName,
                $indexColumn,
                $column,
            ),
            [$moduleName, '%' . $searchValue . '%'],
        );
        $labels = [];

        while (count($labels) < self::SEARCH_LIMIT && $row = $db->fetchByAssoc($result, -1, false)) {
            if (Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $row['crmid'])) {
                $labels[(int)$row['crmid']] = (string)$row['label'];
            }
        }

        if ('Leads' === $moduleName && !empty($labels)) {
            $labels = array_diff_key($labels, array_filter(Leads_Module_Model::getConvertedInfo(array_keys($labels))));
        }

        return $labels;
    }

    /**
     * Loads the record numbers of the given records in one query.
     *
     * @param array $recordIds Record ids
     * @return array record id => record number
     */
    protected function fetchRecordNumbers(array $recordIds): array
    {
        $numberColumn = $this->getNumberColumn();

        if (empty($numberColumn) || empty($recordIds)) {
            return [];
        }

        [$tableName, $indexColumn, $column] = $numberColumn;
        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            sprintf('SELECT %2$s AS id, %3$s AS number FROM %1$s WHERE %2$s IN (%4$s)', $tableName, $indexColumn, $column, generateQuestionMarks($recordIds)),
            $recordIds,
        );
        $numbers = [];

        while ($row = $db->fetchByAssoc($result, -1, false)) {
            $numbers[(int)$row['id']] = (string)$row['number'];
        }

        return $numbers;
    }

    /**
     * Returns the table, its record id column and the column of the module's record number field (uitype 4).
     *
     * @return array [table, index column, number column], empty when the module has no record number field or the
     *               user's profile does not show it
     */
    protected function getNumberColumn(): array
    {
        $entity = CRMEntity::getInstance($this->moduleModel->getName());

        foreach ($this->moduleModel->getFields() as $fieldModel) {
            if (Vtiger_Field_Model::UITYPE_RECORD_NO !== (int)$fieldModel->get('uitype') || !$fieldModel->isViewable()) {
                continue;
            }

            $tableName = (string)$fieldModel->get('table');
            $indexColumn = (string)($entity->tab_name_index[$tableName] ?? '');
            $column = (string)$fieldModel->get('column');

            if ($this->isSqlIdentifier($tableName) && $this->isSqlIdentifier($indexColumn) && $this->isSqlIdentifier($column)) {
                return [$tableName, $indexColumn, $column];
            }
        }

        return [];
    }

    /**
     * Tells whether a table or column name from the field metadata is safe to place into SQL.
     *
     * @param string $name Table or column name
     * @return bool
     */
    protected function isSqlIdentifier(string $name): bool
    {
        return 1 === preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name);
    }
}

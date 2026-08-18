<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_GlobalSearchField_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_global_search_field';
    protected string $tableId = 'global_search_field_id';
    protected static bool|null $tableAvailable = null;

    protected const SUPPORTED_DATA_TYPES = [
        'string',
        'text',
        'email',
        'phone',
        'url',
        'picklist',
        'multipicklist',
        'country',
        'salutation',
        'skype',
    ];

    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @return array<int>
     * @throws Exception
     */
    public function getSelectedFieldIds(int $tabId): array
    {
        $this->retrieveDB();

        if (!$this->isTableAvailable()) {
            return [];
        }

        $result = $this->db->pquery(
            'SELECT field_id FROM ' . $this->table . ' WHERE tab_id=? ORDER BY sequence, global_search_field_id',
            [$tabId]
        );
        $fieldIds = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            $fieldIds[] = (int)$row['field_id'];
        }

        return $fieldIds;
    }

    /**
     * @param array<int>|null $selectedFieldIds
     * @return array<Vtiger_Field_Model>
     * @throws Exception
     */
    public function getSelectedFields(Vtiger_Module_Model $moduleModel, array|null $selectedFieldIds = null): array
    {
        if ($selectedFieldIds === null) {
            $selectedFieldIds = $this->getSelectedFieldIds((int)$moduleModel->getId());
        }

        $selectedFieldIds = array_flip($selectedFieldIds);

        return array_values(array_filter(
            $this->getSupportedFields($moduleModel),
            static fn(Vtiger_Field_Model $fieldModel): bool => isset($selectedFieldIds[(int)$fieldModel->getId()])
        ));
    }

    /**
     * @return array<Vtiger_Field_Model>
     * @throws Exception
     */
    public function getSupportedFields(Vtiger_Module_Model $moduleModel): array
    {
        $fields = [];

        foreach ($moduleModel->getFields() as $fieldModel) {
            if (!$fieldModel instanceof Vtiger_Field_Model
                || !$fieldModel->isActiveField()
                || !$fieldModel->getPermissions()
                || !$this->isSupportedField($fieldModel)
            ) {
                continue;
            }

            $fields[] = $fieldModel;
        }

        return $fields;
    }

    /**
     * @param array<int|string> $fieldIds
     * @return array<int>
     * @throws Exception
     */
    public function getValidFieldIds(Vtiger_Module_Model $moduleModel, array $fieldIds): array
    {
        $requestedFieldIds = array_flip(array_map('intval', $fieldIds));
        $validFieldIds = [];

        foreach ($this->getSupportedFields($moduleModel) as $fieldModel) {
            $fieldId = (int)$fieldModel->getId();

            if (isset($requestedFieldIds[$fieldId])) {
                $validFieldIds[] = $fieldId;
            }
        }

        return $validFieldIds;
    }

    public function isSupportedField(Vtiger_Field_Model $fieldModel): bool
    {
        return in_array($fieldModel->getFieldDataType(), self::SUPPORTED_DATA_TYPES, true);
    }

    protected function isTableAvailable(): bool
    {
        if (self::$tableAvailable === null) {
            $this->retrieveDB();
            self::$tableAvailable = $this->db->tableExists($this->table);
        }

        return self::$tableAvailable;
    }

    /**
     * @param array<int> $fieldIds
     * @throws Exception
     */
    public function replaceForModule(int $tabId, array $fieldIds): void
    {
        $table = $this->getTable($this->table, $this->tableId);
        $table->deleteData(['tab_id' => $tabId]);

        foreach (array_values(array_unique(array_map('intval', $fieldIds))) as $sequence => $fieldId) {
            $table->insertData([
                'tab_id' => $tabId,
                'field_id' => $fieldId,
                'sequence' => $sequence + 1,
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTable($this->table, $this->tableId)
            ->createTable()
            ->createColumn('tab_id', 'int(19) NOT NULL')
            ->createColumn('field_id', 'int(19) NOT NULL')
            ->createColumn('sequence', 'int(19) NOT NULL DEFAULT 0')
            ->createKey('UNIQUE KEY IF NOT EXISTS `idx_df_global_search_field` (`tab_id`,`field_id`)')
            ->createKey('CONSTRAINT `fk_df_global_search_field_module` FOREIGN KEY IF NOT EXISTS (`tab_id`) REFERENCES `df_global_search_module` (`tab_id`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_df_global_search_field_field` FOREIGN KEY IF NOT EXISTS (`field_id`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE');
        self::$tableAvailable = true;
    }
}

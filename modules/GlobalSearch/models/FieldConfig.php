<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class GlobalSearch_FieldConfig_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_global_search_field';
    protected string $tableId = 'global_search_field_id';
    protected static array|null $selectionConfiguration = null;

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
        return $this->retrieveSelectionConfiguration()[$tabId] ?? [];
    }

    /**
     * @return array<int, array<int>>
     * @throws Exception
     */
    protected function retrieveSelectionConfiguration(): array
    {
        if (self::$selectionConfiguration !== null) {
            return self::$selectionConfiguration;
        }

        $this->retrieveDB();

        if (!$this->db->tableExists($this->table)) {
            return self::$selectionConfiguration = [];
        }

        $result = $this->db->pquery(
            'SELECT tab_id, field_id FROM ' . $this->table . ' ORDER BY tab_id, sequence, global_search_field_id'
        );
        self::$selectionConfiguration = [];

        while ($row = $this->db->fetchByAssoc($result)) {
            self::$selectionConfiguration[(int)$row['tab_id']][] = (int)$row['field_id'];
        }

        return self::$selectionConfiguration;
    }

    /**
     * @param array<int>|null $selectedFieldIds
     * @return array<Vtiger_Field_Model>
     * @throws Exception
     */
    public function getSelectedFields(Vtiger_Module_Model $moduleModel, array|null $selectedFieldIds = null): array
    {
        $selectedFieldIds ??= $this->getSelectedFieldIds((int)$moduleModel->getId());
        $selectedFieldMap = array_flip($selectedFieldIds);

        return array_values(array_filter(
            $this->getSupportedFields($moduleModel),
            static fn(Vtiger_Field_Model $fieldModel): bool => isset($selectedFieldMap[(int)$fieldModel->getId()])
        ));
    }

    /**
     * @return array<Vtiger_Field_Model>
     */
    public function getSupportedFields(Vtiger_Module_Model $moduleModel): array
    {
        return array_values(array_filter(
            $moduleModel->getFields(),
            fn($fieldModel): bool => $fieldModel instanceof Vtiger_Field_Model
                && $fieldModel->isActiveField()
                && $fieldModel->getPermissions()
                && $this->isSupportedField($fieldModel)
        ));
    }

    /**
     * @param array<int|string> $fieldIds
     * @return array<int>
     */
    public function getValidFieldIds(Vtiger_Module_Model $moduleModel, array $fieldIds): array
    {
        $requestedFieldMap = array_flip(array_map('intval', $fieldIds));
        $validFieldIds = [];

        foreach ($this->getSupportedFields($moduleModel) as $fieldModel) {
            $fieldId = (int)$fieldModel->getId();

            if (isset($requestedFieldMap[$fieldId])) {
                $validFieldIds[] = $fieldId;
            }
        }

        return $validFieldIds;
    }

    public function isSupportedField(Vtiger_Field_Model $fieldModel): bool
    {
        return in_array($fieldModel->getFieldDataType(), self::SUPPORTED_DATA_TYPES, true);
    }

    /**
     * @param array<int> $fieldIds
     * @throws Exception
     */
    public function saveModuleFields(int $tabId, array $fieldIds): void
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

        self::$selectionConfiguration = null;
    }
}

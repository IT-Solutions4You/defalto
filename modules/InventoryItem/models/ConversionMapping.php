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
 * Field mapping of a conversion between two documents. A row maps one field of the target document to a field of the source document.
 * An empty source field means that the target field is not copied. Target fields without a row keep the standard mapping of Core_Base_Mapping.
 */
class InventoryItem_ConversionMapping_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_inventoryitem_conversion_mappings';
    protected string $tableId = 'mapping_id';

    /**
     * @return self
     */
    public function getMappingTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getMappingTable()
            ->createTable()
            ->createColumn('conversion_id', 'int(19) NOT NULL')
            ->createColumn('target_field', 'varchar(100) NOT NULL')
            ->createColumn('source_field', 'varchar(100) NOT NULL DEFAULT \'\'')
            ->createKey('UNIQUE INDEX IF NOT EXISTS `conversion_mapping_target_idx` (`conversion_id`, `target_field`)')
            ->createKey('CONSTRAINT `fk_1_df_inventoryitem_conversion_mappings` FOREIGN KEY IF NOT EXISTS (`conversion_id`) REFERENCES `df_inventoryitem_conversions` (`conversion_id`) ON DELETE CASCADE');
    }

    /**
     * @param string $sourceModule
     * @param string $targetModule
     *
     * @return array [targetField => sourceField], an empty sourceField means that the target field is not copied
     * @throws Exception
     */
    public static function getMapping(string $sourceModule, string $targetModule): array
    {
        $conversion = InventoryItem_Conversion_Model::getConversion($sourceModule, $targetModule);

        if (empty($conversion)) {
            return [];
        }

        return self::getConversionMapping($conversion->getId());
    }

    /**
     * @param int $conversionId
     *
     * @return array [targetField => sourceField]
     * @throws Exception
     */
    public static function getConversionMapping(int $conversionId): array
    {
        if (!Vtiger_Utils::CheckTable('df_inventoryitem_conversion_mappings')) {
            return [];
        }

        $table = (new self())->getMappingTable();
        $result = $table->selectResult(['target_field', 'source_field'], ['conversion_id' => $conversionId]);
        $mapping = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $mapping[$row['target_field']] = (string)$row['source_field'];
        }

        return $mapping;
    }

    /**
     * Fields of the target module which can receive a value from the source module.
     *
     * @param string $targetModule
     *
     * @return Vtiger_Field_Model[] indexed by the field name
     * @throws Exception
     */
    public static function getTargetFields(string $targetModule): array
    {
        $fields = [];

        foreach (Vtiger_Module_Model::getInstance($targetModule)->getFields() as $fieldName => $fieldModel) {
            if (self::isTargetField($fieldModel)) {
                $fields[$fieldName] = $fieldModel;
            }
        }

        return $fields;
    }

    /**
     * Fields of the source module which can be copied to the target module.
     *
     * @param string $sourceModule
     *
     * @return Vtiger_Field_Model[] indexed by the field name
     * @throws Exception
     */
    public static function getSourceFields(string $sourceModule): array
    {
        $fields = [];

        foreach (Vtiger_Module_Model::getInstance($sourceModule)->getFields() as $fieldName => $fieldModel) {
            if ($fieldModel->isViewable() && !InventoryItem_Conversion_Model::isCalculatedHeaderField($fieldName)) {
                $fields[$fieldName] = $fieldModel;
            }
        }

        return $fields;
    }

    /**
     * @param Vtiger_Field_Model $fieldModel
     *
     * @return bool
     */
    public static function isTargetField(Vtiger_Field_Model $fieldModel): bool
    {
        return $fieldModel->isEditable()
            && 1 === (int)$fieldModel->get('displaytype')
            && !InventoryItem_Conversion_Model::isCalculatedHeaderField($fieldModel->getName());
    }

    /**
     * Replaces the mapping of the conversion. Rows with unknown fields and duplicated target fields are dropped.
     *
     * @param InventoryItem_Conversion_Model $conversion
     * @param array                          $rows [['target' => string, 'source' => string], ...]
     *
     * @return void
     * @throws Exception
     */
    public function saveConversionMapping(InventoryItem_Conversion_Model $conversion, array $rows): void
    {
        $targetFields = self::getTargetFields((string)$conversion->get('target_module'));
        $sourceFields = self::getSourceFields((string)$conversion->get('source_module'));
        $table = $this->getMappingTable();

        $table->deleteData(['conversion_id' => $conversion->getId()]);

        $saved = [];

        foreach ($rows as $row) {
            $targetField = (string)($row['target'] ?? '');
            $sourceField = (string)($row['source'] ?? '');

            if (!isset($targetFields[$targetField]) || isset($saved[$targetField]) || ('' !== $sourceField && !isset($sourceFields[$sourceField]))) {
                continue;
            }

            $saved[$targetField] = true;
            $table->insertData(['conversion_id' => $conversion->getId(), 'target_field' => $targetField, 'source_field' => $sourceField]);
        }
    }
}

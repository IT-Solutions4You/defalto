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
 * Relations of templates (df_template_rel): a template of one module linked to records of
 * another module in an order — EMAIL Maker templates to PDF Maker templates now, later
 * PDF Maker and links of templates to contacts or other modules. Templates are not CRM
 * records, and vtiger_crmentityrel is looked up by id without the module in the core, so
 * every query here names both modules.
 */
class Core_TemplateRelation_Model extends Core_DatabaseData_Model
{
    public const TABLE = 'df_template_rel';

    protected string $table = self::TABLE;
    protected string $tableId = 'id';

    /**
     * @return self table accessor of df_template_rel
     */
    public function getTemplateRelationTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Creates the table; idempotent.
     *
     * @return void
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTemplateRelationTable()
            ->createTable()
            ->createColumn('record_id', 'INT(19) NOT NULL')
            ->createColumn('module', 'VARCHAR(100) NOT NULL')
            ->createColumn('related_record_id', 'INT(19) NOT NULL')
            ->createColumn('related_module', 'VARCHAR(100) NOT NULL')
            ->createColumn('sequence', 'INT(11) NOT NULL DEFAULT 0')
            ->createKey('UNIQUE KEY IF NOT EXISTS `relation_idx` (`module`, `record_id`, `related_module`, `related_record_id`)')
            ->createKey('KEY IF NOT EXISTS `record_idx` (`module`, `record_id`)')
            ->createKey('KEY IF NOT EXISTS `related_record_idx` (`related_module`, `related_record_id`)');
    }

    /**
     * @return bool the table exists (before its migration ran nothing is related)
     */
    public static function isInstalled(): bool
    {
        return Vtiger_Utils::CheckTable(self::TABLE);
    }

    /**
     * @param string $module        module of the templates
     * @param int[]  $recordIds     templates
     * @param string $relatedModule module of the related records
     *
     * @return array record id => related record ids in their order (one query)
     */
    public static function getRelatedIds(string $module, array $recordIds, string $relatedModule): array
    {
        $recordIds = array_values(array_unique(array_filter(array_map('intval', $recordIds))));

        if (!$recordIds || !self::isInstalled()) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT record_id, related_record_id FROM ' . self::TABLE . '
             WHERE module = ? AND related_module = ? AND record_id IN (' . generateQuestionMarks($recordIds) . ')
             ORDER BY record_id, sequence, id',
            array_merge([$module, $relatedModule], $recordIds)
        );
        $related = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $related[(int)$row['record_id']][] = (int)$row['related_record_id'];
        }

        return $related;
    }

    /**
     * Replaces the related records of one module of a template, in the given order.
     *
     * @param string $module
     * @param int    $recordId
     * @param string $relatedModule
     * @param int[]  $relatedRecordIds in their order
     *
     * @return void
     */
    public static function saveRelated(string $module, int $recordId, string $relatedModule, array $relatedRecordIds): void
    {
        if (!$recordId || !self::isInstalled()) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'DELETE FROM ' . self::TABLE . ' WHERE module = ? AND record_id = ? AND related_module = ?',
            [$module, $recordId, $relatedModule]
        );
        $table = (new self())->getTemplateRelationTable();

        foreach (array_values(array_unique(array_filter(array_map('intval', $relatedRecordIds)))) as $index => $relatedRecordId) {
            $table->insertData([
                'record_id' => $recordId,
                'module' => $module,
                'related_record_id' => $relatedRecordId,
                'related_module' => $relatedModule,
                'sequence' => $index + 1,
            ]);
        }
    }

    /**
     * Deletes every relation of deleted templates.
     *
     * @param string $module
     * @param int[]  $recordIds
     *
     * @return void
     */
    public static function deleteByRecord(string $module, array $recordIds): void
    {
        self::deleteBy('module', 'record_id', $module, $recordIds);
    }

    /**
     * Deletes the relations to deleted records of a module.
     *
     * @param string $relatedModule
     * @param int[]  $relatedRecordIds
     *
     * @return void
     */
    public static function deleteByRelated(string $relatedModule, array $relatedRecordIds): void
    {
        self::deleteBy('related_module', 'related_record_id', $relatedModule, $relatedRecordIds);
    }

    /**
     * @param string $moduleColumn module or related_module
     * @param string $idColumn     record_id or related_record_id
     * @param string $module
     * @param int[]  $ids
     *
     * @return void
     */
    private static function deleteBy(string $moduleColumn, string $idColumn, string $module, array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (!$ids || !self::isInstalled()) {
            return;
        }

        PearDatabase::getInstance()->pquery(
            'DELETE FROM ' . self::TABLE . ' WHERE ' . $moduleColumn . ' = ? AND ' . $idColumn . ' IN (' . generateQuestionMarks($ids) . ')',
            array_merge([$module], $ids)
        );
    }

    /**
     * Deletes every relation of all templates of a module (the module's own "delete all").
     *
     * @param string $module
     *
     * @return void
     */
    public static function deleteByModule(string $module): void
    {
        if (self::isInstalled()) {
            PearDatabase::getInstance()->pquery('DELETE FROM ' . self::TABLE . ' WHERE module = ?', [$module]);
        }
    }
}

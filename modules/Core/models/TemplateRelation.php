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
 * every lookup here names both modules. Writes and deletes go through Core_DatabaseData_Model (a list
 * of ids as an IN condition); the ordered read is one query of its own.
 */
class Core_TemplateRelation_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_template_rel';
    protected string $tableId = 'id';

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @return self table accessor of df_template_rel
     */
    public function getTemplateRelationTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Creates the table; idempotent. Listed in Core_CreateTables_Config, so the core install
     * and the system update (Install_Utils_Model::installTables()) run it.
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
     * @return bool the table exists (until the system update created it nothing is related)
     */
    public function isInstalled(): bool
    {
        return Vtiger_Utils::CheckTable($this->table);
    }

    /**
     * @param string $module        module of the templates
     * @param int[]  $recordIds     templates
     * @param string $relatedModule module of the related records
     *
     * @return array record id => related record ids in their order
     * @throws Exception
     */
    public function getRelatedIds(string $module, array $recordIds, string $relatedModule): array
    {
        $recordIds = self::getIds($recordIds);

        if (!$recordIds || !$this->isInstalled()) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT record_id, related_record_id FROM ' . $this->table . '
             WHERE module = ? AND related_module = ? AND record_id IN (' . generateQuestionMarks($recordIds) . ')
             ORDER BY sequence, ' . $this->tableId,
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
     * @throws Exception
     */
    public function saveRelated(string $module, int $recordId, string $relatedModule, array $relatedRecordIds): void
    {
        if (!$recordId || !$this->isInstalled()) {
            return;
        }

        $table = $this->getTemplateRelationTable();
        $table->deleteData(['module' => $module, 'record_id' => $recordId, 'related_module' => $relatedModule]);

        foreach (self::getIds($relatedRecordIds) as $index => $relatedRecordId) {
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
     * @throws Exception
     */
    public function deleteByRecord(string $module, array $recordIds): void
    {
        $this->deleteBy('module', 'record_id', $module, $recordIds);
    }

    /**
     * Deletes the relations to deleted records of a module.
     *
     * @param string $relatedModule
     * @param int[]  $relatedRecordIds
     *
     * @return void
     * @throws Exception
     */
    public function deleteByRelated(string $relatedModule, array $relatedRecordIds): void
    {
        $this->deleteBy('related_module', 'related_record_id', $relatedModule, $relatedRecordIds);
    }

    /**
     * Deletes every relation of all templates of a module (the module's own "delete all").
     *
     * @param string $module
     *
     * @return void
     * @throws Exception
     */
    public function deleteByModule(string $module): void
    {
        if ($this->isInstalled()) {
            $this->getTemplateRelationTable()->deleteData(['module' => $module]);
        }
    }

    /**
     * @param string $moduleColumn module or related_module
     * @param string $idColumn     record_id or related_record_id
     * @param string $module
     * @param int[]  $ids
     *
     * @return void
     * @throws Exception
     */
    private function deleteBy(string $moduleColumn, string $idColumn, string $module, array $ids): void
    {
        $ids = self::getIds($ids);

        if (!$ids || !$this->isInstalled()) {
            return;
        }

        $this->getTemplateRelationTable()->deleteData([$moduleColumn => $module, $idColumn => $ids]);
    }

    /**
     * @param array $ids
     *
     * @return int[] positive integer ids, each once, in their order
     */
    private static function getIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }
}

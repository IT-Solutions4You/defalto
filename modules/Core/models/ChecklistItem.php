<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_ChecklistItem_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_checklist_item';
    protected string $tableId = 'id';

    public static function getInstance(): self
    {
        return new self();
    }

    public function getChecklistItemTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function createTables(): void
    {
        $this->getChecklistItemTable()->createTable()
            ->createColumn('checklist_id', 'INT(19) NOT NULL')
            ->createColumn('text_hash', "CHAR(64) NOT NULL DEFAULT ''")
            ->createColumn('sequence', 'INT(11) NOT NULL DEFAULT 0')
            ->createColumn('checked', 'TINYINT(1) NOT NULL DEFAULT 0')
            ->createKey('KEY IF NOT EXISTS `checklist_idx` (`checklist_id`)')
            ->createKey('CONSTRAINT `fk_checklist_item_parent` FOREIGN KEY IF NOT EXISTS (`checklist_id`) REFERENCES `df_checklist` (`id`) ON DELETE CASCADE');

    }

    /** Match unchanged lines by hash and occurrence, including duplicate texts. Reads never write. */
    public function getItems(int $checklistId, array $lines): array
    {
        $table = $this->getChecklistItemTable();
        $result = $table->selectResult([], ['checklist_id' => $checklistId]);
        $metadata = [];

        while ($row = $table->getDB()->fetchByAssoc($result, -1, false)) {
            $metadata[] = $row;
        }

        return $this->getItemsFromMetadata($lines, $metadata);
    }

    /**
     * Retrieve raw item metadata grouped by checklist ID; records remain in database order.
     *
     * @param int[] $checklistIds
     * @return array<int, list<array{id: int|string, checklist_id: int|string, text_hash: string, sequence: int|string, checked: int|string}>>
     */
    public function getChecklistMetadata(array $checklistIds): array
    {
        if (!$checklistIds) {
            return [];
        }

        $table = $this->getChecklistItemTable();
        $result = $table->selectResult([], ['checklist_id' => $checklistIds]);
        $items = [];

        while ($row = $table->getDB()->fetchByAssoc($result, -1, false)) {
            $items[(int)$row['checklist_id']][] = $row;
        }

        return $items;
    }

    public function getItemsFromMetadata(array $lines, array $metadata): array
    {
        usort($metadata, static fn(array $a, array $b): int => [$a['sequence'], $a['id']] <=> [$b['sequence'], $b['id']]);
        $states = [];

        foreach ($metadata as $row) {
            $states[$row['text_hash']][] = (bool)$row['checked'];
        }

        $items = [];

        foreach ($lines as $index => $text) {
            $hash = hash('sha256', $text);
            $items[] = ['id' => $index + 1, 'text' => $text, 'checked' => !empty($states[$hash]) ? array_shift($states[$hash]) : false];
        }

        return $items;
    }

    /** The caller owns the transaction. No item text is persisted here. */
    public function saveItems(int $checklistId, array $items): void
    {
        $table = $this->getChecklistItemTable();
        $table->deleteData(['checklist_id' => $checklistId]);

        foreach ($items as $index => $item) {
            $table->insertData([
                'checklist_id' => $checklistId, 'text_hash' => hash('sha256', $item['text']),
                'sequence' => $index + 1, 'checked' => (int)$item['checked'],
            ]);
        }
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ModTracker_Basic_Model extends Core_DatabaseData_Model
{
    protected string $table = 'vtiger_modtracker_basic';
    protected string $tableId = 'id';

    public function getModTrackerBasicTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function saveHistory($data, array $delta, array $current, int $userId, array $context = []): ?int
    {
        unset($delta['modifiedtime']);

        if (!$delta) {
            return null;
        }

        $this->retrieveDB();
        $table = $this->getModTrackerBasicTable();
        $status = ModTracker::$UPDATED;

        if ($data->isNew() && !$table->selectData(['id'], ['crmid' => $data->getId(), 'status' => ModTracker::$CREATED])) {
            $status = ModTracker::$CREATED;
        }

        $id = $this->getDB()->getUniqueId($this->table);
        $changedOn = $data->getModuleName() === 'Users' ? $this->getDB()->formatDate(date('Y-m-d H:i:s'), true) : $current['modifiedtime'];
        $table->insertData([
            'id' => $id,
            'crmid' => $data->getId(),
            'module' => $data->getModuleName(),
            'whodid' => $userId,
            'changedon' => $changedOn,
            'status' => $status,
            'workflow_id' => $context['workflow_id'] ?? null,
            'task_id' => $context['task_id'] ?? null,
        ]);
        $detail = new ModTracker_Detail_Model();

        foreach ($delta as $fieldName => $values) {
            $detail->saveFieldChange($id, $fieldName, $values);
        }

        return $id;
    }

    public function retrieveRecordData(int $recordId, string $module): array
    {
        $delta = new VTEntityDelta();
        $delta->fetchEntity($module, $recordId);

        return $delta->getNewEntity($module, $recordId)->getData()->getColumnFields();
    }
}

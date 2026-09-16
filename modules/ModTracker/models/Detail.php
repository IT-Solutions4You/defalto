<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ModTracker_Detail_Model extends Core_DatabaseData_Model
{
    protected string $table = 'vtiger_modtracker_detail';
    protected string $tableId = 'id';

    public function getModTrackerDetailTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function saveFieldChange(int $historyId, string $fieldName, array $values): void
    {
        $this->getModTrackerDetailTable()->insertData([
            'id' => $historyId,
            'fieldname' => $fieldName,
            'prevalue' => $values['oldValue'],
            'postvalue' => $values['currentValue'],
        ]);
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_ClosedStatus_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_projecttask_closed_status';
    protected string $tableId = 'statusid';

    protected function getClosedStatusTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function isAvailable(): bool
    {
        return Vtiger_Utils::CheckTable($this->table);
    }

    public function hasClosedStatuses(): bool
    {
        return !empty($this->getClosedStatusTable()->selectData(['statusid'], []));
    }

    public function getOptions(): array
    {
        $this->retrieveDB();
        $db = $this->getDB();
        $result = $db->pquery('SELECT p.projecttaskstatusid AS id,p.projecttaskstatus AS value,c.statusid'
            . ' FROM vtiger_projecttaskstatus p LEFT JOIN df_projecttask_closed_status c ON c.statusid=p.projecttaskstatusid ORDER BY p.sortorderid');
        $options = [];

        while ($row = $db->fetchByAssoc($result)) {
            $options[(int)$row['id']] = ['value' => decode_html($row['value']), 'closed' => !empty($row['statusid'])];
        }

        return $options;
    }

    public function getClosedValues(): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        return array_column(array_filter($this->getOptions(), static fn(array $option): bool => $option['closed']), 'value');
    }

    public function saveClosedStatuses(array $ids): void
    {
        $options = $this->getOptions();
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (array_diff($ids, array_keys($options))) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $table = $this->getClosedStatusTable();
        $current = array_keys(array_filter($options, static fn(array $option): bool => $option['closed']));

        if ($current) {
            $table->deleteData(['statusid' => $current]);
        }

        foreach ($ids as $id) {
            $table->insertData(['statusid' => $id]);
        }
    }
}

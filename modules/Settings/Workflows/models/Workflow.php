<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Workflows_Workflow_Model extends Core_DatabaseData_Model
{
    protected string $table = 'com_vtiger_workflows';
    protected string $tableId = 'workflow_id';

    public function getWorkflowTable(): Core_DatabaseTable_Model
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function migrateConditions(array $definitions): array
    {
        $counts = ['updated' => 0, 'current' => 0, 'missing' => 0, 'ambiguous' => 0, 'invalid' => 0, 'changed' => 0];
        $registered = [];

        foreach ($definitions as $definition) {
            [$name, $module] = $definition;
            $registered[$module][$name][] = $definition[4];
        }

        if (!$registered) {
            return $counts;
        }

        $table = $this->getWorkflowTable();
        $db = $table->getDB();

        foreach ($registered as $module => $workflows) {
            foreach ($workflows as $name => $definitions) {
                if (count($definitions) !== 1) {
                    ++$counts['ambiguous'];
                    continue;
                }

                $conditions = Settings_Workflows_Condition_Model::getEditableConditions($definitions[0]);

                if ($conditions === null) {
                    ++$counts['invalid'];
                    continue;
                }

                $result = $table->selectResult(['workflow_id', 'test', 'filtersavedinnew'], ['module_name' => $module, 'workflowname' => $name]);
                $rowCount = $db->num_rows($result);

                if ($rowCount !== 1) {
                    ++$counts[$rowCount ? 'ambiguous' : 'missing'];
                    continue;
                }

                $row = $db->fetchByAssoc($result, -1, false);
                $stored = json_decode($row['test'] ?? '', true);

                if ((string)$row['filtersavedinnew'] === '6' && Settings_Workflows_Condition_Model::isModernFormat($stored)) {
                    ++$counts['current'];
                    continue;
                }

                // Preserve concurrent edits and every field outside the condition contract.
                $result = $db->pquery(
                    'UPDATE com_vtiger_workflows SET test=?, filtersavedinnew=6'
                    . ' WHERE workflow_id=? AND module_name=? AND workflowname=? AND BINARY test <=> BINARY ? AND filtersavedinnew <=> ?',
                    [Zend_Json::encode($conditions), $row['workflow_id'], $module, $name, $row['test'], $row['filtersavedinnew']],
                );

                if (!$result) {
                    throw new Exception('Workflow condition migration failed for workflow ' . (int)$row['workflow_id']);
                }

                ++$counts[$db->getAffectedRowCount($result) ? 'updated' : 'changed'];
            }
        }

        return $counts;
    }

    public function createWorkflow(Workflow $workflow): void
    {
        global $current_user;

        $table = $this->getWorkflowTable();
        $createdtime = (new DateTimeImmutable('now', new DateTimeZone(DateTimeField::getDBTimeZone())))->format('Y-m-d H:i:s');
        $creator = !empty($current_user->id) ? (int)$current_user->id : null;
        $workflowId = $table->getDB()->getUniqueID($this->table);
        $data = [
            'workflow_id' => $workflowId,
            'module_name' => $workflow->moduleName,
            'summary' => $workflow->description,
            'test' => $workflow->test ?: '[]',
            'execution_condition' => $workflow->executionCondition,
            'defaultworkflow' => $workflow->defaultworkflow,
            'filtersavedinnew' => $workflow->filtersavedinnew ?? 5,
            'schtypeid' => $workflow->schtypeid,
            'schtime' => $workflow->schtime,
            'schdayofmonth' => $workflow->schdayofmonth,
            'schdayofweek' => $workflow->schdayofweek,
            'schannualdates' => $workflow->schannualdates,
            'nexttrigger_time' => $workflow->nexttrigger_time,
            'status' => $workflow->status,
            'workflowname' => $workflow->name,
            'createdtime' => $createdtime,
            'creator' => $creator,
        ];

        if (in_array('type', $table->getDB()->getColumnNames($this->table), true)) {
            $data['type'] = $workflow->type;
        }

        $table->insertData($data);
        $workflow->id = $workflowId;
        $workflow->createdtime = $createdtime;
        $workflow->creator = $creator;
    }
}

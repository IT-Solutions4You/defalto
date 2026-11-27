<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_Dependency_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_project_dependency';
    protected string $tableId = 'id';
    protected static array $recordTables = [
        'ProjectTask' => ['vtiger_projecttask', 'projecttaskid'],
        'ProjectMilestone' => ['vtiger_projectmilestone', 'projectmilestoneid'],
    ];

    protected function getDependencyTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function isAvailable(): bool
    {
        return Vtiger_Utils::CheckTable($this->table);
    }

    public function getProjectLinks(int $projectId): array
    {
        $this->retrieveDB();
        $db = $this->getDB();
        $queries = $parameters = [];

        foreach (self::$recordTables as $module => [$table, $id]) {
            $queries[] = 'SELECT d.id, d.sourceid, d.targetid FROM df_project_dependency d'
                . " INNER JOIN $table s ON s.$id=d.sourceid AND s.projectid=d.projectid"
                . " INNER JOIN $table t ON t.$id=d.targetid AND t.projectid=d.projectid"
                . ' INNER JOIN vtiger_crmentity se ON se.crmid=d.sourceid AND se.deleted=0 AND se.setype=?'
                . ' INNER JOIN vtiger_crmentity te ON te.crmid=d.targetid AND te.deleted=0 AND te.setype=? WHERE d.projectid=?';
            array_push($parameters, $module, $module, $projectId);
        }

        $result = $db->pquery(implode(' UNION ALL ', $queries), $parameters);
        $links = [];

        while ($row = $db->fetchByAssoc($result)) {
            $links[] = array_map('intval', $row);
        }

        return $links;
    }

    public function validateEndpoints(int $projectId, int $sourceId, int $targetId): void
    {
        if (getSalesEntityType($projectId) !== 'Project'
            || !Users_Privileges_Model::isPermitted('Project', 'EditView', $projectId)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $chart = new Project_Chart_Model();
        $moduleName = getSalesEntityType($sourceId);

        if (!isset(self::$recordTables[$moduleName]) || getSalesEntityType($targetId) !== $moduleName) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        $module = Vtiger_Module_Model::getInstance($moduleName);

        foreach ([$sourceId, $targetId] as $id) {
            if (!isRecordExists($id) || !Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $id)) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $editable = $moduleName === 'ProjectTask' ? $chart->isTaskEditable($module, $id) : $chart->isMilestoneDateEditable($id);

            if (!$editable) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $record = Vtiger_Record_Model::getInstanceById($id, $moduleName);

            if ((int)$record->get('projectid') !== $projectId) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }
    }

    public function createDependency(int $projectId, int $sourceId, int $targetId): void
    {
        $this->validateEndpoints($projectId, $sourceId, $targetId);
        $error = $this->getNewLinkError($this->getProjectLinks($projectId), $sourceId, $targetId);

        if ($error !== '') {
            throw new Exception(vtranslate($error, 'Project'));
        }

        $this->getDependencyTable()->insertData(['projectid' => $projectId, 'sourceid' => $sourceId, 'targetid' => $targetId]);
    }

    public function removeDependency(int $projectId, int $sourceId, int $targetId): void
    {
        $this->validateEndpoints($projectId, $sourceId, $targetId);
        $this->getDependencyTable()->deleteData(['projectid' => $projectId, 'sourceid' => $sourceId, 'targetid' => $targetId]);
    }

    public function getNewLinkError(array $links, int $sourceId, int $targetId): string
    {
        if ($sourceId === $targetId) {
            return 'LBL_DEPENDENCY_SELF';
        }

        $graph = [];

        foreach ($links as $link) {
            if ($link['sourceid'] === $sourceId && $link['targetid'] === $targetId) {
                return 'LBL_DEPENDENCY_DUPLICATE';
            }

            $graph[$link['sourceid']][] = $link['targetid'];
        }

        $pending = [$targetId];
        $visited = [];

        while ($pending) {
            $node = array_pop($pending);

            if ($node === $sourceId) {
                return 'LBL_DEPENDENCY_CYCLE';
            }

            if (isset($visited[$node])) {
                continue;
            }

            $visited[$node] = true;
            array_push($pending, ...($graph[$node] ?? []));
        }

        return '';
    }

}

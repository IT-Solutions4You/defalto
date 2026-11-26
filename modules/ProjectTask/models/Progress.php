<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class ProjectTask_Progress_Model extends Vtiger_Base_Model
{
    protected static array $pendingDeletions = [];

    public function handleEvent($eventName, $entityData)
    {
        if ($entityData->getModuleName() !== 'ProjectTask') {
            return;
        }

        $taskId = $entityData->getId();

        if ($eventName === 'vtiger.entity.beforedelete') {
            // Bootstrap the legacy workflow/webservice stack only for task deletion.
            require_once 'modules/com_vtiger_workflow/VTEventHandler.inc';
            require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';
            $cache = new VTEntityCache(Users::getActiveAdminUser());
            $wsId = vtws_getWebserviceEntityId('ProjectTask', $taskId);
            VTEntityCache::unsetCachedEntity($wsId);
            $entity = $cache->forId($wsId);
            $tasks = $this->getDeleteTasks($cache, $wsId);
            self::$pendingDeletions[$taskId] = [$entity, $tasks];
        } elseif ($eventName === 'vtiger.entity.afterdelete' && isset(self::$pendingDeletions[$taskId])) {
            [$entity, $tasks] = self::$pendingDeletions[$taskId];
            unset(self::$pendingDeletions[$taskId]);
            VTEntityCache::unsetCachedEntity($entity->getId());

            foreach ($tasks as $task) {
                ModTracker_History_Helper::processTask($task, $entity);
            }
        }
    }

    protected function getWorkflows(): array
    {
        return (new VTWorkflowManager())->getWorkflowsForModule('ProjectTask');
    }

    protected function getWorkflowTasks(object $workflow): array
    {
        return (new VTTaskManager())->getTasksForWorkflow($workflow->id);
    }

    public function isDeleteWorkflow(object $workflow): bool
    {
        return in_array((int)$workflow->executionCondition, [VTWorkflowManager::$ON_EVERY_SAVE, VTWorkflowManager::$ON_MODIFY], true);
    }

    public function getDeleteTasks(object $cache, string $wsId): array
    {
        $tasks = [];

        // The workflow manager returns enabled workflows only. Evaluate before deletion,
        // while reference fields and normal workflow conditions remain available.
        foreach ($this->getWorkflows() as $workflow) {
            if (!$this->isDeleteWorkflow($workflow)
                || !$workflow->getEvaluationClone()->setNewRecord(false)->evaluate($cache, $wsId)) {
                continue;
            }

            foreach ($this->getWorkflowTasks($workflow) as $task) {
                if ($task instanceof VTProjectProgressTask && $task->active) {
                    $tasks[] = $task;
                }
            }
        }

        return $tasks;
    }

    public function getProjectIds(int $taskId): array
    {
        return ProjectTask_Record_Model::getRelatedProjectIds($taskId);
    }

    public function updateProjects(int $taskId, object $task): void
    {
        $model = new Project_Progress_Model();

        foreach ($this->getProjectIds($taskId) as $projectId) {
            $model->updateProgress($projectId, (array)$task->completedStatuses, (array)$task->excludedStatuses,
                (float)$task->hoursPerDay, (float)$task->fallbackHours);
        }
    }
}

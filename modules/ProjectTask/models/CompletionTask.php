<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class VTProjectCompletionTask extends VTTask
{
    public $executeImmediately = true;
    public $completedStatuses = ['Completed', 'Canceled'];
    public $projectStatus = 'completed';

    public function getFieldNames()
    {
        return ['completedStatuses', 'projectStatus'];
    }

    public function getTaskStatusOptions(): array
    {
        return Vtiger_Util_Helper::getPickListValues('projecttaskstatus');
    }

    public function getProjectStatusOptions(): array
    {
        return Vtiger_Util_Helper::getPickListValues('projectstatus');
    }

    public function doTask($entity)
    {
        if ($entity->getModuleName() !== 'ProjectTask') {
            return;
        }

        [, $taskId] = vtws_getIdComponents($entity->getId());
        $model = new Project_Completion_Model();

        foreach (ProjectTask_Record_Model::getRelatedProjectIds((int)$taskId) as $projectId) {
            $model->updateCompletion($projectId, (array)$this->completedStatuses, (string)$this->projectStatus);
        }
    }
}

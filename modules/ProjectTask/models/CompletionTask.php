<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

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

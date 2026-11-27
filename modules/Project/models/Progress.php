<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_Progress_Model extends Vtiger_Base_Model
{
    protected static array $activeProjects = [];

    protected function retrieveTasks(int $projectId): array
    {
        $db = PearDatabase::getInstance();
        // A project aggregate must be independent of the initiating user's task visibility.
        $result = $db->pquery('SELECT t.planed_hours,t.startdate,t.enddate,t.projecttaskstatus,t.projecttaskprogress'
            . ' FROM vtiger_projecttask t INNER JOIN vtiger_crmentity e ON e.crmid=t.projecttaskid'
            . ' AND e.deleted=0 AND e.setype=? WHERE t.projectid=?', ['ProjectTask', $projectId]);
        $tasks = [];

        while ($row = $db->fetchByAssoc($result, -1, false)) {
            $tasks[] = $row;
        }

        return $tasks;
    }

    public function updateProgress(int $projectId, array $completed, array $excluded, float $hoursPerDay, float $fallbackHours): void
    {
        if (!$this->isProjectAvailable($projectId)) {
            return;
        }

        self::$activeProjects[$projectId] = true;
        $value = $this->getCalculator()->getProgress($this->retrieveTasks($projectId), $completed, $excluded, $hoursPerDay, $fallbackHours);
        $focus = CRMEntity::getInstance('Project');
        $focus->retrieve_entity_info($projectId, 'Project');

        if (!isset($focus->column_fields['progress']) || (int)$focus->column_fields['progress'] !== $value) {
            $this->saveProgress($focus, $projectId, $value);
        }

        unset(self::$activeProjects[$projectId]);
    }

    protected function saveProgress(CRMEntity $focus, int $projectId, int $value): void
    {
        $focus->id = $projectId;
        $focus->mode = 'edit';
        $focus->clearSingletonSaveFields();
        $meta = vtws_getModuleHandlerFromName('Project', vglobal('current_user'))->getMeta();
        Core_RecordUpdate_Model::convertToUserFormat($focus, $meta);
        $focus->column_fields->pauseTracking();

        foreach ($focus->column_fields as $fieldName => $fieldValue) {
            if (is_string($fieldValue)) {
                $focus->column_fields[$fieldName] = html_entity_decode($fieldValue, ENT_QUOTES, vglobal('default_charset') ?: 'UTF-8');
            }
        }

        $focus->column_fields->resumeTracking();
        $focus->column_fields['progress'] = (string)$value;
        // Match Update Fields: retain attributed history without firing another workflow chain.
        ModTracker_History_Helper::saveRecord($focus, 'Project');
        VTEntityCache::unsetCachedEntity(vtws_getWebserviceEntityId('Project', $projectId));
    }

    protected function isProjectAvailable(int $projectId): bool
    {
        return $projectId > 0 && !isset(self::$activeProjects[$projectId]) && isRecordExists($projectId)
            && getSalesEntityType($projectId) === 'Project';
    }

    protected function getCalculator(): Project_ProgressCalculator_Model
    {
        return new Project_ProgressCalculator_Model();
    }
}

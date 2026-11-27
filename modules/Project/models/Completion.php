<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_Completion_Model extends Vtiger_Base_Model
{
    public function hasOnlyCompletedTasks(int $projectId, array $completedStatuses): bool
    {
        if (!$completedStatuses) {
            return false;
        }

        $db = PearDatabase::getInstance();
        // Completion must include tasks hidden from the initiating user.
        $result = $db->pquery('SELECT COUNT(*) AS total,'
            . ' SUM(CASE WHEN t.projecttaskstatus IN (' . generateQuestionMarks($completedStatuses) . ') THEN 0 ELSE 1 END) AS incomplete'
            . ' FROM vtiger_projecttask t INNER JOIN vtiger_crmentity e ON e.crmid=t.projecttaskid'
            . ' AND e.deleted=0 AND e.setype=? WHERE t.projectid=?', [...$completedStatuses, 'ProjectTask', $projectId]);

        return (int)$db->query_result($result, 0, 'total') > 0
            && (int)$db->query_result($result, 0, 'incomplete') === 0;
    }

    public function updateCompletion(int $projectId, array $completedStatuses, string $projectStatus): void
    {
        if (!$this->isProjectAvailable($projectId) || !$this->hasOnlyCompletedTasks($projectId, $completedStatuses)) {
            return;
        }

        if (!in_array($projectStatus, Vtiger_Util_Helper::getPickListValues('projectstatus'), true)) {
            throw new Exception(vJSTranslate('JS_PLEASE_ENTER_VALID_VALUE', 'Core'));
        }

        $focus = CRMEntity::getInstance('Project');
        $focus->retrieve_entity_info($projectId, 'Project');

        if ($focus->column_fields['projectstatus'] === $projectStatus) {
            return;
        }

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
        $focus->column_fields['projectstatus'] = $projectStatus;
        // Match Update Fields: attribute history without starting another workflow chain.
        ModTracker_History_Helper::saveRecord($focus, 'Project');
        VTEntityCache::unsetCachedEntity(vtws_getWebserviceEntityId('Project', $projectId));
    }

    protected function isProjectAvailable(int $projectId): bool
    {
        return $projectId > 0 && isRecordExists($projectId) && getSalesEntityType($projectId) === 'Project';
    }
}

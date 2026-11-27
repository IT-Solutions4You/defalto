<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_Record_Model extends Vtiger_Record_Model
{
    protected static array $dateFields = ['startdate', 'enddate'];

    public static function getRelatedProjectIds(int $taskId): array
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT projectid FROM vtiger_projecttask WHERE projecttaskid=?', [$taskId]);
        $projectId = (int)$db->query_result($result, 0, 'projectid');
        $oldEntity = (new VTEntityDelta())->getOldEntity('ProjectTask', $taskId);
        $oldProjectId = $oldEntity ? (int)$oldEntity->get('projectid') : 0;

        return array_values(array_unique(array_filter([$projectId, $oldProjectId])));
    }

    public static function getRecordSnapshots(array $recordIds): array
    {
        $recordIds = array_values(array_unique(array_filter(array_map('intval', $recordIds), static fn(int $id): bool => $id > 0)));

        if (!$recordIds) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT t.*, c.*, e.* FROM vtiger_projecttask t'
            . ' LEFT JOIN vtiger_projecttaskcf c ON c.projecttaskid=t.projecttaskid'
            . ' INNER JOIN vtiger_crmentity e ON e.crmid=t.projecttaskid AND e.deleted=0 AND e.setype=?'
            . ' WHERE t.projecttaskid IN (' . generateQuestionMarks($recordIds) . ')', ['ProjectTask', ...$recordIds]);
        $snapshots = [];

        while ($row = $db->fetchByAssoc($result)) {
            $snapshots[(int)$row['crmid']] = ['values' => $row];
        }

        return $snapshots;
    }

    public static function getLatestMilestoneTaskDate(int $milestoneId): ?string
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT MAX(GREATEST(COALESCE(NULLIF(t.startdate, ?), ?),'
            . ' COALESCE(NULLIF(t.enddate, ?), ?))) AS enddate FROM vtiger_projecttask t'
            . ' INNER JOIN vtiger_crmentity e ON e.crmid=t.projecttaskid AND e.deleted=0 AND e.setype=?'
            . ' WHERE t.milestoneid=?',
            ['0000-00-00', '', '0000-00-00', '', 'ProjectTask', $milestoneId]);
        $date = $db->query_result($result, 0, 'enddate');

        return $date ? (string)$date : null;
    }

    public static function isQuickEditPermitted(int $recordId): bool
    {
        if (!$recordId) {
            return Users_Privileges_Model::isPermitted('ProjectTask', 'EditView');
        }

        return getSalesEntityType($recordId) === 'ProjectTask'
            && Users_Privileges_Model::isPermitted('ProjectTask', 'DetailView', $recordId)
            && Users_Privileges_Model::isPermitted('ProjectTask', 'EditView', $recordId);
    }

    public function save(): void
    {
        $error = $this->getDateValidationError();

        if ($error !== null) {
            throw new Exception(vtranslate($error, 'Project'));
        }

        parent::save();
    }

    public function getDateValidationError(): ?string
    {
        if (!$this->hasValidDates()) {
            return 'LBL_INVALID_DATE_RANGE';
        }

        $project = $this->getDateReferenceRecord('projectid', 'Project');

        if ($project && !$this->hasDatesWithinProject($project)) {
            return 'LBL_TASK_OUTSIDE_PROJECT_DATES';
        }

        $milestone = $this->getDateReferenceRecord('milestoneid', 'ProjectMilestone');

        if ($milestone) {
            if ((int)$milestone->get('projectid') !== (int)$this->get('projectid')) {
                return 'LBL_TASK_MILESTONE_PROJECT';
            }

            if (!$this->hasDatesBeforeMilestoneEnd($milestone)) {
                return 'LBL_TASK_AFTER_MILESTONE';
            }
        }

        return null;
    }

    public function isDateReferencePermitted(string $field, string $module, int $id): bool
    {
        if (!$id || $id === (int)$this->get($field)) {
            return true;
        }

        return isRecordExists($id) && getSalesEntityType($id) === $module
            && Users_Privileges_Model::isPermitted($module, 'DetailView', $id);
    }

    protected function getDateReferenceRecord(string $field, string $module): ?Vtiger_Record_Model
    {
        $id = (int)$this->get($field);

        if (!$id || !isRecordExists($id) || getSalesEntityType($id) !== $module) {
            return null;
        }

        return Vtiger_Record_Model::getInstanceById($id, $module);
    }

    protected function hasDatesWithinProject(Vtiger_Record_Model $project): bool
    {
        $start = (string)$this->convertDateForValidation((string)$project->get('startdate'));
        $end = (string)$this->convertDateForValidation((string)$project->get('targetenddate'));

        foreach (self::$dateFields as $field) {
            $date = (string)$this->convertDateForValidation((string)$this->get($field));

            if ($date === '') {
                continue;
            }

            if ($start !== '' && $date < $start) {
                return false;
            }

            if ($end !== '' && $date > $end) {
                return false;
            }
        }

        return true;
    }

    protected function hasDatesBeforeMilestoneEnd(Vtiger_Record_Model $milestone): bool
    {
        $date = (string)$this->convertDateForValidation((string)$milestone->get('projectmilestonedate'));

        if ($date === '') {
            return true;
        }

        foreach (self::$dateFields as $field) {
            $boundary = $this->convertDateForValidation((string)$this->get($field));

            if ($boundary !== null && $boundary !== '' && $boundary > $date) {
                return false;
            }
        }

        return true;
    }

    public function hasValidDates(): bool
    {
        $start = $this->convertDateForValidation((string)$this->get('startdate'));
        $end = $this->convertDateForValidation((string)$this->get('enddate'));

        return $start !== null && $end !== null && ($start === '' || $end === '' || $start <= $end);
    }

    public function hasValidMilestoneEnd(): bool
    {
        $milestone = $this->getDateReferenceRecord('milestoneid', 'ProjectMilestone');

        return !$milestone || $this->hasDatesBeforeMilestoneEnd($milestone);
    }

    protected function convertDateForValidation(string $value): ?string
    {
        return Core_DateOnly_Helper::convertToDBFormat($value);
    }
}

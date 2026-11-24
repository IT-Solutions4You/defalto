<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class ProjectMilestone_Record_Model extends Vtiger_Record_Model
{
    public function save(): void
    {
        $date = Core_DateOnly_Helper::convertToDBFormat((string)$this->get('projectmilestonedate'));

        if ($date === null) {
            throw new Exception(vtranslate('LBL_INVALID_DATE_RANGE', 'Project'));
        }

        if ($date !== '' && $this->getId()) {
            $end = ProjectTask_Record_Model::getLatestMilestoneTaskDate($this->getId());

            if ($end !== null && $date < $end) {
                throw new Exception(vtranslate('LBL_MILESTONE_BEFORE_TASK_END', 'Project'));
            }
        }

        parent::save();
    }
}

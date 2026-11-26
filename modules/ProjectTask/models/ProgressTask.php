<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class VTProjectProgressTask extends VTTask
{
    protected static array $settingFields = [
        'hoursPerDay' => ['label' => 'LBL_PROGRESS_HOURS_PER_DAY', 'type' => 'number'],
        'fallbackHours' => ['label' => 'LBL_PROGRESS_FALLBACK_HOURS', 'type' => 'number'],
        'completedStatuses' => ['label' => 'LBL_PROGRESS_COMPLETED_STATUSES', 'type' => 'statuses'],
        'excludedStatuses' => ['label' => 'LBL_PROGRESS_EXCLUDED_STATUSES', 'type' => 'statuses'],
    ];
    public $executeImmediately = true;
    public $hoursPerDay = 8;
    public $fallbackHours = 8;
    public $completedStatuses = ['Completed'];
    public $excludedStatuses = ['Canceled'];

    public function getFieldNames()
    {
        return array_keys(self::$settingFields);
    }

    public function getSettingFields(): array
    {
        return self::$settingFields;
    }

    public function getStatusOptions(): array
    {
        return (new ProjectTask_ClosedStatus_Model())->getOptions();
    }

    public function doTask($entity)
    {
        if ($entity->getModuleName() !== 'ProjectTask') {
            return;
        }

        [, $taskId] = vtws_getIdComponents($entity->getId());
        (new ProjectTask_Progress_Model())->updateProjects((int)$taskId, $this);
    }
}

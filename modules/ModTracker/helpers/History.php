<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/ModTracker/ModTracker.php';
require_once 'data/VTEntityDelta.php';

/** Keep audit baselines separate from the deltas used by workflow conditions. */
class ModTracker_History_Helper
{
    private static ?WeakMap $saves = null;
    private static array $context = [];

    public static function getUserId(): int
    {
        global $current_user;

        return (int)($_SESSION['authenticated_user_id'] ?? $current_user->id);
    }

    public static function registerSave($data): void
    {
        self::$saves ??= new WeakMap();

        // A nested save must not consume changes still owned by its outer save.
        foreach (self::$saves as $pending => $scope) {
            if ($data->getId() && $pending !== $data && $pending->getId() == $data->getId()
                && $pending->getModuleName() === $data->getModuleName()) {
                self::saveHistory($pending);
            }
        }

        $model = new ModTracker_Basic_Model();
        self::$saves[$data] = [
            'before' => $data->isNew() ? [] : $model->retrieveRecordData((int)$data->getId(), $data->getModuleName()),
            'user' => self::getUserId(),
            'context' => self::$context,
        ];
    }

    public static function saveHistory($data): ?int
    {
        $model = new ModTracker_Basic_Model();
        $current = $model->retrieveRecordData((int)$data->getId(), $data->getModuleName());
        $scope = self::$saves[$data] ?? null;

        if ($scope !== null) {
            $delta = VTEntityDelta::getDataDelta($scope['before'], $current);
        }

        else {
            // Preserve callers that emit the final event without a before-save event.
            $delta = (new VTEntityDelta())->getEntityDelta($data->getModuleName(), $data->getId(), true);
        }

        $id = $model->saveHistory($data, (array)$delta, $current, $scope['user'] ?? self::getUserId(), $scope['context'] ?? self::$context);
        self::setRecordBaseline($data, $current);

        return $id;
    }

    public static function removeSave($data): void
    {
        if (self::$saves !== null) {
            unset(self::$saves[$data]);
        }
    }

    public static function setRecordBaseline($data, array $current): void
    {
        foreach (self::$saves ?? [] as $pending => $scope) {
            if ($pending->getId() == $data->getId() && $pending->getModuleName() === $data->getModuleName()) {
                $scope['before'] = $current;
                self::$saves[$pending] = $scope;
            }
        }
    }

    public static function savePendingHistory(): void
    {
        foreach (self::$saves ?? [] as $data => $scope) {
            if ($data->getId()) {
                self::saveHistory($data);
            }
        }
    }

    public static function processTask($task, $entity): void
    {
        if (!vtlib_isModuleActive('ModTracker')) {
            $task->doTask($entity);
            return;
        }

        // Flush the initiating save under its own identity before entering the WF.
        self::savePendingHistory();
        $previous = self::$context;
        self::$context = ['workflow_id' => (int)$task->workflowId, 'task_id' => (int)$task->id];
        try {
            $task->doTask($entity);
        } finally {
            self::$context = $previous;
        }
    }

    public static function saveRecord($focus, string $module): void
    {
        if (!vtlib_isModuleActive('ModTracker') || !ModTracker::isTrackingEnabledForModule($module)) {
            $focus->saveentity($module);
            return;
        }

        $data = VTEntityData::fromCRMEntity($focus);
        self::registerSave($data);
        try {
            $focus->saveentity($module);
            self::saveHistory($data);
        } finally {
            self::removeSave($data);
        }
    }
}

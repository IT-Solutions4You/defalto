<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Workflow task: allows or denies selected actions on the record for selected members
 * (Core_RecordActionPermission_Model rules with source "workflow" and the task id as reference).
 */
class RestrictRecordActions extends VTTask
{
    public $executeImmediately = true;

    public $trigger;
    public $actions = [];
    public $effect = Core_RecordActionPermission_Model::EFFECT_DENY;
    public $members = [];
    public $reason = '';
    public $fields = [];
    public $statusValues = [];

    /**
     * Returns the task properties saved from the task editor.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return ['actions', 'effect', 'members', 'reason', 'fields', 'statusValues'];
    }

    /**
     * Replaces the rules this task wrote for the record with the configured actions and members.
     *
     * @param VTWorkflowEntity $entity
     *
     * @return void
     * @throws Exception
     */
    public function doTask($entity)
    {
        $recordId = (int)vtws_getIdComponents($entity->getId())[1];
        $actions = array_values(array_filter(array_map('strval', (array)$this->actions)));
        $members = array_values(array_filter(array_map('strval', (array)$this->members)));
        $fields = array_values(array_filter(array_map('strval', (array)$this->fields)));
        $statusValues = array_values(array_filter(array_map('strval', (array)$this->statusValues)));

        // Without the task id the rules of other workflow tasks could not be told apart
        if (!$recordId || empty($this->id) || empty($actions) || empty($members)) {
            return;
        }

        $permissions = Core_RecordActionPermission_Model::getInstance();
        $effect = Core_RecordActionPermission_Model::EFFECT_ALLOW === $this->effect
            ? Core_RecordActionPermission_Model::EFFECT_ALLOW
            : Core_RecordActionPermission_Model::EFFECT_DENY;

        $permissions->deleteBySource($recordId, Core_RecordActionPermission_Model::SOURCE_WORKFLOW, (string)$this->id);

        foreach ($members as $member) {
            $scope = Core_RecordActionPermissionTask_Helper::getMemberScope($member);

            if (empty($scope)) {
                continue;
            }

            foreach ($actions as $action) {
                foreach (Core_RecordActionPermissionTask_Helper::getActionValues($action, $fields, $statusValues) as $actionValue) {
                    $permissions->saveRule(
                        $recordId,
                        $action,
                        $effect,
                        $scope[0],
                        $scope[1],
                        Core_RecordActionPermission_Model::SOURCE_WORKFLOW,
                        (string)$this->id,
                        (string)$this->reason,
                        $actionValue
                    );
                }
            }
        }
    }

    /**
     * Checks whether an action is selected in the task editor.
     *
     * @param string $action
     *
     * @return bool
     */
    public function isSelectedAction(string $action): bool
    {
        return in_array($action, (array)$this->actions, true);
    }

    /**
     * Checks whether a field of the action "Edit field" is selected in the task editor.
     *
     * @param string $field
     *
     * @return bool
     */
    public function isSelectedField(string $field): bool
    {
        return in_array($field, (array)$this->fields, true);
    }

    /**
     * Checks whether a value of the action "Change to value" is selected in the task editor.
     *
     * @param string $statusValue "field=value"
     *
     * @return bool
     */
    public function isSelectedStatusValue(string $statusValue): bool
    {
        return in_array($statusValue, (array)$this->statusValues, true);
    }

    /**
     * Checks whether a member is selected in the task editor.
     *
     * @param string $member
     *
     * @return bool
     */
    public function isSelectedMember(string $member): bool
    {
        return in_array($member, (array)$this->members, true);
    }
}

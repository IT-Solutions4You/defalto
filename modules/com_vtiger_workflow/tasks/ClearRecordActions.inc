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
 * Workflow task: removes the record action rules written by workflows, for the selected actions or for all of them.
 */
class ClearRecordActions extends VTTask
{
    public $executeImmediately = true;

    public $trigger;
    public $actions = [];

    /**
     * Returns the task properties saved from the task editor.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return ['actions'];
    }

    /**
     * Deletes the workflow rules of the record.
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

        Core_RecordActionPermission_Model::getInstance()->deleteBySource(
            $recordId,
            Core_RecordActionPermission_Model::SOURCE_WORKFLOW,
            '',
            $actions
        );
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
}

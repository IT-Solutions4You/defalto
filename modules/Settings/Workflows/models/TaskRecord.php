<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/*
 * Workflow Task Record Model Class
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

class Settings_Workflows_TaskRecord_Model extends Settings_Vtiger_Record_Model
{
    const TASK_STATUS_ACTIVE = 1;

    public function getId()
    {
        return $this->get('task_id');
    }

    public function getName()
    {
        return $this->get('summary');
    }

    public function isActive()
    {
        return $this->get('status') == self::TASK_STATUS_ACTIVE;
    }

    public function getTaskObject()
    {
        return $this->task_object;
    }

    public function setTaskObject($task)
    {
        $this->task_object = $task;

        return $this;
    }

    public function getTaskManager()
    {
        return $this->task_manager;
    }

    public function setTaskManager($tm)
    {
        $this->task_manager = $tm;
    }

    public function getEditViewUrl()
    {
        return 'index.php?module=Workflows&parent=Settings&view=EditTask&type=' . $this->getTaskType()->getName() . '&task_id=' . $this->getId(
            ) . '&for_workflow=' . $this->getWorkflow()->getId();
    }

    public function getV7EditViewUrl()
    {
        return 'index.php?module=Workflows&parent=Settings&view=EditV7Task&type=' . $this->getTaskType()->getName() . '&task_id=' . $this->getId(
            ) . '&for_workflow=' . $this->getWorkflow()->getId();
    }

    public function getDeleteActionUrl()
    {
        return 'index.php?module=Workflows&parent=Settings&action=TaskAjax&mode=Delete&task_id=' . $this->getId();
    }

    public function getChangeStatusUrl()
    {
        return 'index.php?module=Workflows&parent=Settings&action=TaskAjax&mode=ChangeStatus&task_id=' . $this->getId();
    }

    public function getWorkflow()
    {
        return $this->workflow;
    }

    public function setWorkflowFromInstance($workflowModel)
    {
        $this->workflow = $workflowModel;

        return $this;
    }

    public function getTaskType()
    {
        if (!$this->task_type) {
            $taskObject = $this->getTaskObject();
            $taskClass = get_class($taskObject);
            $this->task_type = Settings_Workflows_TaskType_Model::getInstanceFromClassName($taskClass);
        }

        return $this->task_type;
    }

    public static function getAllForWorkflow($workflowModel, $active = false)
    {
        $db = PearDatabase::getInstance();

        $tm = new VTTaskManager($db);
        $tasks = $tm->getTasksForWorkflow($workflowModel->getId());
        $taskModels = [];
        foreach ($tasks as $task) {
            if (!$active || $task->active == self::TASK_STATUS_ACTIVE) {
                $taskModels[$task->id] = self::getInstanceFromTaskObject($task, $workflowModel, $tm);
            }
        }

        return $taskModels;
    }

    public static function getInstance()
    {
        [$taskId, $workflowModel] = func_get_args();
        $db = PearDatabase::getInstance();
        $tm = new VTTaskManager($db);
        $task = $tm->retrieveTask($taskId);
        if ($workflowModel == null) {
            $workflowModel = Settings_Workflows_Record_Model::getInstance($task->workflowId);
        }

        return self::getInstanceFromTaskObject($task, $workflowModel, $tm);
    }

    /**
     * @param string $taskName
     * @param object $workflowModel
     *
     * @return bool|self
     * @throws Exception
     */
    public static function getInstanceByName(string $taskName, object $workflowModel): bool|self
    {
        $data = (new self())->getTaskTable()->selectData(['task_id as id'], ['workflow_id' => $workflowModel->getId(), 'summary' => $taskName]);

        return $data['id'] ? self::getInstance((int)$data['id'], $workflowModel) : false;
    }

    public function getTaskTable(): Core_DatabaseData_Model
    {
        return (new Core_DatabaseData_Model())->getTable('com_vtiger_workflowtasks', 'task_id');
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTaskTable()
            ->createTable()
            ->createColumn('workflow_id', 'int(11) DEFAULT NULL')
            ->createColumn('summary', 'varchar(400) NOT NULL')
            ->createColumn('task', 'text NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (task_id)')
            ->createKey('UNIQUE KEY IF NOT EXISTS com_vtiger_workflowtasks_idx (task_id)');
    }

    public static function getCleanInstance($workflowModel, $taskName)
    {
        $db = PearDatabase::getInstance();
        $tm = new VTTaskManager($db);
        $task = $tm->createTask($taskName, $workflowModel->getId());

        return self::getInstanceFromTaskObject($task, $workflowModel, $tm);
    }

    public static function getInstanceFromTaskObject($task, $workflowModel, $tm)
    {
        $taskId = $task->id;
        $summary = $task->summary;
        $status = $task->active;

        $taskModel = new self();
        $taskModel->setTaskManager($tm);

        return $taskModel->set('task_id', $taskId)->set('summary', $summary)->set('status', $status)
            ->setTaskObject($task)->setWorkflowFromInstance($workflowModel);
    }

    /**
     * Function deletes workflow task
     */
    public function delete()
    {
        $this->task_manager->deleteTask($this->getId());
    }

    /**
     * Function saves workflow task
     */
    public function save()
    {
        $taskObject = $this->getTaskObject();
        $this->task_manager->saveTask($taskObject);
    }
}
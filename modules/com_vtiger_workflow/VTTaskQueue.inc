<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Time based Queue of tasks ready for execution.
 *
 */
class VTTaskQueue
{
    /**
     * Queue a task for execution.
     *
     * @param $taskId   The id of the task to queue
     * @param $entityId The id of the crm entity the task is assiciated with.
     * @param $when     The time after which the task should be executed. This is
     *                  an optional value with a default value of 0.
     */
    public function queueTask($taskId, $entityId, $when = 0, $taskContents = false, $relatedInfo = [])
    {
        $adb = PearDatabase::getInstance();
        //Mention all related info for the given $entityId
        $relatedInfo = json_encode($relatedInfo);
        if ($entityId) {
            $adb->pquery(
                'INSERT INTO com_vtiger_workflowtask_queue (task_id, entity_id, do_after, task_contents,relatedinfo) VALUES(?, ?, ?, ?,?)',
                [$taskId, $entityId, $when, $taskContents, $relatedInfo]
            );
        }

        return true;
    }

    /**
     * Get a list of taskId/entityId pairs ready for execution.
     *
     * The method fetches task id/entity id where the when timestamp
     * is less than the current time when the method was called.
     *
     * @return A list of pairs of the form array(taskId, entityId)
     */
    public function getReadyTasks()
    {
        $adb = PearDatabase::getInstance();
        $time = time();
        $result = $adb->pquery('SELECT task_id, entity_id, task_contents,relatedinfo FROM com_vtiger_workflowtask_queue WHERE do_after<?', [$time]);
        $it = new SqlResultIterator($adb, $result);
        $arr = [];
        foreach ($it as $row) {
            if ($this->checkEntityExists($row->entity_id)) {
                $arr[] = [$row->task_id, $row->entity_id, $row->task_contents, $row->relatedinfo];
            }
        }
        $adb->pquery("delete from com_vtiger_workflowtask_queue where do_after<?", [$time]);

        return $arr;
    }

    public function checkEntityExists($id)
    {
        $idParts = explode('x', $id);
        $recordId = $idParts[1];
        $status = Vtiger_Util_Helper::checkRecordExistance($recordId);
        if ($status == 0) {
            $db = PearDatabase::getInstance();
            $webServiceObject = VtigerWebserviceObject::fromId($db, $idParts[0]);
            if ($webServiceObject->getEntityName() == 'Leads' && isLeadConverted($recordId)) {
                return false;
            }

            return true;
        }

        return false;
    }
}
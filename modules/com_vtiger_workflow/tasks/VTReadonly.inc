<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class VTReadonly extends VTTask
{
    public $executeImmediately = true;

    public function doTask($entity)
    {
        $module = $entity->getModuleName();

        [$moduleId, $record] = vtws_getIdComponents($entity->getId());

        $readonly = Core_Readonly_Model::getInstance($module);
        $readonly->setRecord($record);
        $readonly->setReadonly();
    }

    public function getFieldNames()
    {
        return [];
    }
}
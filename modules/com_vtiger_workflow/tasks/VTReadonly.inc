<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class VTReadonly extends VTTask
{
    public $executeImmediately = true;

    public function doTask($entity)
    {
        $module = $entity->getModuleName();

        list($moduleId, $record) = vtws_getIdComponents($entity->getId());

        $readonly = Core_Readonly_Model::getInstance($module);
        $readonly->setRecord($record);
        $readonly->setReadonly();
    }

    public function getFieldNames()
    {
        return array();
    }
}
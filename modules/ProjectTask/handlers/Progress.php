<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class ProjectTask_Progress_Handler extends VTEventHandler
{
    public function handleEvent($eventName, $entityData)
    {
        (new ProjectTask_Progress_Model())->handleEvent($eventName, $entityData);
    }
}

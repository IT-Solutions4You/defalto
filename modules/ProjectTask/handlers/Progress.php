<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_Progress_Handler extends VTEventHandler
{
    public function handleEvent($eventName, $entityData)
    {
        (new ProjectTask_Progress_Model())->handleEvent($eventName, $entityData);
    }
}

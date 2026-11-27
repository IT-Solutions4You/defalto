<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Checklist_Handler extends VTEventHandler
{
    public function handleEvent($name, $data)
    {
        if ($name === 'vtiger.entity.aftersave.final') {
            Core_Checklist_Model::getInstance()->saveRecordMetadata((int)$data->getId(), $data->getModuleName());
        }
    }
}

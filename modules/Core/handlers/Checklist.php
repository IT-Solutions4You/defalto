<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Core_Checklist_Handler extends VTEventHandler
{
    public function handleEvent($name, $data)
    {
        if ($name === 'vtiger.entity.aftersave.final') {
            Core_Checklist_Model::getInstance()->saveRecordMetadata((int)$data->getId(), $data->getModuleName());
        }
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Mention_Comment_Handler extends VTEventHandler
{
    public function handleEvent($handlerType, $entityData)
    {
        if ($handlerType !== 'vtiger.entity.aftersave' || $entityData->getModuleName() !== 'ModComments') {
            return;
        }

        if (!vtlib_isModuleActive('Mention') || !vtlib_isModuleActive('ITS4YouEmails')) {
            return;
        }

        $module = Vtiger_Module_Model::getInstance('Mention');
        $parentId = (int)$entityData->get('related_to');

        if ($parentId && isRecordExists($parentId)) {
            $module->sendEmailToMentioned($parentId, $entityData->get('commentcontent'));
        }
    }
}

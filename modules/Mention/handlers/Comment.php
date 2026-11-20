<?php
/*
 * Copyright (C) IT-Solutions4You s.r.o. All Rights Reserved.
 * Subject to the original ITS4YouMention license.
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

<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class EmailLookupHandler extends VTEventHandler
{


    /**
     * To delete email lookup record
     * @param object $entityData
     */
    public function handleEmailLookupDeleteEvent($entityData)
    {
        $recordId = $entityData->getId();

        $emailLookup = ITS4YouEmails_EmailLookup_Model::getInstance();
        $emailLookup->delete($recordId);
    }

    /**
     * To restore email lookup record
     * @param object $entityData
     * @param string $moduleName
     */
    public function handleEmailLookupRestoreEvent($entityData, $moduleName)
    {
        $recordId = $entityData->getId();
        //To get the record model of the restored record
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $fieldModels = $moduleInstance->getFieldsByType('email');
        $values['crmid'] = $recordId;
        $values['setype'] = $moduleName;

        foreach ($fieldModels as $field => $fieldModel) {
            $fieldName = $fieldModel->get('name');
            $fieldId = $fieldModel->get('id');
            $values[$fieldId] = $recordModel->get($fieldName);

            if ($values[$fieldId]) {
                $emailLookup = ITS4YouEmails_EmailLookup_Model::getInstance();
                $emailLookup->recieve($fieldId, $values);
            }
        }
    }

    /**
     * To save email lookup record
     * @param object $entityData
     * @param string $moduleName
     */
    public static function handleEmailLookupSaveEvent($entityData, $moduleName)
    {
        $emailSupportedModulesList = getEmailRelatedModules();

        if (in_array($moduleName, $emailSupportedModulesList) && $moduleName != 'Users') {
            $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
            $fieldModels = $moduleInstance->getFieldsByType('email');

            $data = $entityData->getData();

            $values['crmid'] = $entityData->getId();
            $values['setype'] = $moduleName;
            $isNew = $entityData->isNew();

            foreach ($fieldModels as $field => $fieldModel) {
                $fieldName = $fieldModel->get('name');
                $fieldId = $fieldModel->get('id');
                $values[$fieldId] = $data[$fieldName];

                $emailLookup = ITS4YouEmails_EmailLookup_Model::getInstance();

                if (!$isNew && !$values[$fieldId]) {
                    $emailLookup->delete($values['crmid'], $fieldId);
                } elseif ($values[$fieldId]) {
                    $emailLookup->recieve($fieldId, $values);
                }
            }
        }
    }

    /**
     * @param string $eventName
     * @param object $entityData
     * @return void
     */
    public function handleEvent($eventName, $entityData)
    {
        $moduleName = $entityData->getModuleName();

        if ($eventName == 'vtiger.entity.aftersave') {
            EmailLookupHandler::handleEmailLookupSaveEvent($entityData, $moduleName);
        }

        if ($eventName == 'vtiger.entity.afterdelete' || $eventName == 'vtiger.lead.convertlead') {
            $this->handleEmailLookupDeleteEvent($entityData);
        }

        if ($eventName == 'vtiger.entity.afterrestore') {
            $this->handleEmailLookupRestoreEvent($entityData, $moduleName);
        }
    }
}

class EmailLookupBatchHandler extends VTEventHandler
{

    /**
     * For handling email lookup events for import
     * @param string $eventName
     * @param object $entityDatas
     */
    public function handleEvent($eventName, $entityDatas)
    {
        foreach ($entityDatas as $entityData) {
            $moduleName = $entityData->getModuleName();

            if ($eventName == 'vtiger.batchevent.save') {
                EmailLookupHandler::handleEmailLookupSaveEvent($entityData, $moduleName);
            }
        }
    }
}
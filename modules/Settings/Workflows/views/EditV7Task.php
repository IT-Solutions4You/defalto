<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Workflows_EditV7Task_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $taskData = $request->get('taskData');

        $recordId = $request->get('task_id');
        $workflowId = $request->get('for_workflow');

        if ($workflowId) {
            $workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
            $selectedModule = $workflowModel->getModule();
            $selectedModuleName = $selectedModule->getName();
        } else {
            $selectedModuleName = $request->get('module_name');
            $selectedModule = Vtiger_Module_Model::getInstance($selectedModuleName);
            $workflowModel = Settings_Workflows_Record_Model::getCleanInstance($selectedModuleName);
        }

        $taskTypes = $workflowModel->getTaskTypes();
        if ($recordId) {
            $taskModel = Settings_Workflows_TaskRecord_Model::getInstance($recordId);
        } else {
            $taskType = $request->get('type');
            if (empty($taskType)) {
                $taskType = !empty($taskTypes[0]) ? $taskTypes[0]->getName() : 'VTEmailTask';
            }
            $taskModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $taskType);
            if (!empty($taskData)) {
                $taskModel->set('summary', $taskData['summary']);
                $taskModel->set('status', $taskData['status']);
                $taskModel->set('tmpTaskId', $taskData['tmpTaskId']);
                $taskModel->set('active', $taskData['active']);
                $tmpTaskObject = $taskModel->getTaskObject();
                foreach ($taskData as $key => $value) {
                    if (substr($key, -2) === '[]') {
                        $key = substr($key, 0, -2);

                        if (!is_array($value)) {
                            $value = [$value];
                        }
                    }

                    $tmpTaskObject->$key = $value;
                }
                $taskModel->setTaskObject($tmpTaskObject);
            }
        }

        $taskTypeModel = $taskModel->getTaskType();
        $viewer->assign('TASK_TYPE_MODEL', $taskTypeModel);

        $viewer->assign('TASK_TEMPLATE_PATH', $taskTypeModel->getTemplatePath());
        $recordStructureInstance = Settings_Workflows_RecordStructure_Model::getInstanceForWorkFlowModule(
            $workflowModel,
            Settings_Workflows_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDITTASK
        );
        $recordStructureInstance->setTaskRecordModel($taskModel);

        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

        $moduleModel = $workflowModel->getModule();
        $dateTimeFields = $moduleModel->getFieldsByType(['date', 'datetime']);

        $taskObject = $taskModel->getTaskObject();
        $taskType = get_class($taskObject);

        if ($taskType === 'VTCreateEntityTask') {
            if ($taskObject->entity_type && getTabid($taskObject->entity_type)) {
                $relationModuleModel = Vtiger_Module_Model::getInstance($taskObject->entity_type);
                $ownerFieldModels = $relationModuleModel->getFieldsByType('owner');

                $fieldMapping = Zend_Json::decode($taskObject->field_value_mapping);
                foreach ($fieldMapping as $key => $mappingInfo) {
                    if (array_key_exists($mappingInfo['fieldname'], $ownerFieldModels)) {
                        if (!empty($mappingInfo['value'])) {
                            if (is_numeric($mappingInfo['value'])) {
                                $userRecordModel = Users_Record_Model::getInstanceById($mappingInfo['value'], 'Users');
                            } else {
                                $userRecordModel = Users_Record_Model::getInstanceByName($mappingInfo['value']);
                            }
                        }

                        if ($userRecordModel) {
                            $ownerName = $userRecordModel->getId();
                        } elseif (!empty ($mappingInfo['value'])) {
                            $groupRecordModel = Settings_Groups_Record_Model::getInstance($mappingInfo['value']);
                            $ownerName = $groupRecordModel->getId();
                        }

                        if (!empty($mappingInfo['value'])) {
                            $fieldMapping[$key]['value'] = $ownerName;
                        }
                    }
                }
                $taskObject->field_value_mapping = json_encode($fieldMapping, JSON_HEX_APOS);
            }
        }
        if ($taskType === 'VTUpdateFieldsTask') {
            if ($moduleModel->getName() == "Documents") {
                $restrictFields = ['folderid', 'filename', 'filelocationtype'];
                $viewer->assign('RESTRICTFIELDS', $restrictFields);
            }
        }

        $viewer->assign('SOURCE_MODULE', $moduleModel->getName());
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('TASK_ID', $recordId);
        $viewer->assign('WORKFLOW_ID', $workflowId);
        $viewer->assign('DATETIME_FIELDS', $dateTimeFields);
        $viewer->assign('WORKFLOW_MODEL', $workflowModel);
        $viewer->assign('TASK_TYPES', $taskTypes);
        $viewer->assign('TASK_MODEL', $taskModel);
        $viewer->assign('CURRENTDATE', date('Y-n-j'));
        $metaVariables = Settings_Workflows_Module_Model::getMetaVariables();
        if ($moduleModel->getName() == 'Invoice' || $moduleModel->getName() == 'Quotes') {
            $metaVariables['Portal Pdf Url'] = '(general : (__VtigerMeta__) portalpdfurl)';
        }

        foreach ($metaVariables as $variableName => $variableValue) {
            if (strpos(strtolower($variableName), 'url') !== false) {
                $metaVariables[$variableName] = "<a href='$" . $variableValue . "'>" . vtranslate($variableName, $qualifiedModuleName) . '</a>';
            }
        }
        // Adding option Line Item block for Individual tax mode
        $individualTaxBlockLabel = vtranslate("LBL_LINEITEM_BLOCK_GROUP", $qualifiedModuleName);
        $individualTaxBlockValue = $viewer->view('LineItemsGroupTemplate.tpl', $qualifiedModuleName, $fetch = true);

        // Adding option Line Item block for group tax mode
        $groupTaxBlockLabel = vtranslate("LBL_LINEITEM_BLOCK_INDIVIDUAL", $qualifiedModuleName);
        $groupTaxBlockValue = $viewer->view('LineItemsIndividualTemplate.tpl', $qualifiedModuleName, $fetch = true);

        $templateVariables = [
            $individualTaxBlockValue => $individualTaxBlockLabel,
            $groupTaxBlockValue      => $groupTaxBlockLabel
        ];

        $viewer->assign('META_VARIABLES', $metaVariables);
        $viewer->assign('TEMPLATE_VARIABLES', $templateVariables);
        $viewer->assign('TASK_OBJECT', $taskObject);
        $viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
        $repeat_date = $taskModel->getTaskObject()->calendar_repeat_limit_date;
        if (!empty ($repeat_date)) {
            $repeat_date = Vtiger_Date_UIType::getDisplayDateValue($repeat_date);
        }
        $viewer->assign('REPEAT_DATE', $repeat_date);

        $userModel = Users_Record_Model::getCurrentUserModel();
        $viewer->assign('dateFormat', $userModel->get('date_format'));
        $viewer->assign('timeFormat', $userModel->get('hour_format'));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        $userList = $currentUser->getAccessibleUsers();
        $groupList = $currentUser->getAccessibleGroups();
        $assignedToValues = [];
        $assignedToValues[vtranslate('LBL_USERS', 'Vtiger')] = $userList;
        $assignedToValues[vtranslate('LBL_GROUPS', 'Vtiger')] = $groupList;

        $viewer->assign('ASSIGNED_TO', $assignedToValues);

        $fromEmailFields = $recordStructureInstance->getFromEmailFields();
        $emailFields = $recordStructureInstance->getEmailFields();
        $allFields = $recordStructureInstance->getAllFields();

        $viewer->assign('EMAIL_FIELDS', $emailFields);
        $viewer->assign('EMAIL_FIELD_OPTION', $recordStructureInstance->getHtmlOptions($emailFields));

        $viewer->assign('FROM_EMAIL_FIELDS', $fromEmailFields);
        $viewer->assign('FROM_EMAIL_FIELD_OPTION', $recordStructureInstance->getHtmlOptions($fromEmailFields));

        $viewer->assign('ALL_FIELDS', $allFields);
        $viewer->assign('ALL_FIELD_OPTIONS', $recordStructureInstance->getHtmlOptions($allFields));

        $memberGroups = Settings_Groups_Member_Model::getAll(false);

        if (false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany') && false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany')->isActive()) {
            $allCompany = ITS4YouMultiCompany_Module_Model::getCompaniesList('all');
            $viewer->assign('MULTICOMPANY4YOU', 1);

            foreach ($allCompany as $companyId => $company) {
                $type = 'MultiCompany4you';
                $qualifiedId = $type . ':' . $companyId;
                $member = new Vtiger_Base_Model();
                $memberGroups['MultiCompany4you'][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $company['companyname']);
            }
        }

        $viewer->assign('MEMBER_GROUPS', $memberGroups);

        if ('AddSharing' === $taskType || 'RemoveSharing' === $taskType) {
            $memberViewList = $memberEditList = [];

            if (is_array($taskObject->memberViewList)) {
                $memberViewList = array_flip($taskObject->memberViewList);
            }

            if (is_array($taskObject->memberEditList)) {
                $memberEditList = array_flip($taskObject->memberEditList);
            }

            $viewer->assign('memberViewList', $memberViewList);
            $viewer->assign('memberEditList', $memberEditList);
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('EditTask.tpl', $qualifiedModuleName);
    }
}
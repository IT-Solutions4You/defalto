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

class Settings_Workflows_CreateEntity_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

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

        $taskType = 'VTCreateEntityTask';
        $taskModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $taskType);

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

        $relatedModule = $request->get('relatedModule');
        $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);

        $workflowModuleModel = $workflowModel->getModule();

        $viewer->assign('WORKFLOW_MODEL', $workflowModel);
        $viewer->assign('REFERENCE_FIELD_NAME', $workflowModel->getReferenceFieldName($relatedModule));
        $viewer->assign('RELATED_MODULE_MODEL', $relatedModuleModel);
        $viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
        $viewer->assign('MODULE_MODEL', $workflowModuleModel);
        $viewer->assign('SOURCE_MODULE', $workflowModuleModel->getName());
        $viewer->assign('RELATED_MODULE_MODEL_NAME', '');
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('CreateEntity.tpl', $qualifiedModuleName);
    }
}
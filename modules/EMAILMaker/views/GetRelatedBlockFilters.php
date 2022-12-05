<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ******************************************************************************* */

class EMAILMaker_GetRelatedBlockFilters_View extends Vtiger_BasicAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $primaryModule = $request->get('primodule');
        $secondaryModules = $request->get('secmodule');
        $record = $request->get('record');
        $reportModel = Reports_Record_Model::getCleanInstance($record);
        $reportModel->setPrimaryModule($primaryModule);
        if (!empty($secondaryModules)) {
            $reportModel->setSecondaryModule($secondaryModules);
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
        $viewer->assign('PRIMARY_MODULE', $primaryModule);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($reportModel);
        $primaryModuleRecordStructure = $recordStructureInstance->getPrimaryModuleRecordStructure();
        $secondaryModuleRecordStructures = $recordStructureInstance->getSecondaryModuleRecordStructure();
        $viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
        $viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);
        $viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
        $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Vtiger_Field_Model::getAdvancedFilterOpsByFieldType());
        $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
        foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $module);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }
        $viewer->assign('DATE_FILTERS', $dateFilters);
        $viewer->view('BlockFilters.tpl', 'EMAILMaker');
    }
}
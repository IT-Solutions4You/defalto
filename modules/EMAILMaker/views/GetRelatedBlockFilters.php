<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_GetRelatedBlockFilters_View extends Vtiger_BasicAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $primaryModule = $request->get('primodule');
        $secondaryModules = $request->get('secmodule');
        $record = $request->get('record');
        $relatedBlockModel = new EMAILMaker_RelatedBlock_Model();
        $relatedBlockModel->setPrimaryModule($primaryModule);

        if (!empty($secondaryModules)) {
            $relatedBlockModel->setSecondaryModule($secondaryModules);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $relatedBlockModel->transformToNewAdvancedFilter());
        $viewer->assign('PRIMARY_MODULE', $primaryModule);
        $primaryModuleRecordStructure = $relatedBlockModel->getPrimaryModuleRecordStructure();
        $secondaryModuleRecordStructures = $relatedBlockModel->getSecondaryModuleRecordStructure();
        $viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
        $viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);
        $viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
        $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Vtiger_Field_Model::getAdvancedFilterOpsByFieldType());
        $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
        foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label']);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }
        $viewer->assign('DATE_FILTERS', $dateFilters);
        $viewer->view('BlockFilters.tpl', 'EMAILMaker');
    }
}
<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_EditDisplayConditions_View extends Vtiger_Index_View
{

    public function process(Vtiger_Request $request)
    {

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $templateid = "";
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();

        $isFilterSavedInNew = false;

        if ($request->has('record') && !$request->isEmpty('record')) {
            $templateid = $request->get('record');
            $emailtemplateResult = $EMAILMaker->GetEditViewData($templateid);
            $select_module = $emailtemplateResult["module"];
            $recordModel = EMAILMaker_Record_Model::getInstanceById($templateid, $moduleName);
        } else {
            $recordModel = EMAILMaker_Record_Model::getCleanInstance($moduleName);
        }

        $selectedModuleName = $select_module;
        $selectedModuleModel = Vtiger_Module_Model::getInstance($selectedModuleName);
        $recordStructureInstance = EMAILMaker_RecordStructure_Model::getInstanceForEMAILMakerModule($recordModel, EMAILMaker_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);

        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $recordStructure = $recordStructureInstance->getStructure();

        if (in_array($selectedModuleName, InventoryItem_Utils_Helper::getInventoryItemModules())) {
            $itemsBlock = "LBL_ITEM_DETAILS";
            unset($recordStructure[$itemsBlock]);
        }

        $viewer->assign('RECORDID', $templateid);
        $viewer->assign('RECORD_STRUCTURE', $recordStructure);

        $viewer->assign('MODULE_MODEL', $selectedModuleModel);
        $viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);

        $dateFilters = Vtiger_Field_Model::getDateFilterTypes();
        foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $qualifiedModuleName);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }

        $viewer->assign('DATE_FILTERS', $dateFilters);

        $viewer->assign('ADVANCED_FILTER_OPTIONS', EMAILMaker_Field_Model::getAdvancedFilterOptions());
        $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', EMAILMaker_Field_Model::getAdvancedFilterOpsByFieldType());
        $viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
        $viewer->assign('META_VARIABLES', Settings_Workflows_Module_Model::getMetaVariables());
        $viewer->assign('ADVANCE_CRITERIA', "");
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $userModel = Users_Record_Model::getCurrentUserModel();

        $viewer->assign('DATE_FORMAT', $userModel->get('date_format'));
        $viewer->assign('EMAIL_TEMPLATE_RESULT', $emailtemplateResult);
        if (!empty($templateid)) {
            $EMAILMaker_Display_Model = new EMAILMaker_Display_Model();
            $is_old_contition_format = $EMAILMaker_Display_Model->isOldContitionFormat(decode_html($emailtemplateResult["conditions"]));

            if (!$is_old_contition_format) {
                $viewer->assign('ADVANCE_CRITERIA', $EMAILMaker_Display_Model->transformToAdvancedFilterCondition($emailtemplateResult["conditions"]));
            } else {
                $viewer->assign('OLD_CONDITIONS', "yes");
            }
        }
        $viewer->assign('IS_FILTER_SAVED_NEW', $isFilterSavedInNew);
        $viewer->assign('EMAILMAKER_RECORD_MODEL', $recordModel);

        $viewer->view('EditDisplayConditions.tpl', $moduleName);

    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = array(
            "layouts.$layout.modules.Vtiger.resources.Edit",
            "layouts.$layout.modules.$moduleName.resources.Edit",
            "layouts.$layout.modules.$moduleName.resources.EditDisplayConditions",
            "layouts.$layout.modules.Vtiger.resources.AdvanceFilter",
            "layouts.$layout.modules.$moduleName.resources.AdvanceFilter",
            '~libraries/jquery/jquery.datepick.package-4.1.0/jquery.datepick.js',
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}

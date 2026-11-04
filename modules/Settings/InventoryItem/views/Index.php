<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_InventoryItem_Index_View extends Settings_Vtiger_Index_View
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedName = $request->getModule(false);
        $selectedModule = $request->get('selectedModule');

        if (empty($selectedModule)) {
            $selectedModule = 0;
        }

        $supportedModules = InventoryItem_Utils_Helper::getInventoryItemModules();
        $selectedFields = InventoryItem_Module_Model::getSelectedFields($selectedModule);
        $moduleModel = Vtiger_Module_Model::getInstance('InventoryItem');
        $fieldModelList = $moduleModel->getFields();
        $fieldModelList = array_filter($fieldModelList, static function ($fieldModel, $fieldName) {
            return !in_array($fieldName, InventoryItem_Field_Model::preventDisplay, true);
        }, ARRAY_FILTER_USE_BOTH);
        $selectedFields = array_values(array_filter($selectedFields, static function ($fieldName) {
            return !in_array($fieldName, InventoryItem_Field_Model::preventDisplay, true);
        }));
        $decimals = InventoryItem_Utils_Helper::fetchDecimals();
        $conversions = [];

        foreach (InventoryItem_Conversion_Model::getAllConversions() as $conversion) {
            $sourceModule = $conversion->get('source_module');
            $targetModule = $conversion->get('target_module');
            $headerFieldOptions = [];

            foreach (InventoryItem_Conversion_Model::getSelectableHeaderFields($targetModule) as $fieldName => $fieldModel) {
                $headerFieldOptions[$fieldName] = vtranslate($fieldModel->get('label'), $targetModule);
            }

            $conversions[] = [
                'id'                 => $conversion->getId(),
                'sourceLabel'        => vtranslate($sourceModule, $sourceModule),
                'targetLabel'        => vtranslate($targetModule, $targetModule),
                'enabled'            => $conversion->isActionEnabled(),
                'useModal'           => (bool)$conversion->get('use_modal'),
                'columns'            => $conversion->getEditableColumns(),
                'headerFields'       => $conversion->getHeaderFields(),
                'headerFieldOptions' => $headerFieldOptions,
            ];
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('QUALIFIED_NAME', $qualifiedName);
        $viewer->assign('SUPPORTED_MODULES', $supportedModules);
        $viewer->assign('SELECTED_MODULE', $selectedModule);
        $viewer->assign('FIELD_MODEL_LIST', $fieldModelList);
        $viewer->assign('SELECTED_FIELDS', $selectedFields);
        $viewer->assign('MANDATORY_FIELDS', ['productid', 'quantity']);
        $viewer->assign('DECIMALS', $decimals);
        $viewer->assign('CONVERSIONS', $conversions);
        $viewer->assign('CONVERSION_COLUMNS', InventoryItem_Conversion_Model::SUPPORTED_COLUMNS);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Index.tpl', $qualifiedName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $jsFileNames = [
            'modules.Vtiger.resources.Vtiger',
            'modules.Settings.Vtiger.resources.Vtiger',
            'modules.Settings.Vtiger.resources.Edit',
            "modules.Settings.$moduleName.resources.$moduleName",
            'modules.Settings.Vtiger.resources.Index',
            "modules.Settings.$moduleName.resources.Index",
            "~layouts/$layout/lib/jquery/Lightweight-jQuery-In-page-Filtering-Plugin-instaFilta/instafilta.min.js",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
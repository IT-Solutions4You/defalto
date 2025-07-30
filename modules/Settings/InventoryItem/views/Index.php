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
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedName = $request->getModule(false);
        $selectedModule = $request->get('selectedModule');

        if (empty($selectedModule)) {
            $selectedModule = 0;
        }

        $supportedModules = Settings_InventoryItem_Module_Model::getSupportedModules();
        $selectedFields = InventoryItem_Module_Model::getSelectedFields($selectedModule);
        $moduleModel = Vtiger_Module_Model::getInstance('InventoryItem');
        $fieldModelList = $moduleModel->getFields();
        $decimals = InventoryItem_Utils_Helper::fetchDecimals();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('QUALIFIED_NAME', $qualifiedName);
        $viewer->assign('SUPPORTED_MODULES', $supportedModules);
        $viewer->assign('SELECTED_MODULE', $selectedModule);
        $viewer->assign('FIELD_MODEL_LIST', $fieldModelList);
        $viewer->assign('SELECTED_FIELDS', $selectedFields);
        $viewer->assign('MANDATORY_FIELDS', ['productid', 'quantity']);
        $viewer->assign('DECIMALS', $decimals);

        $viewer->view('Index.tpl', $qualifiedName);
    }

    /**
     * @inheritDoc
     */
    function getHeaderScripts(Vtiger_Request $request)
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
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
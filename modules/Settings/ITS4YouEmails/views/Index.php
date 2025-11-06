<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_ITS4YouEmails_Index_View extends Settings_Vtiger_Index_View
{
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $settingLinks = [];

        foreach ($moduleModel->getSettingLinks() as $settingsLink) {
            $settingsLink['linklabel'] = sprintf(vtranslate($settingsLink['linklabel'], $moduleName), vtranslate($moduleName, $moduleName));
            $settingLinks['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('LISTVIEW_LINKS', $settingLinks);

        parent::preProcess($request, false);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('SUPPORTED_MODULES', ITS4YouEmails_Integration_Model::getSupportedModules());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Index.tpl', $qualifiedModule);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        unset($headerScriptInstances['modules.Vtiger.resources.Edit']);
        unset($headerScriptInstances["modules.Settings.Vtiger.resources.Edit"]);
        unset($headerScriptInstances['modules.Inventory.resources.Edit']);
        unset($headerScriptInstances["modules.$moduleName.resources.Edit"]);
        unset($headerScriptInstances["modules.Settings.$moduleName.resources.Edit"]);

        $jsFileNames = [
            "modules.Settings.$moduleName.resources.Index",
        ];

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }

    /**
     * @inheritDoc
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $moduleName = $request->getModule();
        $headerCssInstances = parent::getHeaderCss($request);
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $cssFileNames = [
            '~/layouts/' . $layout . '/skins/marketing/style.css',
            '~/layouts/' . $layout . '/modules/Settings/' . $moduleName . '/resources/Index.css',
        ];

        return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
    }
}
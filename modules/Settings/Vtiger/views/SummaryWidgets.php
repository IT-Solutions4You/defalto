<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Vtiger_SummaryWidgets_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $supportedModules = Settings_Vtiger_SummaryWidgets_Model::getSupportedModules();
        $sourceModuleName = $request->get('sourceModule');

        if (!$sourceModuleName || !isset($supportedModules[$sourceModuleName])) {
            $sourceModuleName = array_key_first($supportedModules);
        }

        if (!$sourceModuleName) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        $configuration = Settings_Vtiger_SummaryWidgets_Model::getInstance($sourceModuleName)->getConfiguration();
        $viewer = $this->getViewer($request);
        $viewer->assign('SUPPORTED_MODULES', $supportedModules);
        $viewer->assign('SOURCE_MODULE', $sourceModuleName);
        $viewer->assign('LEFT_WIDGETS', $configuration['left']);
        $viewer->assign('RIGHT_WIDGETS', $configuration['right']);
        $viewer->assign('AVAILABLE_WIDGETS', $configuration['available']);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('SummaryWidgets.tpl', $qualifiedModuleName);
    }

    public function getPageTitle(Vtiger_Request $request): string
    {
        return vtranslate('LBL_SUMMARY_WIDGET_EDITOR', $request->getModule(false));
    }

    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $jsFileNames = [
            'modules.Settings.Vtiger.resources.SummaryWidgets',
        ];

        return array_merge(
            parent::getHeaderScripts($request),
            $this->checkAndConvertJsScripts($jsFileNames)
        );
    }

    public function getHeaderCss(Vtiger_Request $request): array
    {
        $cssFileNames = [
            '~/layouts/' . Vtiger_Viewer::getDefaultLayoutName() . '/modules/Settings/Vtiger/resources/SummaryWidgets.css',
        ];

        return array_merge(
            parent::getHeaderCss($request),
            $this->checkAndConvertCssStyles($cssFileNames)
        );
    }
}

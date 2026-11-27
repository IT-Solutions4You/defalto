<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_LinkSettings_View extends Settings_Vtiger_Index_View
{
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_LINK_SETTINGS');

        if ($menuItem) {
            $request->set('block', $menuItem->get('blockid'));
            $request->set('fieldid', $menuItem->getId());
        }

        parent::preProcess($request, $display);
    }

    public function process(Vtiger_Request $request)
    {
        $qualifiedModule = $request->getModule(false);
        $supportedModules = Settings_LayoutEditor_Module_Model::getSupportedModules();
        $supportedModules = array_flip($supportedModules);
        ksort($supportedModules);
        $sourceModule = (string)$request->get('sourceModule');

        if (!$sourceModule) {
            $sourceModule = (string)reset($supportedModules);
        }

        $model = new Settings_LayoutEditor_LinkSettings_Model();
        $links = $model->getLinks($sourceModule);
        $viewer = $this->getViewer($request);
        $viewer->assign('SUPPORTED_MODULES', $supportedModules)
            ->assign('SOURCE_MODULE', $sourceModule)
            ->assign('LINK_SECTIONS', $model->getLinkSections($links))
            ->assign('HAS_MODULE_LINKS', !empty($links))
            ->assign('QUALIFIED_MODULE', $qualifiedModule);

        $template = $request->isAjax() && $request->get('contentsOnly') ? 'LinkSettings.tpl' : 'LinkSettingsPage.tpl';
        $viewer->view($template, $qualifiedModule);
    }

    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(
            parent::getHeaderScripts($request),
            $this->checkAndConvertJsScripts(['modules.Settings.LayoutEditor.resources.LinkSettings'])
        );
    }

    public function getPageTitle(Vtiger_Request $request): string
    {
        return vtranslate('LBL_LINK_SETTINGS', 'Core');
    }
}
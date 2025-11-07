<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Appointments_Integration_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $qualifiedModule = $request->getModule(false);
        $module = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $module);
        $viewer->assign('SUPPORTED_MODULES', Settings_Appointments_Integration_Model::getModules());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Integration.tpl', $qualifiedModule);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $cssFileNames = [
            implode(
                '/',
                array_filter(['~layouts', Vtiger_Viewer::getDefaultLayoutName(), 'modules', $request->get('parent'), $request->get('module'), 'resources', $request->get('view')])
            ) . '.css',
        ];

        return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $jsFileNames = [
            implode('.', array_filter(['layouts', $layout, 'modules', $request->get('parent'), $request->get('module'), 'resources', $request->get('view')])),
        ];

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }
}
<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

        $viewer->view('Integration.tpl', $qualifiedModule);
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $cssFileNames = array(
            implode('/', array_filter(['~layouts', Vtiger_Viewer::getDefaultLayoutName(), 'modules', $request->get('parent'), $request->get('module'), 'resources', $request->get('view')])) . '.css',
        );

        return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $jsFileNames = array(
            implode('.', array_filter(['layouts', $layout, 'modules', $request->get('parent'), $request->get('module'), 'resources', $request->get('view')])),
        );

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }
}
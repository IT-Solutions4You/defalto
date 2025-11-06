<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Vtiger_Country_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $qualifiedModule = $request->getModule(false);

        $countryModel = Core_Country_Model::getInstance();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('COUNTRIES', $countryModel->getCountries());
        $viewer->assign('TITLE', 'LBL_COUNTRIES');
        $viewer->assign('DESCRIPTION', 'LBL_COUNTRIES_INTEGRATION');

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Country.tpl', $qualifiedModule);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $viewName = $request->get('view');
        $jsFileNames = [
            "modules.Settings.$moduleName.resources.$viewName",
        ];

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }
}
<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Settings_Vtiger_Country_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $qualifiedModule = $request->getModule(false);

        $countryModel = Vtiger_Country_Model::getInstance();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('COUNTRIES', $countryModel->getCountries());
        $viewer->assign('TITLE', 'LBL_COUNTRIES');
        $viewer->assign('DESCRIPTION', 'LBL_COUNTRIES_INTEGRATION');
        $viewer->view('Country.tpl', $qualifiedModule);
    }

    public function getHeaderScripts(Vtiger_Request $request)
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
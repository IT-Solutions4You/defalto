<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Country_List_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $qualifiedModule = $request->getModule(false);

        $countryModel = Core_Country_Model::getInstance();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $dataModel = Settings_PostalCodes_Record_Model::getInstance();

        $viewer->assign('COUNTRIES', $countryModel->getCountries());
        $viewer->assign('POSTAL_META', $dataModel->getMeta());
        $viewer->assign('IMPORTED_CODES', $dataModel->getImportedCountryCodes());
        $viewer->assign('ADDRESS_MODULES', Settings_Country_AddressMap_Model::getInstance()->getAddressModules());
        $viewer->assign('TITLE', 'LBL_COUNTRIES');
        $viewer->assign('DESCRIPTION', 'LBL_COUNTRIES_INTEGRATION');

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('List.tpl', $qualifiedModule);
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

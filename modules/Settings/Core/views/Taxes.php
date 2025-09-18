<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Core_Taxes_View extends Settings_Vtiger_Index_View
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function editRegion(Vtiger_Request $request): void
    {
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('TAX_REGION_MODEL', Core_TaxRegion_Model::getInstanceFromRequest($request));

        $viewer->view('TaxesRegionsEdit.tpl', $qualifiedModule);
    }

    /**
     * @param Vtiger_Request $request
     * @return string
     */
    public function getPageTitle(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if ('regions' === $mode) {
            return vtranslate('LBL_REGIONS');
        }

        return vtranslate('LBL_TAXES');
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function editTax(Vtiger_Request $request): void
    {
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('TAX_RECORD_MODEL', Core_Tax_Model::getInstanceFromRequest($request));
        $viewer->assign('TAX_REGIONS', Core_TaxRegion_Model::getAllRegions());
        $viewer->assign('SIMPLE_TAX_MODELS_LIST', Core_Tax_Model::getSimpleTaxes());

        $viewer->view('TaxesEdit.tpl', $qualifiedModule);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = [
            'layouts.' . $layout . '.modules.Settings.Core.resources.Taxes',
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        } else {
            $this->taxes($request);
        }
    }

    /**
     * @throws Exception
     */
    public function regions(Vtiger_Request $request): void
    {
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('REGION_RECORDS', Core_TaxRegion_Model::getAllRegions());
        $viewer->view('TaxesRegions.tpl', $qualifiedModule);
    }

    /**
     * @param $request
     * @param $moduleModel
     *
     * @return void
     */
    public function setModuleInfo($request, $moduleModel)
    {
        $basicLinks = [];
        $isRegion = 'regions' === $request->get('mode');

        if ($isRegion) {
            $basicLinks[] = Vtiger_Link_Model::getInstanceFromValues([
                'linktype'    => 'BASIC',
                'linklabel'   => 'LBL_ADD_REGION',
                'linkurl'     => 'javascript:Settings_Vtiger_Taxes_Js.editRegion()',
                'linkicon'    => 'fa-plus',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ]);
        } else {
            $basicLinks[] = Vtiger_Link_Model::getInstanceFromValues([
                'linktype'    => 'BASIC',
                'linklabel'   => 'LBL_ADD_TAX',
                'linkurl'     => 'javascript:Settings_Vtiger_Taxes_Js.editTax()',
                'linkicon'    => 'fa-plus',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ]);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_BASIC_ACTIONS', $basicLinks);
        $viewer->assign('ACTIVE_BLOCK', ['block' => 'LBL_TAX_MANAGEMENT', 'menu' => $isRegion ? 'LBL_REGIONS' : 'LBL_TAXES']);
    }

    public function taxes(Vtiger_Request $request): void
    {
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('TAX_RECORDS', Core_Tax_Model::getAllTaxes());
        $viewer->view('Taxes.tpl', $qualifiedModule);
    }

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('taxes');
        $this->exposeMethod('regions');
        $this->exposeMethod('editTax');
        $this->exposeMethod('editRegion');
    }
}
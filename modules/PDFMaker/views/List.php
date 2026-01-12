<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_List_View extends Vtiger_Index_View
{
    protected $listViewLinks = false;

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getList');
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        parent::preProcess($request, false);

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();

        if (!empty($moduleName)) {
            $moduleModel = new PDFMaker_PDFMaker_Model('PDFMaker');
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
            $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
            $viewer->assign('MODULE', $moduleName);

            if (!$permission) {
                throw new Exception('LBL_PERMISSION_DENIED');
            }

            $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
            $linkModels = $moduleModel->getSideBarLinks($linkParams);

            $viewer->assign('QUICK_LINKS', $linkModels);
        }

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    /**
     * @inheritDoc
     */
    protected function preProcessTplName(Vtiger_Request $request): string
    {
        return 'ListViewPreProcess.tpl';
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        $viewer = $this->getViewer($request);
        $viewer->view('IndexPostProcess.tpl');

        parent::postProcess($request);
    }

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);

        $qualifiedModuleName = $request->getModule(false);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('URL', vglobal('site_URL'));

        $this->invokeExposedMethod('getList', $request);
    }

    public function getList(Vtiger_Request $request)
    {
        $searchParams = $request->get('search_params');
        $moduleName = $request->getModule();

        PDFMaker_Debugger_Model::GetInstance()->Init();
        $current_user = Users_Record_Model::getCurrentUserModel();
        $PDFMakerModel = Vtiger_Module_Model::getInstance($moduleName);

        $viewer = $this->getViewer($request);

        $return_data = $PDFMakerModel->GetListviewData();
        $viewer->assign('PDFTEMPLATES', $return_data);
        $category = getParentTab();
        $viewer->assign('PARENTTAB', $category);
        $viewer->assign('CATEGORY', $category);

        if ($current_user->isAdminUser()) {
            $viewer->assign('IS_ADMIN', '1');
        }

        $viewer->assign('MAIN_PRODUCT_SUPPORT', '');
        $viewer->assign('MAIN_PRODUCT_WHITELABEL', '');
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('SEARCH_DETAILS', $searchParams);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ListPDFTemplatesContents.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
            'layouts.v7.modules.PDFMaker.resources.FreeInstall',
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
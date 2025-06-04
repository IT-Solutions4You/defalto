<?php

class Installer_Index_View extends Vtiger_Index_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $viewer = $this->getViewer($request);
        $viewer->assign('LISTVIEW_LINKS', $moduleModel->getListViewLinks());

        parent::preProcess($request, $display);
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('uninstall');
        $this->exposeMethod('installer');
        $this->exposeMethod('license');

        $this->invokeExposedMethod($request->get('mode', 'installer'), $request);
    }

    /**
     * @throws AppException
     */
    public function installer(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->view('Index.tpl', $moduleName);
    }

    /**
     * @throws AppException
     */
    public function license(Vtiger_Request $request): void
    {
        $moduleName = $request->get('sourceModule');
        $extensionModel = Installer_ExtensionInstall_Model::getInstance($moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('TEMPLATE', 'License.tpl');
        $viewer->assign('EXTENSION_MODEL', $extensionModel);
        $viewer->assign('MODULE_MODEL', $extensionModel->getModule());

        $this->installer($request);
    }

    /**
     * @throws AppException
     */
    public function uninstall(Vtiger_Request $request): void
    {
        $moduleName = $request->get('sourceModule');
        $extensionModel = Installer_ExtensionInstall_Model::getInstance($moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('TEMPLATE', 'Uninstall.tpl');
        $viewer->assign('EXTENSION_MODEL', $extensionModel);
        $viewer->assign('MODULE_MODEL', $extensionModel->getModule());

        $this->installer($request);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = [
            "modules.$moduleName.resources.Index",
        ];

        return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
    }
}
<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_Install_View extends Vtiger_Basic_View
{
    public function buttons(Vtiger_Request $request): void
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('DEFAULT_LAYOUT', Vtiger_Viewer::getDefaultLayoutName());
        $viewer->assign('INSTALL_MODE', $request->getMode());
        $viewer->assign('LANGUAGE', Vtiger_Language_Handler::getLanguage());
        $viewer->assign('PAGETITLE', vtranslate('LBL_INSTALL_WIZARD', 'Core'));
        $viewer->view('InstallView.tpl', 'Core');
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if (!$currentUser || !$currentUser->isAdminUser()) {
            throw new Exception('Required admin user');
        }

        $class = $request->getModule() . '_Install_Model';

        if (!class_exists($class)) {
            throw new Exception('Installation not supported. Class not found: ' . $class);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->buttons($request);
        $this->processInstallMode($request);
        $this->finished($request);
        $this->showInstallFooter($request);
    }

    /**
     * Executes the requested installation operation without rendering the view.
     *
     * @throws Exception
     */
    public function processInstallMode(Vtiger_Request $request): void
    {
        $mode = $request->getMode();
        $this->exposeMethod('install');
        $this->exposeMethod('update');
        $this->exposeMethod('migrate');
        $this->exposeMethod('delete');

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            error_reporting(E_ALL);
            PearDatabase::getInstance()->setDebug(true);
            PearDatabase::getInstance()->setDieOnError(true);
            vglobal('debug', true);

            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function finished(Vtiger_Request $request): void
    {
        Core_Install_Model::logSuccess('Finished: ' . var_export($request->getAll(), true));
    }

    public function showInstallFooter(Vtiger_Request $request): void
    {
        $viewer = $this->getViewer($request);
        $viewer->view('InstallViewFooter.tpl', 'Core');
    }

    /**
     * @throws Exception
     */
    public function delete(Vtiger_Request $request): void
    {
        Core_Install_Model::getInstance('module.preuninstall', $request->getModule())->deleteModule();
        Core_Install_Model::updateModuleMetaFiles();
    }

    /**
     * @throws Exception
     */
    public function install(Vtiger_Request $request): void
    {
        Core_Install_Model::getInstance('module.postinstall', $request->getModule())->installModule();
        Core_Install_Model::updateModuleMetaFiles();
    }

    /**
     * @throws Exception
     */
    public function update(Vtiger_Request $request): void
    {
        define('VTIGER_UPGRADE', true);

        Core_Install_Model::getInstance('module.postupdate', $request->getModule())->installModule();
        Core_Install_Model::updateModuleMetaFiles();
    }

    /**
     * @throws Exception
     */
    public function migrate(Vtiger_Request $request): void
    {
        Core_Install_Model::getInstance('module.postinstall', $request->getModule())->migrate();
    }
}

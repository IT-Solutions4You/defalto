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
        $viewer->view('InstallView.tpl', 'Install');
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     */
    public function checkPermission(Vtiger_Request $request): void
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if (!$currentUser || !$currentUser->isAdminUser()) {
            throw new AppException('Required admin user');
        }
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
    }

    public function postProcess(Vtiger_Request $request)
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        $this->exposeMethod('install');
        $this->exposeMethod('migrate');
        $this->exposeMethod('delete');

        $this->buttons($request);

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            error_reporting(E_ALL);
            PearDatabase::getInstance()->setDebug(true);
            PearDatabase::getInstance()->setDieOnError(true);
            vglobal('debug', true);

            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @throws AppException
     */
    public function delete(Vtiger_Request $request): void
    {
        Core_Install_Model::getInstance('module.preuninstall', $request->getModule())->deleteModule();
        Core_Install_Model::updateModuleMetaFiles();
    }

    /**
     * @throws AppException
     */
    public function install(Vtiger_Request $request): void
    {
        Core_Install_Model::getInstance('module.postinstall', $request->getModule())->installModule();
        Core_Install_Model::updateModuleMetaFiles();
    }

    /**
     * @throws AppException
     */
    public function migrate(Vtiger_Request $request): void
    {
        Core_Install_Model::getInstance('module.postinstall', $request->getModule())->migrate();
    }
}
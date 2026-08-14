<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_InstallManager_View extends Vtiger_Footer_View
{
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if (!$currentUser || !$currentUser->isAdminUser()) {
            throw new Exception('Required admin user');
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $installOutput = '';
        $installModules = $this->getInstallModules();

        if ($request->getMode()) {
            $installOutput = $this->processInstallMode($request);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULES', $installModules);
        $viewer->assign('INSTALL_MODE', $request->getMode());
        $viewer->assign('INSTALL_MODULE', $request->getModule());
        $viewer->assign('INSTALL_OUTPUT', $installOutput);
        $viewer->view('InstallManager.tpl', $request->getModule());
    }

    /**
     * Runs the existing Install view operation and captures its formatted log.
     *
     * @throws Throwable
     */
    protected function processInstallMode(Vtiger_Request $request): string
    {
        $db = PearDatabase::getInstance();
        $databaseDebug = $db->database->debug ?? false;
        $dieOnError = $db->dieOnError;
        $globalDebug = vglobal('debug');
        $errorReporting = error_reporting();
        $bufferLevel = ob_get_level();

        ob_start();

        try {
            $installView = new Vtiger_Install_View();
            $installView->checkPermission($request);
            $installView->processInstallMode($request);
            $installView->finished($request);
            $output = (string)ob_get_clean();
        } catch (Throwable $throwable) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            throw $throwable;
        } finally {
            error_reporting($errorReporting);
            $db->setDebug($databaseDebug);
            $db->setDieOnError($dieOnError);
            vglobal('debug', $globalDebug);
        }

        return vtlib_purify($output);
    }

    public function getInstallModules(): array
    {
        $modules = Vtiger_Module_Model::getAll();
        $installModules = [];

        foreach ($modules as $module) {
            if (class_exists($module->getName() . '_Install_Model')) {
                $installModules[$module->getName()] = $module;
            }
        }

        return $installModules;
    }

    /**
     * @inheritDoc
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $cssFileNames = [
            "~layouts/$layout/modules/Core/resources/Install.css",
        ];

        return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($cssFileNames));
    }
}

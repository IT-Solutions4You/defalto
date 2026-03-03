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
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULES', $this->getInstallModules());
        $viewer->assign('INSTALL_MODE', $request->getMode());
        $viewer->assign('INSTALL_MODULE', $request->getModule());
        $viewer->view('InstallManager.tpl', $request->getModule());
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
}
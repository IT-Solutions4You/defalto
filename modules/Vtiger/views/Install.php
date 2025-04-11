<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Vtiger_Install_View extends Vtiger_Index_View
{
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

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function postProcess(Vtiger_Request $request)
    {
    }

    /**
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, $display = true): void
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     */
    public function process(Vtiger_Request $request)
    {
        error_reporting(E_ALL);

        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);
        $adb->setDieOnError(true);

        $mode = $request->get('mode');

        if (empty($mode)) {
            throw new AppException('Required parameter "mode" in request "migrate, delete, install"');
        }

        $moduleName = $request->getModule();

        switch ($mode) {
            case 'install':
                Core_Install_Model::getInstance('module.postinstall', $moduleName)->installModule();
                break;
            case 'migrate':
                Core_Install_Model::getInstance('module.postinstall', $moduleName)->migrate();
                break;
            case 'delete':
                Core_Install_Model::getInstance('module.preuninstall', $moduleName)->deleteModule();
                break;
        }

        Core_Install_Model::updateModuleMetaFiles();
    }
}
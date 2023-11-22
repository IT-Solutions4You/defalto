<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Appointments_Install_View extends Vtiger_Index_View
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
     */
    public function process(Vtiger_Request $request)
    {
        error_reporting(E_ALL);

        $adb = PearDatabase::getInstance();
        $adb->setDebug(true);
        $adb->setDieOnError(true);

        $mode = $request->get('mode');

        if ('migrate' === $mode) {
            Appointments_Migration_Model::getInstance()->migrate();
        } elseif ('delete' === $mode) {
            Appointments_Install_Model::getInstance('module.preuninstall', 'Appointments')->deleteModule();
        } elseif ('install' === $mode) {
            Appointments_Install_Model::getInstance('module.postinstall', 'Appointments')->installModule();
        } else {
            throw new AppException('Required parameter "mode" in request "migrate, delete, install"');
        }
    }
}
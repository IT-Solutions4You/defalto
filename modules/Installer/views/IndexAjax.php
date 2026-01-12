<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_IndexAjax_View extends Vtiger_BasicAjax_View
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('systemModal');
        $this->exposeMethod('systemProgress');
        $this->exposeMethod('extensionModal');
        $this->exposeMethod('extensionProgress');
        $this->exposeMethod('extensionUninstall');
        $this->exposeMethod('licenseModal');
        $this->exposeMethod('updateInformation');
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            return $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function systemModal(Vtiger_Request $request): void
    {
        $version = $request->get('version');
        $qualifiedModule = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('SYSTEM_INSTALL', Installer_SystemInstall_Model::getInstance($version));
        $viewer->view('SystemModal.tpl', $qualifiedModule);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function extensionModal(Vtiger_Request $request): void
    {
        $version = $request->get('version');

        $viewer = $this->getViewer($request);
        $viewer->assign('EXTENSION_INSTALL', Installer_ExtensionInstall_Model::getInstance($version));
        $viewer->view('ExtensionModal.tpl', $request->getModule());
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function licenseModal(Vtiger_Request $request): void
    {
        $id = (int)$request->get('license_id');
        $name = $request->get('license_name');

        if ($id) {
            $licenseModel = Installer_License_Model::getInstanceById($id);
        } else {
            $licenseModel = Installer_License_Model::getInstance($name);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('LICENSE_MODEL', $licenseModel);
        $viewer->view('LicenseModal.tpl', $request->getModule());
    }

    /**
     * @throws Exception
     */
    public function systemProgress(Vtiger_Request $request): void
    {
        vglobal('debug', true);

        $licenses = Installer_License_Model::getAll(Installer_License_Model::EXTENSION_PACKAGE);

        if (!empty($licenses)) {
            foreach ($licenses as $license) {
                $extensions = $license->getInfo('extensions');

                if (!empty($extensions)) {
                    foreach ($extensions as $extension) {
                        Installer_ZipArchive_Model::$skipFiles[] = $extension . '.php';
                        Installer_ZipArchive_Model::$skipFolders[] = 'modules/' . $extension;
                        Installer_ZipArchive_Model::$skipFolders[] = 'modules/Settings/' . $extension;
                        Installer_ZipArchive_Model::$skipFolders[] = 'layouts/d1/modules/' . $extension;
                        Installer_ZipArchive_Model::$skipFolders[] = 'layouts/d1/modules/Settings/' . $extension;
                    }
                }
            }
        }

        $version = (string)$request->get('version');
        $install = Installer_SystemInstall_Model::getInstance($version);
        $download = Installer_Download_Model::getInstance($install->get('download-url'), $install->get('download-folder'));
        $download->downloadAndExport();

        (new Migration_Index_View())->applyDBChanges();

        Core_Install_Model::logSuccess('System install finished');
    }

    /**
     * @throws Exception
     */
    public function extensionProgress(Vtiger_Request $request): void
    {
        vglobal('debug', true);

        $version = $request->get('version');

        Core_Install_Model::logSuccess($version);

        $install = Installer_ExtensionInstall_Model::getInstance($version);
        $download = Installer_Download_Model::getInstance($install->get('download-url'), $install->get('download-folder'));
        $download->downloadAndExport();

        $installClass = $version . '_Install_Model';

        if (class_exists($installClass)) {
            $installModel = Core_Install_Model::getInstance('module.postupdate', $version);
        }

        if (isset($installModel)) {
            Core_Install_Model::logSuccess('Install model');
            $installModel->installModule();
        } else {
            Core_Install_Model::logError('Missing install model' . $version);
        }

        Core_Install_Model::logSuccess('Module install finished');
    }

    public function extensionUninstall(Vtiger_Request $request): void
    {
        if ('Yes' === $request->get('confirmed')) {
            header('location:index.php?module=Installer&view=Index');
        } else {
            header('location:' . $_SERVER['HTTP_REFERER']);
        }
    }

    /**
     * @throws Exception
     */
    public function updateInformation(Vtiger_Request $request): void
    {
        Installer_License_Model::updateAll();
        Installer_ExtensionInstall_Model::clearCache();
        Installer_SystemInstall_Model::clearCache();

        header('location:index.php?module=Installer&view=Index');
    }
}
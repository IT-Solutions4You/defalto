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
    public function validateRequest(Vtiger_Request $request): bool
    {
        if ('extensionUninstall' === $request->getMode()) {
            return $request->validateWriteAccess();
        }

        return parent::validateRequest($request);
    }

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
        $this->exposeMethod('licenseCheckModal');
        $this->exposeMethod('licenseCheckProgress');
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

        if (!$licenseModel) {
            $licenseModel = Installer_License_Model::getInstance('');
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('LICENSE_MODEL', $licenseModel);
        $viewer->view('LicenseModal.tpl', $request->getModule());
    }

    /**
     * @throws Exception
     */
    public function licenseCheckModal(Vtiger_Request $request): void
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('LICENSE_MODELS', Installer_License_Model::getAll());
        $viewer->view('LicenseCheckModal.tpl', $request->getModule());
    }

    /**
     * @throws Exception
     */
    public function licenseCheckProgress(Vtiger_Request $request): void
    {
        vglobal('debug', true);

        Core_Install_Model::logInfo(vtranslate('LBL_LICENSE_CHECK_STARTED', 'Installer'));

        Installer_License_Model::updateAll();
        Installer_ExtensionInstall_Model::clearCache();
        Installer_SystemInstall_Model::clearCache();
        Installer_Notification_Model::updateAll();

        $licenses = Installer_License_Model::getAll();
        $hasValidLicense = false;

        foreach ($licenses as $license) {
            $licenseLog = json_encode(
                $license->getLogData(),
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_INVALID_UTF8_SUBSTITUTE
            );
            Core_Install_Model::logInfo(
                vtranslate('LBL_LICENSE_DATA', 'Installer') . ': ' . $license->getItemName()
            );
            Core_Install_Model::logInfo($licenseLog);

            if ($license->isValidLicense()) {
                $hasValidLicense = true;
                Core_Install_Model::logSuccess(vtranslate('LBL_LICENSE_ACTIVE', 'Installer'));
            } else {
                Core_Install_Model::logError(
                    $license->getErrorMessage() ?: vtranslate('LBL_LICENSE_INACTIVE', 'Installer')
                );
            }
        }

        if (!$hasValidLicense) {
            Core_Install_Model::logError(vtranslate('LBL_LICENSE_INACTIVE', 'Installer'));
        }

        Core_Install_Model::logInfo(vtranslate('LBL_LICENSE_CHECK_FINISHED', 'Installer'));
    }

    /**
     * @throws Exception
     */
    public function systemProgress(Vtiger_Request $request): void
    {
        vglobal('debug', true);

        $license = Installer_License_Model::getMembershipLicense();

        if (!$license) {
            throw new Exception(vtranslate('LBL_LICENSE_INACTIVE', 'Installer'));
        }

        $license->check(true);

        if (!$license->isValidLicense()) {
            throw new Exception($license->getErrorMessage() ?: vtranslate('LBL_LICENSE_INACTIVE', 'Installer'));
        }

        foreach ((array)$license->getInfo('extensions') as $extension) {
            if ($extension) {
                Installer_ZipArchive_Model::$skipFiles[] = $extension . '.php';
                Installer_ZipArchive_Model::$skipFolders[] = 'modules/' . $extension;
                Installer_ZipArchive_Model::$skipFolders[] = 'modules/Settings/' . $extension;
                Installer_ZipArchive_Model::$skipFolders[] = 'layouts/d1/modules/' . $extension;
                Installer_ZipArchive_Model::$skipFolders[] = 'layouts/d1/modules/Settings/' . $extension;
            }
        }

        $version = (string)$request->get('version');
        $install = Installer_SystemInstall_Model::getInstance($version);

        if (!$install->hasDownloadUrl()) {
            throw new Exception(vtranslate('LBL_LICENSE_DOWNLOAD_UNAVAILABLE', 'Installer'));
        }

        $download = Installer_Download_Model::getInstance(
            $install->get('download-url'),
            $install->get('download-folder'),
            'index.php',
            'system-' . $version
        );
        $checksum = trim((string)($install->get('sha256') ?: $install->get('checksum')));

        if (str_starts_with(strtolower($checksum), 'sha256:')) {
            $checksum = trim(substr($checksum, 7));
        }

        if ($checksum !== '') {
            $download->setExpectedChecksum($checksum);
        }

        try {
            $download->downloadAndExport(true);
            (new Migration_Index_View())->applyDBChanges();
            $download->commit();
            Core_Install_Model::logSuccess('System install finished');
        } catch (Throwable $throwable) {
            $download->rollback();

            throw $throwable;
        }
    }

    /**
     * @throws Exception
     */
    public function extensionProgress(Vtiger_Request $request): void
    {
        vglobal('debug', true);

        $version = (string)$request->get('version');
        Installer_ExtensionInstall_Model::validateModuleName($version);

        Core_Install_Model::logSuccess(htmlspecialchars($version, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

        $install = Installer_ExtensionInstall_Model::getInstance($version);
        $license = $install->getLicense() ?: Installer_License_Model::getLicenseForExtension($version);

        if (!$license) {
            throw new Exception(vtranslate('LBL_LICENSE_INACTIVE', 'Installer'));
        }

        $license->check(true);

        if (!$license->isValidLicense()) {
            throw new Exception($license->getErrorMessage() ?: vtranslate('LBL_LICENSE_INACTIVE', 'Installer'));
        }

        $install = Installer_ExtensionInstall_Model::getInstance($version);

        if (!$install->hasDownloadUrl()) {
            throw new Exception(vtranslate('LBL_LICENSE_DOWNLOAD_UNAVAILABLE', 'Installer'));
        }

        $module = $install->installPackage();
        $defaultUrl = htmlspecialchars($module->getDefaultUrl(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        Core_Install_Model::logSuccess('Module install finished');
        echo '<div class="installerExtensionResult hide" data-status="success" data-redirect="' . $defaultUrl . '"></div>';
    }

    public function extensionUninstall(Vtiger_Request $request): void
    {
        $redirectUrl = 'index.php?module=Installer&view=Index';

        if ('Yes' !== $request->get('confirmed')) {
            header('location:' . $redirectUrl);

            return;
        }

        $moduleName = (string)$request->get('sourceModule');
        Installer_ExtensionInstall_Model::validateModuleName($moduleName);
        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module) {
            throw new RuntimeException('Extension module is not installed: ' . $moduleName);
        }

        if (Installer_ExtensionInstall_Model::SOURCE_CUSTOM !== strtolower((string)$module->get('source'))) {
            throw new RuntimeException('Core modules cannot be uninstalled through Installer');
        }

        Core_Install_Model::getInstance('module.preuninstall', $moduleName)->deleteModule();
        header('location:' . $redirectUrl);
    }

    /**
     * @throws Exception
     */
    public function updateInformation(Vtiger_Request $request): void
    {
        Installer_License_Model::updateAll();
        Installer_ExtensionInstall_Model::clearCache();
        Installer_SystemInstall_Model::clearCache();
        Installer_Notification_Model::updateAll();

        header('location:index.php?module=Installer&view=Index');
    }
}

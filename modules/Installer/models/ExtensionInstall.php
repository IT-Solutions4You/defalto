<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_ExtensionInstall_Model extends Core_DatabaseData_Model
{
    public const SOURCE_CORE = 'core';
    public const SOURCE_CUSTOM = 'custom';

    public static array $ignoredModules = ['Dashboard', 'Home', 'Import', 'SMSNotifier', 'WSAPP', 'PBXManager', 'RecycleBin', 'Webforms', 'Google', 'ModTracker', 'ModComments', 'MailManager', 'Users', 'CustomerPortal', 'GlobalSearch', 'DragAndDrop', 'TwoFactorAuthentication'];
    public Vtiger_Module_Model|bool|null $module = null;

    public static function clearCache(): void
    {
        unset($_SESSION['Installer_ExtensionInstall']);
    }

    /**
     * @throws Exception
     */
    public static function getAll(): array
    {
        $modules = array_merge(self::getApiModules(), Vtiger_Module_Model::getAll());
        $extensions = [];

        foreach ($modules as $moduleName => $module) {
            if (is_numeric($moduleName)) {
                $moduleName = $module->getName();
            }

            if (in_array($moduleName, self::$ignoredModules)) {
                continue;
            }

            $extensions[$moduleName] = self::getInstance($module);
        }

        ksort($extensions);

        return $extensions;
    }

    /**
     * @throws Exception
     */
    public static function getInstallerModules(): array
    {
        $extensions = [];

        foreach (self::getAll() as $moduleName => $extension) {
            if ($extension->isVisibleInInstaller()) {
                $extensions[$moduleName] = $extension;
            }
        }

        return $extensions;
    }

    public function getLabel(): string
    {
        $version = $this->getVersion();
        $module = $this->getName();
        $translate = vtranslate($module, $module);
        $translate = str_replace(['ITS4You', '4You'], ['', ''], $translate);

        return $translate . ($version ? ' v' . $version : '');
    }

    /**
     * @throws Exception
     */
    public function getApiData()
    {
        $info = self::getApiInfo();

        return isset($info[$this->getName()]) ? $info[$this->getName()] : [];
    }

    /**
     * @throws Exception
     */
    public static function getApiInfo()
    {
        if (!array_key_exists('Installer_ExtensionInstall', $_SESSION)) {
            $_SESSION['Installer_ExtensionInstall'] = Installer_Api_Model::getInstance()->getExtensionInstall();
        }

        return $_SESSION['Installer_ExtensionInstall'];
    }

    /**
     * @throws Exception
     */
    public static function getApiModules(): array
    {
        $modules = array_keys(self::getApiInfo());
        $models = [];

        foreach ($modules as $module) {
            $models[$module] = Vtiger_Module_Model::getCleanInstance($module);
        }

        return $models;
    }

    public function getCRMUrl(): string
    {
        global $site_URL;

        return $site_URL;
    }

    public function getCRMVersion(): string
    {
        return Vtiger_Version::current();
    }

    public function getDefaultUrl(): string
    {
        return $this->module ? $this->module->getDefaultUrl() : '#';
    }

    public function getDownloadUrl(): string
    {
        return 'index.php?module=Installer&view=IndexAjax&mode=extensionProgress&version=' . $this->getName();
    }

    public static function getInstalledModules()
    {
        return Vtiger_Module_Model::getAll();
    }

    /**
     * @throws Exception
     */
    public static function getInstance(string|Vtiger_Module_Model $module): self
    {
        $instance = new self();

        if (is_object($module)) {
            $instance->setName($module->getName());
            $instance->module = $module;
        } else {
            self::validateModuleName($module);
            $instance->setName($module);
            $instance->module = Vtiger_Module_Model::getInstance($module);
        }

        if ($instance->module) {
            $instance->set('version', $instance->module->get('version'));
        }

        $instance->retrieveApiData();

        return $instance;
    }

    /**
     * @throws Exception
     */
    public function getLicenseMessages(): array
    {
        $messages = [];

        if (!$this->hasInstalledFiles()) {
            $messages['danger'] = vtranslate('LBL_MODULE_FILES_MISSING', 'Installer');

            return $messages;
        }

        if (!$this->isModuleActive()) {
            $messages['warning'] = vtranslate('LBL_MODULE_INACTIVE', 'Installer');

            return $messages;
        }

        if ($this->isCoreModule()) {
            $messages['primary'] = vtranslate('LBL_MODULE_ACTIVE', 'Installer');

            return $messages;
        }

        if (Installer_License_Model::isActiveExtension($this->getName())) {
            $messages['primary'] = vtranslate('LBL_LICENSE_ACTIVE', 'Installer');
        } else {
            $messages['danger'] = vtranslate('LBL_LICENSE_INACTIVE', 'Installer');
        }

        return $messages;
    }

    public function getLinks()
    {
        $links = $this->getModule() ? $this->getModule()->getSettingLinks() : [];
        $links = Vtiger_Link_Model::checkAndConvertLinks($links);

        return $links['LISTVIEWSETTING'] ?? [];
    }

    public static function getModuleSettingLinks(Vtiger_Module_Model $module): array
    {
        $extension = self::getLocalInstance($module);

        return self::translateInstallerLinks($extension->getInstallerSettingLinks());
    }

    public static function getLocalInstance(Vtiger_Module_Model $module): self
    {
        $instance = new self();
        $instance->setName($module->getName());
        $instance->module = $module;
        $instance->set('version', $module->get('version'));

        return $instance;
    }

    public function getInstallerSettingLinks(): array
    {
        $links = [];
        $extensionName = $this->getName();

        if (class_exists($extensionName . '_Install_Model')) {
            $links[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_REQUIREMENTS',
                'linkurl'   => 'index.php?module=Installer&view=Requirements&mode=Module&sourceModule=' . $this->getName(),
                'linkicon'  => '',
            ];
            $links[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_LICENSE',
                'linkurl'   => 'index.php?module=Installer&view=Index&mode=license&sourceModule=' . $this->getName(),
                'linkicon'  => '',
            ];
            $links[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_UNINSTALL',
                'linkurl'   => 'index.php?module=Installer&view=Index&mode=uninstall&sourceModule=' . $this->getName(),
                'linkicon'  => '',
            ];
        }

        return $links;
    }

    public static function translateInstallerLinks(array $links): array
    {
        foreach ($links as &$link) {
            $link['linklabel'] = vtranslate($link['linklabel'], 'Installer');
        }
        unset($link);

        return $links;
    }

    /**
     * @return Vtiger_Module_Model|null
     */
    public function getModule(): Vtiger_Module_Model|null
    {
        return $this->module ?: null;
    }

    public function getUpdateVersion(): string
    {
        return (string)$this->get('update_version');
    }

    public function getVersion(): string
    {
        return (string)$this->get('version');
    }

    public function getIcon(): string
    {
        $module = $this->getModule();
        $fontIcon = $module?->getFontIcon();

        return (string)($fontIcon ?: 'fa fa-puzzle-piece');
    }

    public function hasDownloadUrl(): bool
    {
        if ($this->isEmpty('download-url') || $this->isEmpty('download-folder')) {
            return false;
        }

        $license = $this->getLicense();

        return $license
            && $license->isValidLicense()
            && $license->hasExtensionEntitlement($this->getName());
    }

    /**
     * @throws Exception
     */
    public function getLicense(): Installer_License_Model|false
    {
        $licenseId = (int)$this->get('installer_license_id');

        return $licenseId > 0 ? Installer_License_Model::getInstanceById($licenseId) : false;
    }

    public function isCoreModule(): bool
    {
        $source = strtolower((string)$this->getModule()?->get('source'));

        return $source !== self::SOURCE_CUSTOM;
    }

    public function isModuleActive(): bool
    {
        return (bool)$this->getModule()?->isActive();
    }

    public function hasInstalledFiles(): bool
    {
        $moduleName = $this->getName();

        return is_dir('modules/' . $moduleName) || is_dir('modules/Settings/' . $moduleName);
    }

    public function isVisibleInInstaller(): bool
    {
        if ($this->hasDownloadUrl()) {
            return true;
        }

        return null !== $this->getModule() && !$this->isCoreModule();
    }

    public function getDownloadLabel(): string
    {
        if (!vtlib_isModuleActive($this->getName())) {
            return 'LBL_INSTALL';
        }

        return 'LBL_UPDATE';
    }

    /**
     * Downloads the extension into an isolated workspace, applies its files and
     * runs the existing Core module installer with the correct lifecycle event.
     *
     * @throws Throwable
     */
    public function installPackage(): Vtiger_Module_Model
    {
        $moduleName = $this->getName();
        self::validateModuleName($moduleName);

        if (!$this->hasDownloadUrl()) {
            throw new RuntimeException(vtranslate('LBL_LICENSE_DOWNLOAD_UNAVAILABLE', 'Installer'));
        }

        $isNewModule = !$this->getModule();
        $download = Installer_Download_Model::getInstance(
            (string)$this->get('download-url'),
            (string)$this->get('download-folder'),
            'index.php',
            'extension-' . $moduleName
        );
        $download->setAllowedRoots(['modules', 'layouts', 'languages', 'cron']);
        $requiredWritablePaths = [
            'cache',
            'modules',
            'layouts',
            'languages',
            'cron',
            'test/templates_c',
        ];

        if ($isNewModule) {
            $requiredWritablePaths = array_merge($requiredWritablePaths, [
                'user_privileges',
                'tabdata.php',
                'parent_tabdata.php',
            ]);
        }

        $download->setRequiredWritablePaths($requiredWritablePaths);

        $checksum = $this->getPackageChecksum();

        if ($checksum !== '') {
            $download->setExpectedChecksum($checksum);
        }

        $database = PearDatabase::getInstance();
        $previousDieOnError = $database->dieOnError;
        $sharingPermission = $isNewModule ? null : $this->getCurrentSharingPermission();
        $database->setDieOnError(true);

        try {
            $download->downloadAndExport();
            $installClass = $moduleName . '_Install_Model';

            if (!class_exists($installClass)) {
                throw new RuntimeException('Missing extension install model: ' . $installClass);
            }

            $this->validateModuleInstallRequirements();

            $eventType = self::getInstallEventType($isNewModule);

            if (!$isNewModule && !defined('VTIGER_UPGRADE')) {
                define('VTIGER_UPGRADE', true);
            }

            Core_Install_Model::logInfo('Module lifecycle event: ' . $eventType);
            Core_Install_Model::getInstance($eventType, $moduleName)->installModule();
            $this->restoreSharingPermission($sharingPermission);
            Core_Install_Model::updateModuleMetaFiles();
            $module = $this->finalizeInstalledModule();
            $download->commit();
            $this->module = $module;
            $this->set('version', $module->get('version'));

            return $module;
        } catch (Throwable $throwable) {
            $download->rollback();

            try {
                $this->restoreSharingPermission($sharingPermission);
            } catch (Throwable $sharingThrowable) {
                Core_Install_Model::logError('Unable to restore module sharing settings: ' . htmlspecialchars(
                    $sharingThrowable->getMessage(),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ));
            }

            Core_Install_Model::logError('Module installation failed: ' . htmlspecialchars(
                $throwable->getMessage(),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ));

            throw $throwable;
        } finally {
            $database->setDieOnError($previousDieOnError);
        }
    }

    public static function getInstallEventType(bool $isNewModule): string
    {
        return $isNewModule ? 'module.postinstall' : 'module.postupdate';
    }

    public static function validateModuleName(string $moduleName): void
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $moduleName)) {
            throw new InvalidArgumentException('Invalid extension module name');
        }
    }

    protected function getPackageChecksum(): string
    {
        $checksum = trim((string)($this->get('sha256') ?: $this->get('checksum')));

        if (str_starts_with(strtolower($checksum), 'sha256:')) {
            $checksum = trim(substr($checksum, 7));
        }

        return $checksum;
    }

    protected function getCurrentSharingPermission(): ?int
    {
        $sharingModule = Settings_SharingAccess_Module_Model::getInstance($this->getName());

        return $sharingModule ? (int)$sharingModule->getPermissionValue() : null;
    }

    protected function restoreSharingPermission(?int $permission): void
    {
        if (null === $permission) {
            return;
        }

        $sharingModule = Settings_SharingAccess_Module_Model::getInstance($this->getName());

        if (!$sharingModule || (int)$sharingModule->getPermissionValue() === $permission) {
            return;
        }

        $sharingModule->set('permission', $permission);
        $sharingModule->save();
        Settings_SharingAccess_Module_Model::recalculateSharingRules();
        Core_Install_Model::logInfo('Existing module sharing settings restored');
    }

    protected function validateModuleInstallRequirements(): void
    {
        $moduleFocus = CRMEntity::getInstance($this->getName());

        if (!empty($moduleFocus->isEntity) && empty($moduleFocus->table_index)) {
            throw new RuntimeException('Extension entity module has no base table ID');
        }

        if (!empty($moduleFocus->isEntity) && empty($moduleFocus->table_name)) {
            throw new RuntimeException('Extension entity module has no base table');
        }
    }

    /**
     * @throws RuntimeException
     */
    protected function finalizeInstalledModule(): Vtiger_Module_Model
    {
        $moduleName = $this->getName();
        Vtiger_Cache::flushModuleCache($moduleName);
        Vtiger_Cache::delete('module', $moduleName);
        Vtiger_Cache::flush();
        unset($_SESSION[$moduleName . '_listquery'], $_SESSION['lvs'][$moduleName]);
        self::clearCache();

        if (function_exists('clear_smarty_cache')) {
            clear_smarty_cache();
        }

        Core_Install_Model::logInfo('Runtime, module and template caches cleared');

        $module = Vtiger_Module_Model::getInstance($moduleName);

        if (!$module || !$module->isActive()) {
            throw new RuntimeException('Installed module is missing or inactive: ' . $moduleName);
        }

        $expectedVersion = $this->getUpdateVersion();
        $installedVersion = (string)$module->get('version');

        if ($expectedVersion !== '' && version_compare($installedVersion, $expectedVersion, '!=')) {
            throw new RuntimeException(
                'Installed module version does not match package version: ' . $installedVersion . ' != ' . $expectedVersion
            );
        }

        if ($module->getDefaultUrl() === '#') {
            throw new RuntimeException('Installed module has no usable default URL: ' . $moduleName);
        }

        Core_Install_Model::logSuccess('Post-install validation passed: ' . $moduleName . ' ' . $installedVersion);

        return $module;
    }

    /**
     * @throws Exception
     */
    public function retrieveApiData(): void
    {
        $apiData = $this->getApiData();

        if (!empty($apiData)) {
            $this->setData(array_merge($this->getData(), $apiData));
        }
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_ModulePackage_Model extends Vtiger_Base_Model
{
    protected string $zipFile = '';
    protected string $sourceFolder = '';
    protected string $moduleName = '';
    protected string $packageVersion = '';
    protected string $requiredVersion = '';
    protected ?Installer_ZipArchive_Model $archive = null;
    protected string $workspace = '';
    protected mixed $lockHandle = null;
    protected mixed $database = null;
    protected mixed $previousDieOnError = null;
    protected bool $filesCommitted = false;

    public static function getInstance(string $zipFile): self
    {
        $instance = new self();
        $instance->zipFile = $zipFile;
        $instance->inspect();

        return $instance;
    }

    public function __destruct()
    {
        if ($this->archive) {
            if (!$this->filesCommitted) {
                $this->archive->rollbackFiles();
            }

            $this->archive->close();
            $this->archive = null;
        }

        if ($this->database && null !== $this->previousDieOnError) {
            $this->database->setDieOnError($this->previousDieOnError);
        }

        $this->removeWorkspace();
        $this->releaseLock();
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getPackageVersion(): string
    {
        return $this->packageVersion;
    }

    public function getRequiredVersion(): string
    {
        return $this->requiredVersion;
    }

    public function isUpdate(): bool
    {
        return (bool)Vtiger_Module_Model::getInstance($this->moduleName);
    }

    public function install(): Vtiger_Module_Model
    {
        $isNewModule = !$this->isUpdate();
        $this->validateInstallation();

        $this->database = PearDatabase::getInstance();
        $this->previousDieOnError = $this->database->dieOnError;
        $sharingPermission = $isNewModule ? null : $this->getCurrentSharingPermission();
        $this->initializeWorkspace();
        $this->acquireLock();
        $this->database->setDieOnError(true);

        if ($isNewModule !== !$this->isUpdate()) {
            throw new RuntimeException('Module installation state changed while waiting for the package lock');
        }

        $this->validateInstallation();
        $this->openArchive();
        $this->archive->setAllowedRoots(['modules', 'layouts', 'languages']);
        $this->archive->setAllowedModuleName($this->moduleName);
        $errors = $this->archive->extractSubDirTo(
            Core_Utils_Helper::getRootDirectory(),
            $this->sourceFolder,
            $this->workspace
        );

        if ($errors || $this->archive->getCopiedFilesCount() < 1) {
            throw new RuntimeException(
                $errors ? 'ZIP extraction failed: ' . implode(', ', $errors) : 'ZIP package did not contain installable files'
            );
        }

        $installClass = $this->moduleName . '_Install_Model';

        if (!class_exists($installClass)) {
            throw new RuntimeException('Missing extension install model: ' . $installClass);
        }

        $this->validateModuleInstallRequirements();

        if (!$isNewModule && !defined('VTIGER_UPGRADE')) {
            define('VTIGER_UPGRADE', true);
        }

        $eventType = $isNewModule ? 'module.postinstall' : 'module.postupdate';
        Core_Install_Model::logInfo('Module lifecycle event: ' . $eventType);
        Core_Install_Model::getInstance($eventType, $this->moduleName)->installModule();
        $this->restoreSharingPermission($sharingPermission);
        Core_Install_Model::updateModuleMetaFiles();
        $module = $this->finalizeInstalledModule();
        $this->archive->commitFiles();
        $this->filesCommitted = true;
        $this->closeResources();

        return $module;
    }

    public function validateInstallation(): void
    {
        if (!$this->isUpdate()) {
            if (is_dir('modules/' . $this->moduleName)) {
                throw new RuntimeException(
                    'Module files already exist without an installed module: ' . $this->moduleName
                );
            }

            return;
        }

        $installedVersion = $this->getInstalledVersion();

        if ($installedVersion !== '' && version_compare($this->packageVersion, $installedVersion, '<=')) {
            throw new RuntimeException('The package version must be newer than the installed module version');
        }
    }

    protected function inspect(): void
    {
        if (!is_file($this->zipFile)) {
            throw new RuntimeException('Module package file does not exist');
        }

        $fileSize = filesize($this->zipFile);

        if (false === $fileSize || $fileSize > Installer_Download_Model::MAX_DOWNLOAD_SIZE) {
            throw new RuntimeException('Module package exceeds the maximum allowed size');
        }

        $this->openArchive();

        $allPaths = $this->archive->getRelativeFilePaths();
        $this->sourceFolder = $this->resolveSourceFolder($allPaths);
        $packagePaths = $this->archive->getRelativeFilePaths($this->sourceFolder);
        $moduleNames = [];

        foreach ($packagePaths as $path) {
            if (preg_match('/^modules\/([A-Za-z][A-Za-z0-9_]*)\/\1\.php$/', $path, $matches)) {
                $moduleNames[$matches[1]] = true;
            }
        }

        if (count($moduleNames) !== 1) {
            throw new RuntimeException('ZIP must contain exactly one modules/<Module>/<Module>.php file');
        }

        $this->moduleName = (string)array_key_first($moduleNames);
        Installer_ExtensionInstall_Model::validateModuleName($this->moduleName);
        $installModelPath = 'modules/' . $this->moduleName . '/models/Install.php';
        $manifestPath = 'modules/' . $this->moduleName . '/manifest.xml';

        if (!in_array($installModelPath, $packagePaths, true)) {
            throw new RuntimeException('ZIP is missing the module install model: ' . $installModelPath);
        }

        $this->archive->setAllowedRoots(['modules', 'layouts', 'languages']);
        $this->archive->setAllowedModuleName($this->moduleName);
        $this->validatePackagePaths($packagePaths);
        $moduleSource = $this->archive->getFileContent(
            $this->sourceFolder,
            'modules/' . $this->moduleName . '/' . $this->moduleName . '.php'
        );
        $this->packageVersion = self::getVersionFromSource($moduleSource);

        if ($this->packageVersion === '') {
            throw new RuntimeException('Module package has no valid moduleVersion');
        }

        if (in_array($manifestPath, $packagePaths, true)) {
            $manifestSource = $this->archive->getFileContent($this->sourceFolder, $manifestPath);
            $this->requiredVersion = self::validateManifestSource(
                $manifestSource,
                $this->moduleName,
                $this->packageVersion
            );
        }

        $this->archive->close();
        $this->archive = null;
    }

    protected function openArchive(): void
    {
        if ($this->archive) {
            return;
        }

        $this->archive = new Installer_ZipArchive_Model();
        $result = $this->archive->open($this->zipFile);

        if (true !== $result) {
            $this->archive = null;

            throw new RuntimeException('Uploaded ZIP file cannot be opened (code ' . (string)$result . ')');
        }
    }

    /**
     * @param array<string> $paths
     */
    protected function resolveSourceFolder(array $paths): string
    {
        if (!$paths) {
            throw new RuntimeException('ZIP package is empty');
        }

        $allowedRoots = ['modules', 'layouts', 'languages'];

        foreach ($paths as $path) {
            $root = explode('/', $path, 2)[0];

            if (in_array($root, $allowedRoots, true)) {
                return '';
            }
        }

        $topLevelFolders = [];

        foreach ($paths as $path) {
            $segments = explode('/', $path, 2);

            if (count($segments) < 2) {
                throw new RuntimeException('ZIP contains a file outside the Defalto package structure: ' . $path);
            }

            $topLevelFolders[$segments[0]] = true;
        }

        if (count($topLevelFolders) !== 1) {
            throw new RuntimeException('ZIP must use the Defalto structure directly or inside one package directory');
        }

        return (string)array_key_first($topLevelFolders);
    }

    /**
     * @param array<string> $paths
     */
    protected function validatePackagePaths(array $paths): void
    {
        $hasInstallableFile = false;

        foreach ($paths as $path) {
            if (!preg_match('/^(modules|layouts|languages)\//', $path)) {
                throw new RuntimeException('ZIP entry is outside allowed package roots: ' . $path);
            }

            if (!$this->archive->isAllowedPackagePath($path)) {
                throw new RuntimeException('ZIP entry is outside the module package structure: ' . $path);
            }

            $hasInstallableFile = true;
        }

        if (!$hasInstallableFile) {
            throw new RuntimeException('ZIP package did not contain installable files');
        }
    }

    protected static function getVersionFromSource(string $source): string
    {
        if (1 !== preg_match('/\$moduleVersion\s*=\s*([\'\"])([^\'\"]+)\1\s*;/', $source, $matches)) {
            return '';
        }

        return trim($matches[2]);
    }

    public static function validateManifestSource(
        string $source,
        string $moduleName,
        string $moduleVersion
    ): string
    {
        $previousUseErrors = libxml_use_internal_errors(true);

        $manifest = simplexml_load_string($source, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        if (!$manifest) {
            throw new RuntimeException('Module package manifest is not valid XML');
        }

        $manifestModuleName = trim((string)$manifest->name);
        $manifestVersion = trim((string)$manifest->version);

        if ($manifestModuleName !== $moduleName) {
            throw new RuntimeException('Manifest module name does not match the package structure');
        }

        if ($manifestVersion !== $moduleVersion) {
            throw new RuntimeException('Manifest version does not match the moduleVersion');
        }

        $requiredVersion = trim((string)($manifest->dependencies->version ?? ''));

        if ($requiredVersion !== ''
            && version_compare(Vtiger_Version::current(), $requiredVersion, '<')
        ) {
            throw new RuntimeException('Module package requires Defalto ' . $requiredVersion . ' or newer');
        }

        return $requiredVersion;
    }

    protected function getInstalledVersion(): string
    {
        $moduleFile = 'modules/' . $this->moduleName . '/' . $this->moduleName . '.php';

        if (!is_file($moduleFile)) {
            return '';
        }

        $source = file_get_contents($moduleFile);

        return false === $source ? '' : self::getVersionFromSource($source);
    }

    protected function validateModuleInstallRequirements(): void
    {
        $moduleFocus = CRMEntity::getInstance($this->moduleName);

        if (!empty($moduleFocus->isEntity) && empty($moduleFocus->table_index)) {
            throw new RuntimeException('Extension entity module has no base table ID');
        }

        if (!empty($moduleFocus->isEntity) && empty($moduleFocus->table_name)) {
            throw new RuntimeException('Extension entity module has no base table');
        }
    }

    protected function getCurrentSharingPermission(): ?int
    {
        $sharingModule = Settings_SharingAccess_Module_Model::getInstance($this->moduleName);

        return $sharingModule ? (int)$sharingModule->getPermissionValue() : null;
    }

    protected function restoreSharingPermission(?int $permission): void
    {
        if (null === $permission) {
            return;
        }

        $sharingModule = Settings_SharingAccess_Module_Model::getInstance($this->moduleName);

        if (!$sharingModule || (int)$sharingModule->getPermissionValue() === $permission) {
            return;
        }

        $sharingModule->set('permission', $permission);
        $sharingModule->save();
        Settings_SharingAccess_Module_Model::recalculateSharingRules();
        Core_Install_Model::logInfo('Existing module sharing settings restored');
    }

    protected function finalizeInstalledModule(): Vtiger_Module_Model
    {
        Vtiger_Cache::flushModuleCache($this->moduleName);
        Vtiger_Cache::delete('module', $this->moduleName);
        Vtiger_Cache::flush();
        unset($_SESSION[$this->moduleName . '_listquery'], $_SESSION['lvs'][$this->moduleName]);
        Installer_ExtensionInstall_Model::clearCache();

        if (function_exists('clear_smarty_cache')) {
            clear_smarty_cache();
        }

        $module = Vtiger_Module_Model::getInstance($this->moduleName);

        if (!$module || !$module->isActive()) {
            throw new RuntimeException('Installed module is missing or inactive: ' . $this->moduleName);
        }

        $installedVersion = $this->getInstalledVersion();

        if (version_compare($installedVersion, $this->packageVersion, '!=')) {
            throw new RuntimeException(
                'Installed module version does not match package version: '
                . $installedVersion . ' != ' . $this->packageVersion
            );
        }

        if ($module->getDefaultUrl() === '#') {
            throw new RuntimeException('Installed module has no usable default URL: ' . $this->moduleName);
        }

        Core_Install_Model::logSuccess('Post-install validation passed: '
            . $this->moduleName . ' ' . $installedVersion);

        return $module;
    }

    protected function initializeWorkspace(): void
    {
        $cacheDirectory = rtrim(Core_Utils_Helper::getRootDirectory(), '/\\') . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . 'installer';
        $this->workspace = $cacheDirectory . DIRECTORY_SEPARATOR . 'module-package-' . bin2hex(random_bytes(6));

        if (!is_dir($this->workspace) && !mkdir($this->workspace, 0755, true) && !is_dir($this->workspace)) {
            throw new RuntimeException('Unable to create isolated module package workspace');
        }
    }

    protected function acquireLock(): void
    {
        $lockDirectory = rtrim(Core_Utils_Helper::getRootDirectory(), '/\\') . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . 'installer' . DIRECTORY_SEPARATOR . 'locks';

        if (!is_dir($lockDirectory) && !mkdir($lockDirectory, 0755, true) && !is_dir($lockDirectory)) {
            throw new RuntimeException('Unable to create installer lock directory');
        }

        $lockFile = $lockDirectory . DIRECTORY_SEPARATOR . 'installer.lock';
        $this->lockHandle = @fopen($lockFile, 'c');

        if (!is_resource($this->lockHandle) || !flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
            $this->releaseLock();
            throw new RuntimeException('Another package installation is already running');
        }
    }

    protected function releaseLock(): void
    {
        if (is_resource($this->lockHandle)) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
        }

        $this->lockHandle = null;
    }

    protected function closeResources(): void
    {
        $this->archive?->close();
        $this->archive = null;

        if ($this->database && null !== $this->previousDieOnError) {
            $this->database->setDieOnError($this->previousDieOnError);
        }

        $this->database = null;
        $this->previousDieOnError = null;
        $this->removeWorkspace();
        $this->releaseLock();
    }

    protected function removeWorkspace(): void
    {
        if ($this->workspace === '' || !is_dir($this->workspace)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->workspace, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($this->workspace);
        $this->workspace = '';
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_UpdateRequirement_Model extends Vtiger_Base_Model
{
    protected static ?self $instance = null;
    protected string $installedVersion = '';
    protected string $availableVersion = '';

    public static function getInstance(bool $refresh = false): self
    {
        if ($refresh || null === self::$instance) {
            $instance = new self();
            $extension = new Installer_ExtensionInstall_Model();
            $extension->setName('Installer');
            $instance->installedVersion = $extension->getModuleFileVersion();
            $catalog = Installer_Api_Model::getInstance()->getPublicExtensionInstall($refresh);
            $availableVersion = trim((string)($catalog['Installer']['update_version'] ?? ''));

            if (1 === preg_match('/^[0-9]+(?:\.[0-9]+)*$/D', $availableVersion)) {
                $instance->availableVersion = $availableVersion;
            }

            self::$instance = $instance;
        }

        return self::$instance;
    }

    public function getInstalledVersion(): string
    {
        return $this->installedVersion;
    }

    public function getAvailableVersion(): string
    {
        return $this->availableVersion;
    }

    public function isCurrent(): bool
    {
        return 1 === preg_match('/^[0-9]+(?:\.[0-9]+)*$/D', $this->installedVersion)
            && '' !== $this->availableVersion
            && version_compare($this->installedVersion, $this->availableVersion, '>=');
    }

    public function getBlockingMessage(): string
    {
        if ($this->isCurrent()) {
            return '';
        }

        $label = '' === $this->availableVersion
            ? 'LBL_INSTALLER_VERSION_UNAVAILABLE'
            : 'LBL_INSTALLER_UPDATE_REQUIRED';

        return vtranslate($label, 'Installer');
    }

    public function isInstallerPackageAllowed(string $packageVersion): bool
    {
        return 1 === preg_match('/^[0-9]+(?:\.[0-9]+)*$/D', $packageVersion)
            && version_compare($packageVersion, $this->installedVersion, '>=')
            && ('' === $this->availableVersion
                || version_compare($packageVersion, $this->availableVersion, '>='));
    }

    public function validateInstallation(string $moduleName = '', string $packageVersion = ''): void
    {
        if ('Installer' === $moduleName) {
            if (!$this->isInstallerPackageAllowed($packageVersion)) {
                throw new Exception(vtranslate('LBL_INSTALLER_PACKAGE_TOO_OLD', 'Installer'));
            }

            return;
        }

        if (!$this->isCurrent()) {
            throw new Exception($this->getBlockingMessage());
        }
    }
}

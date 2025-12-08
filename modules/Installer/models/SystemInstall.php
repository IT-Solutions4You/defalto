<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_SystemInstall_Model extends Vtiger_Base_Model
{
    /**
     * @throws Exception
     */
    public static function getInstance(string $version): self
    {
        $instance = new self();
        $instance->set('version', $version);
        $instance->retrieveInfo();

        return $instance;
    }

    public static function getInstanceFromData(array $data): self
    {
        $instance = new self();
        $instance->setData($data);

        return $instance;
    }

    /**
     * @throws Exception
     */
    public function retrieveInfo(): void
    {
        $versions = self::getApiInfo();

        if (empty($versions[$this->getVersion()])) {
            throw new Exception(vtranslate('Version not found', 'Installer') . ': ' . $this->getVersion());
        }

        $this->setData($versions[$this->getVersion()]);
    }

    /**
     * @throws Exception
     */
    public static function getApiInfo()
    {
        if (empty($_SESSION['Installer_SystemInstall'])) {
            $_SESSION['Installer_SystemInstall'] = Installer_Api_Model::getInstance()->getSystemInstall();
        }

        return $_SESSION['Installer_SystemInstall'];
    }

    public static function clearCache(): void
    {
        unset($_SESSION['Installer_SystemInstall']);
    }

    /**
     * @throws Exception
     */
    public static function getAll(): array
    {
        $versions = self::getApiInfo();

        foreach ($versions as $version => $versionData) {
            $versions[$version] = self::getInstanceFromData($versionData);
        }

        return $versions;
    }

    public function getVersion(): string
    {
        return $this->get('version');
    }

    /**
     * @return bool
     */
    public function isNewestVersion(): bool
    {
        return (bool)version_compare(Vtiger_Version::current(), $this->get('version'), '>=');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->isNewestVersion()) {
            return '( ' . vtranslate('LBL_UP_TO_DATE', 'Installer') . ' )';
        }

        return '';
    }

    public function getLabel(): string
    {
        return $this->get('label');
    }

    public function getDownloadUrl(): string
    {
        return 'index.php?module=Installer&view=IndexAjax&mode=systemProgress&version=' . $this->getVersion();
    }
}
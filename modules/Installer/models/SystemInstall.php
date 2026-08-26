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
        if (!array_key_exists('Installer_SystemInstall', $_SESSION)) {
            $_SESSION['Installer_SystemInstall'] = Installer_Api_Model::getInstance()->getSystemInstall();
            $_SESSION['Installer_SystemInstallDate'] = time();
        }

        return $_SESSION['Installer_SystemInstall'];
    }

    /**
     * @return void
     */
    public static function clearCache(): void
    {
        unset($_SESSION['Installer_SystemInstall']);
        unset($_SESSION['Installer_SystemInstallDate']);
    }

    /**
     * @return string
     */
    public static function getCacheDate(): string
    {
        $time = (int)($_SESSION['Installer_SystemInstallDate'] ?? 0);

        if (!$time) {
            return '';
        }

        $userDate = Vtiger_Util_Helper::formatDateIntoStrings(date('Y-m-d', $time), date('H:i:s', $time));

        return $userDate;
    }

    /**
     * @return bool
     */
    public static function isCacheRefreshAllowed(): bool
    {
        return time() - (int)($_SESSION['Installer_SystemInstallDate'] ?? 0) > 60;
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

    public function hasDownloadUrl(): bool
    {
        return !$this->isEmpty('download-url') && !$this->isEmpty('download-folder');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->isNewestVersion()) {
            return vtranslate('LBL_UP_TO_DATE', 'Installer');
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

    public function getCurrentVersion(): string
    {
        return Vtiger_Version::current();
    }

    public static function getMembershipBranding(): string
    {
        $branding = vtranslate('LBL_MEMBERSHIP_BRANDING', 'Installer');
        $branding = str_replace(
            '-redirect-membership',
            ' class="fw-bold text-primary" target="_blank" rel="noopener noreferrer"'
                . ' href="index.php?module=Installer&view=Redirect&mode=Membership"',
            $branding
        );

        return str_replace(
            '-redirect-order',
            ' class="fw-bold text-primary" target="_blank" rel="noopener noreferrer"'
                . ' href="index.php?module=Installer&view=Redirect&mode=MembershipOrder"',
            $branding
        );
    }

    public function getBranding(): string
    {
        return self::getMembershipBranding();
    }

}

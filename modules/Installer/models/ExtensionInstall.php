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
    public static array $ignoredModules = ['Dashboard'];
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

        foreach ($modules as $module) {
            if(in_array($module->getName(), self::$ignoredModules)) {
                continue;
            }

            $extensions[$module->getName()] = self::getInstance($module);
        }

        return $extensions;
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
        if (empty($_SESSION['Installer_ExtensionInstall'])) {
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

        if (Installer_License_Model::isActiveExtension($this->getName())) {
            $messages['primary'] = 'Valid license, active extension';
        } else {
            $messages['danger'] = 'Invalid license, inactive extension';
        }

        return $messages;
    }

    public function getLinks()
    {
        $links = $this->getModule() ? $this->getModule()->getSettingLinks() : [];

        $extensionName = $this->getName();

        if (class_exists($extensionName . '_Install_Model')) {
            $links[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_REQUIREMENTS', 'Installer'),
                'linkurl'   => 'index.php?module=Installer&view=Requirements&mode=Module&sourceModule=' . $this->getName(),
                'linkicon'  => '',
            ];
            $links[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_LICENSE', 'Installer'),
                'linkurl'   => 'index.php?module=Installer&view=Index&mode=license&sourceModule=' . $this->getName(),
                'linkicon'  => '',
            ];
            $links[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_UNINSTALL', 'Installer'),
                'linkurl'   => 'index.php?module=Installer&view=Index&mode=uninstall&sourceModule=' . $this->getName(),
                'linkicon'  => '',
            ];
        }

        $links = Vtiger_Link_Model::checkAndConvertLinks($links);

        return $links['LISTVIEWSETTING'] ?? [];
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

    public function hasDownloadUrl(): bool
    {
        return !$this->isEmpty('download-url');
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
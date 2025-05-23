<?php

class Installer_SystemInstall_Model extends Vtiger_Base_Model
{
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
     * @throws AppException
     */
    public function retrieveInfo(): void
    {
        $api = Installer_Api_Model::getInstance();
        $api->connect();
        $versions = $api->getSystemInstall();

        if(empty($versions[$this->getVersion()])) {
            throw new AppException(vtranslate('Version not found', 'Installer') . ': ' . $this->getVersion());
        }

        $this->setData($versions[$this->getVersion()]);
    }

    /**
     * @throws AppException
     */
    public static function getAll(): array
    {
        $api = Installer_Api_Model::getInstance();
        $api->connect();
        $versions = $api->getSystemInstall();

        foreach ($versions as $version => $versionData) {
            $versions[$version] = self::getInstanceFromData($versionData);
        }

        return $versions;
    }

    public function getVersion(): string
    {
        return $this->get('version');
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
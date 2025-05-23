<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'vtlib/Vtiger/Net/Client.php';

class Installer_Api_Model extends Vtiger_Net_Client
{
    public string $apiUrl = 'https://its4you.sk/en/api_defalto/';

    public function activateLicenseInfo($license): array
    {
        $this->setURL($this->apiUrl . 'license/v1/');
        $response = $this->doPost(['url' => $this->getSiteUrl(), 'license' => $license, 'action' => 'activate']);

        return json_decode($response, true);
    }

    /**
     * @throws AppException
     */
    public function connect(): true
    {
        $this->setURL($this->apiUrl . 'connect/v1/');
        $response = $this->doPost();

        if ('connected' === $response) {
            return true;
        }

        throw new AppException('Connection failed: ' . $response);
    }

    public function deactivateLicenseInfo($license): array
    {
        $this->setURL($this->apiUrl . 'license/v1/');
        $response = $this->doPost(['url' => $this->getSiteUrl(), 'license' => $license, 'action' => 'deactivate']);

        return json_decode($response, true);
    }

    /**
     * @throws AppException
     */
    public function getExtensionInstall(): array
    {
        $this->setURL($this->apiUrl . 'extension/v1/');
        $response = $this->doPost(['licenses' => $this->getLicenses(Installer_License_Model::EXTENSION_PACKAGES), 'url' => $this->getSiteUrl(),]);

        return json_decode($response, true);
    }

    public static function getInstance(): self
    {
        return new self('');
    }

    /**
     * @param string $type
     * @return array
     * @throws AppException
     */
    public function getLicenses(string $type = ''): array
    {
        $licenses = Installer_License_Model::getAll();
        $keys = [];

        foreach ($licenses as $license) {
            if ($license->isValidLicense() && $type === $license->getInfo('item_name')) {
                $keys[] = $license->getName();
            }
        }

        return $keys;
    }

    public function getSiteUrl(): string
    {
        global $site_URL;

        return rtrim($site_URL, '/');
    }

    /**
     * @throws AppException
     */
    public function getSystemInstall(): array
    {
        $this->setURL($this->apiUrl . 'system/v1/');
        $response = $this->doPost(['url' => $this->getSiteUrl(), 'licenses' => $this->getLicenses(Installer_License_Model::MEMBERSHIP_PACK),]);

        return json_decode($response, true);
    }
}
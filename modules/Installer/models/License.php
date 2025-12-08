<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_License_Model extends Core_DatabaseData_Model
{
    public static array $deleteLicenseByError = [
        'no_activations_left',
    ];
    public const MEMBERSHIP_PACKAGE = 'Membership Package';
    public const EXTENSION_PACKAGE = 'Extension Package';
    protected array $columns = [
        'name',
        'info',
    ];
    /**
     * @var string
     */
    protected string $table = 'df_licenses';
    /**
     * @var string
     */
    protected string $tableId = 'id';

    /**
     * @throws Exception
     */
    public static function getAll($type = null, $extension = null): array
    {
        $cache = Installer_Cache_Model::getInstance('getAll', $type, $extension);

        if ($cache->has()) {
            return $cache->get();
        }

        $license = new self();
        $table = $license->getLicenseTable();
        $result = $table->selectResult(['id'], []);
        $licenses = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $licenseId = (int)$row['id'];
            $license = self::getInstanceById($licenseId);

            if ($type) {
                if ($type !== $license->getInfo('item_type')) {
                    continue;
                }
            }

            if ($extension) {
                if (!in_array($extension, (array)$license->getInfo('extensions'))) {
                    continue;
                }
            }

            $licenses[$licenseId] = $license;
        }

        $cache->set($licenses);

        return $licenses;
    }

    public function getExpireDate(): string
    {
        return (string)$this->getInfo('expires');
    }

    public function getInfo($key = ''): mixed
    {
        $info = json_decode(base64_decode((string)$this->get('info')), true);

        if ($key) {
            return !empty($info[$key]) ? $info[$key] : null;
        }

        return $info;
    }

    public static function getInstance($name): self
    {
        $instance = new self();
        $instance->set('name', $name);

        return $instance;
    }

    /**
     * @throws Exception
     */
    public static function getInstanceById(int $id): self|false
    {
        $license = new self();
        $data = $license->getLicenseTable()->selectData(array_merge(['id'], $license->columns), ['id' => $id]);

        if (empty($data['id'])) {
            return false;
        }

        $instance = self::getInstance($data['name']);
        $instance->setData($data);

        return $instance;
    }

    public function getItemName(): string
    {
        return (string)$this->getInfo('item_name');
    }

    public function getLicenseTable(): object
    {
        return $this->getTable($this->table, $this->tableId);
    }

    public function getLicenseUrl(): string
    {
        return 'index.php?module=Installer&view=Index';
    }

    public function getSaveParams()
    {
        return [
            'name' => $this->get('name'),
            'info' => $this->get('info'),
        ];
    }

    /**
     * @throws Exception
     */
    public static function isActiveExtension(string $extension): bool
    {
        $licenses = self::getAll(self::EXTENSION_PACKAGE, $extension);

        foreach ($licenses as $license) {
            if ($license->isValidLicense()) {
                return true;
            }
        }

        return false;
    }

    public function isValidLicense(): bool
    {
        $expireDate = $this->getExpireDate();
        $currentDate = date('Y-m-d H:i:s');

        if ('valid' === $this->getInfo('license') && $expireDate > $currentDate) {
            return true;
        }

        return false;
    }

    public function setInfo(array $info): void
    {
        $this->set('info', base64_encode(json_encode($info, true)));
    }

    /**
     * @throws Exception
     */
    public static function isMembershipActive(): bool
    {
        $memberShips = Installer_License_Model::getAll(Installer_License_Model::MEMBERSHIP_PACKAGE);

        foreach ($memberShips as $membership) {
            if ($membership->isValidLicense()) {
                return true;
            }
        }

        return false;
    }

    public function hasExpireDate(): bool
    {
        return !empty($this->getExpireDate());
    }

    public function activate(): static
    {
        $info = Installer_Api_Model::getInstance()->activateLicenseInfo($this->getName());
        $this->setInfo($info);

        return $this;
    }

    public function deactivate(): static
    {
        $info = Installer_Api_Model::getInstance()->deactivateLicenseInfo($this->getName());
        $this->setInfo($info);

        return $this;
    }

    public function hasDeleteLicenseError(): bool
    {
        $error = $this->getInfo('error');

        return in_array($error, self::$deleteLicenseByError);
    }

    /**
     * @throws Exception
     */
    public static function updateAll(): void
    {
        $licenses = Installer_License_Model::getAll();
        /** @var Installer_License_Model $license */

        foreach ($licenses as $license) {
            $license->activate();

            if ($license->hasExpireDate()) {
                $license->save();
            }

            if ($license->hasDeleteLicenseError()) {
                $license->delete();
            }
        }
    }
}
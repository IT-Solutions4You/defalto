<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Installer_License_Model extends Core_DatabaseData_Model
{
    public const MEMBERSHIP_PACK = 'Membership Pack';
    public const EXTENSION_PACKAGES = 'Extension Packages for Defalto CRM';
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
     * @throws AppException
     */
    public static function getAll($type = null, $extension = null): array
    {
        $license = new self();
        $table = $license->getLicenseTable();
        $result = $table->selectResult(['id'], []);
        $licenses = [];

        while ($row = $table->getDB()->fetchByAssoc($result)) {
            $licenseId = (int)$row['id'];
            $license = self::getInstanceById($licenseId);

            if ($type) {
                if ($type !== $license->getInfo('item_name')) {
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

        return $licenses;
    }

    public function getExpireDate(): string
    {
        return (string)$this->getInfo('expires');
    }

    public function getInfo($key = ''): mixed
    {
        $info = json_decode(base64_decode((string)$this->get('info')), true);

        if (!empty($info[$key])) {
            return $info[$key];
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
     * @throws AppException
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
     * @throws AppException
     */
    public static function isActiveExtension(string $extension): bool
    {
        $licenses = self::getAll(self::EXTENSION_PACKAGES, $extension);

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
}
<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_Notification_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_notifications';
    protected string $tableId = 'id';
    protected array $notifications = [];

    public static function getAll()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM df_notifications ORDER BY date DESC', []);
        $notifications = [];

        while ($row = $db->fetchByAssoc($result)) {
            $notification = self::getInstance();
            $notification->setData($row);

            $notifications[] = $notification;
        }

        return $notifications;
    }

    public function getDescription()
    {
        return $this->get('description');
    }

    public function getDisplayDate()
    {
        return Vtiger_Util_Helper::formatDateDiffInStrings($this->get('date'));
    }

    public function getDisplayName()
    {
        return vtranslate($this->getName(), 'Installer');
    }

    public function getDisplayDescription()
    {
        return vtranslate($this->getDescription(), 'Installer');
    }

    /**
     * @throws Exception
     */
    public static function getInstance($name = null): self
    {
        $instance = new self();

        if (empty($name)) {
            return $instance;
        }

        $instance->setName($name);
        $instance->retrieveDataByName();

        return $instance;
    }

    public static function getNotificationTable(): self
    {
        $table = new self();

        return $table->getTable($table->table, $table->tableId);
    }

    public function getSaveParams(): array
    {
        return [
            'name' => $this->get('name'),
            'description' => $this->get('description'),
            'type' => $this->get('type'),
            'icon' => $this->get('icon'),
            'link' => $this->get('link'),
            'date' => date('Y-m-d H:i:s'),
        ];
    }

    public function getType()
    {
        return $this->get('type');
    }

    public function retrieveLicenses(): void
    {
        $licenses = Installer_License_Model::getAll();
        /** @var Installer_License_Model $license */
        foreach ($licenses as $license) {
            $name = $license->getName();
            $label = 'License ' . substr($name, 0, 5) . '...' . substr($name, -5);

            if ($license->isUserLimitReached()) {
                $this->notifications[] = [
                    'name' => $label,
                    'description' => 'License users limit reached',
                    'type' => 'warning',
                    'icon' => 'fa fa-users',
                    'link' => 'index.php?module=Installer#UpdateLicense'
                ];
            } elseif ($license->isExpired()) {
                $this->notifications[] = [
                    'name' => $label,
                    'description' => 'License expired',
                    'type' => 'warning',
                    'icon' => 'fa fa-users',
                    'link' => 'index.php?module=Installer#UpdateLicense'
                ];
            } elseif (!$license->isValid()) {
                $this->notifications[] = [
                    'name' => $label,
                    'description' => 'Invalid license key. Please check your details.',
                    'type' => 'warning',
                    'icon' => 'fa fa-users',
                    'link' => 'index.php?module=Installer#UpdateLicense'
                ];
            } else {
                $this->notifications[] = [
                    'name' => $label,
                    'description' => 'License active',
                    'type' => 'success',
                    'icon' => 'fa fa-check',
                    'link' => 'index.php?module=Installer#UpdateLicense'
                ];
            }
        }
    }

    public function retrieveSystems(): void
    {
        $systems = Installer_SystemInstall_Model::getAll();
        /** @var Installer_SystemInstall_Model $system */
        foreach ($systems as $system) {
            if ($system->getVersion() === $system->getCurrentVersion()) {
                $this->notifications[] = [
                    'name' => 'Update Defalto',
                    'description' => 'System up to date',
                    'type' => 'success',
                    'icon' => 'fa fa-download',
                    'link' => 'index.php?module=Installer#UpdateDefalto'
                ];
            } else {
                $this->notifications[] = [
                    'name' => 'Update Defalto',
                    'description' => vtranslate('System update available', 'Installer', $system->getVersion()),
                    'type' => 'warning',
                    'icon' => 'fa-solid fa-rotate',
                    'link' => 'index.php?module=Installer#UpdateDefalto'
                ];
            }
        }
    }

    /**
     * @throws Exception
     */
    public function retrieveExtensions(): void
    {
        $installInfo = [];
        $packages = Installer_ExtensionInstall_Model::getAll();
        /** @var Installer_ExtensionInstall_Model $package */
        foreach ($packages as $package) {
            if ($package->getVersion() === $package->getUpdateVersion()) {
                continue;
            }

            if ($package->getVersion() && $package->getUpdateVersion()) {
                $this->notifications[] = [
                    'name' => 'Extension ' . $package->getName(),
                    'description' => 'Update available',
                    'type' => 'warning',
                    'icon' => $package->getIcon() ?: 'fa-solid fa-puzzle-piece',
                    'link' => 'index.php?module=Installer#Update' . $package->getName()
                ];
            }

            if (!$package->getVersion() && $package->getUpdateVersion()) {
                $installInfo[] = [
                    'name' => 'Extension ' . $package->getName(),
                    'description' => 'New extension available',
                    'type' => 'primary',
                    'icon' => $package->getIcon() ?: 'fa-solid fa-puzzle-piece',
                    'link' => 'index.php?module=Installer#Update' . $package->getName()
                ];
            }
        }

        $this->notifications = array_merge($this->notifications, $installInfo);
    }

    public function getNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * @throws Exception
     */
    public static function updateAll(): void
    {
        $notification = new self();
        $notification->retrieveLicenses();
        $notification->retrieveSystems();
        $notification->retrieveExtensions();

        foreach ($notification->getNotifications() as $info) {
            $notification = self::getInstance($info['name']);
            $notification->setData(array_merge($notification->getData(), $info));
            $notification->save();
        }
    }
}

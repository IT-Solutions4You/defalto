<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_Install_Model extends Core_Install_Model
{
    public array $registerSettingsLinks = [
        ['Installer', 'index.php?module=Installer&view=Index', 'LBL_EXTENSIONS',],
        ['Requirements', 'index.php?module=Installer&view=Requirements', 'LBL_EXTENSIONS',],
    ];

    public array $registerCron = [
        ['InstallerLicenses', 'modules/Installer/cron/UpdateLicenses.php', 86400, 'Installer',],
    ];

    /**
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateSettingsLinks();
        $this->updateCron();
    }

    public function deleteCustomLinks(): void
    {
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('df_licenses', 'id')
            ->createTable()
            ->createColumn('name', 'VARCHAR(200)')
            ->createColumn('info', 'TEXT');
    }
}
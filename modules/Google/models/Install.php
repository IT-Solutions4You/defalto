<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Google_Install_Model extends Core_Install_Model
{
    public array $registerSettingsLinks = [
        ['LBL_GOOGLE', 'index.php?module=Contacts&parent=Settings&view=Extension&extensionModule=Google&extensionView=Index&mode=settings', 'LBL_EXTENSIONS']
    ];


    /**
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateSettingsLinks(false);
    }

    /**
     * @throws Exception
     */
    public function deleteCustomLinks(): void
    {
        $this->updateSettingsLinks(false);
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
        $this->getTable('vtiger_google_sync', '')
            ->createTable('googlemodule', 'varchar(50)')
            ->createColumn('user', 'int(10)')
            ->createColumn('synctime', 'datetime')
            ->createColumn('lastsynctime', 'datetime');

        $this->getTable('vtiger_google_sync_fieldmapping', '')
            ->createTable('vtiger_field', 'varchar(255) DEFAULT NULL')
            ->createColumn('google_field', 'varchar(255) DEFAULT NULL')
            ->createColumn('google_field_type', 'varchar(255) DEFAULT NULL')
            ->createColumn('google_custom_label', 'varchar(255) DEFAULT NULL')
            ->createColumn('user', 'int(11) DEFAULT NULL');

        $this->getTable('vtiger_google_sync_settings', '')
            ->createTable('user', 'int(11) DEFAULT NULL')
            ->createColumn('module', 'varchar(50) DEFAULT NULL')
            ->createColumn('clientgroup', 'varchar(255) DEFAULT NULL')
            ->createColumn('direction', 'varchar(50) DEFAULT NULL')
            ->createColumn('enabled', 'tinyint(3) DEFAULT 1');

        $this->getTable('vtiger_google_event_calendar_mapping', '')
            ->createTable('event_id','varchar(255) DEFAULT NULL')
            ->createColumn('calendar_id','varchar(255) DEFAULT NULL')
            ->createColumn('user_id','int(11) NOT NULL');

        $this->getTable('vtiger_google_oauth2', '')
            ->createTable('service','varchar(20) DEFAULT NULL')
            ->createColumn('access_token','varchar(500) DEFAULT NULL')
            ->createColumn('refresh_token','varchar(500) DEFAULT NULL')
            ->createColumn('userid','int(19) DEFAULT NULL');
    }
}
<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Import_Install_Model extends Core_Install_Model
{
    protected string $moduleName = 'Import';

    public array $registerCron = [
        ['Scheduled Import', 'cron/modules/Import/ScheduledImport.service', 900, 'Import', 0, 'Recommended frequency for MailScanner is 15 mins'],
    ];

    public function addCustomLinks(): void
    {
        $this->installTables();
        $this->updateCron();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateCron(false);
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return ['vtiger_import_locks', 'vtiger_import_queue'];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_import_locks', null)
            ->createTable('vtiger_import_lock_id')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('tabid', 'int(11) NOT NULL')
            ->createColumn('importid', 'int(11) NOT NULL')
            ->createColumn('locked_since', 'datetime DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`vtiger_import_lock_id`)');

        $this->getTable('vtiger_import_queue', null)
            ->createTable('importid')
            ->createColumn('userid', 'INT NOT NULL')
            ->createColumn('tabid', 'INT NOT NULL')
            ->createColumn('field_mapping', 'TEXT')
            ->createColumn('default_values', 'TEXT')
            ->createColumn('merge_type', 'INT')
            ->createColumn('merge_fields', 'TEXT')
            ->createColumn('status', 'INT default 0')
            ->createColumn('lineitem_currency_id', 'INT(5) DEFAULT NULL')
            ->createColumn('paging', 'INT(1) DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`importid`)');

        $this->getTable('vtiger_import_maps', 'id')
            ->createTable()
            ->createColumn('name', 'varchar(36) NOT NULL')
            ->createColumn('module', 'varchar(36) NOT NULL')
            ->createColumn('content', 'longblob DEFAULT NULL')
            ->createColumn('has_header', 'int(1) NOT NULL DEFAULT 1')
            ->createColumn('deleted', 'int(1) NOT NULL DEFAULT 0')
            ->createColumn('date_entered', 'timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()')
            ->createColumn('date_modified', 'datetime DEFAULT NULL')
            ->createColumn('assigned_user_id', 'varchar(36) DEFAULT NULL')
            ->createColumn('is_published', 'varchar(3) NOT NULL DEFAULT \'no\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)')
            ->createKey('KEY IF NOT EXISTS `import_maps_assigned_user_id_module_name_deleted_idx` (`assigned_user_id`,`module`,`name`,`deleted`)');
    }
}
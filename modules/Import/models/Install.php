<?php

class Import_Install_Model extends Vtiger_Install_Model
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
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_import_locks', null)
            ->createTable('vtiger_import_lock_id')
            ->createColumn('userid', 'INT NOT NULL')
            ->createColumn('tabid', 'INT NOT NULL')
            ->createColumn('importid', 'INT NOT NULL')
            ->createColumn('locked_since', 'DATETIME')
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
            ->createKey('PRIMARY KEY IF NOT EXISTS(`importid`)');
    }
}
<?php

class Installer_Install_Model extends Core_Install_Model {

    public function addCustomLinks(): void
    {
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
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('df_licenses', 'id')
            ->createTable()
            ->createColumn('name', 'VARCHAR(200)')
            ->createColumn('info', 'TEXT')
        ;
    }
}
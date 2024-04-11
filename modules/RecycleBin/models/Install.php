<?php

class RecycleBin_Install_Model extends Vtiger_Install_Model
{

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [];
    }

    /**
     * @return void
     */
    public function installTables(): void
    {
    }
}
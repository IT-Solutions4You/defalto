<?php

class Import
{
    public string $moduleName = 'Import';
    public string $moduleLabel = 'Import';
    public string $parentName = '';

    /**
     * @throws AppException
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        Vtiger_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}
<?php

class Import extends CRMExtension
{
    public string $moduleName = 'Import';
    public string $moduleLabel = 'Import';
    public string $parentName = '';

    /**
     * @throws AppException
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        Core_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}
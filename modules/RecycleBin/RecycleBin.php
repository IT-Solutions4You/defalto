<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class RecycleBin
{
    public string $moduleLabel = 'Recycle Bin';
    public string $moduleName = 'RecycleBin';
    public string $parentName = 'Tools';

    /**
     * Invoked when special actions are performed on the module.
     * @param String $moduleName Module name
     * @param String $eventType Event Type
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        Vtiger_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}

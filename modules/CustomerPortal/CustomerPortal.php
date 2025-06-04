<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class CustomerPortal extends CRMExtension
{
    public string $moduleName = 'CustomerPortal';
    public string $parentName = '';

    /**
     * Invoked when special actions are performed on the module.
     * @param string $moduleName Module name
     * @param string $eventType Event Type
     * @throws AppException
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        Core_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}

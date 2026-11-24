<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Mention extends CRMExtension
{
    public string $moduleName = 'Mention';
    public string $moduleLabel = 'Mention';
    public string $moduleVersion = '1.0';
    public string $parentName = 'Settings';

    public function vtlib_handler($moduleName, $eventType): void
    {
        Core_Install_Model::getInstance((string)$eventType, $this->moduleName)->install();
    }
}

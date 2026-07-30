<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class DragAndDrop extends CRMExtension
{
    public string $moduleLabel = 'Drag And Drop';
    public string $moduleName = 'DragAndDrop';
    public string $moduleVersion = '1.0';

    /**
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType): void
    {
        Core_Install_Model::getInstance((string)$eventType, (string)$moduleName)->install();
    }
}

<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Import extends CRMExtension
{
    public string $moduleName = 'Import';
    public string $moduleLabel = 'Import';
    public string $parentName = '';

    /**
     * @throws Exception
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        Core_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}
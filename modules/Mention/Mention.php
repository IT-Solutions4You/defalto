<?php
/*
 * Copyright (C) IT-Solutions4You s.r.o. All Rights Reserved.
 * Subject to the original ITS4YouMention license.
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

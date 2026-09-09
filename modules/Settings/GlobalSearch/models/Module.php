<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_GlobalSearch_Module_Model extends Settings_Vtiger_Module_Model
{
    public $name = 'GlobalSearch';

    public static function getInstance($moduleName = 'GlobalSearch'): self
    {
        return new self();
    }

    public function getDefaultUrl(): string
    {
        return 'index.php?module=GlobalSearch&parent=Settings&view=List';
    }

    public function getSettingLinks(): array
    {
        return [];
    }
}

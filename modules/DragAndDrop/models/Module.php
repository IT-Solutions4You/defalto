<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class DragAndDrop_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-cloud-arrow-up';

    public function getDefaultUrl(): string
    {
        return 'index.php?module=Accounts&view=List';
    }

    public function getSettingLinks(): array
    {
        return [];
    }

    public function getDatabaseTables(): array
    {
        return [];
    }

    public function getPicklistFields(): array
    {
        return [];
    }
}

<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_CreateTables_Config
{
    public static array $all = [
        'Vtiger_Module_Model',
        'Vtiger_Field_Model',
        'Vtiger_Block_Model',
        'Vtiger_Record_Model',
        'Core_BlockUiType_Model',
        'Settings_Workflows_Record_Model',
        'Settings_Workflows_TaskRecord_Model',
        'Core_InventoryItemsBlock_Model',
        'Core_RelatedBlock_Model',
        'Core_Tax_Model',
        'Core_TaxRegion_Model',
        'Core_TaxRecord_Model',
        'Settings_Vtiger_MenuItem_Model',
        'Settings_Vtiger_Menu_Model',
        'Core_Modifiers_Model',
        'CustomView_Record_Model',
        'Settings_LayoutEditor_RelatedListSettings_Model',
        'Settings_LayoutEditor_PopupSettings_Model',
        'Core_Country_Model',
    ];

    public static function getAll(): array
    {
        return self::$all;
    }
}


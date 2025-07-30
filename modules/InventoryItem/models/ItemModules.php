<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_ItemModules_Model
{
    public static function getItemModules(): array
    {
        $return = [];
        $db = PearDatabase::getInstance();
        $sql = 'SELECT * FROM df_inventoryitem_itemmodules';
        $result = $db->query($sql);

        while ($row = $db->fetchByAssoc($result)) {
            $module = vtlib_getModuleNameById($row['tabid']);

            if (vtlib_isModuleActive($module)) {
                $return[] = $module;
            }
        }

        return $return;
    }
}
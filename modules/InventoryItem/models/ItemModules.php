<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_ItemModules_Model
{
    public static function getItemModules(): array
    {
        $return = [];

        $itemModules = ['Products', 'Services'];

        foreach ($itemModules as $module) {
            if (vtlib_isModuleActive($module)) {
                $return[] = $module;
            }
        }

        return $return;
    }
}
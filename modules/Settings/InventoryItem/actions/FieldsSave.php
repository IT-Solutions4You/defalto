<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Settings_InventoryItem_FieldsSave_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request): void
    {
        $db = PearDatabase::getInstance();
        $db->pquery('REPLACE INTO its4you_inventoryitemcolumns (tabid, columnslist) VALUES (?,?)', [$request->get('selectedModule'), implode(',', $request->get('columnslist'))]);
        $link = 'index.php?module=InventoryItem&parent=Settings&view=Index';

        if ($request->get('selectedModule')) {
            $link .= '&selectedModule=' . $request->get('selectedModule');
        }

        header('Location: ' . $link);
    }
}
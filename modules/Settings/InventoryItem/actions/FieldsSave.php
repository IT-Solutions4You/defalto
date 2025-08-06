<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_InventoryItem_FieldsSave_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request): void
    {
        $db = PearDatabase::getInstance();
        $db->pquery('REPLACE INTO df_inventoryitemcolumns (tabid, columnslist) VALUES (?,?)', [$request->get('selectedModule'), implode(',', $request->get('columnslist'))]);
        $link = 'index.php?module=InventoryItem&parent=Settings&view=Index';

        if ($request->get('selectedModule')) {
            $link .= '&selectedModule=' . $request->get('selectedModule');
        }

        header('Location: ' . $link);
    }
}
<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_InventoryItem_DecimalsSave_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request): void
    {
        $db = PearDatabase::getInstance();
        $db->pquery('UPDATE df_inventoryitem_quantitydecimals SET decimals = ? WHERE field = ?', [$request->get('quantityDecimals'), 'quantity']);
        $db->pquery('UPDATE df_inventoryitem_quantitydecimals SET decimals = ? WHERE field = ?', [$request->get('priceDecimals'), 'price']);
        $link = 'index.php?module=InventoryItem&parent=Settings&view=Index&mode=decimals';

        header('Location: ' . $link);
    }
}
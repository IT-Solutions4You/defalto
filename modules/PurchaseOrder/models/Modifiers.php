<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PurchaseOrder_Modifiers_Model extends Core_Modifiers_Model
{
    protected static array $modifiers = [
        'DetailView' => ['InventoryItem_DetailView_Modifier'],
        'EditView' => ['InventoryItem_EditView_Modifier'],
    ];
}
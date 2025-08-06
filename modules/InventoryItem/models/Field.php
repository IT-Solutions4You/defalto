<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_Field_Model extends Vtiger_Field_Model
{
    const totalFields = [
        'subtotal',
        'discount_amount',
        'price_after_discount',
        'overall_discount_amount',
        'price_after_overall_discount',
        'discounts_amount',
        'tax_amount',
        'price_total',
        'margin',
        'margin_amount',
    ];
    const excludedFields = ['assigned_user_id', 'description', 'item_text', 'parentid', 'parentitemid', 'sequence', 'discount_type', 'discount', 'overall_discount',];
    const preventDisplay = ['description', 'discount', 'overall_discount',];
}
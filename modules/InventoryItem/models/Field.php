<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_Field_Model extends Vtiger_Field_Model
{
    const computedFields = ['subtotal', 'price_after_discount', 'overall_discount_amount', 'price_after_overall_discount', 'tax_amount', 'price_total', 'margin', 'margin_amount',];
    const totalFields = ['subtotal', 'discount_amount', 'price_after_discount', 'overall_discount_amount', 'price_after_overall_discount', 'tax_amount', 'price_total', 'margin', 'margin_amount',];
    const excludedFields = ['assigned_user_id', 'description', 'item_text', 'parentid', 'parentitemid', 'sequence', 'discount_type', 'discount', 'overall_discount',];
}
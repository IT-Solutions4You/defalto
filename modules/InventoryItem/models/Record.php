<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_Record_Model extends Vtiger_Record_Model
{
    /**
     * @inheritDoc
     */
    public function save()
    {
        $this->recalculate();
        parent::save();
    }

    private function recalculate() {
        $quantity = $this->get('quantity');
        $price = $this->get('price');
        $subtotal = round($quantity * $price, 2);
        $discountPercent = $this->get('discount');

        if ($discountPercent > 0) {
            $discountAmount = round($subtotal * $discountPercent / 100, 2);
        } else {
            $discountAmount = $this->get('discount_amount');
        }

        $priceAfterDiscount = $subtotal - $discountAmount;
        $overallDiscount = $this->get('overall_discount');
        $overallDiscountAmount = 0;

        if ($overallDiscount > 0) {
            $overallDiscountAmount = round($priceAfterDiscount * $overallDiscount / 100, 2);
        }

        $priceAfterOverallDiscount = $priceAfterDiscount - $overallDiscountAmount;
        $tax = $this->get('tax');
        $taxAmount = round($priceAfterOverallDiscount * $tax / 100, 2);
        $priceTotal = $priceAfterOverallDiscount + $taxAmount;

        if ($subtotal != $this->get('subtotal')) {
            $this->set('subtotal', $subtotal);
        }

        if ($discountAmount != $this->get('discount_amount')) {
            $this->set('discount_amount', $discountAmount);
        }

        if ($priceAfterDiscount != $this->get('price_after_discount')) {
            $this->set('price_after_discount', $priceAfterDiscount);
        }

        if ($overallDiscountAmount != $this->get('overall_discount_amount')) {
            $this->set('overall_discount_amount', $overallDiscountAmount);
        }

        if ($priceAfterOverallDiscount != $this->get('price_after_overall_discount')) {
            $this->set('price_after_overall_discount', $priceAfterOverallDiscount);
        }

        if ($taxAmount != $this->get('tax_amount')) {
            $this->set('tax_amount', $taxAmount);
        }

        if ($priceTotal != $this->get('price_total')) {
            $this->set('price_total', $priceTotal);
        }
    }
}
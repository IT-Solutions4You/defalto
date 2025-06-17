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
        $productId = $this->get('productid');

        if ($productId) {
            $this->recalculate();
        }

        parent::save();
    }

    private function recalculate()
    {
        $quantity = (float)$this->get('quantity');
        $price = (float)$this->get('price');
        $subtotal = round($quantity * $price, 2);
        $discountType = $this->get('discount_type');
        $discount = $this->get('discount');
        $discountAmount = 0;

        switch ($discountType) {
            case 'Percentage':
                $discountAmount = $subtotal * $discount / 100;
                break;
            case 'Direct':
                $discountAmount = $this->get('discount_amount');
                break;
            case 'Discount per Unit':
                $discountAmount = $quantity * $discount;
        }

        $priceAfterDiscount = $subtotal - $discountAmount;
        $overallDiscount = $this->get('overall_discount');
        $overallDiscountAmount = 0;

        if ($overallDiscount > 0) {
            $overallDiscountAmount = round($priceAfterDiscount * $overallDiscount / 100, 2);
        }

        $priceAfterOverallDiscount = $priceAfterDiscount - $overallDiscountAmount;
        $purchaseCost = $this->get('purchase_cost');
        $margin = $marginAmount = 0;

        if ($purchaseCost > 0) {
            $marginAmount = $priceAfterOverallDiscount - ($purchaseCost * $quantity);
            $margin = (100 * ($priceAfterOverallDiscount - $marginAmount)) / $priceAfterOverallDiscount;
        }

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

        if ($margin != $this->get('margin')) {
            $this->set('margin', $margin);
        }

        if ($marginAmount != $this->get('margin_amount')) {
            $this->set('margin_amount', $marginAmount);
        }
    }
}
<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_Record_Model extends Vtiger_Record_Model
{
    /**
     * @inheritDoc
     */
    public function save(): void
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
        $discountsAmount = $discountAmount + $overallDiscountAmount;
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

        if ($discountsAmount != $this->get('discounts_amount')) {
            $this->set('discounts_amount', $discountsAmount);
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

    /**
     * @param int $taxId
     *
     * @return void
     * @throws Exception
     */
    public function saveTaxId(int $taxId): void
    {
        if (!$this->getId()) {
            return;
        }

        $taxPercentage = $this->get('tax');
        $taxRel = $this->retrieveTaxRel();
        $oldTaxId = $taxRel['taxId'];
        $oldTaxPercentage = $taxRel['percentage'];

        if ($oldTaxId === $taxId && $oldTaxPercentage == $taxPercentage) {
            return;
        }

        $taxRecord = Core_TaxRecord_Model::getInstance($this->getId());
        $taxRecord->set('record_id', $this->getId());
        $taxRecord->set('tax_id', $oldTaxId);
        $taxRecord->retrieveId();

        if (empty($taxPercentage)) {
            $taxRecord->delete();
        } else {
            $taxRecord->set('tax_id', $taxId);
            $taxRecord->set('percentage', $taxPercentage);
            $taxRecord->set('amount', $this->get('tax_amount'));
            $taxRecord->save();
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function retrieveTaxRel(): array
    {
        $taxRecord = Core_TaxRecord_Model::getInstance($this->getId());
        /** @var Core_Tax_Model $tax */
        foreach ($taxRecord->getTaxes() as $tax) {
            if ($tax->isActiveForRecord()) {
                return [
                    'taxId' => $tax->getId(),
                    'percentage' => $tax->getPercentage(),
                ];
            }
        }

        return [
            'taxId' => 0,
            'percentage' => 0.0,
        ];
    }
}
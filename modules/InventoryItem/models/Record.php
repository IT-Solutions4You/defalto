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
     */
    public function saveTaxId(int $taxId)
    {
        if (!$this->getId()) {
            return;
        }

        $db = PearDatabase::getInstance();
        $taxRel = $this->retrieveTaxRel();
        $oldTaxId = $taxRel['taxid'];
        $oldTaxPercentage = $taxRel['percentage'];

        if ($oldTaxId === $taxId && $oldTaxPercentage == $this->get('tax')) {
            return;
        }

        $sql = 'INSERT INTO df_inventoryitemtaxrel (inventoryitemid, taxid, percentage, amount) VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE taxid = ?, percentage = ?, amount = ?';
        $params = [
            $this->getId(),
            $taxId,
            $this->get('tax'),
            $this->get('tax_amount'),
            $taxId,
            $this->get('tax'),
            $this->get('tax_amount'),
        ];
        $db->pquery($sql, $params);
    }

    /**
     * @return array
     */
    public function retrieveTaxRel(): array
    {
        $db = PearDatabase::getInstance();
        $taxId = 0;
        $percentage = 0.0;

        if (!$this->getId()) {
            return [
                'taxId'      => $taxId,
                'percentage' => $percentage,
            ];
        }

        $result = $db->pquery('SELECT taxid, percentage FROM df_inventoryitemtaxrel WHERE inventoryitemid = ?', [$this->getId()]);

        if ($db->num_rows($result)) {
            $row = $db->fetchByAssoc($result);
            $taxId = (int)$row['taxid'];
            $percentage = (float)$row['percentage'];
        }

        return [
            'taxId'      => $taxId,
            'percentage' => $percentage,
        ];
    }
}
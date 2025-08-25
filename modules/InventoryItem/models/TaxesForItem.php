<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_TaxesForItem_Model
{
    protected int $inventoryItemId;
    protected int $productId;
    protected int $parentId;
    protected array $taxes;

    /**
     * @param int $inventoryItemId
     * @param int $productId
     * @param int $parentId
     */
    public function __construct(int $inventoryItemId, int $productId, int $parentId)
    {
        $this->inventoryItemId = $inventoryItemId;
        $this->productId = $productId;
        $this->parentId = $parentId;
        $this->taxes = InventoryItem_Utils_Helper::getTaxesForProduct($this->productId);
    }

    /**
     * @param int $inventoryItemId
     * @param int $productId
     * @param int $parentId
     *
     * @return array
     */
    public static function fetchTaxes(int $inventoryItemId, int $productId, int $parentId): array
    {
        $model = new self($inventoryItemId, $productId, $parentId);

        return $model->fetchTaxesForItem();
    }

    /**
     * @return array
     */
    protected function fetchTaxesForItem(): array
    {
        $selectedTaxId = $this->fetchSelectedTaxForItem();

        if (isset($this->taxes[$selectedTaxId])) {
            $this->taxes[$selectedTaxId]['selected'] = true;
        }

        return $this->adaptPercentageForRegion();
    }

    /**
     * @return int
     * @throws Exception
     */
    private function fetchSelectedTaxForItem(): int
    {
        $taxeRecord = Core_TaxRecord_Model::getInstance($this->inventoryItemId);

        /** @var Core_Tax_Model $tax */
        foreach ($taxeRecord->getTaxes() as $tax) {
            if ($tax->isActiveForRecord()) {
                return $tax->getId();
            }
        }

        return 0;
    }

    /**
     * @return array
     */
    protected function adaptPercentageForRegion(): array
    {
        $regionId = $this->getRegionForRecord();

        if (!$regionId) {
            return $this->taxes;
        }

        foreach ($this->taxes as $taxId => $taxData) {
            if (!isset($taxData['regions']) || empty($taxData['regions']) || strlen($taxData['regions']) === 2) {
                continue;
            }

            $regions = json_decode($taxData['regions']);

            if (isset($regions->$regionId)) {
                $this->taxes[$taxId]['percentage'] = number_format($regions->$regionId, 2);
            }
        }

        return $this->taxes;
    }

    /**
     * @return int
     */
    private function getRegionForRecord(): int
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($this->parentId, getSalesEntityType($this->parentId));
        $regionId = $recordModel->get('region_id');

        return (int)$regionId;
    }
}
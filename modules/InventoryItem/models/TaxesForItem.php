<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
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
     */
    private function fetchSelectedTaxForItem(): int
    {
        $taxId = 0;
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT taxid FROM df_inventoryitemtaxrel WHERE inventoryitemid = ?', [$this->inventoryItemId]);

        if ($db->num_rows($result)) {
            $row = $db->fetchByAssoc($result);
            $taxId = (int)$row['taxid'];
        }

        return $taxId;
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
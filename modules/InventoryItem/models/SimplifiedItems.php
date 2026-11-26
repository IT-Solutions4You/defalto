<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Items of the simplified product block. The rows (item name, quantity, unit, price) are posted together with the edit form
 * of the document and saved as InventoryItem records when the document is saved.
 */
class InventoryItem_SimplifiedItems_Model extends Vtiger_Base_Model
{
    public const REQUEST_MARKER = 'simplifiedItemsPosted';
    public const REQUEST_ROWS = 'simplified_items';

    /** The totals update saves the document again, which must not save the posted rows twice */
    protected static bool $saving = false;

    /**
     * @param int $parentId
     *
     * @return array rows to show in the form: id, item_text, description, productid, module, is_text, quantity, unit, price, tax, taxid, purchase_cost, pricebookid, total
     *               (numbers as strings with a decimal point, the total is with the tax). A text row has no product, quantity, price, tax or description.
     */
    public static function getRows(int $parentId): array
    {
        $rows = [];
        $decimals = InventoryItem_Utils_Helper::fetchDecimals();

        if (!$parentId) {
            return $rows;
        }

        $items = InventoryItem_Module_Model::fetchItemsForId($parentId, true);
        $descriptions = self::fetchDescriptions(array_column($items, 'inventoryitemid'));

        foreach ($items as $item) {
            $itemText = (string)$item['item_text'];

            if ('' === $itemText && !empty($item['productid'])) {
                $itemText = (string)(getEntityName((string)getSalesEntityType($item['productid']), [$item['productid']])[$item['productid']] ?? '');
            }

            $productId = (int)($item['productid'] ?? 0);
            $quantity = (float)$item['quantity'];
            $price = (float)$item['price'];
            // The total of the row as the system calculated it (with the tax and with the discounts of the row, if there are any)
            $total = (float)$item['price_total'] ?: self::calculateTotal($quantity, $price, (float)$item['tax']);

            // Numbers are formatted with a decimal point, the way the edit form posts them
            $isText = !$productId;
            $rows[] = [
                'id' => (int)$item['inventoryitemid'],
                'item_text' => $itemText,
                'description' => $isText ? '' : (string)($descriptions[(int)$item['inventoryitemid']] ?? ''),
                'productid' => $productId,
                // Module of the product or service, empty for a text row
                'module' => $productId ? (string)getSalesEntityType($productId) : '',
                'is_text' => $isText,
                'quantity' => $isText ? '' : number_format($quantity, $decimals['quantity'] ?? 2, '.', ''),
                'unit' => $isText ? '' : (string)$item['unit'],
                'price' => $isText ? '' : number_format($price, $decimals['price'] ?? 2, '.', ''),
                'tax' => $isText ? '' : number_format((float)$item['tax'], 2, '.', ''),
                'taxid' => $isText ? 0 : (int)($item['tax_id'] ?? 0),
                // Not shown in the block, but posted back with the row, so a row which is not changed keeps them
                // The discount of the item (set in the standard block) is kept, the edit view only uses it for the total of the row
                'discount_type' => $isText ? '' : (string)$item['discount_type'],
                'discount' => $isText ? 0 : (float)$item['discount'],
                'discount_amount' => $isText ? 0 : (float)$item['discount_amount'],
                'purchase_cost' => $isText ? '' : number_format((float)$item['purchase_cost'], $decimals['price'] ?? 2, '.', ''),
                'pricebookid' => $isText ? 0 : (int)($item['pricebookid'] ?? 0),
                'total' => $isText ? '' : number_format($total, 2, '.', ''),
            ];
        }

        return $rows;
    }

    /**
     * @param array $itemIds
     *
     * @return array description by item id
     */
    protected static function fetchDescriptions(array $itemIds): array
    {
        $itemIds = array_values(array_filter(array_map('intval', $itemIds)));
        $descriptions = [];

        if (empty($itemIds)) {
            return $descriptions;
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT crmid, description FROM vtiger_crmentity WHERE crmid IN (' . generateQuestionMarks($itemIds) . ')', $itemIds);

        while ($row = $db->fetchByAssoc($result)) {
            $descriptions[(int)$row['crmid']] = (string)$row['description'];
        }

        return $descriptions;
    }

    /**
     * The same calculation as InventoryItem_Record_Model::recalculate() does for a row without discounts: the tax is rounded per row
     *
     * @param float $quantity
     * @param float $price
     * @param float $taxPercentage
     *
     * @return float total of the row with the tax
     */
    public static function calculateTotal(float $quantity, float $price, float $taxPercentage): float
    {
        $subtotal = round($quantity * $price, 2);

        return $subtotal + round($subtotal * $taxPercentage / 100, 2);
    }

    /**
     * Totals of the document for the read-only views: total without the tax, the tax, total with the tax, adjustment and grand total
     *
     * @param Vtiger_Viewer $viewer
     * @param int           $parentId
     *
     * @return void
     * @throws Exception
     */
    public static function assignTotals(Vtiger_Viewer $viewer, int $parentId): void
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $recordModel = $parentId ? Vtiger_Record_Model::getInstanceById($parentId, getSalesEntityType($parentId)) : null;

        $viewer->assign('PRICE_WITHOUT_VAT_DISPLAY', $recordModel ? (string)$recordModel->getCurrencyDisplayValue('price_after_overall_discount', $user) : '');
        $viewer->assign('VAT_DISPLAY', $recordModel ? (string)$recordModel->getCurrencyDisplayValue('tax_amount', $user) : '');
        $viewer->assign('PRICE_TOTAL_DISPLAY', $recordModel ? (string)$recordModel->getCurrencyDisplayValue('price_total', $user) : '');
        $viewer->assign('ADJUSTMENT_DISPLAY', $recordModel ? (string)$recordModel->getCurrencyDisplayValue('adjustment', $user, true) : '');
        $viewer->assign('GRAND_TOTAL_DISPLAY', $recordModel ? (string)$recordModel->getCurrencyDisplayValue('grand_total', $user) : '');
    }

    /**
     * @return array the taxes defined in the settings which are active: taxid, tax_label, percentage, regions (JSON of the percentage by region).
     *               Empty when the company does not use taxes, the block then has no tax column.
     * @throws Exception
     */
    public static function getTaxes(): array
    {
        $taxes = [];

        foreach (Core_Tax_Model::getSimpleTaxes() as $taxModel) {
            $regions = [];

            foreach ($taxModel->getRegions() as $region) {
                $regions[$region->getId()] = $region->get('percentage');
            }

            $taxes[(int)$taxModel->getId()] = [
                'taxid' => (int)$taxModel->getId(),
                'tax_label' => (string)$taxModel->getLabel(),
                'percentage' => number_format((float)$taxModel->getPercentage(), 2, '.', ''),
                'regions' => json_encode($regions),
            ];
        }

        return $taxes;
    }

    /**
     * Values for the templates of the simplified block, the same for the edit view, the detail view and the widget
     *
     * @param Vtiger_Viewer $viewer
     *
     * @return void
     * @throws Exception
     */
    public static function assignCommon(Vtiger_Viewer $viewer): void
    {
        $taxes = self::getTaxes();

        $viewer->assign('SIMPLIFIED_UNITS', self::getUnits());
        $viewer->assign('SIMPLIFIED_UNITS_BY_MODULE', self::getUnitsByModule());
        $viewer->assign('SIMPLIFIED_TAXES', $taxes);
        // Name, quantity, unit, price, [tax], total
        $viewer->assign('SIMPLIFIED_COLUMNS', empty($taxes) ? 5 : 6);
    }

    /**
     * @return array unit value => label of the units of the products and of the services, as the product or service itself offers them
     */
    public static function getUnitsByModule(): array
    {
        require_once 'modules/PickList/PickListUtils.php';

        $units = ['Products' => [], 'Services' => []];

        foreach (array_keys(getAllPickListValues('usageunit')) as $unit) {
            $units['Products'][(string)$unit] = InventoryItem_Utils_Helper::getUnitLabel((string)$unit, 'Products');
        }

        foreach (array_keys(getAllPickListValues('service_usageunit')) as $unit) {
            $units['Services'][(string)$unit] = InventoryItem_Utils_Helper::getUnitLabel((string)$unit, 'Services');
        }

        return $units;
    }

    /**
     * @return array unit value => label of all the units, for the read-only views
     */
    public static function getUnits(): array
    {
        require_once 'modules/PickList/PickListUtils.php';

        $units = [];

        foreach (array_keys(getAllPickListValues('usageunit')) as $unit) {
            $units[(string)$unit] = InventoryItem_Utils_Helper::getUnitLabel((string)$unit);
        }

        // Services have their own units
        foreach (array_keys(getAllPickListValues('service_usageunit')) as $unit) {
            $units[(string)$unit] ??= InventoryItem_Utils_Helper::getUnitLabel((string)$unit, 'Services');
        }

        return $units;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return int document the rows of a new document are taken from (duplicate, conversion), 0 for none
     */
    public static function getSourceRecordId(Vtiger_Request $request): int
    {
        if (is_numeric($request->get('duplicateFrom'))) {
            return (int)$request->get('duplicateFrom');
        }

        $sourceRecord = (int)$request->get('sourceRecord');

        if ($sourceRecord && InventoryItem_Utils_Helper::isInventoryModule((string)$request->get('sourceModule'))) {
            return $sourceRecord;
        }

        foreach (['quote_id', 'salesorder_id', 'invoice_id'] as $fieldName) {
            if ((int)$request->get($fieldName)) {
                return (int)$request->get($fieldName);
            }
        }

        return 0;
    }

    /**
     * Saves the rows posted by the edit form of the document. The permission to change the document is the one
     * that allowed saving the document itself.
     *
     * @param CRMEntity $entity
     *
     * @return bool true when the rows were posted, so the items must not be copied from another document
     * @throws Exception
     */
    public static function saveFromRequest(CRMEntity $entity): bool
    {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        if (self::$saving || !$request->get(self::REQUEST_MARKER) || !InventoryItem_Module_Model::isSimplifiedBlock()) {
            return false;
        }

        $parentId = (int)$entity->id;
        $posted = $request->get(self::REQUEST_ROWS);

        if (!$parentId || !InventoryItem_Utils_Helper::isInventoryModule((string)$entity->moduleName)) {
            return false;
        }

        self::$saving = true;

        try {
            self::saveRows($entity, $parentId, is_array($posted) ? $posted : []);
        } finally {
            self::$saving = false;
        }

        return true;
    }

    /**
     * @param CRMEntity $entity
     * @param int       $parentId
     * @param array     $rows
     *
     * @return void
     * @throws Exception
     */
    protected static function saveRows(CRMEntity $entity, int $parentId, array $rows): void
    {
        $existingIds = array_column(InventoryItem_Module_Model::fetchItemsForId($parentId, true), 'inventoryitemid');
        $existingIds = array_map('intval', $existingIds);
        $ownerId = (int)($entity->column_fields['assigned_user_id'] ?? 0) ?: (int)Users_Record_Model::getCurrentUserModel()->getId();
        $sequence = 0;
        $keptIds = [];
        $taxes = self::getTaxes();
        $currencyId = (int)($entity->column_fields['currency_id'] ?? 0);
        // The items take the overall discount of the document, the same as the items added in the standard block
        $overallDiscount = (float)($entity->column_fields['overall_discount'] ?? 0);
        $purchaseCostEditable = (bool)Vtiger_Module_Model::getInstance('InventoryItem')->getField('purchase_cost')?->isEditable();

        // The order of the rows is the position set by dragging them (the sort is stable, rows without a position keep the posted order)
        $rows = array_values($rows);
        usort($rows, static fn($a, $b) => (int)($a['position'] ?? 0) <=> (int)($b['position'] ?? 0));

        foreach ($rows as $row) {
            $itemId = (int)($row['id'] ?? 0);

            // Only the items of this document can be changed
            if ($itemId && !in_array($itemId, $existingIds, true)) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $itemText = trim((string)($row['item_text'] ?? ''));

            if (!empty($row['deleted']) || '' === $itemText) {
                continue;
            }

            $itemModel = $itemId ? Vtiger_Record_Model::getInstanceById($itemId, 'InventoryItem') : Vtiger_Record_Model::getCleanInstance('InventoryItem');

            if ($itemId) {
                $itemModel->set('mode', 'edit');
            } else {
                $itemModel->set('assigned_user_id', $ownerId);
            }

            $productId = self::getValidProductId($row['productid'] ?? 0);
            $isText = !$productId;
            $taxId = 0;
            $storedProductId = $itemId ? (int)$itemModel->get('productid') : 0;
            $productChanged = $productId !== $storedProductId;

            $itemModel->set('item_text', $itemText);
            $itemModel->set('productid', $productId);

            if ($isText) {
                // A text row only has the text
                $itemModel->set('quantity', 0);
                $itemModel->set('price', 0);
                $itemModel->set('unit', '');
                $itemModel->set('tax', 0);
                $itemModel->set('description', '');
                $itemModel->set('purchase_cost', 0);
                $itemModel->set('pricebookid', 0);
            } else {
                $itemModel->set('description', trim((string)($row['description'] ?? '')));
                self::setPurchaseCostAndPriceBook($itemModel, $row, $productId, $currencyId, $productChanged, $purchaseCostEditable, (int)($entity->column_fields['pricebookid'] ?? 0));
                $itemModel->set('quantity', self::parseNumber($row['quantity'] ?? 0));
                $itemModel->set('price', self::parseNumber($row['price'] ?? 0));
                $itemModel->set('unit', InventoryItem_Utils_Helper::getUnitValue((string)($row['unit'] ?? '')));

                if (!empty($taxes)) {
                    // The tax is one of the taxes defined in the settings
                    $taxId = isset($taxes[(int)($row['taxid'] ?? 0)]) ? (int)$row['taxid'] : 0;
                    // The percentage is the one of the selected tax (it depends on the tax region) or the one saved with the row earlier
                    $itemModel->set('tax', max(0, self::parseNumber($row['tax'] ?? 0)));
                }
            }

            $itemModel->set('parentid', $parentId);
            $itemModel->set('overall_discount', $overallDiscount);
            $itemModel->set('sequence', ++$sequence);
            // The totals of a row are calculated by the system, whatever the profile allows the user to edit
            $itemModel->getEntity()->isWorkFlowFieldUpdate = true;
            $itemModel->recalculate();
            $itemModel->save();

            if (!$isText && !empty($taxes) && ($taxId || !(float)$itemModel->get('tax'))) {
                $itemModel->saveTaxId($taxId);
            }

            $keptIds[] = (int)$itemModel->getId();
        }

        foreach (array_diff($existingIds, $keptIds) as $removedId) {
            Vtiger_Record_Model::getInstanceById($removedId, 'InventoryItem')->delete();
        }

        InventoryItem_ParentEntity_Model::updateTotals($parentId);
    }

    /**
     * The purchase cost of the product is saved with the row, the margin of the row is calculated from it. The row posts the cost it
     * was filled with when the product was selected. A user who cannot edit the purchase cost gets the cost of the product,
     * and an existing row keeps its stored values unless its product was changed.
     *
     * @param Vtiger_Record_Model $itemModel
     * @param array               $row
     * @param int                 $productId
     * @param int                 $currencyId currency of the document
     * @param bool                $productChanged
     * @param bool                $purchaseCostEditable
     * @param int                 $documentPriceBookId
     *
     * @return void
     * @throws Exception
     */
    protected static function setPurchaseCostAndPriceBook(Vtiger_Record_Model $itemModel, array $row, int $productId, int $currencyId, bool $productChanged, bool $purchaseCostEditable, int $documentPriceBookId): void
    {
        if ($purchaseCostEditable && isset($row['purchase_cost']) && '' !== trim((string)$row['purchase_cost'])) {
            $itemModel->set('purchase_cost', max(0, self::parseNumber($row['purchase_cost'])));
        } elseif ($productChanged || '' === (string)$itemModel->get('purchase_cost')) {
            $itemModel->set('purchase_cost', InventoryItem_Utils_Helper::getItemPurchaseCost($productId, $currencyId));
        }

        if (isset($row['pricebookid'])) {
            $itemModel->set('pricebookid', max(0, (int)$row['pricebookid']));
        } elseif ($productChanged) {
            $itemModel->set('pricebookid', InventoryItem_Utils_Helper::decideItemPriceAndPriceBook($productId, $currencyId, $documentPriceBookId)['priceBookId']);
        }
    }

    /**
     * @param mixed $value product or service the row was selected from
     *
     * @return int the record, or 0 for a text row or a record which cannot be used as an item
     */
    protected static function getValidProductId($value): int
    {
        $productId = (int)$value;

        if (!$productId) {
            return 0;
        }

        $moduleName = (string)getSalesEntityType($productId);

        if (!in_array($moduleName, InventoryItem_ItemModules_Model::getItemModules(), true) || !Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $productId)) {
            return 0;
        }

        return $productId;
    }

    /**
     * @param mixed $value number with a decimal point or comma
     *
     * @return float
     */
    protected static function parseNumber($value): float
    {
        return (float)str_replace(',', '.', trim((string)$value));
    }
}

<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_SaveItemsBlockDetail_Action extends Vtiger_SaveAjax_Action
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (method_exists($this, $mode)) {
            $this->$mode($request);
        }
    }

    /**
     * Changes the PriceBook and updates prices of Products.
     * If the Product is found in the PriceBook, it updates the price and the PriceBook for given InventoryItem.
     * If the PriceBook was in fact removed, then Products with price from this PriceBook will get standard price.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    protected function savePriceBook(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('for_record');
        $moduleName = $request->get('for_module');
        $priceBookId = (int)$request->get('pricebookid');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $oldPriceBookId = $recordModel->get('pricebookid');
        $recordModel->set('pricebookid', $priceBookId);
        $currencyId = $recordModel->get('currency_id');
        $recordModel->save();

        $priceBookModel = false;
        $toNewCurrency = 1;

        if ($priceBookId) {
            $priceBookModel = Vtiger_Record_Model::getInstanceById($priceBookId, 'PriceBooks');
            $priceBookCurrencyId = $priceBookModel->get('currency_id');

            if ($priceBookCurrencyId != $currencyId) {
                $currenciesConversionTable = InventoryItem_Utils_Helper::getCurrenciesConversionTable();
                $toBaseCurrency = 1 / $currenciesConversionTable[$oldPriceBookId];
                $toNewCurrency = $toBaseCurrency * $currenciesConversionTable[$currencyId];
            }
        }

        $items = $this->fetchItems($recordId);

        foreach ($items as $item) {
            if (!$item['productid']) {
                // Text lines
                continue;
            }

            $recordModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');

            if (!$priceBookId && $oldPriceBookId != $item['pricebookid']) {
                // The global pricebook was removed, but current line has price from different pricebook
                continue;
            }

            $changed = false;
            $price = 0;

            if ($priceBookModel) {
                $price = $priceBookModel->getProductsListPrice($item['productid']);
            }

            if (!$price) {
                $price = $recordModel->get('price');

                if (!$priceBookId) {
                    // handle removed pricebook
                    $recordModel->set('pricebookid', $priceBookId);
                    $changed = true;
                    $currencyPriceList = Products_Record_Model::getListPriceValues($item['productid']);

                    if (isset($currencyPriceList[$currencyId])) {
                        $price = $currencyPriceList[$currencyId];
                    } else {
                        $itemModel = Vtiger_Record_Model::getInstanceById($item['productid'], getSalesEntityType($item['productid']));
                        $price = $itemModel->get('unit_price');
                    }
                }
            } else {
                $price *= $toNewCurrency;

                if ($priceBookId != $recordModel->get('pricebookid')) {
                    $recordModel->set('pricebookid', $priceBookId);
                    $changed = true;
                }
            }

            if ($price != $recordModel->get('price')) {
                $recordModel->set('price', $price);
                $changed = true;
            }

            if ($changed) {
                $recordModel->set('mode', 'edit');
                $recordModel->save();
            }
        }

        InventoryItem_ParentEntity_Model::updateTotals($recordId);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    protected function saveRegion(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('for_record');
        $moduleName = $request->get('for_module');
        $regionId = (float)$request->get('region_id');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $recordModel->set('region_id', $regionId);
        $recordModel->save();

        $items = $this->fetchItems($recordId);

        foreach ($items as $item) {
            if (!$item['productid']) {
                // Text lines
                continue;
            }

            $taxes = InventoryItem_TaxesForItem_Model::fetchTaxes((int)$item['inventoryitemid'], (int)$item['productid'], (int)$item['parentid']);

            if (empty($taxes)) {
                continue;
            }

            foreach ($taxes as $tax) {
                if (isset($tax['selected']) && (float)$tax['percentage'] != (float)$item['tax']) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
                    $recordModel->set('tax', $tax['percentage']);
                    $recordModel->set('mode', 'edit');
                    $recordModel->save();
                    $entity = $recordModel->getEntity();
                    $entity->saveTaxId($tax['taxid']);
                }
            }
        }

        InventoryItem_ParentEntity_Model::updateTotals($recordId);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    protected function saveOverallDiscount(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();
        $recordId = (int)$request->get('for_record');
        $discount = (float)$request->get('overall_discount_percent');

        $items = $this->fetchItems($recordId);

        foreach ($items as $item) {
            $recordModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
            $recordModel->set('overall_discount', $discount);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
        }

        InventoryItem_ParentEntity_Model::updateTotals($recordId);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    protected function saveAdjustment(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('for_record');
        $moduleName = $request->get('for_module');
        $adjustment = (float)$request->get('adjustment');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $recordModel->set('adjustment', $adjustment);
        $recordModel->save();

        InventoryItem_ParentEntity_Model::updateTotals($recordId);
    }

    /**
     * Saves new currency and recalculates all Product prices into new currency.
     * Implemented logic:
     * If the InventoryItem record has a selected PriceBook, then:
     *  - Check if the selected PriceBook is in the currency we are switching to
     *  -   - If yes, set the price from the PriceBook
     *  -   - Otherwise, recalculate the given price based on the exchange rate
     * If there is no PriceBook, then:
     *  - If the product has a defined price in the new currency, set this price
     *  - Otherwise, recalculate the current price based on the exchange rate
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    protected function saveCurrency(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('for_record');
        $moduleName = $request->get('for_module');
        $currencyId = (float)$request->get('currency_id');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $oldCurrencyId = $recordModel->get('currency_id');
        $recordModel->set('currency_id', $currencyId);
        $recordModel->save();

        $currenciesConversionTable = InventoryItem_Utils_Helper::getCurrenciesConversionTable();
        $toBaseCurrency = 1 / $currenciesConversionTable[$oldCurrencyId];
        $toNewCurrency = $toBaseCurrency * $currenciesConversionTable[$currencyId];

        $items = $this->fetchItems($recordId);

        foreach ($items as $item) {
            if (!$item['productid']) {
                continue;
            }

            $recordModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
            $priceBookId = $recordModel->get('pricebookid');

            if ($priceBookId) {
                $priceBookModel = Vtiger_Record_Model::getInstanceById($priceBookId, 'PriceBooks');
                $priceBookCurrencyId = $priceBookModel->get('currency_id');

                if ($priceBookCurrencyId != $currencyId) {
                    $price = $recordModel->get('price') * $toNewCurrency;
                } else {
                    $price = $priceBookModel->getProductsListPrice($item['productid']);
                }
            } else {
                $currencyPriceList = Products_Record_Model::getListPriceValues($item['productid']);

                if (isset($currencyPriceList[$currencyId])) {
                    $price = $currencyPriceList[$currencyId];
                } else {
                    $price = $recordModel->get('price') * $toNewCurrency;
                }
            }

            $recordModel->set('price', $price);
            $recordModel->set('purchase_cost', $recordModel->get('purchase_cost') * $toNewCurrency);
            $discountType = $recordModel->get('discount_type');

            if ($discountType == 'Direct') {
                $recordModel->set('discount_amount', $recordModel->get('discount_amount') * $toNewCurrency);
            } elseif ($discountType == 'Discount per Unit') {
                $recordModel->set('discount', $recordModel->get('discount') * $toNewCurrency);
            }

            $recordModel->set('mode', 'edit');
            $recordModel->save();
        }

        InventoryItem_ParentEntity_Model::updateTotals($recordId);
    }

    /**
     * @param int $parentId
     *
     * @return array
     */
    protected function fetchItems(int $parentId): array
    {
        $db = PearDatabase::getInstance();
        $items = [];
        $sql = 'SELECT df_inventoryitem.*, df_inventoryitemcf.* 
                FROM df_inventoryitem
                    LEFT JOIN df_inventoryitemcf ON df_inventoryitemcf.inventoryitemid = df_inventoryitem.inventoryitemid
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                WHERE parentid = ?   
                    AND productid IS NOT NULL
                    AND productid <> 0';
        $res = $db->pquery($sql, [$parentId]);

        while ($row = $db->fetchByAssoc($res)) {
            $items[] = $row;
        }

        return $items;
    }
}
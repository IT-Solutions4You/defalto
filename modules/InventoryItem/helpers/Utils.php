<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_Utils_Helper
{

    /**
     * Get items for given record.
     *
     * @param int $record
     *
     * @return array[]
     * @throws AppException
     */
    public static function fetchItems(int $record): array
    {
        $inventoryItems = [[],];
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = 'InventoryItem';
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $fieldModelList = $moduleModel->getFields();

        $sql = 'SELECT df_inventoryitem.*, df_inventoryitemcf.*, vtiger_crmentity.description 
            FROM df_inventoryitem
            LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid
            LEFT JOIN df_inventoryitemcf ON df_inventoryitemcf.inventoryitemid = df_inventoryitem.inventoryitemid
            WHERE vtiger_crmentity.deleted = 0
            AND df_inventoryitem.parentid = ?
            ORDER BY df_inventoryitem.sequence, vtiger_crmentity.crmid';
        $result = $db->pquery($sql, [$record]);

        while ($row = $db->fetchByAssoc($result)) {
            if (empty($row['productid']) && !empty($row['item_text'])) {
                $row['entityType'] = 'Text';
            } else {
                $row['entityType'] = getSalesEntityType($row['productid']);
                $row['isDeleted'] = !isRecordExists($row['productid']);

                if (empty($row['item_text'])) {
                    $row['item_text'] = getEntityName($row['entityType'], $row['productid'])[$row['productid']];
                }

                if (!$row['isDeleted']) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($row['productid'], $row['entityType']);

                    if (method_exists($recordModel, 'isBundle') && $recordModel->isBundle() && method_exists(
                            $recordModel,
                            'isBundleViewable'
                        ) && $recordModel->isBundleViewable()) {
                        $subProducts = $recordModel->getSubProducts();
                        $row['subProducts'] = $subProducts;
                    }
                }

                $recordModel = Vtiger_Record_Model::getInstanceById($row['inventoryitemid'], $moduleName);

                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    $fieldValue = $recordModel->get($fieldName);
                    $fieldValue = $fieldModel->getUITypeModel()->getDisplayValue($fieldValue);
                    $row[$fieldName . '_display'] = $fieldValue;
                }
            }

            foreach (InventoryItem_RoundValues_Helper::$roundValues as $fieldName) {
                $row[$fieldName] = number_format((float)$row[$fieldName], 2, '.', '');
                $row[$fieldName . '_display'] = CurrencyField::convertToUserFormat($row[$fieldName], $currentUser, true);
            }

            $decimals = InventoryItem_Utils_Helper::fetchDecimals();

            foreach ($decimals as $fieldName => $decimalCount) {
                if (isset($row[$fieldName])) {
                    $row[$fieldName . '_display'] = number_format($row[$fieldName], $decimalCount, '.', '');
                }
            }

            $row['taxes'] = InventoryItem_TaxesForItem_Model::fetchTaxes((int)$row['inventoryitemid'], (int)$row['productid'], $record);

            $inventoryItems[] = $row;
        }

        unset($inventoryItems[0]);

        return $inventoryItems;
    }

    /**
     * @return array
     */
    public static function getCurrenciesConversionTable(): array
    {
        $db = PearDatabase::getInstance();
        $currencies = [];
        $sql = 'SELECT id, conversion_rate FROM vtiger_currency_info WHERE deleted = 0 AND currency_status = ?';
        $res = $db->pquery($sql, ['Active']);

        while ($row = $db->fetchByAssoc($res)) {
            $currencies[$row['id']] = $row['conversion_rate'];
        }

        return $currencies;
    }

    /**
     * @param int $productId
     *
     * @return array
     * @throws AppException
     */
    public static function getTaxesForProduct(int $productId): array
    {
        $taxRecordModel = Core_TaxRecord_Model::getInstance($productId);
        $taxModels = $taxRecordModel->getTaxes();
        $taxInfo = $taxRecordModel->getTaxesInfo();
        $taxes = [];

        if (count($taxInfo)) {
            foreach ($taxInfo as $taxId => $taxData) {
                $tax = $taxModels[$taxId];
                unset($taxData['default']);
                $taxes[$taxId] = $tax->getSaveParams();
                $taxes[$taxId]['regions'] = json_encode($taxData);
                $taxes[$taxId]['taxid'] = $taxId;
                $taxes[$taxId]['percentage'] = number_format($taxes[$taxId]['percentage'], 2);
            }
        } else {
            foreach ($taxModels as $taxId => $taxModel) {
                $taxModel = Core_Tax_Model::getInstanceById($taxId);
                $taxes[$taxId] = $taxModel->getSaveParams();
                $taxRegions = $taxModel->getRegions();
                $regions = [];

                foreach ($taxRegions as $taxRegion) {
                    $regions[$taxRegion->getId()] = $taxRegion->percentage;
                }

                $taxes[$taxId]['regions'] = json_encode($regions);
                $taxes[$taxId]['taxid'] = $taxId;
                $taxes[$taxId]['percentage'] = number_format($taxes[$taxId]['percentage'], 2);
            }
        }

        return $taxes;
    }

    /**
     * @param int $itemId
     *
     * @return array
     */
    public static function decideItemPriceAndPriceBook(int $itemId, int $currencyId, int $priceBookId): array
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($itemId);
        $listPriceValuesList = [];
        $conversionRate = 1;
        $setPriceBookId = 0;

        if (method_exists($recordModel, 'getListPriceValues')) {
            $listPriceValuesList = $recordModel::getListPriceValues($recordModel->getId());
        }

        if (method_exists($recordModel, 'getPriceDetails')) {
            $priceDetails = $recordModel->getPriceDetails();

            foreach ($priceDetails as $currencyDetails) {
                if ($currencyId == $currencyDetails['curid']) {
                    $conversionRate = $currencyDetails['conversionrate'];
                }
            }
        }

        $priceBookPrice = 0;

        if ($priceBookId) {
            $priceBookModel = Vtiger_Record_Model::getInstanceById($priceBookId, 'PriceBooks');
            $priceBookPrice = $priceBookModel->getProductsListPrice($itemId);
            $priceBookCurrency = $priceBookModel->get('currency_id');

            if ($priceBookCurrency != $currencyId) {
                $currenciesConversionTable = InventoryItem_Utils_Helper::getCurrenciesConversionTable();
                $toBaseCurrency = 1 / $currenciesConversionTable[$priceBookCurrency];
                $toNewCurrency = $toBaseCurrency * $currenciesConversionTable[$currencyId];
                $priceBookPrice *= $toNewCurrency;
            }
        }

        if ($priceBookPrice) {
            $price = (float)$priceBookPrice;
            $setPriceBookId = $priceBookId;
        } elseif (isset($listPriceValuesList[$currencyId])) {
            $price = (float)$listPriceValuesList[$currencyId];
        } else {
            $price = (float)$recordModel->get('unit_price') * (float)$conversionRate;
        }

        return ['price' => $price, 'priceBookId' => $setPriceBookId];
    }

    /**
     * @return array
     */
    public static function fetchDecimals(): array
    {
        $db = PearDatabase::getInstance();
        $return = [];
        $sql = 'SELECT * FROM df_inventoryitem_quantitydecimals';
        $res = $db->query($sql);

        while ($row = $db->fetchByAssoc($res)) {
            $return[$row['field']] = (int)$row['decimals'];
        }

        return $return;
    }
}
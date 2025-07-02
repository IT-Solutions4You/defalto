<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_Utils_Helper
{
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

        $taxes = [];
        $taxInfo = $taxRecordModel->getTaxesInfo();

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
     * @param int            $itemId
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
}
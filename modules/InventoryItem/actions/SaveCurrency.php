<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_SaveCurrency_Action extends Vtiger_SaveAjax_Action
{
    public function process(Vtiger_Request $request)
    {
        $currencyId = (float)$request->get('currency_id');
        $recordId = $request->get('for_record');
        $moduleName = $request->get('for_module');

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $recordModel->set('mode', 'edit');
        $oldCurrencyId = $recordModel->get('currency_id');
        $recordModel->set('currency_id', $currencyId);
        $recordModel->save();
        $db = PearDatabase::getInstance();

        $currenciesConversionTable = $this->getCurrenciesConversionTable();
        $toBaseCurrency = 1 / $currenciesConversionTable[$oldCurrencyId];
        $toNewCurrency = $toBaseCurrency * $currenciesConversionTable[$currencyId];

        $sql = 'SELECT df_inventoryitem.*
                FROM df_inventoryitem
                    INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid AND vtiger_crmentity.deleted = 0
                WHERE parentid = ?
                    AND productid IS NOT NULL
                    AND productid <> 0';
        $res = $db->pquery($sql, [$request->get('for_record')]);

        while ($row = $db->fetchByAssoc($res)) {
            if (!$row['productid']) {
                continue;
            }

            $recordModel = Vtiger_Record_Model::getInstanceById($row['inventoryitemid'], 'InventoryItem');
            $currencyPriceList = Products_Record_Model::getListPriceValues($row['productid']);

            if (isset($currencyPriceList[$currencyId])) {
                $price = $currencyPriceList[$currencyId];
            } else {
                $price = $recordModel->get('price') * $toNewCurrency;
            }

            $recordModel->set('price', $price);
            $recordModel->set('purchase_cost', $recordModel->get('purchase_cost') * $toNewCurrency);
            $discountType = $recordModel->get('discount_type');

            if ($discountType == 'Direct') {
                $recordModel->set('discount_amount', $recordModel->get('discount_amount') * $toNewCurrency);
            } elseif ($discountType == 'Product Unit Price') {
                $recordModel->set('discount', $recordModel->get('discount') * $toNewCurrency);
            }

            $recordModel->set('mode', 'edit');
            $recordModel->save();
        }
    }

    protected function getCurrenciesConversionTable()
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
}
<?php
/*
 *
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

trait Core_UnitPrice_Trait
{
    /**
     * @return void
     * @throws Exception
     */
    public function insertPriceInformation(): void
    {
        /** @var Products_Record_Model $productModel */
        $productModel = Products_Record_Model::getCleanInstance('Products');
        $table = $productModel->getProductCurrencyRelTable();

        $recordId = (int)$this->id;
        $baseCurrency = $_REQUEST['base_currency'] ?: '';
        $baseCurrencyId = $this->column_fields['currency_id'] = (int)str_replace('curname', '', $baseCurrency);

        //Delete the existing currency relationship if any
        if ($this->mode == 'edit' && $_REQUEST['action'] !== 'CurrencyUpdate') {
            $table->deleteData(['productid' => $recordId]);
        }

        $currencyDetails = getAllCurrencies('all');
        $productConversionRate = getBaseConversionRateForProduct($this->id, $this->mode, $this->moduleName);

        //Save the Product - Currency relationship if corresponding currency check box is enabled
        foreach ($currencyDetails as $currencyDetail) {
            $currencyId = (int)$currencyDetail['curid'];
            $currencyStatusKey = 'cur_' . $currencyId . '_check';
            $currencyValueKey = 'curname' . $currencyId;
            $currencyStatus = $_REQUEST[$currencyStatusKey];
            $currencyValue = (float)$_REQUEST[$currencyValueKey];
            $requestPrice = (float)$_REQUEST['unit_price'];
            $isQuickCreate = false;

            if ($_REQUEST['action'] == 'SaveAjax' && $baseCurrencyId === $currencyId) {
                $currencyValue = $requestPrice;
                $isQuickCreate = true;
            }

            $search = ['productid' => $recordId, 'currencyid' => $currencyId];

            if ('on' === $currencyStatus || $isQuickCreate) {
                $convertedPrice = $productConversionRate * $currencyDetail['conversionrate'] * $requestPrice;
                $data = $table->selectData(['productid'], $search);
                $update = ['converted_price' => $convertedPrice, 'actual_price' => $currencyValue,];

                if (!empty($data['productid'])) {
                    $table->updateData($update, $search);
                } else {
                    $table->insertData(array_merge($update, $search));
                }
            } elseif ('off' === $currencyStatus) {
                $table->deleteData($search);
            }
        }
    }
}
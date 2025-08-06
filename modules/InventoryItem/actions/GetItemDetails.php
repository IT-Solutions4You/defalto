<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_GetItemDetails_Action extends Vtiger_Action_Controller
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'sourceModule', 'action' => 'DetailView'];

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    function process(Vtiger_Request $request)
    {
        $decimalPlaces = getCurrencyDecimalPlaces();
        $currencyId = $request->get('currency_id');
        $priceBookId = $request->get('pricebookid');
        $idList = $request->get('idlist');

        if (!$idList) {
            $recordId = $request->get('record');
            $idList = [$recordId];
        }

        $namesList = $purchaseCostsList = $taxesList = $listPricesList = $unit = [];
        $descriptionsList = $quantitiesList = $imageSourcesList = $productIdsList = [];
        $setPriceBookId = [];

        foreach ($idList as $id) {
            $recordModel = Vtiger_Record_Model::getInstanceById($id);

            $taxesList[$id] = InventoryItem_Utils_Helper::getTaxesForProduct($id);
            $namesList[$id] = decode_html($recordModel->getName());
            $descriptionsList[$id] = decode_html($recordModel->get('description'));

            $priceData = InventoryItem_Utils_Helper::decideItemPriceAndPriceBook((int)$id, (int)$currencyId, (int)$priceBookId);
            $listPricesList[$id] = $priceData['price'];
            $setPriceBookId[$id] = $priceData['priceBookId'];
            $itemCurrencyId = $recordModel->fetchCurrencyId();

            if ($itemCurrencyId == $currencyId) {
                $purchaseCostsList[$id] = round((float)$recordModel->get('purchase_cost'), $decimalPlaces);
            } else {
                $currenciesConversionTable = InventoryItem_Utils_Helper::getCurrenciesConversionTable();
                $toBaseCurrency = 1 / $currenciesConversionTable[$itemCurrencyId];
                $toNewCurrency = $toBaseCurrency * $currenciesConversionTable[$currencyId];
                $purchaseCostsList[$id] = round((float)$recordModel->get('purchase_cost') * (float)$toNewCurrency, $decimalPlaces);
            }

            if ($recordModel->getModuleName() === 'Products') {
                $productIdsList[] = $id;
                $unit[$id] = $recordModel->get('usageunit');
            } elseif ($recordModel->getModuleName() === 'Services') {
                $unit[$id] = $recordModel->get('service_usageunit');
            }
        }

        if ($productIdsList) {
            $imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

            foreach ($imageDetailsList as $productId => $imageDetails) {
                $imageSourcesList[$productId] = $imageDetails[0]['path'] . '_' . $imageDetails[0]['orgname'];
            }
        }

        $decimals = InventoryItem_Utils_Helper::fetchDecimals();
        $info = [];

        foreach ($idList as $id) {
            $resultData = [
                'id'              => $id,
                'name'            => $namesList[$id],
                'quantity'        => number_format(1, $decimals['quantity'], '.', ''),
                'taxes'           => $taxesList[$id],
                'listprice'       => number_format($listPricesList[$id], $decimals['price'], '.', ''),
                'purchaseCost'    => $request->get('sourceModule') === 'PurchaseOrder' ? number_format(
                    $purchaseCostsList[$id],
                    $decimals['price'],
                    '.',
                    ''
                ) : $purchaseCostsList[$id],
                'description'     => $descriptionsList[$id],
                'quantityInStock' => $quantitiesList[$id] ?? '',
                'imageSource'     => $imageSourcesList[$id] ?? '',
                'unit'            => $unit[$id],
                'pricebookid'     => $setPriceBookId[$id],
            ];

            $info[] = [$id => $resultData];
        }

        $response = new Vtiger_Response();
        $response->setResult($info);
        $response->emit();
    }
}
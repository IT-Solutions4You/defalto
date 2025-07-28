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
        $decimalPlace = getCurrencyDecimalPlaces();
        $currencyId = $request->get('currency_id');
        $currencies = getAllCurrencies();
        $priceBookId = $request->get('pricebookid');
        $conversionRateForPurchaseCost = 1;
        $idList = $request->get('idlist');

        if (!$idList) {
            $recordId = $request->get('record');
            $idList = [$recordId];
        }

        $response = new Vtiger_Response();
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

            foreach ($currencies as $currencyInfo) {
                if ($currencyId == $currencyInfo['curid']) {
                    $conversionRateForPurchaseCost = $currencyInfo['conversionrate'];
                    break;
                }
            }

            $purchaseCostsList[$id] = round((float)$recordModel->get('purchase_cost') * (float)$conversionRateForPurchaseCost, $decimalPlace);

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
                'quantityInStock' => $quantitiesList[$id],
                'imageSource'     => $imageSourcesList[$id],
                'unit'            => $unit[$id],
                'pricebookid'     => $setPriceBookId[$id],
            ];

            $info[] = [$id => $resultData];
        }

        $response->setResult($info);
        $response->emit();
    }
}
<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_GetTaxes_Action extends Vtiger_Action_Controller
{

    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'sourceModule', 'action' => 'DetailView'];

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        $decimalPlace = getCurrencyDecimalPlaces();
        $currencyId = $request->get('currency_id');
        $currencies = Inventory_Module_Model::getAllCurrencies();
        $conversionRate = $conversionRateForPurchaseCost = 1;
        $idList = $request->get('idlist');

        if (!$idList) {
            $recordId = $request->get('record');
            $idList = [$recordId];
        }

        $response = new Vtiger_Response();
        $namesList = $purchaseCostsList = $taxesList = $listPricesList = $listPriceValuesList = $unit = [];
        $descriptionsList = $quantitiesList = $imageSourcesList = $productIdsList = $baseCurrencyIdsList = [];

        foreach ($idList as $id) {
            $recordModel = Vtiger_Record_Model::getInstanceById($id);
            $taxes = [];

            if (method_exists($recordModel, 'getTaxes')) {
                $taxes = $recordModel->getTaxes();

                foreach ($taxes as $key => $taxInfo) {
                    $taxInfo['compoundOn'] = json_encode($taxInfo['compoundOn']);
                    $taxes[$key] = $taxInfo;
                }
            }

            $taxesList[$id] = $taxes;
            $namesList[$id] = decode_html($recordModel->getName());
            $descriptionsList[$id] = decode_html($recordModel->get('description'));
            $listPriceValuesList[$id] = [];

            if (method_exists($recordModel, 'getListPriceValues')) {
                $listPriceValuesList[$id] = $recordModel::getListPriceValues($recordModel->getId());
            }

            if (method_exists($recordModel, 'getPriceDetails')) {
                $priceDetails = $recordModel->getPriceDetails();

                foreach ($priceDetails as $currencyDetails) {
                    if ($currencyId == $currencyDetails['curid']) {
                        $conversionRate = $currencyDetails['conversionrate'];
                    }
                }
            }

            if (isset($listPriceValuesList[$id][$currencyId])) {
                $listPricesList[$id] = (float)$listPriceValuesList[$id][$currencyId];
            } else {
                $listPricesList[$id] = (float)$recordModel->get('unit_price') * (float)$conversionRate;
            }

            foreach ($currencies as $currencyInfo) {
                if ($currencyId == $currencyInfo['curid']) {
                    $conversionRateForPurchaseCost = $currencyInfo['conversionrate'];
                    break;
                }
            }

            $purchaseCostsList[$id] = round((float)$recordModel->get('purchase_cost') * (float)$conversionRateForPurchaseCost, $decimalPlace);
            $baseCurrencyIdsList[$id] = getProductBaseCurrency($id, $recordModel->getModuleName());

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

        foreach ($idList as $id) {
            $resultData = [
                'id'              => $id,
                'name'            => $namesList[$id],
                'taxes'           => $taxesList[$id],
                'listprice'       => $listPricesList[$id],
                'listpricevalues' => $listPriceValuesList[$id],
                'purchaseCost'    => $purchaseCostsList[$id],
                'description'     => $descriptionsList[$id],
                'baseCurrencyId'  => $baseCurrencyIdsList[$id],
                'quantityInStock' => $quantitiesList[$id],
                'imageSource'     => $imageSourcesList[$id],
                'unit'            => $unit[$id],
            ];

            $info[] = [$id => $resultData];
        }
        $response->setResult($info);
        $response->emit();
    }
}
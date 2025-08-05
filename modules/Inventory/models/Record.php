<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Inventory Record Model Class
 */
class Inventory_Record_Model extends Vtiger_Record_Model
{
    function getProducts()
    {
        $numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces();
        $relatedProducts = $this->getAssociatedProducts($this->getModuleName(), $this->getEntity());
        $productsCount = php7_count($relatedProducts);

        //Updating Tax details
        $taxtype = $relatedProducts[1]['final_details']['taxtype'];
        $productIdsList = [];
        for ($i = 1; $i <= $productsCount; $i++) {
            $product = $relatedProducts[$i];
            $productId = $product['hdnProductId' . $i];
            $totalAfterDiscount = $product['totalAfterDiscount' . $i];

            if ($taxtype == 'individual') {
                $taxDetails = getTaxDetailsForProduct($productId, 'all');
                $taxCount = php7_count($taxDetails);
                $taxTotal = '0';

                for ($j = 0; $j < $taxCount; $j++) {
                    $taxValue = $product['taxes'][$j]['percentage'];

                    $taxAmount = $totalAfterDiscount * $taxValue / 100;
                    $taxTotal = $taxTotal + $taxAmount;

                    $product['taxes'][$j]['amount'] = $taxAmount;
                    $relatedProducts[$i]['taxes'][$j]['amount'] = $taxAmount;
                }

                $productTaxes = [];
                if ($product['taxes']) {
                    foreach ($product['taxes'] as $key => $taxInfo) {
                        $taxInfo['key'] = $key;
                        $productTaxes[$taxInfo['taxid']] = $taxInfo;
                    }
                }

                $taxTotal = 0.00;
                foreach ($productTaxes as $taxId => $taxInfo) {
                    $taxAmount = $taxInfo['amount'];
                    if ($taxInfo['compoundon']) {
                        $amount = $totalAfterDiscount;
                        foreach ($taxInfo['compoundon'] as $compTaxId) {
                            $amount = $amount + $productTaxes[$compTaxId]['amount'];
                        }
                        $taxAmount = $amount * $taxInfo['percentage'] / 100;
                    }
                    $taxTotal = $taxTotal + $taxAmount;

                    $relatedProducts[$i]['taxes'][$taxInfo['key']]['amount'] = $taxAmount;
                    $relatedProducts[$i]['taxTotal' . $i] = number_format($taxTotal, $numOfCurrencyDecimalPlaces, '.', '');
                }
                $netPrice = $totalAfterDiscount + $taxTotal;
                $relatedProducts[$i]['netPrice' . $i] = number_format($netPrice, $numOfCurrencyDecimalPlaces, '.', '');

                $preTaxTotal += $totalAfterDiscount;
            }

            if ($relatedProducts[$i]['entityType' . $i] == 'Products') {
                $productIdsList[] = $productId;
            }
        }

        //Updating Pre tax total if not calculated from individual taxtype
        if (!$preTaxTotal) {
            $preTaxTotal = (float)$relatedProducts[1]['final_details']['subtotal']
                + (float)$relatedProducts[1]['final_details']['shipping_handling_charge']
                - (float)$relatedProducts[1]['final_details']['discountTotal_final'];
        }

        $relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $numOfCurrencyDecimalPlaces, '.', '');

        //Updating Total After Discount
        $totalAfterDiscount = (float)$relatedProducts[1]['final_details']['subtotal'] - (float)$relatedProducts[1]['final_details']['discountTotal_final'];

        $relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $numOfCurrencyDecimalPlaces, '.', '');
        $relatedProducts[1]['final_details']['discount_amount_final'] = number_format(
            (float)$relatedProducts[1]['final_details']['discount_amount_final'],
            $numOfCurrencyDecimalPlaces,
            '.',
            ''
        );

        //charge value setting to related products array
        $selectedChargesAndItsTaxes = $this->getCharges();
        if (!$selectedChargesAndItsTaxes) {
            $selectedChargesAndItsTaxes = [];
        }
        $relatedProducts[1]['final_details']['chargesAndItsTaxes'] = $selectedChargesAndItsTaxes;

        $allChargeTaxes = [];
        foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
            if (is_array($chargeInfo['taxes'])) {
                $allChargeTaxes = array_merge($allChargeTaxes, array_keys($chargeInfo['taxes']));
            } else {
                $selectedChargesAndItsTaxes[$chargeId]['taxes'] = [];
            }
        }

        $shippingTaxes = [];
        $allShippingTaxes = getAllTaxes('all', 'sh');
        foreach ($allShippingTaxes as $shTaxInfo) {
            $shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
        }

        $totalAmount = 0.00;
        foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
            foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
                $amount = $calculatedOn = $chargeInfo['value'];

                if ($shippingTaxes[$taxId]['method'] === 'Compound') {
                    $compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
                    if (is_array($compoundTaxes)) {
                        foreach ($compoundTaxes as $comTaxId) {
                            if ($shippingTaxes[$comTaxId]) {
                                $calculatedOn += ((float)$amount * (float)$chargeInfo['taxes'][$comTaxId]) / 100;
                            }
                        }
                    }
                }
                $totalAmount += ((float)$calculatedOn * (float)$taxPercent) / 100;
            }
        }
        $relatedProducts[1]['final_details']['shtax_totalamount'] = number_format($totalAmount, $numOfCurrencyDecimalPlaces, '.', '');

        //deduct tax values setting to related products
        $totalAfterDiscount = (float)$relatedProducts[1]['final_details']['totalAfterDiscount'];
        $deductedTaxesTotalAmount = 0.00;

        $deductTaxes = $this->getDeductTaxes();
        foreach ($deductTaxes as $taxId => $taxInfo) {
            $taxAmount = ($totalAfterDiscount * (float)$taxInfo['percentage']) / 100;
            $deductTaxes[$taxId]['amount'] = number_format($taxAmount, $numOfCurrencyDecimalPlaces, '.', '');
            if ($taxInfo['selected']) {
                $deductedTaxesTotalAmount = $deductedTaxesTotalAmount + $taxAmount;
            }
        }

        $relatedProducts[1]['final_details']['deductTaxes'] = $deductTaxes;
        $relatedProducts[1]['final_details']['deductTaxesTotalAmount'] = number_format($deductedTaxesTotalAmount, $numOfCurrencyDecimalPlaces, '.', '');

        if ($productIdsList) {
            $imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

            for ($i = 1; $i <= $productsCount; $i++) {
                $product = $relatedProducts[$i];
                $productId = $product['hdnProductId' . $i];
                $imageDetails = $imageDetailsList[$productId];
                if ($imageDetails) {
                    $relatedProducts[$i]['productImage' . $i] = $imageDetails[0]['path'] . '_' . $imageDetails[0]['orgname'];
                }
            }
        }

        return $relatedProducts;
    }

    /**
     * Function to get charges
     * @return <Array>
     */
    public function getCharges()
    {
        if (!isset($this->chargesAndItsTaxes) || !$this->chargesAndItsTaxes) {
            $this->chargesAndItsTaxes = [];
            $recordId = $this->getId();
            if ($recordId) {
                $db = PearDatabase::getInstance();
                $result = $db->pquery('SELECT * FROM vtiger_inventorychargesrel WHERE recordid = ?', [$recordId]);
                while ($rowData = $db->fetch_array($result)) {
                    $this->chargesAndItsTaxes = Zend_Json::decode(html_entity_decode($rowData['charges']));
                }
            }
        }

        return $this->chargesAndItsTaxes;
    }

    /**
     * Function to get deduct taxes
     * @return <Array>
     */
    public function getDeductTaxes()
    {
        $deductTaxes = $this->get('deductTaxes');
        if ($deductTaxes) {
            return $deductTaxes;
        }

        $deductTaxes = Inventory_TaxRecord_Model::getDeductTaxesList($active = false);
        $record = $this->getId();
        if ($record && $deductTaxes) {
            $db = PearDatabase::getInstance();
            $deductTaxNamesList = [];
            foreach ($deductTaxes as $taxId => $taxInfo) {
                $deductTaxNamesList[] = $taxInfo['taxname'];
            }

            $result = $db->pquery('SELECT ' . implode(',', $deductTaxNamesList) . ' FROM vtiger_inventoryproductrel WHERE id = ?', [$record]);
            foreach ($deductTaxes as $taxId => $taxInfo) {
                $percent = $db->query_result($result, 0, $taxInfo['taxname']);
                if ($percent !== null && $percent < 0) {
                    $deductTaxes[$taxId]['selected'] = true;
                    $deductTaxes[$taxId]['percentage'] = -$percent;
                }
            }
        }

        $this->set('deductTaxes', $deductTaxes);

        return $deductTaxes;
    }

    /** This function returns the detailed list of vtiger_products associated to a given entity or a record.
     * Param $module - module name
     * Param $focus - module object
     * Param $seid - sales entity id
     * Return type is an object array
     */
    function getAssociatedProducts($module, $focus, $seid = '', $refModuleName = false)
    {
        global $log;
        $log->debug("Entering getAssociatedProducts(" . $module . "," . get_class($focus) . "," . $seid . "='') method ...");
        global $adb;
        $output = '';
        $taxtype = '';
        global $theme, $current_user;

        $no_of_decimal_places = getCurrencyDecimalPlaces();
        $theme_path = "themes/" . $theme . "/";
        $image_path = $theme_path . "images/";
        $product_Detail = [];

        $inventoryModules = InventoryItem_Utils_Helper::getInventoryItemModules();
        if (in_array($module, $inventoryModules)) {
            $taxtype = getInventoryTaxType($module, $focus->id);
        }

        $additionalProductFieldsString = $additionalServiceFieldsString = '';
        $lineItemSupportedModules = ['Accounts', 'Contacts', 'Leads', 'Potentials'];

        // DG 15 Aug 2006
        // Add "ORDER BY sequence_no" to retain add order on all inventoryproductrel items

        if (in_array($module, $inventoryModules)) {
            $query = "SELECT
					case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as productname,
					case when vtiger_products.productid != '' then vtiger_products.product_no else vtiger_service.service_no end as productcode,
					case when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,
					case when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,
					case when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,
					vtiger_inventoryproductrel.listprice, vtiger_products.is_subproducts_viewable, 
					vtiger_inventoryproductrel.description AS product_description, vtiger_inventoryproductrel.*,
					vtiger_crmentity.deleted FROM vtiger_inventoryproductrel
					LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid
					LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel.productid
					LEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
					WHERE id=? ORDER BY sequence_no";
            $params = [$focus->id];
        } elseif (in_array($module, $lineItemSupportedModules)) {
            $query = '(SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.product_no AS productcode, vtiger_products.purchase_cost,
					vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_crmentity.deleted, "Products" AS entitytype,
					vtiger_products.is_subproducts_viewable, vtiger_crmentity.description ' . $additionalProductFieldsString . ' FROM vtiger_products
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid
					INNER JOIN vtiger_seproductsrel ON vtiger_seproductsrel.productid=vtiger_products.productid
					INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
					WHERE vtiger_seproductsrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_products.discontinued=1)
					UNION
					(SELECT vtiger_service.serviceid AS productid, vtiger_service.servicename AS productname, vtiger_service.service_no AS productcode,
					vtiger_service.purchase_cost, vtiger_service.unit_price, "NA" as qtyinstock, vtiger_crmentity.deleted,
					"Services" AS entitytype, 1 AS is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_service
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid
					INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_service.serviceid
					INNER JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid
					WHERE vtiger_crmentityrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_service.discontinued=1)';
            $params = [$seid, $seid];
        } elseif ($module == 'Vendors') {
            $query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.product_no AS productcode, vtiger_products.purchase_cost,
					vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_crmentity.deleted, "Products" AS entitytype,
					vtiger_products.is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_products
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid
					INNER JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_products.vendor_id
					INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
					WHERE vtiger_vendor.vendorid=? AND vtiger_crmentity.deleted=0 AND vtiger_products.discontinued=1';
            $params = [$seid];
        } elseif ($module == 'HelpDesk') {
            $query = 'SELECT vtiger_service.serviceid AS productid, vtiger_service.servicename AS productname, vtiger_service.service_no AS productcode,
					vtiger_service.purchase_cost, vtiger_service.unit_price, "NA" as qtyinstock, vtiger_crmentity.deleted,
					"Services" AS entitytype, 1 AS is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_service
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid
					INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_service.serviceid
					INNER JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid
					WHERE vtiger_crmentityrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_service.discontinued=1';
            $params = [$seid];
        } elseif ($module == 'Products') {
            $query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.product_no AS productcode, vtiger_products.purchase_cost,
					vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_crmentity.deleted, "Products" AS entitytype,
					vtiger_products.is_subproducts_viewable, vtiger_crmentity.description ' . $additionalProductFieldsString . ' FROM vtiger_products
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid
					INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
					WHERE vtiger_crmentity.deleted=0 AND vtiger_products.productid=?';
            $params = [$seid];
        } elseif ($module == 'Services') {
            $query = 'SELECT vtiger_service.serviceid AS productid, vtiger_service.servicename AS productname, vtiger_service.service_no AS productcode,
					vtiger_service.purchase_cost, vtiger_service.unit_price AS unit_price, "NA" AS qtyinstock, vtiger_crmentity.deleted,
					"Services" AS entitytype, 1 AS is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_service
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_service.serviceid
					INNER JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid
					WHERE vtiger_crmentity.deleted=0 AND vtiger_service.serviceid=?';
            $params = [$seid];
        }

        $result = $adb->pquery($query, $params);
        $num_rows = $adb->num_rows($result);
        for ($i = 1; $i <= $num_rows; $i++) {
            $deleted = $adb->query_result($result, $i - 1, 'deleted');
            $hdnProductId = $adb->query_result($result, $i - 1, 'productid');
            $hdnProductcode = $adb->query_result($result, $i - 1, 'productcode');
            $productname = $adb->query_result($result, $i - 1, 'productname');
            $productdescription = $adb->query_result($result, $i - 1, 'description');
            $comment = $adb->query_result($result, $i - 1, 'comment');
            $qtyinstock = $adb->query_result($result, $i - 1, 'qtyinstock');
            $qty = $adb->query_result($result, $i - 1, 'quantity');
            $unitprice = $adb->query_result($result, $i - 1, 'unit_price');
            $listprice = $adb->query_result($result, $i - 1, 'listprice');
            $entitytype = $adb->query_result($result, $i - 1, 'entitytype');
            $purchaseCost = $adb->query_result($result, $i - 1, 'purchase_cost');
            $margin = $adb->query_result($result, $i - 1, 'margin');
            $isSubProductsViewable = $adb->query_result($result, $i - 1, 'is_subproducts_viewable');

            if ($purchaseCost) {
                $product_Detail[$i]['purchaseCost' . $i] = number_format($purchaseCost, $no_of_decimal_places, '.', '');
            }

            if ($margin) {
                $product_Detail[$i]['margin' . $i] = number_format($margin, $no_of_decimal_places, '.', '');
            }

            if (($deleted) || (!isset($deleted))) {
                $product_Detail[$i]['productDeleted' . $i] = true;
            } elseif (!$deleted) {
                $product_Detail[$i]['productDeleted' . $i] = false;
            }

            if (!empty($entitytype)) {
                $product_Detail[$i]['entityType' . $i] = $entitytype;
            }

            if ($listprice == '') {
                $listprice = $unitprice;
            }
            if ($qty == '') {
                $qty = 1;
            }

            //calculate productTotal
            $productTotal = $qty * $listprice;

            //Delete link in First column
            if ($i != 1) {
                $product_Detail[$i]['delRow' . $i] = "Del";
            }

            if (in_array($module, $lineItemSupportedModules) || $module === 'Vendors' || (!$focus->mode && $seid)) {
                $subProductsQuery = 'SELECT vtiger_seproductsrel.crmid AS prod_id, quantity FROM vtiger_seproductsrel
								 INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seproductsrel.crmid
								 INNER JOIN vtiger_products ON vtiger_products.productid = vtiger_seproductsrel.crmid
								 WHERE vtiger_seproductsrel.productid=? AND vtiger_seproductsrel.setype=? AND vtiger_products.discontinued=1';

                $subParams = [$seid];
                if (in_array($module, $lineItemSupportedModules) || $module === 'Vendors') {
                    $subParams = [$hdnProductId];
                }
                array_push($subParams, 'Products');
            } else {
                $subProductsQuery = 'SELECT productid AS prod_id, quantity FROM vtiger_inventorysubproductrel WHERE id=? AND sequence_no=?';
                $subParams = [$focus->id, $i];
            }
            $subProductsResult = $adb->pquery($subProductsQuery, $subParams);
            $subProductsCount = $adb->num_rows($subProductsResult);

            $subprodid_str = '';
            $subprodname_str = '';

            $subProductQtyList = [];
            for ($j = 0; $j < $subProductsCount; $j++) {
                $sprod_id = $adb->query_result($subProductsResult, $j, 'prod_id');
                $sprod_name = Vtiger_Functions::getCRMRecordLabel($sprod_id);
                if (isset($sprod_name)) {
                    $subQty = $adb->query_result($subProductsResult, $j, 'quantity');
                    $subProductQtyList[$sprod_id] = ['name' => $sprod_name, 'qty' => $subQty];
                    if (isRecordExists($sprod_id) && $_REQUEST['view'] === 'Detail') {
                        $subprodname_str .= "<a href='index.php?module=Products&view=Detail&record=$sprod_id' target='_blank'> <em> - $sprod_name ($subQty)</em><br></a>";
                    } else {
                        $subprodname_str .= "<em> - $sprod_name ($subQty)</em><br>";
                    }
                    $subprodid_str .= "$sprod_id:$subQty,";
                }
            }
            $subprodid_str = rtrim($subprodid_str, ',');

            $product_Detail[$i]['hdnProductId' . $i] = $hdnProductId;
            $product_Detail[$i]['productName' . $i] = from_html($productname);
            /* Added to fix the issue Product Pop-up name display*/
            if ($_REQUEST['action'] == 'CreateSOPDF' || $_REQUEST['action'] == 'CreatePDF' || $_REQUEST['action'] == 'SendPDFMail') {
                $product_Detail[$i]['productName' . $i] = htmlspecialchars($product_Detail[$i]['productName' . $i]);
            }
            $product_Detail[$i]['hdnProductcode' . $i] = $hdnProductcode;
            $product_Detail[$i]['productDescription' . $i] = from_html($productdescription);
            if ($module == 'Vendors' || $module == 'Products' || $module == 'Services' || in_array($module, $lineItemSupportedModules)) {
                $product_Detail[$i]['comment' . $i] = $productdescription;
            } else {
                $product_Detail[$i]['comment' . $i] = $comment;
            }

            if ($module != 'PurchaseOrder' && $focus->object_name != 'Order') {
                $product_Detail[$i]['qtyInStock' . $i] = decimalFormat($qtyinstock);
            }
            $listprice = number_format($listprice, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['qty' . $i] = decimalFormat($qty);
            $product_Detail[$i]['listPrice' . $i] = $listprice;
            $product_Detail[$i]['unitPrice' . $i] = number_format($unitprice, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['productTotal' . $i] = number_format($productTotal, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['subproduct_ids' . $i] = $subprodid_str;
            if ($isSubProductsViewable) {
                $product_Detail[$i]['subprod_qty_list' . $i] = $subProductQtyList;
                $product_Detail[$i]['subprod_names' . $i] = $subprodname_str;
            }
            $discount_percent = decimalFormat($adb->query_result($result, $i - 1, 'discount_percent'));
            $discount_amount = $adb->query_result($result, $i - 1, 'discount_amount');
            $discount_amount = decimalFormat(number_format($discount_amount, $no_of_decimal_places, '.', ''));
            $discountTotal = 0;
            //Based on the discount percent or amount we will show the discount details

            //To avoid NaN javascript error, here we assign 0 initially to' %of price' and 'Direct Price reduction'(for Each Product)
            $product_Detail[$i]['discount_percent' . $i] = 0;
            $product_Detail[$i]['discount_amount' . $i] = 0;

            if (!empty($discount_percent)) {
                $product_Detail[$i]['discount_type' . $i] = "percentage";
                $product_Detail[$i]['discount_percent' . $i] = $discount_percent;
                $product_Detail[$i]['checked_discount_percent' . $i] = ' checked';
                $product_Detail[$i]['style_discount_percent' . $i] = ' style="visibility:visible"';
                $product_Detail[$i]['style_discount_amount' . $i] = ' style="visibility:hidden"';
                $discountTotal = $productTotal * $discount_percent / 100;
            } elseif (!empty($discount_amount)) {
                $product_Detail[$i]['discount_type' . $i] = "amount";
                $product_Detail[$i]['discount_amount' . $i] = $discount_amount;
                $product_Detail[$i]['checked_discount_amount' . $i] = ' checked';
                $product_Detail[$i]['style_discount_amount' . $i] = ' style="visibility:visible"';
                $product_Detail[$i]['style_discount_percent' . $i] = ' style="visibility:hidden"';
                $discountTotal = $discount_amount;
            } else {
                $product_Detail[$i]['checked_discount_zero' . $i] = ' checked';
            }
            $totalAfterDiscount = $productTotal - $discountTotal;
            $totalAfterDiscount = number_format($totalAfterDiscount, $no_of_decimal_places, '.', '');
            $discountTotal = number_format($discountTotal, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['discountTotal' . $i] = $discountTotal;
            $product_Detail[$i]['totalAfterDiscount' . $i] = $totalAfterDiscount;

            $taxTotal = 0;
            $taxTotal = number_format($taxTotal, $no_of_decimal_places, '.', '');
            $product_Detail[$i]['taxTotal' . $i] = $taxTotal;

            //Calculate netprice
            $netPrice = $totalAfterDiscount + $taxTotal;
            //if condition is added to call this function when we create PO/SO/Quotes/Invoice from Product module
            if (in_array($module, $inventoryModules)) {
                if ($taxtype == 'individual') {
                    //Add the tax with product total and assign to netprice
                    $netPrice = $netPrice + $taxTotal;
                }
            }
            $product_Detail[$i]['netPrice' . $i] = number_format($netPrice, getCurrencyDecimalPlaces(), '.', '');

            //First we will get all associated taxes as array
            $tax_details = getTaxDetailsForProduct($hdnProductId, 'all');
            $regionsList = [];
            foreach ($tax_details as $taxInfo) {
                $regionsInfo = ['default' => $taxInfo['percentage']];
                foreach ($taxInfo['productregions'] as $list) {
                    if (is_array($list['list'])) {
                        foreach (array_fill_keys($list['list'], $list['value']) as $key => $value) {
                            $regionsInfo[$key] = $value;
                        }
                    }
                }
                $regionsList[$taxInfo['taxid']] = $regionsInfo;
            }
            //Now retrieve the tax values from the current query with the name
            for ($tax_count = 0; $tax_count < php7_count($tax_details); $tax_count++) {
                $tax_name = $tax_details[$tax_count]['taxname'];
                $tax_label = $tax_details[$tax_count]['taxlabel'];
                $tax_value = 0;

                $tax_value = $tax_details[$tax_count]['percentage'];
                if ($focus->id != '' && $taxtype == 'individual') {
                    $lineItemId = $adb->query_result($result, $i - 1, 'lineitem_id');
                    $tax_value = getInventoryProductTaxValue($focus->id, $hdnProductId, $tax_name, $lineItemId);
                    $selectedRegionId = $focus->column_fields['region_id'];
                    if ($selectedRegionId) {
                        $regionsList[$tax_details[$tax_count]['taxid']][$selectedRegionId] = $tax_value;
                    } else {
                        $regionsList[$tax_details[$tax_count]['taxid']]['default'] = $tax_value;
                    }
                }

                $product_Detail[$i]['taxes'][$tax_count]['taxname'] = $tax_name;
                $product_Detail[$i]['taxes'][$tax_count]['taxlabel'] = $tax_label;
                $product_Detail[$i]['taxes'][$tax_count]['percentage'] = $tax_value;
                $product_Detail[$i]['taxes'][$tax_count]['deleted'] = $tax_details[$tax_count]['deleted'];
                $product_Detail[$i]['taxes'][$tax_count]['taxid'] = $tax_details[$tax_count]['taxid'];
                $product_Detail[$i]['taxes'][$tax_count]['type'] = $tax_details[$tax_count]['type'];
                $product_Detail[$i]['taxes'][$tax_count]['method'] = $tax_details[$tax_count]['method'];
                $product_Detail[$i]['taxes'][$tax_count]['regions'] = $tax_details[$tax_count]['regions'];
                $product_Detail[$i]['taxes'][$tax_count]['compoundon'] = $tax_details[$tax_count]['compoundon'];
                $product_Detail[$i]['taxes'][$tax_count]['regionsList'] = $regionsList[$tax_details[$tax_count]['taxid']];
            }
        }

        //set the taxtype
        $product_Detail[1]['final_details']['taxtype'] = $taxtype;

        //Get the Final Discount, S&H charge, Tax for S&H and Adjustment values
        //To set the Final Discount details
        $finalDiscount = 0;
        $product_Detail[1]['final_details']['discount_type_final'] = 'zero';

        $subTotal = ($focus->column_fields['subtotal'] != '') ? $focus->column_fields['subtotal'] : 0;
        $subTotal = number_format($subTotal, $no_of_decimal_places, '.', '');

        $product_Detail[1]['final_details']['subtotal'] = $subTotal;
        $discountPercent = ($focus->column_fields['hdnDiscountPercent'] != '') ? $focus->column_fields['hdnDiscountPercent'] : 0;
        $discountAmount = ($focus->column_fields['discount_amount'] != '') ? $focus->column_fields['discount_amount'] : 0;
        if ($discountPercent != '0') {
            $discountAmount = ($product_Detail[1]['final_details']['subtotal'] * $discountPercent / 100);
        }

        //To avoid NaN javascript error, here we assign 0 initially to' %of price' and 'Direct Price reduction'(For Final Discount)
        $discount_amount_final = 0;
        $discount_amount_final = number_format($discount_amount_final, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['discount_percentage_final'] = 0;
        $product_Detail[1]['final_details']['discount_amount_final'] = $discount_amount_final;

        $hdnDiscountPercent = (float)$focus->column_fields['hdnDiscountPercent'];
        $hdnDiscountAmount = (float)$focus->column_fields['discount_amount'];

        if (!empty($hdnDiscountPercent)) {
            $finalDiscount = ($subTotal * $discountPercent / 100);
            $product_Detail[1]['final_details']['discount_type_final'] = 'percentage';
            $product_Detail[1]['final_details']['discount_percentage_final'] = $discountPercent;
            $product_Detail[1]['final_details']['checked_discount_percentage_final'] = ' checked';
            $product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:visible"';
            $product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:hidden"';
        } elseif (!empty($hdnDiscountAmount)) {
            $finalDiscount = $focus->column_fields['discount_amount'];
            $product_Detail[1]['final_details']['discount_type_final'] = 'amount';
            $product_Detail[1]['final_details']['discount_amount_final'] = $discountAmount;
            $product_Detail[1]['final_details']['checked_discount_amount_final'] = ' checked';
            $product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:visible"';
            $product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:hidden"';
        }
        $finalDiscount = number_format($finalDiscount, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['discountTotal_final'] = $finalDiscount;

        //To set the Final Tax values
        //we will get all taxes. if individual then show the product related taxes only else show all taxes
        //suppose user want to change individual to group or vice versa in edit time the we have to show all taxes. so that here we will store all the taxes and based on need we will show the corresponding taxes

        //First we should get all available taxes and then retrieve the corresponding tax values
        $tax_details = getAllTaxes('available', '', 'edit', $focus->id);
        $taxDetails = [];

        for ($tax_count = 0; $tax_count < php7_count($tax_details); $tax_count++) {
            if ($tax_details[$tax_count]['method'] === 'Deducted') {
                continue;
            }

            $tax_name = $tax_details[$tax_count]['taxname'];
            $tax_label = $tax_details[$tax_count]['taxlabel'];

            //if taxtype is individual and want to change to group during edit time then we have to show the all available taxes and their default values
            //Also taxtype is group and want to change to individual during edit time then we have to provide the asspciated taxes and their default tax values for individual products
            if ($taxtype == 'group') {
                $tax_percent = $adb->query_result($result, 0, $tax_name);
            } else {
                $tax_percent = $tax_details[$tax_count]['percentage'];
            }//$adb->query_result($result,0,$tax_name);

            if ($tax_percent == '' || $tax_percent == 'NULL') {
                $tax_percent = 0;
            }
            $taxamount = ($subTotal - $finalDiscount) * $tax_percent / 100;
            $split_taxamounts = explode('.', $taxamount);
            $after_dot = $split_taxamounts[1] ?? '';

            if (isset($after_dot[$no_of_decimal_places]) && $after_dot[$no_of_decimal_places] == 5) {
                $taxamount = round($taxamount, $no_of_decimal_places);
            } else {
                $taxamount = number_format($taxamount, $no_of_decimal_places, '.', '');
            }

            $taxId = $tax_details[$tax_count]['taxid'];
            $taxDetails[$taxId]['taxname'] = $tax_name;
            $taxDetails[$taxId]['taxlabel'] = $tax_label;
            $taxDetails[$taxId]['percentage'] = $tax_percent;
            $taxDetails[$taxId]['amount'] = $taxamount;
            $taxDetails[$taxId]['taxid'] = $taxId;
            $taxDetails[$taxId]['type'] = $tax_details[$tax_count]['type'];
            $taxDetails[$taxId]['method'] = $tax_details[$tax_count]['method'];
            $taxDetails[$taxId]['regions'] = Zend_Json::decode(html_entity_decode($tax_details[$tax_count]['regions']));
            $taxDetails[$taxId]['compoundon'] = Zend_Json::decode(html_entity_decode($tax_details[$tax_count]['compoundon']));
        }

        $compoundTaxesInfo = getCompoundTaxesInfoForInventoryRecord($focus->id, $module);
        //Calculating compound info
        $taxTotal = 0;
        foreach ($taxDetails as $taxId => $taxInfo) {
            $compoundOn = $taxInfo['compoundon'];
            if ($compoundOn) {
                $existingCompounds = $compoundTaxesInfo[$taxId];
                if (!is_array($existingCompounds)) {
                    $existingCompounds = [];
                }
                $compoundOn = array_unique(array_merge($existingCompounds, $compoundOn));
                $taxDetails[$taxId]['compoundon'] = $compoundOn;

                $amount = $subTotal - $finalDiscount;
                foreach ($compoundOn as $id) {
                    $amount = (float)$amount + (float)$taxDetails[$id]['amount'];
                }
                $taxAmount = ((float)$amount * (float)$taxInfo['percentage']) / 100;
                [$beforeDot, $afterDot] = explode('.', $taxAmount);

                if ($afterDot[$no_of_decimal_places] == 5) {
                    $taxAmount = round($taxAmount, $no_of_decimal_places, PHP_ROUND_HALF_DOWN);
                } else {
                    $taxAmount = number_format($taxAmount, $no_of_decimal_places, '.', '');
                }

                $taxDetails[$taxId]['amount'] = $taxAmount;
            }
            $taxTotal = $taxTotal + $taxDetails[$taxId]['amount'];
        }
        $product_Detail[1]['final_details']['taxes'] = $taxDetails;
        $product_Detail[1]['final_details']['tax_totalamount'] = number_format($taxTotal, $no_of_decimal_places, '.', '');

        //To set the Shipping & Handling charge
        $shCharge = ($focus->column_fields['s_h_amount'] != '') ? $focus->column_fields['s_h_amount'] : 0;
        $shCharge = number_format($shCharge, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['shipping_handling_charge'] = $shCharge;

        //To set the Shipping & Handling tax values
        //calculate S&H tax
        $shtaxtotal = 0;
        //First we should get all available taxes and then retrieve the corresponding tax values
        $shtax_details = getAllTaxes('available', 'sh', 'edit', $focus->id);

        //if taxtype is group then the tax should be same for all products in vtiger_inventoryproductrel table
        for ($shtax_count = 0; $shtax_count < php7_count($shtax_details); $shtax_count++) {
            $shtax_name = $shtax_details[$shtax_count]['taxname'];
            $shtax_label = $shtax_details[$shtax_count]['taxlabel'];
            $shtax_percent = 0;
            //if condition is added to call this function when we create PO/SO/Quotes/Invoice from Product module
            if (in_array($module, $inventoryModules)) {
                $shtax_percent = getInventorySHTaxPercent($focus->id, $shtax_name);
            }
            $shtaxamount = $shCharge * $shtax_percent / 100;
            $shtaxtotal = $shtaxtotal + $shtaxamount;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxname'] = $shtax_name;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxlabel'] = $shtax_label;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['percentage'] = $shtax_percent;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['amount'] = $shtaxamount;
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxid'] = $shtax_details[$shtax_count]['taxid'];
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['type'] = $shtax_details[$shtax_count]['type'];
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['method'] = $shtax_details[$shtax_count]['method'];
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['regions'] = Zend_Json::decode(html_entity_decode($shtax_details[$shtax_count]['regions']));
            $product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['compoundon'] = Zend_Json::decode(html_entity_decode($shtax_details[$shtax_count]['compoundon']));
        }
        $shtaxtotal = number_format($shtaxtotal, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['shtax_totalamount'] = $shtaxtotal;

        //To set the Adjustment value
        $adjustment = ($focus->column_fields['adjustment'] != '') ? $focus->column_fields['adjustment'] : 0;
        $adjustment = number_format($adjustment, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['adjustment'] = $adjustment;

        //To set the grand total
        $grandTotal = ($focus->column_fields['grand_total'] != '') ? $focus->column_fields['grand_total'] : 0;
        $grandTotal = number_format($grandTotal, $no_of_decimal_places, '.', '');
        $product_Detail[1]['final_details']['grand_total'] = $grandTotal;

        $log->debug("Exiting getAssociatedProducts method ...");

        return $product_Detail;
    }
}

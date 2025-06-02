<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PurchaseOrder Record Model Class
 */
class PurchaseOrder_Record_Model extends Vtiger_Record_Model
{

    /**
     * This Function adds the specified product quantity to the Product Quantity in Stock
     *
     * @param type $recordId
     */
    function addStockToProducts($recordId)
    {
        $db = PearDatabase::getInstance();

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $relatedProducts = $recordModel->getProducts();

        foreach ($relatedProducts as $key => $relatedProduct) {
            if ($relatedProduct['qty' . $key]) {
                $productId = $relatedProduct['hdnProductId' . $key];
                $result = $db->pquery("SELECT qtyinstock FROM vtiger_products WHERE productid=?", [$productId]);
                $qty = $db->query_result($result, 0, "qtyinstock");
                $stock = $qty + $relatedProduct['qty' . $key];
                $db->pquery("UPDATE vtiger_products SET qtyinstock=? WHERE productid=?", [$stock, $productId]);
            }
        }
    }

    /**
     * This Function returns the current status of the specified Purchase Order.
     *
     * @param type $purchaseOrderId
     *
     * @return <String> PurchaseOrderStatus
     */
    function getPurchaseOrderStatus($purchaseOrderId)
    {
        $db = PearDatabase::getInstance();
        $sql = "SELECT postatus FROM vtiger_purchaseorder WHERE purchaseorderid=?";
        $result = $db->pquery($sql, [$purchaseOrderId]);
        $purchaseOrderStatus = $db->query_result($result, 0, "postatus");

        return $purchaseOrderStatus;
    }
}
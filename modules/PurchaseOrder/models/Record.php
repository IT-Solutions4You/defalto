<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
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

    /**
     * @inheritdoc
     */
    public function save() {
        if ($this->has('conversion_rate')) {
            $conversion_rate = $this->get('conversion_rate');

            if (empty($conversion_rate)) {
                $this->set('conversion_rate', 1);
            }
        }

        $entity = $this->getEntity();

        if (empty($entity->column_fields['conversion_rate'])) {
            $entity->column_fields['conversion_rate'] = 1;
            $this->setEntity($entity);
        }

        parent::save();
    }
}
<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

/**
 * Quotes Record Model Class
 */
class Quotes_Record_Model extends Vtiger_Record_Model
{

    public function getCreateInvoiceUrl()
    {
        $invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');

        return 'index.php?module=' . $invoiceModuleModel->getName() . '&view=' . $invoiceModuleModel->getEditViewName() . '&sourceModule=Quotes&sourceRecord=' . $this->getId() . '&quote_id=' . $this->getId();
    }

    public function getCreateSalesOrderUrl()
    {
        $salesOrderModuleModel = Vtiger_Module_Model::getInstance('SalesOrder');

        return 'index.php?module=' . $salesOrderModuleModel->getName() . '&view=' . $salesOrderModuleModel->getEditViewName() . '&sourceModule=Quotes&sourceRecord=' . $this->getId() . '&quote_id=' . $this->getId();
    }

    public function getCreatePurchaseOrderUrl()
    {
        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');

        return 'index.php?module=' . $purchaseOrderModuleModel->getName() . '&view=' . $purchaseOrderModuleModel->getEditViewName() . '&sourceModule=Quotes&sourceRecord=' . $this->getId() . '&quote_id=' . $this->getId();
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
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
 * Vtiger Record Model Class
 */
class Invoice_Record_Model extends Vtiger_Record_Model
{
    public function getCreatePurchaseOrderUrl()
    {
        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');

        return 'index.php?module=' . $purchaseOrderModuleModel->getName() . '&view=' . $purchaseOrderModuleModel->getEditViewName(
            ) . '&sourceModule=Invoice&sourceRecord=' . $this->getId() . '&invoice_id=' . $this->getId();
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
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
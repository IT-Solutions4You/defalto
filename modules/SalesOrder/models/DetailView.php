<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class SalesOrder_DetailView_Model extends Vtiger_DetailView_Model
{
    use InventoryItem_DetailView_Trait;

    /**
     * @inheritDoc
     */
    public function getDetailViewLinks($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $recordModel = $this->getRecord();
        $invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');
        $links = [];

        if ($currentUserModel->hasModuleActionPermission($invoiceModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_CREATE') . ' ' . vtranslate($invoiceModuleModel->getSingularLabelKey(), 'Invoice'),
                'linkurl'   => $recordModel->getCreateInvoiceUrl(),
                'linkicon'  => ''
            ];
        }

        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');

        if ($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_CREATE') . ' ' . vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
                'linkurl'   => $recordModel->getCreatePurchaseOrderUrl(),
                'linkicon'  => '',
            ];
        }

        return Vtiger_Link_Model::merge(parent::getDetailViewLinks($linkParams), Vtiger_Link_Model::checkAndConvertLinks($links));
    }
}
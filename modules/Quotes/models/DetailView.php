<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Quotes_DetailView_Model extends Inventory_DetailView_Model
{
    /**
     * Function to get the detail view links (links and widgets)
     *
     * @param <array> $linkParams - parameters which will be used to calicaulate the params
     *
     * @return <array> - array of link models in the format as below
     *                   array('linktype'=>list of link models);
     */
    public function getDetailViewLinks($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $recordModel = $this->getRecord();
        $links = [];
        $invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');
        if ($currentUserModel->hasModuleActionPermission($invoiceModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_GENERATE') . ' ' . vtranslate($invoiceModuleModel->getSingularLabelKey(), 'Invoice'),
                'linkurl'   => $recordModel->getCreateInvoiceUrl(),
                'linkicon'  => '',
            ];
        }

        $salesOrderModuleModel = Vtiger_Module_Model::getInstance('SalesOrder');
        if ($currentUserModel->hasModuleActionPermission($salesOrderModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_GENERATE') . ' ' . vtranslate($salesOrderModuleModel->getSingularLabelKey(), 'SalesOrder'),
                'linkurl'   => $recordModel->getCreateSalesOrderUrl(),
                'linkicon'  => '',
            ];
        }

        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');
        if ($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_GENERATE') . ' ' . vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
                'linkurl'   => $recordModel->getCreatePurchaseOrderUrl(),
                'linkicon'  => '',
            ];
        }

        return Vtiger_Link_Model::merge(parent::getDetailViewLinks($linkParams), Vtiger_Link_Model::checkAndConvertLinks($links));
    }
}
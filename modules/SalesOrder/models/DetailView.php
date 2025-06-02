<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class SalesOrder_DetailView_Model extends Vtiger_DetailView_Model
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

        $linkModelList = parent::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();

        $invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');
        if ($currentUserModel->hasModuleActionPermission($invoiceModuleModel->getId(), 'CreateView')) {
            $basicActionLink = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_CREATE') . ' ' . vtranslate($invoiceModuleModel->getSingularLabelKey(), 'Invoice'),
                'linkurl'   => $recordModel->getCreateInvoiceUrl(),
                'linkicon'  => ''
            ];
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');
        if ($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'CreateView')) {
            $basicActionLink = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_CREATE') . ' ' . vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
                'linkurl'   => $recordModel->getCreatePurchaseOrderUrl(),
                'linkicon'  => ''
            ];
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        return $linkModelList;
    }

}

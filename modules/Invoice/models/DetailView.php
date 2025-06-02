<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Invoice_DetailView_Model extends Vtiger_DetailView_Model
{

    public function getDetailViewLinks($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $linkModelList = parent::getDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();

        $purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');

        if ($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'CreateView')) {
            $basicActionLink = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_GENERATE') . ' ' . vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
                'linkurl'   => $recordModel->getCreatePurchaseOrderUrl(),
                'linkicon'  => ''
            ];
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        return $linkModelList;
    }
}

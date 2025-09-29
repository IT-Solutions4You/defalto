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

class Potentials_DetailView_Model extends Vtiger_DetailView_Model
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
        $invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');
        $quoteModuleModel = Vtiger_Module_Model::getInstance('Quotes');
        $salesOrderModuleModel = Vtiger_Module_Model::getInstance('SalesOrder');
        $projectModuleModel = Vtiger_Module_Model::getInstance('Project');
        $links = [];

        if ($currentUserModel->hasModuleActionPermission($invoiceModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => Vtiger_DetailView_Model::LINK_MORE,
                'linklabel' => vtranslate('LBL_CREATE_INVOICE', $recordModel->getModuleName()),
                'linkurl'   => $recordModel->getCreateInvoiceUrl(),
                'linkicon'  => $invoiceModuleModel->getModuleIcon(),
            ];
        }

        if ($currentUserModel->hasModuleActionPermission($quoteModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => Vtiger_DetailView_Model::LINK_MORE,
                'linklabel' => vtranslate('LBL_CREATE_QUOTES', $recordModel->getModuleName()),
                'linkurl'   => $recordModel->getCreateQuoteUrl(),
                'linkicon'  => $quoteModuleModel->getModuleIcon(),
            ];
        }

        if ($currentUserModel->hasModuleActionPermission($salesOrderModuleModel->getId(), 'CreateView')) {
            $links[] = [
                'linktype'  => Vtiger_DetailView_Model::LINK_MORE,
                'linklabel' => vtranslate('LBL_CREATE_SALESORDER', $recordModel->getModuleName()),
                'linkurl'   => $recordModel->getCreateSalesOrderUrl(),
                'linkicon'  => $salesOrderModuleModel->getModuleIcon(),
            ];
        }

        if ($currentUserModel->hasModuleActionPermission($projectModuleModel->getId(), 'CreateView') && !$recordModel->isPotentialConverted()) {
            $links[] = [
                'linktype'  => Vtiger_DetailView_Model::LINK_ADVANCED,
                'linklabel' => vtranslate('LBL_CREATE_PROJECT', $recordModel->getModuleName()),
                'linkurl'   => 'Javascript:Potentials_Detail_Js.convertPotential("' . $recordModel->getConvertPotentialUrl() . '",this);',
                'linkicon'  => '<i class="fa-solid fa-briefcase"></i>',
            ];
        }

        return Vtiger_Link_Model::merge(parent::getDetailViewLinks($linkParams), Vtiger_Link_Model::checkAndConvertLinks($links));
    }

    /**
     * Function to get the detail view widgets
     * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
     */
    public function getWidgets()
    {
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $widgetLinks = parent::getWidgets();
        $contactsInstance = Vtiger_Module_Model::getInstance('Contacts');

        if ($userPrivilegesModel->hasModuleActionPermission($contactsInstance->getId(), 'DetailView')) {
            $createPermission = $userPrivilegesModel->hasModuleActionPermission($contactsInstance->getId(), 'CreateView');
            $widgetLinks[] = [
                'linktype'  => 'DETAILVIEWWIDGET',
                'linklabel' => 'LBL_RELATED_CONTACTS',
                'linkName'  => $contactsInstance->getName(),
                'linkurl'   => 'module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getRecord()->getId(
                    ) . '&relatedModule=Contacts&mode=showRelatedRecords&page=1&limit=5',
                'action'    => $createPermission ? ['Add'] : [],
                'actionURL' => $contactsInstance->getQuickCreateUrl(),
            ];
        }

        $productsInstance = Vtiger_Module_Model::getInstance('Products');

        if ($userPrivilegesModel->hasModuleActionPermission($productsInstance->getId(), 'DetailView')) {
            $createPermission = $userPrivilegesModel->hasModuleActionPermission($productsInstance->getId(), 'CreateView');
            $widgetLinks[] = [
                'linktype'  => 'DETAILVIEWWIDGET',
                'linklabel' => 'LBL_RELATED_PRODUCTS',
                'linkName'  => $productsInstance->getName(),
                'linkurl'   => 'module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getRecord()->getId(
                    ) . '&relatedModule=Products&mode=showRelatedRecords&page=1&limit=5',
                'action'    => $createPermission ? ['Add'] : [],
                'actionURL' => $productsInstance->getQuickCreateUrl(),
            ];
        }

        return $widgetLinks;
    }
}
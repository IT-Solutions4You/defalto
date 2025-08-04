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

class Leads_DetailView_Model extends Accounts_DetailView_Model
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
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();
        $baseDetailViewModel = new Vtiger_DetailView_Model();
        $baseDetailViewModel->setModule($moduleModel);
        $baseDetailViewModel->setRecord($recordModel);
        $links = [];
        $SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');

        if ($SMSNotifierModuleModel && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
            $links[] = [
                'linktype'  => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_SEND_SMS',
                'linkurl'   => 'javascript:Vtiger_Detail_Js.triggerSendSms("index.php?module=' . $this->getModule()->getName(
                    ) . '&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
                'linkicon'  => '',
            ];
        }

        if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'ConvertLead', $recordModel->getId()) && Users_Privileges_Model::isPermitted(
                $moduleModel->getName(),
                'EditView',
                $recordModel->getId()
            ) && !$recordModel->isLeadConverted()) {
            $links[] = [
                'linktype'  => 'DETAILVIEWADVANCED',
                'linklabel' => 'LBL_CONVERT_LEAD',
                'linkurl'   => 'Javascript:Leads_Detail_Js.convertLead("' . $recordModel->getConvertLeadUrl() . '",this);',
                'linkicon'  => '<i class="fa-solid fa-right-from-bracket"></i>',
            ];
        }

        return Vtiger_Link_Model::merge($baseDetailViewModel::getDetailViewLinks($linkParams), Vtiger_Link_Model::checkAndConvertLinks($links));
    }
}
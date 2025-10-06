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

class Leads_ListView_Model extends Vtiger_ListView_Model
{
    /**
     * Function to get the list of Mass actions for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
     */
    public function getListViewMassActions($linkParams)
    {
        $massActionLinks = parent::getListViewMassActions($linkParams);

        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
        if ($SMSNotifierModuleModel && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
            $massActionLink = [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_SEND_SMS',
                'linkurl'   => 'javascript:Vtiger_List_Js.triggerSendSms("index.php?module=' . $this->getModule()->getName() . '&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
                'linkicon'  => 'fa-solid fa-comment-sms'
            ];
            $massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }

        return $massActionLinks;
    }

    /**
     * Function to get the list of listview links for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
     */
    function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $links = parent::getListViewLinks($linkParams);

        $index = 0;
        foreach ($links['LISTVIEWBASIC'] as $link) {
            if ($link->linklabel == 'Send SMS') {
                unset($links['LISTVIEWBASIC'][$index]);
            }
            $index++;
        }

        return $links;
    }
}
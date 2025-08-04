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

class Users_DetailView_Model extends Vtiger_DetailView_Model
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
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $recordModel = $this->getRecord();
        $recordId = $recordModel->getId();
        $links = [];

        if (($currentUserModel->isAdminUser() == true || $currentUserModel->get('id') == $recordId) && $recordModel->get('status') == 'Active') {
            $recordModel = $this->getRecord();

            $links[] = [
                'linktype'  => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_EDIT',
                'linkurl'   => $recordModel->getEditViewUrl(),
                'linkicon'  => '',
            ];

            $links[] = [
                'linktype'  => 'DETAILVIEWPREFERENCE',
                'linklabel' => 'LBL_EDIT',
                'linkurl'   => $recordModel->getPreferenceEditViewUrl(),
                'linkicon'  => '',
            ];

            if ($currentUserModel->isAdminUser() && $currentUserModel->get('id') != $recordId) {
                $links[] = [
                    'linktype'  => 'DETAILVIEW',
                    'linklabel' => 'LBL_DELETE',
                    'linkurl'   => 'javascript:Users_Detail_Js.triggerDeleteUser("' . $recordModel->getDeleteUrl() . '")',
                    'linkicon'  => '',
                ];
            }

            if (Users_Privileges_Model::isPermittedToChangeUsername($recordId)) {
                $links[] = [
                    'linktype'  => 'DETAILVIEW',
                    'linklabel' => 'LBL_CHANGE_USERNAME',
                    'linkurl'   => "javascript:Users_Detail_Js.triggerChangeUsername('" . $recordModel->getChangeUsernameUrl() . "')",
                    'linkicon'  => '',
                ];
            }

            $links[] = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => 'LBL_CHANGE_PASSWORD',
                'linkurl'   => "javascript:Users_Detail_Js.triggerChangePassword('" . $recordModel->getChangePwdUrl() . "','Users')",
                'linkicon'  => '',
            ];
            $links[] = [
                'linktype'  => 'DETAILVIEW',
                'linklabel' => 'LBL_CHANGE_ACCESS_KEY',
                'linkurl'   => "javascript:Users_Detail_Js.triggerChangeAccessKey('index.php?module=Users&action=SaveAjax&mode=changeAccessKey&record=$recordId')",
                'linkicon'  => '',
            ];

            return Vtiger_Link_Model::checkAndConvertLinks($links);
        }
    }
}
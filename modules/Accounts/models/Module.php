<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class Accounts_Module_Model extends Vtiger_Module_Model
{

    protected string $fontIcon = 'fa-solid fa-building-user';

    /**
     * Function to get the Quick Links for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $parentQuickLinks = parent::getSideBarLinks($linkParams);

        $quickLink = [
            'linktype'  => 'SIDEBARLINK',
            'linklabel' => 'LBL_DASHBOARD',
            'linkurl'   => $this->getDashBoardUrl(),
            'linkicon'  => '',
        ];

        //Check profile permissions for Dashboards
        $moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
        if ($permission) {
            $parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        return $parentQuickLinks;
    }

    /**
     * Function to get list view query for popup window
     *
     * @param <String>  $sourceModule Parent module
     * @param <String>  $field        parent fieldname
     * @param <Integer> $record       parent id
     * @param <String>  $listQuery
     *
     * @return <String> Listview Query
     */
    public function getQueryByModuleField($sourceModule, $field, $record, $listQuery)
    {
        if (($sourceModule == 'Accounts' && $field == 'account_id' && $record)
            || in_array($sourceModule, ['Campaigns', 'Products', 'Services', 'ITS4YouEmails'])) {
            $db = PearDatabase::getInstance();
            $params = [$record];
            if ($sourceModule === 'Campaigns') {
                $condition = " vtiger_account.accountid NOT IN (SELECT accountid FROM vtiger_campaignaccountrel WHERE campaignid = ?)";
            } elseif ($sourceModule === 'Products') {
                $condition = " vtiger_account.accountid NOT IN (SELECT crmid FROM vtiger_seproductsrel WHERE productid = ?)";
            } elseif ($sourceModule === 'Services') {
                $condition = " vtiger_account.accountid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ";
                $params = [$record, $record];
            } elseif ($sourceModule === 'ITS4YouEmails') {
                $condition = ' vtiger_account.emailoptout = 0';
                $params = [];
            } else {
                $condition = " vtiger_account.accountid != ?";
            }
            $condition = $db->convert2Sql($condition, $params);

            $position = stripos($listQuery, 'where');
            if ($position) {
                $split = preg_split('/where/i', $listQuery);
                $overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
            } else {
                $overRideQuery = $listQuery . ' WHERE ' . $condition;
            }

            return $overRideQuery;
        }
    }

    /**
     * @return bool
     */
    public function isShowMapSupported(): bool
    {
        return true;
    }
}
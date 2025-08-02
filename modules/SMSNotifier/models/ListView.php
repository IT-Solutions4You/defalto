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

class SMSNotifier_ListView_Model extends Vtiger_ListView_Model
{
    /**
     * Function to get the list of listview links for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
     */
    public function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();

        $linkTypes = ['LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING'];
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks = $this->getSettingLinks();
            foreach ($settingsLinks as $settingsLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
        }

        return $links;
    }

    /**
     * Function to get the list of Mass actions for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
     */
    public function getListViewMassActions($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();

        $linkTypes = ['LISTVIEWMASSACTION'];
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        $massActionLink = [];
        if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
            $massActionLink = [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => vtranslate('LBL_DELETE', $moduleName),
                'linkurl'   => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleName . '&action=MassDelete");',
                'linkicon'  => ''
            ];
        }
        $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);

        return $links;
    }
}
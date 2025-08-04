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

/**
 * ListView Model Class for Project module
 */
class Project_ListView_Model extends Vtiger_ListView_Model
{
    /**
     * Function to get the list of listview links
     *
     * @param <Array> $linkParams Parameters to be replaced in the link template
     *
     * @return <Array> - an array of Vtiger_Link_Model instances
     */
    public function getListViewLinks($linkParams)
    {
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $links = parent::getListViewLinks($linkParams);
        $quickLinks = [];

        $projectTaskInstance = Vtiger_Module_Model::getInstance('ProjectTask');
        if ($userPrivilegesModel->hasModulePermission($projectTaskInstance->getId())) {
            $quickLinks[] = [
                'linktype'  => 'LISTVIEWQUICK',
                'linklabel' => 'Tasks List',
                'linkurl'   => $this->getModule()->getDefaultUrl(),
                'linkicon'  => ''
            ];
        }

        foreach ($quickLinks as $quickLink) {
            $links['LISTVIEWQUICK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        return $links;
    }
}
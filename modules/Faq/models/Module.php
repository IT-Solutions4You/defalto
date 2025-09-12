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

class Faq_Module_Model extends Vtiger_Module_Model
{
    /**
     * Function is used to give links in the All menu bar
     */
    public function getQuickMenuModels()
    {
        if ($this->isEntityModule()) {
            $moduleName = $this->getName();
            $listViewModel = Vtiger_ListView_Model::getCleanInstance($moduleName);
            $basicListViewLinks = $listViewModel->getBasicLinks();
        }

        if ($basicListViewLinks) {
            foreach ($basicListViewLinks as $basicListViewLink) {
                if (is_array($basicListViewLink)) {
                    $links[] = Vtiger_Link_Model::getInstanceFromValues($basicListViewLink);
                } elseif (is_a($basicListViewLink, 'Vtiger_Link_Model')) {
                    $links[] = $basicListViewLink;
                }
            }
        }

        return $links;
    }

    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames()
    {
        return [];
    }

    /**
     * Function to get Module Header Links (for Vtiger7)
     * @return array
     */
    public function getModuleBasicLinks()
    {
        $createPermission = Users_Privileges_Model::isPermitted($this->getName(), 'CreateView');
        $basicLinks = [];
        if ($createPermission) {
            $basicLinks[] = [
                'linktype'    => 'BASIC',
                'linklabel'   => 'LBL_ADD_RECORD',
                'linkurl'     => $this->getCreateRecordUrl(),
                'linkicon'    => 'fa-plus',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ];
        }

        return $basicLinks;
    }
}
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

class Campaigns_Module_Model extends Vtiger_Module_Model
{
    /**
     * Function to get Specific Relation Query for this Module
     *
     * @param <type> $relatedModule
     *
     * @return <type>
     */
    public function getSpecificRelationQuery($relatedModule)
    {
        if ($relatedModule === 'Leads') {
            $specificQuery = ' AND vtiger_leaddetails.converted = 0 ';

            return $specificQuery;
        }

        return parent::getSpecificRelationQuery($relatedModule);
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
        if (in_array($sourceModule, ['Leads', 'Accounts', 'Contacts'])) {
            $db = PearDatabase::getInstance();
            $condition = ' vtiger_crmentity.crmid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ';
            $params = [$record, $record];
            $condition = $db->convert2Sql($condition, $params);

            return $this->addConditionToQuery($listQuery, $condition);
        }
    }

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
        return array();
    }
}
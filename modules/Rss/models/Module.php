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

class Rss_Module_Model extends Vtiger_Module_Model
{
    /**
     * Function to get the Quick Links for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        $quickLinks = [
            [
                'linktype'  => 'SIDEBARLINK',
                'linklabel' => 'LBL_ADD_FEED_SOURCE',
                'linkurl'   => $this->getDefaultUrl(),
                'linkicon'  => '',
            ]
        ];
        foreach ($quickLinks as $quickLink) {
            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }
        $quickWidgets = [
            [
                'linktype'  => 'SIDEBARWIDGET',
                'linklabel' => 'LBL_RSS_FEED_SOURCES',
                'linkurl'   => 'module=' . $this->get('name') . '&view=ViewTypes&mode=getRssWidget',
                'linkicon'  => ''
            ],
        ];
        foreach ($quickWidgets as $quickWidget) {
            $links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
        }

        return $links;
    }

    /**
     * Function to get rss sources list
     */
    public function getRssSources()
    {
        $db = PearDatabase::getInstance();

        $sql = 'Select * from vtiger_rss';
        $result = $db->pquery($sql, []);
        $noOfRows = $db->num_rows($result);

        $records = [];
        for ($i = 0; $i < $noOfRows; ++$i) {
            $row = $db->query_result_rowdata($result, $i);
            $row['id'] = $row['rssid'];
            $records[$row['id']] = $this->getRecordFromArray($row);
        }

        return $records;
    }

    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames()
    {
        return [];
    }
}
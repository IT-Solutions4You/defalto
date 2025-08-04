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

class Portal_Module_Model extends Vtiger_Module_Model
{
    public string $fontIcon = 'fa fa-link';

    public function getSideBarLinks($linkParams)
    {
        $quickLink = [
            'linktype'  => 'SIDEBARLINK',
            'linklabel' => 'LBL_OUR_SITES_LIST',
            'linkurl'   => $this->getListViewUrl(),
            'linkicon'  => '',
        ];
        $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);

        return $links;
    }

    public static function savePortalRecord($recordId, $bookmarkName = false, $bookmarkUrl = false)
    {
        $db = PearDatabase::getInstance();
        if (empty($recordId)) {
            $portalId = $db->getUniqueID('vtiger_portal');
            $query = "INSERT INTO vtiger_portal VALUES(?,?,?,?,?,?)";
            $params = [$portalId, $bookmarkName, $bookmarkUrl, 0, 0, date('Y-m-d H:i:s')];
        } else {
            $query = "UPDATE vtiger_portal SET portalname=?, portalurl=? WHERE portalid=?";
            $params = [$bookmarkName, $bookmarkUrl, $recordId];
        }

        $db->pquery($query, $params);

        return true;
    }

    public static function getPortalRecord($recordId)
    {
        $db = PearDatabase::getInstance();

        $result = $db->pquery('SELECT portalname, portalurl FROM vtiger_portal WHERE portalid = ?', [$recordId]);

        $data['bookmarkName'] = $db->query_result($result, 0, 'portalname');
        $data['bookmarkUrl'] = $db->query_result($result, 0, 'portalurl');

        return $data;
    }

    public static function deletePortalRecord($recordId)
    {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM vtiger_portal WHERE portalid = ?', [$recordId]);
    }

    public static function getWebsiteUrl($recordId)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT portalurl FROM vtiger_portal WHERE portalid=?', [$recordId]);

        return $db->query_result($result, 0, 'portalurl');
    }

    public static function getAllRecords()
    {
        $db = PearDatabase::getInstance();
        $record = [];

        $result = $db->pquery('SELECT portalid, portalname FROM vtiger_portal', []);

        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->fetch_row($result, $i);
            $record[$i]['id'] = $row['portalid'];
            $record[$i]['portalname'] = $row['portalname'];
        }

        return $record;
    }

    public static function deleteRecords(Vtiger_Request $request)
    {
        $searchValue = $request->get('search_value');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');

        $db = PearDatabase::getInstance();

        $query = 'DELETE FROM vtiger_portal';
        $params = [];

        if (!empty($selectedIds) && $selectedIds != 'all' && php7_count($selectedIds) > 0) {
            $query .= " WHERE portalid IN (" . generateQuestionMarks($selectedIds) . ")";
            $params = $selectedIds;
        } elseif ($selectedIds == 'all') {
            if (empty($searchValue) && php7_count($excludedIds) > 0) {
                $query .= " WHERE portalid NOT IN (" . generateQuestionMarks($excludedIds) . ")";
                $params = $excludedIds;
            } elseif (!empty($searchValue) && php7_count($excludedIds) < 1) {
                $query .= " WHERE portalname LIKE '%" . $searchValue . "%'";
            } elseif (!empty($searchValue) && php7_count($excludedIds) > 0) {
                $query .= " WHERE portalname LIKE '%" . $searchValue . "%' AND portalid NOT IN (" . generateQuestionMarks($excludedIds) . ")";
                $params = $excludedIds;
            }
        }
        $db->pquery($query, $params);
    }

    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getModuleBasicLinks(): array
    {
        $basicLinks = [];
        $basicLinks[] = [
            'linktype'    => 'BASIC',
            'linklabel'   => 'LBL_ADD_BOOKMARK',
            'linkurl'     => 'javascript:Portal_List_Js.editBookmarkAction()',
            'linkicon'    => 'fa-plus',
            'linkclass'   => 'addBookmark',
            'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
        ];

        return $basicLinks;
    }
}
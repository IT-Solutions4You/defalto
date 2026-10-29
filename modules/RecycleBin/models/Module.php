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

class RecycleBin_Module_Model extends Vtiger_Module_Model
{
    /**
     * Function to get the url for list view of the module
     * @return <string> - url
     */
    public function getDefaultUrl()
    {
        return 'index.php?module=' . $this->get('name') . '&view=' . $this->getListViewName();
    }

    /**
     * Function to get the list of listview links for the module
     * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
     */
    public function getListViewLinks()
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $basicLinks = [];
        if ($currentUserModel->isAdminUser()) {
            $basicLinks = [
                [
                    'linktype'  => 'LISTVIEWBASIC',
                    'linklabel' => 'LBL_EMPTY_RECYCLEBIN',
                    'linkurl'   => 'javascript:RecycleBin_List_Js.emptyRecycleBin("index.php?module=' . $this->get('name') . '&action=RecycleBinAjax")',
                    'linkicon'  => ''
                ]
            ];
        }

        foreach ($basicLinks as $basicLink) {
            $links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
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
    public function getListViewMassActions()
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if (!$currentUserModel->isAdminUser()) {
            return [];
        }

        $massActionLinks = [
            [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DELETE',
                'linkurl'   => 'javascript:RecycleBin_List_Js.deleteRecords("index.php?module=' . $this->get('name') . '&action=RecycleBinAjax")',
                'linkicon'  => ''
            ],
            [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_RESTORE',
                'linkurl'   => 'javascript:RecycleBin_List_Js.restoreRecords("index.php?module=' . $this->get('name') . '&action=RecycleBinAjax")',
                'linkicon'  => ''
            ],
        ];

        foreach ($massActionLinks as $massActionLink) {
            $links[] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }

        return $links;
    }

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
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl'   => $this->getDefaultUrl(),
                'linkicon'  => '',
            ],
        ];
        foreach ($quickLinks as $quickLink) {
            $links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }

        return $links;
    }

    /**
     * Function to get all entity modules
     * @return <array>
     */
    public function getAllModuleList()
    {
        $moduleModels = parent::getEntityModules();
        $restrictedModules = ['ProjectMilestone', 'ModComments', 'Integration', 'PBXManager', 'Dashboard', 'Home'];
        foreach ($moduleModels as $key => $moduleModel) {
            if (in_array($moduleModel->getName(), $restrictedModules) || $moduleModel->get('isentitytype') != 1) {
                unset($moduleModels[$key]);
            }
        }

        return $moduleModels;
    }

    public function isSourceModuleAllowed(string $sourceModule): bool
    {
        foreach ($this->getAllModuleList() as $moduleModel) {
            if ($moduleModel->getName() === $sourceModule) {
                return true;
            }
        }

        return false;
    }

    /**
     * Function to delete the reccords perminently in vitger CRM database
     */
    public function emptyRecycleBin()
    {
        $db = PearDatabase::getInstance();

        $db->pquery(
            'DELETE vtiger_modtracker_basic.* FROM vtiger_modtracker_basic INNER JOIN vtiger_crmentity on 
					vtiger_crmentity.crmid = vtiger_modtracker_basic.crmid AND vtiger_crmentity.deleted = 1',
            []
        );

        $getIdsQuery = 'SELECT crmid from vtiger_crmentity WHERE deleted=?';
        $resultIds = $db->pquery($getIdsQuery, [1]);
        $recordIds = [];
        if ($db->num_rows($resultIds)) {
            for ($i = 0; $i < $db->num_rows($resultIds); $i++) {
                $recordIds[$i] = $db->query_result($resultIds, $i, 'crmid');
            }
        }
        $this->deleteFiles($recordIds);
        $db->pquery('DELETE FROM vtiger_crmentity WHERE deleted = 1', []);
        $db->pquery('DELETE FROM vtiger_relatedlists_rb', []);

        return true;
    }

    /**
     * Function to deleted the records perminently in CRM
     *
     * @param type $reocrdIds
     */
    public function deleteRecords($recordIds, string $sourceModule = '')
    {
        $db = PearDatabase::getInstance();
        $recordIds = $this->fetchDeletedRecordIds($recordIds, $sourceModule);

        if (!$recordIds) {
            return;
        }

        $query = 'DELETE FROM vtiger_relatedlists_rb WHERE entityid in(' . generateQuestionMarks($recordIds) . ')';
        $db->pquery($query, [$recordIds]);

        // Delete related mod comments
        $this->deleteRelatedComments($recordIds);

        // TODO - Remove records from module tables and other related stores.
        $query = 'DELETE FROM vtiger_modtracker_basic WHERE crmid in(' . generateQuestionMarks($recordIds) . ')';
        $db->pquery($query, [$recordIds]);
        // Delete entries of attachments from vtiger_attachments and vtiger_seattachmentsrel
        $this->deleteFiles($recordIds);

        //Delete the records in vtiger crmentity and relatedlists.
        $query = 'DELETE FROM vtiger_crmentity WHERE deleted = ? and crmid in(' . generateQuestionMarks($recordIds) . ')';
        $db->pquery($query, [1, $recordIds]);
    }

    /**Function to delete files from CRM.
     *
     * @param type $recordIds
     */
    public function deleteFiles($recordIds)
    {
        if (!$recordIds) {
            return;
        }

        $db = PearDatabase::getInstance();
        $getAttachmentsIdQuery = 'SELECT DISTINCT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid in(' . generateQuestionMarks($recordIds) . ')';
        $result = $db->pquery($getAttachmentsIdQuery, [$recordIds]);
        $attachmentsIds = [];

        while ($row = $db->fetchByAssoc($result)) {
            $attachmentsIds[] = $row['attachmentsid'];
        }

        if (!empty($attachmentsIds)) {
            $deleteRelQuery = 'DELETE FROM vtiger_seattachmentsrel WHERE crmid in(' . generateQuestionMarks($recordIds) . ')';
            $db->pquery($deleteRelQuery, [$recordIds]);

            $getPathQuery = 'SELECT vtiger_attachments.* FROM vtiger_attachments '
                . 'LEFT JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid '
                . 'LEFT JOIN vtiger_salesmanattachmentsrel ON vtiger_salesmanattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid '
                . 'LEFT JOIN vtiger_mailmanager_mailattachments ON vtiger_mailmanager_mailattachments.attachid = vtiger_attachments.attachmentsid '
                . 'WHERE vtiger_attachments.attachmentsid in (' . generateQuestionMarks($attachmentsIds) . ') '
                . 'AND vtiger_seattachmentsrel.attachmentsid IS NULL '
                . 'AND vtiger_salesmanattachmentsrel.attachmentsid IS NULL '
                . 'AND vtiger_mailmanager_mailattachments.attachid IS NULL';
            $pathResult = $db->pquery($getPathQuery, [$attachmentsIds]);
            $orphanedAttachmentIds = [];

            while ($attachment = $db->fetchByAssoc($pathResult)) {
                $orphanedAttachmentIds[] = $attachment['attachmentsid'];
                $fileName = Core_Storage_Model::getInstance()->getAttachmentFilePath($attachment);

                if (file_exists($fileName)) {
                    chmod($fileName, 0750);
                    unlink($fileName);
                }
            }

            if ($orphanedAttachmentIds) {
                $deleteAttachmentQuery = 'DELETE FROM vtiger_attachments WHERE attachmentsid in (' . generateQuestionMarks($orphanedAttachmentIds) . ')';
                $db->pquery($deleteAttachmentQuery, [$orphanedAttachmentIds]);
            }
        }
    }

    /**
     * Return only requested records that are currently in the Recycle Bin.
     */
    public function fetchDeletedRecordIds(array $recordIds, string $sourceModule = ''): array
    {
        $recordIds = array_values(array_unique(array_filter(array_map('intval', $recordIds))));

        if (!$recordIds) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $query = 'SELECT vtiger_crmentity.crmid FROM vtiger_crmentity';

        if ($sourceModule) {
            $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);

            if (!$moduleModel || $moduleModel->get('isentitytype') != 1) {
                return [];
            }

            $baseTable = $moduleModel->get('basetable');
            $baseTableId = $moduleModel->get('basetableid');
            $query .= ' INNER JOIN ' . $baseTable . ' ON ' . $baseTable . '.' . $baseTableId . ' = vtiger_crmentity.crmid';
        }

        $query .= ' WHERE vtiger_crmentity.deleted = 1 AND vtiger_crmentity.crmid in (' . generateQuestionMarks($recordIds) . ')';
        $result = $db->pquery($query, [$recordIds]);
        $deletedRecordIds = [];

        while ($row = $db->fetchByAssoc($result)) {
            $deletedRecordIds[] = $row['crmid'];
        }

        return $deletedRecordIds;
    }

    /**
     * Function to restore the deleted records.
     *
     * @param type $sourceModule
     * @param type $recordIds
     */
    public function restore($sourceModule, $recordIds)
    {
        $recordIds = $this->fetchDeletedRecordIds($recordIds, $sourceModule);
        $focus = CRMEntity::getInstance($sourceModule);

        for ($i = 0; $i < php7_count($recordIds); $i++) {
            if (!empty($recordIds[$i])) {
                $focus->restore($sourceModule, $recordIds[$i]);
            }
        }
    }

    public function getDeletedRecordsTotalCount()
    {
        $db = PearDatabase::getInstance();
        $totalCount = $db->pquery('select count(*) as count from vtiger_crmentity where deleted=1', []);

        return $db->query_result($totalCount, 0, 'count');
    }

    /**
     * Function to check deleted records exists in recyclebin
     * @return <Boolean> true/false
     */
    public function isRecordsDeleted()
    {
        $db = PearDatabase::getInstance();
        $totalCount = $db->pquery('SELECT 1 FROM vtiger_crmentity WHERE deleted=1 LIMIT 1', []);
        if ($db->num_rows($totalCount) > 0) {
            return true;
        }

        return false;
    }

    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames()
    {
        return [];
    }

    /**
     * Funxtion to identify if the module supports quick search or not
     */
    public function isQuickSearchEnabled()
    {
        return true;
    }

    public function deleteRelatedComments($recordIds)
    {
        $db = PearDatabase::getInstance();
        $query = 'DELETE vtiger_crmentity.* FROM vtiger_crmentity '
            . 'INNER JOIN vtiger_modcomments ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid '
            . 'WHERE vtiger_modcomments.related_to in(' . generateQuestionMarks($recordIds) . ')';

        $db->pquery($query, array($recordIds));
    }
}

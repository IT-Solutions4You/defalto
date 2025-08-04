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

class RecycleBin_ListView_Model extends Vtiger_ListView_Model
{
    /**
     * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
     *
     * @param <String> $moduleName - Module Name
     * @param <Number> $viewId     - Custom View Id
     *
     * @return Vtiger_ListView_Model instance
     */
    public static function getInstance($moduleName, $viewId = '0', $listHeaders = [])
    {
        [$moduleName, $sourceModule] = func_get_args();
        $db = PearDatabase::getInstance();
        $currentUser = vglobal('current_user');

        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
        $instance = new $modelClassName();

        $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $queryGenerator = new EnhancedQueryGenerator($sourceModuleModel->get('name'), $currentUser);
        $cvidObj = CustomView_Record_Model::getAllFilterByModule($sourceModuleModel->get('name'));
        $cvid = $cvidObj->getId('cvid');
        $queryGenerator->initForCustomViewById($cvid);

        $controller = new ListViewController($db, $currentUser, $queryGenerator);

        return $instance->set('module', $sourceModuleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
    }

    /**
     * Function to get the list view entries
     *
     * @param Vtiger_Paging_Model $pagingModel
     *
     * @return array - Associative array of record id mapped to Vtiger_Record_Model instance.
     * @throws Exception
     */
    public function getListViewEntries($pagingModel)
    {
        $db = PearDatabase::getInstance();
        $moduleName = $this->getModule()->get('name');
        $moduleFocus = CRMEntity::getInstance($moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $queryGenerator = $this->get('query_generator');
        $listViewContoller = $this->get('listview_controller');
        $searchParams = $this->get('search_params');

        if (empty($searchParams)) {
            $searchParams = [];
        }

        $glue = "";

        if (php7_count($queryGenerator->getWhereFields()) > 0 && (php7_count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }

        $queryGenerator->parseAdvFilterList($searchParams, $glue);

        if ($moduleName == 'Documents') {
            //Document source required in list view for managing delete
            $listViewFields = $queryGenerator->getFields();
            if (!in_array('document_source', $listViewFields)) {
                $listViewFields[] = 'document_source';
            }
            $queryGenerator->setFields($listViewFields);
        }

        $this->retrieveOrderBy();
        $listQuery = $this->getQuery();
        $listQuery = preg_replace("/vtiger_crmentity.deleted\s*=\s*0/i", 'vtiger_crmentity.deleted = 1', $listQuery);

        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();

        $listQuery .= $this->getQueryGenerator()->getOrderByClause();
        $listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);

        $listResult = $db->pquery($listQuery, []);
        $listViewRecordModels = [];
        $listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);
        $pagingModel->calculatePageRange($listViewEntries);

        if ($db->num_rows($listResult) > $pageLimit) {
            array_pop($listViewEntries);
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }

        $index = 0;
        foreach ($listViewEntries as $recordId => $record) {
            $rawData = $db->query_result_rowdata($listResult, $index++);
            $record['id'] = $recordId;
            $listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
        }

        return $listViewRecordModels;
    }

    /**
     * Function to get the list view entries
     *
     * @param Vtiger_Paging_Model $pagingModel
     *
     * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
     */
    public function getListViewCount()
    {
        $db = PearDatabase::getInstance();

        $queryGenerator = $this->get('query_generator');

        $searchParams = $this->get('search_params');
        if (empty($searchParams)) {
            $searchParams = [];
        }

        $glue = "";
        if (php7_count($queryGenerator->getWhereFields()) > 0 && (php7_count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);

        $listQuery = $queryGenerator->getQuery();
        $listQuery = preg_replace("/vtiger_crmentity.deleted\s*=\s*0/i", 'vtiger_crmentity.deleted = 1', $listQuery);

        $position = stripos($listQuery, ' from ');
        if ($position) {
            $split = preg_split('/ from /i', $listQuery);
            $splitCount = php7_count($split);
            $listQuery = 'SELECT count(*) AS count ';
            for ($i = 1; $i < $splitCount; $i++) {
                $listQuery = $listQuery . ' FROM ' . $split[$i];
            }
        }

        $listResult = $db->pquery($listQuery, []);

        return $db->query_result($listResult, 0, 'count');
    }
}
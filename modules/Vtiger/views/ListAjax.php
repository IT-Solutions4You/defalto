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

class Vtiger_ListAjax_View extends Vtiger_List_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $moduleName = $request->get('module');
        if ($moduleName == 'Vtiger') {
            $permissions = [];
        }

        return $permissions;
    }

    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getListViewCount');
        $this->exposeMethod('getRecordsCount');
        $this->exposeMethod('getPageCount');
        $this->exposeMethod('showSearchResults');
        $this->exposeMethod('ShowListColumnsEdit');
        $this->exposeMethod('showSearchResultsWithValue');
        $this->exposeMethod('searchAll');
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function showSearchResults(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $listMode = $request->get('listMode');
        if (!empty($listMode)) {
            $request->set('mode', $listMode);
        }

        $customView = new CustomView();
        $this->viewName = $customView->getViewIdByName('All', $moduleName);
        $request->set('viewname', $this->viewName);

        $this->initializeListViewContents($request, $viewer);
        $totalCount = (int)$this->getListViewCount($request);
        $pageLimit = (int)$this->pagingModel->getPageLimit();
        $pageCount = max(1, (int)ceil($totalCount / max(1, $pageLimit)));

        $viewer->assign('LISTVIEW_COUNT', $totalCount);
        $viewer->assign('PAGE_COUNT', $pageCount);
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('RECORD_ACTIONS', $this->getRecordActionsFromModule($moduleModel));
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $moduleFields = $moduleModel->getFields();
        $fieldsInfo = [];
        foreach ($moduleFields as $fieldName => $fieldModel) {
            $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
        }
        $viewer->assign('ADV_SEARCH_FIELDS_INFO', json_encode($fieldsInfo));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showSearchResults', $request->getModule(), $viewer, $request);

        if ($request->get('_onlyContents', false)) {
            $viewer->view('UnifiedSearchResultsContents.tpl', $moduleName);
        } else {
            $viewer->view('UnifiedSearchResults.tpl', $moduleName);
        }
    }

    public function ShowListColumnsEdit(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $cvId = $request->get('cvid');
        $cvModel = CustomView_Record_Model::getInstanceById($cvId);

        $moduleModel = Vtiger_Module_Model::getInstance($request->get('source_module'));
        $recordStructureModel = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);
        $recordStructure = $recordStructureModel->getStructure();

        $cvSelectedFields = $cvModel->getSelectedFields();

        $cvSelectedFieldModelsMapping = [];
        foreach ($recordStructure as $blockFields) {
            foreach ($blockFields as $field) {
                $cvSelectedFieldModelsMapping[$field->getCustomViewColumnName()] = $field;
            }
        }

        $selectedFields = [];
        foreach ($cvSelectedFields as $cvFieldName) {
            $selectedFields[$cvFieldName] = $cvSelectedFieldModelsMapping[$cvFieldName];
        }

        $viewer->assign('CV_MODEL', $cvModel);
        $viewer->assign('RECORD_STRUCTURE', $recordStructure);
        $viewer->assign('SELECTED_FIELDS', $selectedFields);
        $viewer->assign('MODULE', $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'ShowListColumnsEdit', $request->getModule(), $viewer, $request);

        $viewer->view('ListColumnsEdit.tpl', $moduleName);
    }

    public function searchAll(Vtiger_Request $request)
    {
        $searchValue = $request->get('value');
        $searchModule = (string)$request->get('searchModule');
        $searchResults = GlobalSearch_Search_Model::getInstance()->search($searchValue, $searchModule);
        $matchingRecordsList = $this->getGlobalSearchListViewModels($searchResults, 1);

        $viewer = $this->getViewer($request);
        $viewer->assign('SEARCH_VALUE', $searchValue);
        $viewer->assign('PAGE_NUMBER', 1);
        $viewer->assign('MATCHING_RECORDS', $matchingRecordsList);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'searchAll', $request->getModule(), $viewer, $request);

        echo $viewer->view('SearchResults.tpl', '', true);
    }

    public function showSearchResultsWithValue(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $pageNumber = max(1, (int)$request->get('page', 1));
        $searchValue = $request->get('value');
        $searchResults = GlobalSearch_Search_Model::getInstance()->search($searchValue, $moduleName, $pageNumber);
        $matchingRecordsList = $this->getGlobalSearchListViewModels($searchResults, $pageNumber);

        if (!isset($matchingRecordsList[$moduleName])) {
            return;
        }

        $listViewModel = $matchingRecordsList[$moduleName];
        $viewer = $this->getViewer($request);
        $viewer->assign('LISTVIEW_MODEL', $listViewModel);
        $viewer->assign('LISTVIEW_HEADERS', $listViewModel->listViewHeaders);
        $viewer->assign('LISTVIEW_ENTRIES', $listViewModel->listViewEntries);
        $viewer->assign('PAGING_MODEL', $listViewModel->pagingModel);
        $viewer->assign('PAGE_NUMBER', $pageNumber);
        $viewer->assign('RECORDS_COUNT', $listViewModel->recordsCount);
        $viewer->assign('MODULE_MODEL', $listViewModel->getModule());
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showSearchResultsWithValue', $request->getModule(), $viewer, $request);

        $viewer->view('ModuleSearchResults.tpl', $moduleName);
    }

    public function getGlobalSearchPageLimit()
    {
        return GlobalSearch_Search_Model::PAGE_LIMIT;
    }

    /**
     * @param array<string, array{record_ids:array<int>,has_more:bool}> $searchResults
     * @return array<string, Vtiger_ListView_Model>
     * @throws Exception
     */
    protected function getGlobalSearchListViewModels(array $searchResults, int $pageNumber): array
    {
        $matchingRecordsList = [];
        $pageLimit = $this->getGlobalSearchPageLimit();

        foreach ($searchResults as $moduleName => $moduleResults) {
            $customView = new CustomView();
            $cvId = $customView->getViewIdByName('All', $moduleName);
            $listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
            $listViewModel->listViewHeaders = $listViewModel->getListViewHeaders();
            $listViewModel->listViewEntries = [];
            $listViewModel->set('pageNumber', $pageNumber);

            foreach ($moduleResults['record_ids'] as $recordId) {
                if (!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)) {
                    continue;
                }

                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $listViewModel->getModule());
                $recordModel->setRawData($recordModel->getData());

                foreach ($listViewModel->listViewHeaders as $fieldName => $fieldModel) {
                    $recordModel->set($fieldName, $fieldModel->getDisplayValue($recordModel->get($fieldName), $recordId));
                }

                $listViewModel->listViewEntries[$recordId] = $recordModel;
            }

            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('limit', $pageLimit);
            $pagingModel->set('page', $pageNumber);
            $pagingModel->calculatePageRange(array_keys($listViewModel->listViewEntries));
            $pagingModel->set('nextPageExists', $moduleResults['has_more']);
            $listViewModel->pagingModel = $pagingModel;
            $listViewModel->recordsCount = (($pageNumber - 1) * $pageLimit)
                + count($listViewModel->listViewEntries)
                + ($moduleResults['has_more'] ? 1 : 0);
            $matchingRecordsList[$moduleName] = $listViewModel;
        }

        return $matchingRecordsList;
    }
}

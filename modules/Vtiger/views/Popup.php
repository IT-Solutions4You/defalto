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

class Vtiger_Popup_View extends Vtiger_Footer_View
{
    protected $listViewEntries = false;
    protected $listViewHeaders = false;
    protected $listViewLinks = false;

    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);

        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    /**
     * Function returns the module name for which the popup should be initialized
     *
     * @param Vtiger_request $request
     *
     * @return <String>
     */
    public function getModule(Vtiger_request $request)
    {
        $moduleName = $request->getModule();

        return $moduleName;
    }

    public function process (Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $this->getModule($request);
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();

        $this->initializeListViewContents($request, $viewer);

        $viewer->assign('COMPANY_LOGO', $companyLogo);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Popup.tpl', $moduleName);
    }

    public function postProcess(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $this->getModule($request);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'postProcess', $request->getModule(), $viewer, $request);

        $viewer->view('PopupFooter.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            '~libraries/jquery/timepicker/jquery.timepicker.min.js',

            'modules.Vtiger.resources.Popup',
            "modules.$moduleName.resources.Popup",
            'modules.Vtiger.resources.BaseList',
            "modules.$moduleName.resources.BaseList",
            'libraries.jquery.jquery_windowmsg',
            'modules.Vtiger.resources.validator.BaseValidator',
            'modules.Vtiger.resources.validator.FieldValidator',
            "modules.$moduleName.resources.validator.FieldValidator"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /*
     * Function to initialize the required data in smarty to display the List View Contents
     */
    /**
     * @param Vtiger_Request $request
     * @param Vtiger_Viewer  $viewer
     *
     * @return void
     * @throws Exception
     */
    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $moduleName = $this->getModule($request);
        $cvId = $request->get('cvid', '0');
        $pageNumber = $request->get('page', '1');
        $orderBy = $request->get('orderby');
        $sortOrder = $request->get('sortorder');
        $sourceModule = $request->get('src_module');
        $sourceField = $request->get('src_field');
        $sourceRecord = $request->get('src_record');
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $currencyId = $request->get('currency_id');
        $relatedParentModule = $request->get('related_parent_module');
        $relatedParentId = $request->get('related_parent_id');
        $searchParams = $request->get('search_params');
        $relationId = $request->get('relationId');
        //To handle special operation when selecting record from Popup
        $getUrl = $request->get('get_url');
        //Check whether the request is in multi select mode
        $multiSelectMode = !$request->isEmpty('multi_select');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $autoFillModule = $moduleModel->getAutoFillModule($moduleName);

        if (empty($getUrl) && !empty($sourceField) && !empty($autoFillModule) && !$multiSelectMode) {
            $getUrl = 'getParentPopupContentsUrl';
        }

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);

        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

        if (!empty($relatedParentId) && isRecordExists($relatedParentId)) {
            $relatedParentModule = getSalesEntityType($relatedParentId);
        }

        if (!empty($relatedParentId)) {
            /** Required for focus function get_merged_list for example creating Appointments record with quick create in module Accounts and related to is Project  */
            vglobal('currentModule', $relatedParentModule);

            $parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
            $listViewModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, false, $relationId);
            $relationModel = $listViewModel->getRelationModel();
            $searchModuleModel = $listViewModel->getRelatedModuleModel();
        }

        if (empty($listViewModel) || empty($relationModel)) {
            $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);
            $searchModuleModel = $listViewModel->getModule();
        }

        if (!empty($orderBy)) {
            $listViewModel->set('orderby', $orderBy);
            $listViewModel->set('sortorder', $sortOrder);
        }
        if (!empty($sourceModule)) {
            $listViewModel->set('src_module', $sourceModule);
            $listViewModel->set('src_field', $sourceField);
            $listViewModel->set('src_record', $sourceRecord);
        }
        if ((!empty($searchKey)) && (!empty($searchValue))) {
            $listViewModel->set('search_key', $searchKey);
            $listViewModel->set('search_value', $searchValue);
        }

        $listViewModel->set('relationId', $relationId);

        if (!empty($searchParams)) {
            $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $searchModuleModel);
            $listViewModel->set('search_params', $transformedSearchParams);
        }

        if (is_a($listViewModel, 'Vtiger_RelationListView_Model')) {
            $this->listViewHeaders = $listViewModel->getHeaders();
            $relatedModuleModel = Vtiger_Module_Model::getInstance($moduleName);
            $moduleFields = $relatedModuleModel->getFields();

            if (empty($searchParams)) {
                $searchParams = [];
            }

            $whereCondition = [];

            foreach ($searchParams as $fieldListGroup) {
                foreach ($fieldListGroup as $fieldSearchInfo) {
                    $fieldModel = $moduleFields[$fieldSearchInfo[0]];
                    $tableName = Vtiger_Util_Helper::validateStringForSql($fieldModel->get('table'));
                    $column = Vtiger_Util_Helper::validateStringForSql($fieldModel->get('column'));
                    $whereCondition[$fieldSearchInfo[0]] = [$tableName . '.' . $column, $fieldSearchInfo[1], $fieldSearchInfo[2]];
                }
            }

            if (!empty($whereCondition)) {
                $listViewModel->set('whereCondition', $whereCondition);
            }

            $models = $listViewModel->getEntries($pagingModel);
            $noOfEntries = php7_count($models);
            foreach ($models as $recordId => $recordModel) {
                foreach ($this->listViewHeaders as $fieldName => $fieldModel) {
                    $recordModel->set($fieldName, $recordModel->getDisplayValue($fieldName));
                }
                $models[$recordId] = $recordModel;
            }
            $this->listViewEntries = $models;
            if (php7_count($this->listViewEntries) > 0) {
                $parent_related_records = true;
            }
        } else {
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }

        // If there are no related records with parent module then, we should show all the records
        if ((!isset($parent_related_records) || !$parent_related_records) && !empty($relatedParentModule) && !empty($relatedParentId)) {
            $relatedParentModule = null;
            $relatedParentId = null;
            $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);

            if (!empty($orderBy)) {
                $listViewModel->set('orderby', $orderBy);
                $listViewModel->set('sortorder', $sortOrder);
            }
            if (!empty($sourceModule)) {
                $listViewModel->set('src_module', $sourceModule);
                $listViewModel->set('src_field', $sourceField);
                $listViewModel->set('src_record', $sourceRecord);
            }
            if ((!empty($searchKey)) && (!empty($searchValue))) {
                $listViewModel->set('search_key', $searchKey);
                $listViewModel->set('search_value', $searchValue);
            }

            if (!empty($searchParams)) {
                $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $searchModuleModel);
                $listViewModel->set('search_params', $transformedSearchParams);
            }
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }
        // End
        if (empty($searchParams)) {
            $searchParams = [];
        }
        //To make smarty to get the details easily accesible
        foreach ($searchParams as $fieldListGroup) {
            foreach ($fieldListGroup as $fieldSearchInfo) {
                $fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
                $fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
                $fieldSearchInfo['comparator'] = $fieldSearchInfo[1];
                $searchParams[$fieldName] = $fieldSearchInfo;
            }
        }

        $noOfEntries = php7_count($this->listViewEntries);

        if (empty($sortOrder)) {
            $sortOrder = "ASC";
        }
        if ($sortOrder == "ASC") {
            $nextSortOrder = "DESC";
            $sortImage = "icon-chevron-down";
            $faSortImage = "fa-sort-desc";
        } else {
            $nextSortOrder = "ASC";
            $sortImage = "icon-chevron-up";
            $faSortImage = "fa-sort-asc";
        }

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RELATED_MODULE', $moduleName);
        $viewer->assign('MODULE_NAME', $moduleName);

        $viewer->assign('SOURCE_MODULE', $sourceModule);
        $viewer->assign('SOURCE_FIELD', $sourceField);
        $viewer->assign('SOURCE_RECORD', $sourceRecord);
        $viewer->assign('RELATED_PARENT_MODULE', $relatedParentModule);
        $viewer->assign('RELATED_PARENT_ID', $relatedParentId);

        $viewer->assign('SEARCH_KEY', $searchKey);
        $viewer->assign('SEARCH_VALUE', $searchValue);

        $viewer->assign('RELATION_ID', $relationId);
        $viewer->assign('ORDER_BY', $orderBy);
        $viewer->assign('SORT_ORDER', $sortOrder);
        $viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
        $viewer->assign('SORT_IMAGE', $sortImage);
        $viewer->assign('FASORT_IMAGE', $faSortImage);
        $viewer->assign('GETURL', $getUrl);
        $viewer->assign('CURRENCY_ID', $currencyId);

        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('PAGE_NUMBER', $pageNumber);

        $viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
        $viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
        $viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
        $viewer->assign('SEARCH_DETAILS', $searchParams);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('VIEW', $request->get('view'));

        if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
            if (!$this->listViewCount) {
                $this->listViewCount = $listViewModel->getListViewCount();
            }
            $totalCount = $this->listViewCount;
            $pageLimit = $pagingModel->getPageLimit();
            $pageCount = ceil((int)$totalCount / (int)$pageLimit);

            if ($pageCount == 0) {
                $pageCount = 1;
            }
            $viewer->assign('PAGE_COUNT', $pageCount);
            $viewer->assign('LISTVIEW_COUNT', $totalCount);
            $viewer->assign('LISTVIEW_ENTRIES_COUNT', $totalCount);
        }

        $viewer->assign('MULTI_SELECT', $multiSelectMode);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
    }

    /**
     * Function to get listView count
     *
     * @param Vtiger_Request $request
     */
    public function getListViewCount(Vtiger_Request $request)
    {
        $moduleName = $this->getModule($request);
        $sourceModule = $request->get('src_module');
        $sourceField = $request->get('src_field');
        $sourceRecord = $request->get('src_record');
        $orderBy = $request->get('orderby');
        $sortOrder = $request->get('sortorder');
        $currencyId = $request->get('currency_id');

        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $searchParams = $request->get('search_params');

        $relatedParentModule = $request->get('related_parent_module');
        $relatedParentId = $request->get('related_parent_id');

        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
            $listViewModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, $label);
        } else {
            $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);
        }

        if (!empty($sourceModule)) {
            $listViewModel->set('src_module', $sourceModule);
            $listViewModel->set('src_field', $sourceField);
            $listViewModel->set('src_record', $sourceRecord);
            $listViewModel->set('currency_id', $currencyId);
        }

        if (!empty($orderBy)) {
            $listViewModel->set('orderby', $orderBy);
            $listViewModel->set('sortorder', $sortOrder);
        }
        if ((!empty($searchKey)) && (!empty($searchValue))) {
            $listViewModel->set('search_key', $searchKey);
            $listViewModel->set('search_value', $searchValue);
        }

        if (!empty($searchParams)) {
            $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $listViewModel->getModule());
            $listViewModel->set('search_params', $transformedSearchParams);
        }
        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $count = $listViewModel->getRelatedEntriesCount();
        } else {
            $count = $listViewModel->getListViewCount();
        }

        return $count;
    }

    /**
     * Function to get the page count for list
     * @return total number of pages
     */
    public function getPageCount(Vtiger_Request $request)
    {
        $listViewCount = $this->getListViewCount($request);
        $pagingModel = new Vtiger_Paging_Model();
        $pageLimit = $pagingModel->getPageLimit();
        $pageCount = ceil((int)$listViewCount / (int)$pageLimit);

        if ($pageCount == 0) {
            $pageCount = 1;
        }
        $result = [];
        $result['page'] = $pageCount;
        $result['numberOfRecords'] = $listViewCount;
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel)
    {
        return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
    }
}
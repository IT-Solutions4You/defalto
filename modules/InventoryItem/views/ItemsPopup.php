<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 */

class InventoryItem_ItemsPopup_View extends Vtiger_Popup_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'DetailView'];
        $request->set('custom_module', $request->get('item_module'));

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    public function getModule(Vtiger_request $request)
    {
        return $request->get('module', 'Products');
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyLogo = $companyDetails->getLogo();

        $this->initializeListViewContents($request, $viewer);

        $viewer->assign('COMPANY_LOGO', $companyLogo);
        $moduleName = $request->get('module');
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->view('ItemsPopup.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
    {
        $moduleName = $this->getModule($request);
        $pageNumber = $request->get('page');
        $orderBy = $request->get('orderby');
        $sortOrder = $request->get('sortorder');
        $sourceModule = $request->get('src_module');
        $sourceField = $request->get('src_field');
        $sourceRecord = $request->get('src_record');
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $currencyId = $request->get('currency_id');
        $searchParams = $request->get('search_params');
        $getUrl = $request->get('get_url');
        $multiSelectMode = $request->get('multi_select');
        $itemModule = $request->get('item_module');

        if (empty($multiSelectMode)) {
            $multiSelectMode = false;
        }

        if (empty ($pageNumber)) {
            $pageNumber = '1';
        }

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);

        $moduleModel = Vtiger_Module_Model::getInstance($itemModule);
        $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($itemModule);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

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
            $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $listViewModel->getModule());
            $listViewModel->set('search_params', $transformedSearchParams);
        }

        if (!$this->listViewHeaders) {
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
        }

        if (!$this->listViewEntries && $moduleModel->isActive()) {
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        } else {
            $this->listViewEntries = [];
            $viewer->assign('IS_MODULE_DISABLED', true);
        }

        foreach ($this->listViewEntries as $listViewEntry) {
            $productId = $listViewEntry->getId();
            $subProducts = $listViewModel->getSubProducts($productId);

            if ($subProducts) {
                $listViewEntry->set('subProducts', $subProducts);
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

        $fieldList = $moduleModel->getFields();
        $fieldsInfo = [];

        foreach ($fieldList as $name => $model) {
            $fieldsInfo[$name] = $model->getFieldInfo();
        }

        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RELATED_MODULE', $itemModule);
        $viewer->assign('ITEM_MODULE', $moduleName);
        $viewer->assign('SOURCE_MODULE', $sourceModule);
        $viewer->assign('SOURCE_FIELD', $sourceField);
        $viewer->assign('SOURCE_RECORD', $sourceRecord);

        $viewer->assign('SEARCH_KEY', $searchKey);
        $viewer->assign('SEARCH_VALUE', $searchValue);

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
        }

        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('MULTI_SELECT', $multiSelectMode);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('TARGET_MODULE', $moduleName);
        $viewer->assign('GETURL', 'getTaxesURL');
        $viewer->assign('VIEW', 'ItemsPopup');
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $moduleName = $request->getModule();
        $modulePopUpFile = 'modules.' . $moduleName . '.resources.Popup';
        unset($headerScriptInstances[$modulePopUpFile]);

        $jsFileNames = ['modules.InventoryItem.resources.ItemsPopup'];
        $jsFileNames[] = $modulePopUpFile;

        if ($request->get('item_module') === 'Products') {
            $jsFileNames[] = 'modules.Products.resources.ProductRelatedProductBundles';
        }

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    /**
     * @inheritDoc
     */
    public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel)
    {
        return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
    }

    /**
     * @inheritDoc
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
}
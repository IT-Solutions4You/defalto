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

/**
 * Vtiger ListView Model Class
 */
class Vtiger_ListView_Model extends Vtiger_Base_Model
{
    protected $focus = null;

    /**
     * Function to get the Module Model
     * @return Vtiger_Module_Model instance
     */
    public function getModule()
    {
        return $this->get('module');
    }

    /**
     * Function to get the Quick Links for the List view of the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
        $moduleLinks = $this->getModule()->getSideBarLinks($linkParams);

        $listLinkTypes = ['LISTVIEWSIDEBARLINK', 'LISTVIEWSIDEBARWIDGET'];
        $listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

        if ($listLinks['LISTVIEWSIDEBARLINK']) {
            foreach ($listLinks['LISTVIEWSIDEBARLINK'] as $link) {
                $moduleLinks['SIDEBARLINK'][] = $link;
            }
        }

        if ($listLinks['LISTVIEWSIDEBARWIDGET']) {
            foreach ($listLinks['LISTVIEWSIDEBARWIDGET'] as $link) {
                $moduleLinks['SIDEBARWIDGET'][] = $link;
            }
        }

        return $moduleLinks;
    }

    /**
     * Function to get the list of listview links for the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
     */
    public function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleModel = $this->getModule();

        $linkTypes = ['LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING'];
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        $basicLinks = $this->getBasicLinks();

        foreach ($basicLinks as $basicLink) {
            $links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
        }

        $advancedLinks = $this->getAdvancedLinks();

        foreach ($advancedLinks as $advancedLink) {
            $links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
        }

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks = $this->getSettingLinks();
            foreach ($settingsLinks as $settingsLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
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
    public function getListViewMassActions($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();

        $linkTypes = ['LISTVIEWMASSACTION'];
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        $massActionLinks = [];
        if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
            $massActionLinks[] = [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_EDIT',
                'linkurl'   => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showMassEditForm");',
                'linkicon'  => ''
            ];
        }
        if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
            $massActionLinks[] = [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DELETE',
                'linkurl'   => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
                'linkicon'  => ''
            ];
        }

        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
        if ($moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('CreateView')) {
            $massActionLinks[] = [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_ADD_COMMENT',
                'linkurl'   => 'index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showAddCommentForm',
                'linkicon'  => ''
            ];
        }

        $duplicatePermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'DuplicatesHandling');
        $editPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');

        if ($duplicatePermission && $editPermission) {
            $massActionLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_MERGE_SELECTED_RECORDS',
                'linkurl' => 'Javascript:Vtiger_List_Js.triggerMergeRecord()',
                'linkicon' => 'fa-solid fa-code-merge',
            ];
        }

        if($moduleModel->isStarredEnabled()) {
            $massActionLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_FOLLOW',
                'linkurl' => 'Javascript:Vtiger_List_Js.triggerAddStar()',
                'linkicon' => 'bi bi-bookmark-fill',
            ];
            $massActionLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_UNFOLLOW',
                'linkurl' => 'Javascript:Vtiger_List_Js.triggerRemoveStar()',
                'linkicon' => 'bi bi-bookmark',
            ];
        }

        $massActionLinks[] = [
            'linktype' => 'LISTVIEW',
            'linklabel' => 'LBL_ADD_TAG',
            'linkurl' => 'Javascript:Vtiger_List_Js.triggerAddTag()',
            'linkicon' => 'bi bi-tag-fill',
        ];

        if ($linkParams['TAG']) {
            $massActionLinks[] = [
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_REMOVE_TAG',
                'linkurl' => sprintf('Javascript:Vtiger_List_Js.triggerRemoveTag(%s)', $linkParams['TAG']),
                'linkicon' => 'bi bi-tag',
            ];
        }

        foreach ($massActionLinks as $massActionLink) {
            $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }

        return $links;
    }

    /**
     * Function to get the list view header
     * @return <Array> - List of Vtiger_Field_Model instances
     */
    public function getListViewHeaders()
    {
        $listViewContoller = $this->get('listview_controller');
        $module = $this->getModule();
        $headerFieldModels = [];
        $headerFields = $listViewContoller->getListViewHeaderFields();
        foreach ($headerFields as $fieldName => $webserviceField) {
            if ($webserviceField && !in_array($webserviceField->getPresence(), [0, 2])) {
                continue;
            }
            if ($webserviceField && isset($webserviceField->parentReferenceField) && !in_array($webserviceField->parentReferenceField->getPresence(), [0, 2])) {
                continue;
            }
            if ($webserviceField->getDisplayType() == '6') {
                continue;
            }
            // check if the field is reference field
            preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
            if (php7_count($matches) > 0) {
                [$full, $referenceParentField, $referenceModule, $referenceFieldName] = $matches;
                $referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
                $referenceFieldModel = Vtiger_Field_Model::getInstance($referenceFieldName, $referenceModuleModel);
                $referenceFieldModel->set('webserviceField', $webserviceField);
                // added tp use in list view to see the title, for reference field rawdata key is different than the actual field
                // eg: in rawdata its account_idcf_2342 (raw column name used in querygenerator), actual field name (account_id ;(Accounts) cf_2342)
                // When generating the title we use rawdata and from field model we have no way to find querygenrator raw column name.

                $referenceFieldModel->set('listViewRawFieldName', $referenceParentField . $referenceFieldName);

                // this is added for picklist colorizer (picklistColorMap.tpl), for fetching picklist colors we need the actual field name of the picklist
                $referenceFieldModel->set('_name', $referenceFieldName);
                $headerFieldModels[$fieldName] = $referenceFieldModel->set('name', $fieldName); // resetting the fieldname as we use it to fetch the value from that name
                $matches = null;
            } else {
                $fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $module);
                $fieldInstance->set('listViewRawFieldName', $fieldInstance->get('column'));
                $headerFieldModels[$fieldName] = $fieldInstance;
            }
        }

        return $headerFieldModels;
    }

    /**
     * @param object $pagingModel
     *
     * @return int
     */
    public function getViewId(object $pagingModel): int
    {
        $moduleName = $this->getModuleName();
        $viewId = ListViewSession::getCurrentView($moduleName);

        if (empty($viewId)) {
            $viewId = $pagingModel->get('viewid');
        }

        return (int)$viewId;
    }

    public function retrieveOrderBy($viewId = 0)
    {
        $moduleFocus = $this->getFocus();
        $moduleModel = $this->getModule();
        $moduleName = $this->getModuleName();
        $orderBy = $this->getForSql('orderby');
        $sortOrder = $this->getForSql('sortorder');
        $orderByColumns = [];

        if (!empty($orderBy)) {
            $queryGenerator = $this->get('query_generator');
            $fieldModels = $queryGenerator->getModuleFields();
            $orderByFieldModel = $fieldModels[$orderBy];

            if ($orderByFieldModel && ($orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE || $orderByFieldModel->getFieldDataType(
                    ) == Vtiger_Field_Model::OWNER_TYPE)) {
                $queryGenerator->addWhereField($orderBy);
            }
        }

        if (!empty($orderBy) && $orderByFieldModel) {
            if ($orderBy == 'roleid' && $moduleName == 'Users') {
                $orderByColumn = 'vtiger_role.rolename';
            } else {
                $orderByColumn = $queryGenerator->getOrderByColumn($orderBy);
            }

            $orderByColumns[$orderByColumn] = $sortOrder;

            if ($orderBy == 'first_name' && $moduleName == 'Users') {
                $orderByColumns['last_name'] = $sortOrder;
                $orderByColumns['email1'] = $sortOrder;
            }
        } elseif (empty($orderBy) && empty($sortOrder) && $moduleName !== 'Users') {
            $cvModel = CustomView_Record_Model::getInstanceById($viewId);
            $cvOrderBy = false;
            $cvOrderByField = false;

            if ($cvModel) {
                $cvOrderBy = $cvModel->fetchOrderBy();
                $cvOrderByField = $moduleModel->getFieldByColumn($cvOrderBy['orderby']);
            }

            if ($cvOrderBy && $cvOrderByField && $cvOrderByField->isActiveField()) {
                $cvOrderBy['orderby'] = $moduleModel->getOrderBySql($cvOrderBy['orderby']);
                $orderByColumns[$cvOrderBy['orderby']] = $cvOrderBy['sortorder'];
            } elseif (PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
                $orderBy = $moduleFocus->default_order_by;
                $qualifiedOrderBy = $orderBy;
                $orderByField = $moduleModel->getFieldByColumn($orderBy);

                if ($orderByField) {
                    $qualifiedOrderBy = $moduleModel->getOrderBySql($qualifiedOrderBy);
                    $sortOrder = $moduleFocus->default_sort_order;
                    $orderByColumns[$qualifiedOrderBy] = $sortOrder;
                }
            }

            if (empty($orderByColumns)) {
                $orderByColumns['vtiger_crmentity.createdtime'] = 'DESC';
            }
        }

        $this->getQueryGenerator()->setOrderByColumns($orderByColumns);
    }

    public function getFocus()
    {
        if (!empty($this->focus)) {
            $this->focus = CRMEntity::getInstance($this->getModuleName());
        }

        return $this->focus;
    }

    public function getModuleName()
    {
        return $this->getModule()->getName();
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
        $moduleModel = $this->getModule();
        $moduleName = $this->getModuleName();
        $moduleFocus = $this->getFocus();
        $viewId = $this->getViewId($pagingModel);
        /** @var EnhancedQueryGenerator $queryGenerator */
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
        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');

        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(['search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator]);
        }

        $this->retrieveOrderBy($viewId);

        $listQuery = $this->getQuery();

        $sourceModule = $this->get('src_module');
        if (!empty($sourceModule)) {
            if (method_exists($moduleModel, 'getQueryByModuleField')) {
                $overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery, $this->get('relationId'));
                if (!empty($overrideQuery)) {
                    $listQuery = $overrideQuery;
                }
            }
        }

        $listQuery .= $this->getQueryGenerator()->getOrderByClause();

        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();

        $_SESSION['lvs'][$moduleName][$viewId]['start'] = $pagingModel->get('page');

        ListViewSession::setSessionQuery($moduleName, $listQuery, $viewId);

        $listQuery .= ' LIMIT ' . $startIndex . ', ' . ($pageLimit + 1);

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

        // for Documents folders we should filter with folder id as well
        $folderKey = $this->get('folder_id');
        $folderValue = $this->get('folder_value');
        if (!empty($folderValue)) {
            $queryGenerator->addCondition($folderKey, $folderValue, 'e');
        }

        $glue = "";
        if (php7_count($queryGenerator->getWhereFields()) > 0 && (php7_count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);

        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');
        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(['search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator]);
        }
        $moduleName = $this->getModule()->get('name');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $listQuery = $this->getQuery();
        $sourceModule = $this->get('src_module');
        if (!empty($sourceModule)) {
            $moduleModel = $this->getModule();
            if (method_exists($moduleModel, 'getQueryByModuleField')) {
                $overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
                if (!empty($overrideQuery)) {
                    $listQuery = $overrideQuery;
                }
            }
        }
        $position = stripos($listQuery, ' from ');
        if ($position) {
            $split = preg_split('/ from /i', $listQuery);
            $splitCount = php7_count($split);
            // If records is related to two records then we'll get duplicates. Then count will be wrong
            $meta = $queryGenerator->getMeta($this->getModule()->getName());
            $columnIndex = $meta->getObectIndexColumn();
            $baseTable = $meta->getEntityBaseTable();
            $listQuery = "SELECT count(distinct($baseTable.$columnIndex)) AS count ";
            for ($i = 1; $i < $splitCount; $i++) {
                $listQuery = $listQuery . ' FROM ' . $split[$i];
            }
        }

        $listResult = $db->pquery($listQuery, []);

        return $db->query_result($listResult, 0, 'count');
    }

    public function getQuery()
    {
        return $this->getQueryGenerator()->getQuery();
    }

    /**
     * @return EnhancedQueryGenerator
     */
    public function getQueryGenerator(): EnhancedQueryGenerator
    {
        return $this->get('query_generator');
    }

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
        $db = PearDatabase::getInstance();
        $currentUser = vglobal('current_user');

        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
        $instance = new $modelClassName();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $queryGenerator = new EnhancedQueryGenerator($moduleModel->get('name'), $currentUser);
        $customView = new CustomView();
        if (!empty($viewId) && $viewId != "0") {
            $queryGenerator->initForCustomViewById($viewId);

            //Used to set the viewid into the session which will be used to load the same filter when you refresh the page
            $viewId = $customView->getViewId($moduleName);
        } else {
            $viewId = $customView->getViewId($moduleName);
            if (!empty($viewId) && $viewId != 0) {
                $queryGenerator->initForDefaultCustomView();
            } else {
                $entityInstance = CRMEntity::getInstance($moduleName);
                $listFields = $entityInstance->list_fields_name;
                $listFields[] = 'id';
                $queryGenerator->setFields($listFields);
            }
        }

        $fieldsList = $queryGenerator->getFields();
        if (!empty($listHeaders) && is_array($listHeaders) && php7_count($listHeaders) > 0) {
            $fieldsList = $listHeaders;
            $fieldsList[] = 'id';
        }
        //to show starred field in list view
        $fieldsList[] = 'starred';
        $queryGenerator->setFields($fieldsList);

        $moduleSpecificControllerPath = 'modules/' . $moduleName . '/controllers/ListViewController.php';
        if (file_exists($moduleSpecificControllerPath)) {
            include_once $moduleSpecificControllerPath;
            $moduleSpecificControllerClassName = $moduleName . 'ListViewController';
            $controller = new $moduleSpecificControllerClassName($db, $currentUser, $queryGenerator);
        } else {
            $controller = new ListViewController($db, $currentUser, $queryGenerator);
        }

        return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
    }

    /**
     * Function to create clean instance
     *
     * @param type $moduleName -- module for which list view model has to be created
     *
     * @return type -- List view model
     */
    public static function getCleanInstance($moduleName)
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
        $instance = new $modelClassName();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        return $instance->set('module', $moduleModel);
    }

    /**
     * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
     *
     * @param <String> $value  - Module Name
     * @param <Number> $viewId - Custom View Id
     *
     * @return Vtiger_ListView_Model instance
     */
    public static function getInstanceForPopup($value)
    {
        $db = PearDatabase::getInstance();
        $currentUser = vglobal('current_user');

        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
        $instance = new $modelClassName();
        $moduleModel = Vtiger_Module_Model::getInstance($value);

        $queryGenerator = new EnhancedQueryGenerator($moduleModel->get('name'), $currentUser);

        $listFields = $moduleModel->getPopupViewFieldsList();

        $listFields[] = 'id';
        $queryGenerator->setFields($listFields);

        $controller = new ListViewController($db, $currentUser, $queryGenerator);

        return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
    }

    /*
     * Function to give advance links of a module
     *	@RETURN array of advanced links
     */
    public function getAdvancedLinks()
    {
        $moduleModel = $this->getModule();
        $advancedLinks = [];

        $duplicatePermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'DuplicatesHandling');
        $editPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');

        if ($duplicatePermission && $editPermission) {
            $advancedLinks[] = [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'LBL_FIND_DUPLICATES',
                'linkurl'   => 'Javascript:Vtiger_List_Js.showDuplicateSearchForm("index.php?module=' . $moduleModel->getName() . '&view=MassActionAjax&mode=showDuplicatesSearchForm")',
                'linkicon'  => 'fa-solid fa-clone'
            ];
        }

        $exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');

        if ($exportPermission) {
            $advancedLinks[] = [
                'linktype'  => 'LISTVIEW',
                'linklabel' => 'LBL_EXPORT',
                'linkurl'   => 'javascript:Vtiger_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
                'linkicon'  => 'fa-solid fa-file-export'
            ];
        }

        return $advancedLinks;
    }

    /*
     * Function to get Setting links
     * @return array of setting links
     */
    public function getSettingLinks()
    {
        return $this->getModule()->getSettingLinks();
    }

    /*
     * Function to get Basic links
     * @return array of Basic links
     */
    public function getBasicLinks()
    {
        $basicLinks = [];
        $moduleModel = $this->getModule();
        $createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView');
        if ($createPermission) {
            $basicLinks[] = [
                'linktype'    => 'LISTVIEWBASIC',
                'linklabel'   => 'LBL_ADD_RECORD',
                'linkurl'     => $moduleModel->getCreateRecordUrl(),
                'linkicon'    => '',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ];
        }

        return $basicLinks;
    }

    public function extendPopupFields($fieldsList)
    {
        $moduleModel = $this->get('module');
        $queryGenerator = $this->get('query_generator');

        $listFields = $moduleModel->getPopupViewFieldsList();

        $listFields[] = 'id';
        $listFields = array_merge($listFields, $fieldsList);
        $queryGenerator->setFields($listFields);
        $this->get('query_generator', $queryGenerator);
    }

    public static function getSortParamsSession($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public static function setSortParamsSession($key, $params)
    {
        $_SESSION[$key] = $params;
    }

    public static function deleteParamsSession($key, $params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }
        foreach ($params as $param) {
            $_SESSION[$key][$param] = '';
        }
    }

    public function isImportEnabled()
    {
        $linkParams = ['MODULE' => $this->getModule()->getName(), 'ACTION' => 'LIST'];
        $listViewLinks = $this->getListViewLinks($linkParams);
        $listViewActions = $listViewLinks['LISTVIEW'];
        if (is_array($listViewActions)) {
            foreach ($listViewActions as $linkAction) {
                if ($linkAction->getLabel() == 'LBL_IMPORT') {
                    return true;
                }
            }
        }

        return false;
    }
}
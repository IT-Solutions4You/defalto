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

class Vtiger_Detail_View extends Vtiger_Index_View
{
    protected $record = false;
    protected $isAjaxEnabled = null;

    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showDetailViewByMode');
        $this->exposeMethod('showModuleDetailView');
        $this->exposeMethod('showModuleSummaryView');
        $this->exposeMethod('showModuleBasicView');
        $this->exposeMethod('showRecentActivities');
        $this->exposeMethod('showRecentComments');
        $this->exposeMethod('showRelatedList');
        $this->exposeMethod('showChildComments');
        $this->exposeMethod('getEvents');
        $this->exposeMethod('showRelatedRecords');
        $this->exposeMethod('DetailSharingRecord');
        $this->exposeMethod('showTagsModalWindow');
        $this->exposeMethod('showAllTagsModalWindow');
    }

    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $mode = $request->getMode();
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];
        if (!empty($mode)) {
            switch ($mode) {
                case 'showModuleDetailView':
                case 'showModuleSummaryView':
                case 'showModuleBasicView':
                    $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];
                    break;
                case 'showRecentComments':
                case 'showChildComments':
                    $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'DetailView'];
                    $request->set('custom_module', 'ModComments');
                    break;
                case 'showRelatedList':
                case 'showRelatedRecords':
                    $permissions[] = ['module_parameter' => 'relatedModule', 'action' => 'DetailView'];
                    break;
                default:
                    break;
            }
        }

        return $permissions;
    }

    function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $nonEntityModules = ['Users', 'Portal', 'Rss'];
        if ($recordId && !in_array($moduleName, $nonEntityModules)) {
            $recordEntityName = getSalesEntityType($recordId);
            if ($recordEntityName !== $moduleName) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }
        }

        return true;
    }

    function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);

        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $recordModel = $this->record->getRecord();
        $recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
        $summaryInfo = [];
        // Take first block information as summary information
        $stucturedValues = $recordStrucure->getStructure();
        foreach ($stucturedValues as $blockLabel => $fieldList) {
            $summaryInfo[$blockLabel] = $fieldList;
            break;
        }

        $detailViewLinkParams = ['MODULE' => $moduleName, 'RECORD' => $recordId];

        $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
        $navigationInfo = ListViewSession::getListViewNavigation($recordId);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('NAVIGATION', $navigationInfo);

        //Intially make the prev and next records as null
        $prevRecordId = null;
        $nextRecordId = null;
        $found = false;
        if ($navigationInfo) {
            foreach ($navigationInfo as $page => $pageInfo) {
                foreach ($pageInfo as $index => $record) {
                    //If record found then next record in the interation
                    //will be next record
                    if ($found) {
                        $nextRecordId = $record;
                        break;
                    }
                    if ($record == $recordId) {
                        $found = true;
                    }
                    //If record not found then we are assiging previousRecordId
                    //assuming next record will get matched
                    if (!$found) {
                        $prevRecordId = $record;
                    }
                }
                //if record is found and next record is not calculated we need to perform iteration
                if ($found && !empty($nextRecordId)) {
                    break;
                }
            }
        }

        $viewer->assign('NO_PAGINATION', true);

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        if (!empty($prevRecordId)) {
            $viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
        }
        if (!empty($nextRecordId)) {
            $viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
        }

        $viewer->assign('MODULE_MODEL', $this->record->getModule());
        $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

        $viewer->assign('IS_EDITABLE', $this->record->getRecord()->isEditable($moduleName));
        $viewer->assign('IS_DELETABLE', $this->record->getRecord()->isDeletable($moduleName));

        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
        $linkModels = $this->record->getSideBarLinks($linkParams);
        $viewer->assign('QUICK_LINKS', $linkModels);
        $viewer->assign('MODULE_NAME', $moduleName);

        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $viewer->assign('DEFAULT_RECORD_VIEW', $currentUserModel->get('default_record_view'));

        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));

        $tagsList = Vtiger_Tag_Model::getAllAccessible($currentUserModel->getId(), $moduleName, $recordId);
        $allUserTags = Vtiger_Tag_Model::getAllUserTags($currentUserModel->getId());
        $viewer->assign('TAGS_LIST', $tagsList);
        $viewer->assign('ALL_USER_TAGS', $allUserTags);

        $relationId = $request->get('relationId');
        $viewer->assign('SELECTED_TAB_LABEL', $request->get('tab_label', $currentUserModel->getDefaultTabLabel()));
        $viewer->assign('SELECTED_RELATION_ID', $relationId);

        //Vtiger7 - TO show custom view name in Module Header
        $viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));

        $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    function preProcessTplName(Vtiger_Request $request)
    {
        return 'DetailViewPreProcess.tpl';
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);

            return;
        }

        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleModel = Vtiger_Module_Model::getInstance($request->getModule());

        if ($currentUserModel->get('default_record_view') === 'Summary' && $moduleModel->isSummaryViewSupported()) {
            echo $this->showModuleBasicView($request);
        } else {
            echo $this->showModuleDetailView($request);
        }
    }

    public function postProcess(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $detailViewLinkParams = ['MODULE' => $moduleName, 'RECORD' => $recordId];
        $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

        $relationId = $request->get('relationId');
        $viewer = $this->getViewer($request);

        $viewer->assign('SELECTED_TAB_LABEL', $request->get('tab_label'));
        $viewer->assign('SELECTED_RELATION_ID', $relationId);
        $viewer->assign('MODULE_MODEL', $this->record->getModule());
        $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

        $viewer->view('DetailViewPostProcess.tpl', $moduleName);

        parent::postProcess($request);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $jsFileNames = [
            'modules.Vtiger.resources.Detail',
            "modules.$moduleName.resources.Detail",
            'modules.Vtiger.resources.RelatedList',
            "modules.$moduleName.resources.RelatedList",
            'libraries.jquery.jquery_windowmsg',
            "libraries.jquery.ckeditor.ckeditor",
            "libraries.jquery.ckeditor.adapters.jquery",
            "modules.Vtiger.resources.CkEditor",
            "~/libraries/jquery/twitter-text-js/twitter-text.js",
            "libraries.jquery.multiplefileupload.jquery_MultiFile",
            '~/libraries/jquery.bxslider/jquery.bxslider.min.js',
            '~layouts/' . $layout . '/lib/jquery/Lightweight-jQuery-In-page-Filtering-Plugin-instaFilta/instafilta.js',
            'modules.Vtiger.resources.Tag',
            'modules.Google.resources.Map',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    function showDetailViewByMode(Vtiger_Request $request)
    {
        $requestMode = $request->get('requestMode');
        if ($requestMode == 'full') {
            return $this->showModuleDetailView($request);
        }

        return $this->showModuleBasicView($request);
    }

    /**
     * Function shows the entire detail for the record
     *
     * @param Vtiger_Request $request
     *
     * @return <type>
     */
    function showModuleDetailView(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $recordModel = $this->record->getRecord();
        $recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
        $structuredValues = $recordStrucure->getStructure();

        $moduleModel = $recordModel->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('RECORD_STRUCTURE', $structuredValues);
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('DAY_STARTS', '');

        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showModuleDetailView', $moduleName, $viewer, $request);

        if ($request->get('displayMode') == 'overlay') {
            $viewer->assign('MODULE_MODEL', $moduleModel);
            $this->setModuleInfo($request, $moduleModel);
            $viewer->assign('SCRIPTS', $this->getOverlayHeaderScripts($request));

            $detailViewLinkParams = ['MODULE' => $moduleName, 'RECORD' => $recordId];
            $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
            $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

            return $viewer->view('OverlayDetailView.tpl', $moduleName, true);
        } else {
            return $viewer->view('DetailViewFullContents.tpl', $moduleName, true);
        }
    }

    public function getOverlayHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = [
            "modules.$moduleName.resources.Detail",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return $jsScriptInstances;
    }

    public function getQuickPreviewHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $jsFileNames = [
            "modules.Vtiger.resources.Detail",
            "modules.$moduleName.resources.Detail",
            "modules.Vtiger.resources.RelatedList",
            "modules.$moduleName.resources.RelatedList",
        ];

        return $this->checkAndConvertJsScripts($jsFileNames);
    }

    function showModuleSummaryView($request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $recordModel = $this->record->getRecord();
        $recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);

        $moduleModel = $recordModel->getModule();
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
        $viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStructure->getStructure());
        $viewer->assign('RELATED_ACTIVITIES', $this->getEvents($request));

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $pagingModel = new Vtiger_Paging_Model();
        $viewer->assign('PAGING_MODEL', $pagingModel);

        $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
        $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showModuleSummaryView', $request->getModule(), $viewer, $request);

        return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
    }

    /**
     * Function shows basic detail for the record
     *
     * @param <type> $request
     */
    function showModuleBasicView(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        if (!$this->record) {
            $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
        }
        $recordModel = $this->record->getRecord();

        $detailViewLinkParams = ['MODULE' => $moduleName, 'RECORD' => $recordId];
        $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE_SUMMARY', $this->showModuleSummaryView($request));

        $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
        $viewer->assign('MODULE_NAME', $moduleName);

        $recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);

        $moduleModel = $recordModel->getModule();
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('RECORD_STRUCTURE', $recordStructure->getStructure());
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());

        return $viewer->view('DetailViewSummaryContents.tpl', $moduleName, true);
    }

    /**
     * Added to support Engagements view in Vtiger7
     *
     * @param Vtiger_Request $request
     */
    function _showRecentActivities(Vtiger_Request $request)
    {
        $parentRecordId = $request->get('record');
        $pageNumber = $request->get('page');
        $limit = $request->get('limit');
        $moduleName = $request->getModule();

        if (empty($pageNumber)) {
            $pageNumber = 1;
        }

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);
        if (!empty($limit)) {
            $pagingModel->set('limit', $limit);
        }

        $recentActivities = ModTracker_Record_Model::getUpdates($parentRecordId, $pagingModel, $moduleName);
        $pagingModel->calculatePageRange($recentActivities);

        if ($pagingModel->getCurrentPage() == ModTracker_Record_Model::getTotalRecordCount($parentRecordId) / $pagingModel->getPageLimit()) {
            $pagingModel->set('nextPageExists', false);
        }
        $recordModel = Vtiger_Record_Model::getInstanceById($parentRecordId);
        $viewer = $this->getViewer($request);
        $viewer->assign('SOURCE', $recordModel->get('source'));
        $recentActivities = ModTracker_Record_Model::getUpdates($parentRecordId, $pagingModel, $moduleName);

        $totalCount = ModTracker_Record_Model::getTotalRecordCount($parentRecordId);
        $pageLimit = $pagingModel->getPageLimit();
        $pageCount = ceil((int)$totalCount / (int)$pageLimit);
        if ($pageCount - $pagingModel->getCurrentPage() == 0) {
            $pagingModel->set('nextPageExists', false);
        } else {
            $pagingModel->set('nextPageExists', true);
        }
        $viewer->assign('RECENT_ACTIVITIES', $recentActivities);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('RECORD_ID', $parentRecordId);
    }

    /**
     * Function returns recent changes made on the record
     *
     * @param Vtiger_Request $request
     */
    function showRecentActivities(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $this->_showRecentActivities($request);

        $viewer = $this->getViewer($request);
        $viewer->assign('IS_AJAX', $request->isAjax());

        echo $viewer->view('RecentActivities.tpl', $moduleName, true);
    }

    /**
     * Function returns latest comments
     *
     * @param Vtiger_Request $request
     *
     * @return <type>
     */
    function showRecentComments(Vtiger_Request $request)
    {
        $parentId = $request->get('record');
        $pageNumber = $request->get('page');
        $limit = $request->get('limit');
        $moduleName = $request->getModule();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (empty($pageNumber)) {
            $pageNumber = 1;
        }

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);

        if (!empty($limit)) {
            $pagingModel->set('limit', $limit);
        }

        if ($request->get('rollup-toggle')) {
            $rollUpSettings = ModComments_Module_Model::storeRollupSettingsForUser($currentUserModel, $request);
        } else {
            $rollUpSettings = ModComments_Module_Model::getRollupSettingsForUser($currentUserModel, $moduleName);
        }

        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);

        if (!empty($rollUpSettings['rollup_status'])) {
            $recentComments = $parentRecordModel->getRollupCommentsForModule(0, 6);
        } else {
            $recentComments = ModComments_Record_Model::getRecentComments($parentId, $pagingModel);
        }

        $pagingModel->calculatePageRange($recentComments);

        if ($pagingModel->get('limit') < php7_count($recentComments)) {
            array_pop($recentComments);
        }

        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
        $fileNameFieldModel = Vtiger_Field::getInstance("filename", $modCommentsModel);
        $fileFieldModel = Vtiger_Field_Model::getInstanceFromFieldObject($fileNameFieldModel);

        $viewer = $this->getViewer($request);
        $viewer->assign('COMMENTS', $recentComments);
        $viewer->assign('CURRENTUSER', $currentUserModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('FIELD_MODEL', $fileFieldModel);
        $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
        $viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
        $viewer->assign('ROLLUP_STATUS', isset($rollUpSettings['rollup_status']) ? $rollUpSettings['rollup_status'] : false);
        $viewer->assign('ROLLUPID', isset($rollUpSettings['rollupid']) ? $rollUpSettings['rollupid'] : 0);
        $viewer->assign('PARENT_RECORD', $parentId);
        $viewer->assign('STARTINDEX', 0);

        if (!empty($parentRecordModel)) {
            $relationModel = Vtiger_Relation_Model::getInstance($parentRecordModel->getModule(), $modCommentsModel);
            $viewer->assign('RELATION_LIST_URL', $relationModel ? $relationModel->getListUrl($parentRecordModel) : '');
        }

        return $viewer->view('RecentComments.tpl', $moduleName, 'true');
    }

    /**
     * Function returns related records
     *
     * @param Vtiger_Request $request
     *
     * @return <type>
     */
    function showRelatedList(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $relatedModuleName = $request->get('relatedModule');
        $targetControllerClass = null;

        if ($relatedModuleName == 'ModComments') {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
            $rollupSettings = ModComments_Module_Model::getRollupSettingsForUser($currentUserModel, $moduleName);
            $request->set('rollup_settings', $rollupSettings);
        }

        // Added to support related list view from the related module, rather than the base module.
        try {
            $targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'In' . $moduleName . 'Relation', $relatedModuleName);
        } catch (Exception $e) {
            try {
                // If any module wants to have same view for all the relation, then invoke this.
                $targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'InRelation', $relatedModuleName);
            } catch (Exception $e) {
                // Default related list
                $targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'RelatedList', $moduleName);
            }
        }
        if ($targetControllerClass) {
            $targetController = new $targetControllerClass();
            if ($targetController->checkPermission($request)) {
                return $targetController->process($request);
            }
        }
    }

    /**
     * Function sends the child comments for a comment
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function showChildComments(Vtiger_Request $request): void
    {
        $parentCommentId = $request->get('commentid');
        $parentCommentModel = ModComments_Record_Model::getInstanceById($parentCommentId);
        $parentRecordModel = $parentCommentModel->getParentRecordModel();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
        $moduleName = $parentRecordModel->getModuleName();

        $viewer = $this->getViewer($request);
        $viewer->assign('CURRENTUSER', $currentUserModel);
        $viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('COMMENT', $parentCommentModel);
        $viewer->assign('SHOW_REPLIES', true);
        $viewer->assign('IS_CREATABLE', $modCommentsModel->isPermitted('CreateView'));
        $viewer->assign('IS_EDITABLE', $modCommentsModel->isPermitted('EditView'));
        $viewer->assign('PARENT_RECORD', $request->get('record'));
        $viewer->assign('ROLLUP_STATUS', 1);

        $viewer->view('comments/Comment.tpl', $moduleName);
    }

    /**
     * Function to get Ajax is enabled or not
     *
     * @param Vtiger_Record_Model record model
     *
     * @return <boolean> true/false
     */
    function isAjaxEnabled($recordModel)
    {
        if (is_null($this->isAjaxEnabled)) {
            $this->isAjaxEnabled = $recordModel->isEditable();
        }

        return $this->isAjaxEnabled;
    }

    /**
     * Function returns related records based on related moduleName
     *
     * @param Vtiger_Request $request
     *
     * @return <type>
     */
    function showRelatedRecords(Vtiger_Request $request)
    {
        $parentId = $request->get('record');
        $pageNumber = $request->get('page');
        $limit = $request->get('limit');
        $relatedModuleName = $request->get('relatedModule');
        $moduleName = $request->getModule();

        if (empty($pageNumber)) {
            $pageNumber = 1;
        }

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);
        if (!empty($limit)) {
            $pagingModel->set('limit', $limit);
        }

        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
        $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
        $models = $relationListView->getEntries($pagingModel);
        $header = $relationListView->getHeaders();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RELATED_RECORDS', $models);
        $viewer->assign('RELATED_HEADERS', $header);
        $viewer->assign('RELATED_MODULE', $relatedModuleName);
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('RELATION_LIST_URL', $relationListView->getRelationModel()->getListUrl($parentRecordModel));

        return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
    }

    /**
     * @param Vtiger_Request $request
     */
    public function DetailSharingRecord(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        $recordModel = Core_SharingRecord_Model::getInstance($recordId);

        $viewer = $this->getViewer($request);

        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->view('DetailSharingRecord.tpl', $moduleName);
    }

    public function getEvents(Vtiger_Request $request)
    {
        $activitiesModuleName = 'Appointments';
        $activitiesModule = Vtiger_Module_Model::getInstance($activitiesModuleName);
        $parentFieldName = 'parent_id';
        $parentField = $activitiesModule->getField($parentFieldName);
        $parentModules = array_merge($parentField->getReferenceList(), ['Accounts', 'Contacts']);
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if ($currentUserPrivilegesModel->hasModulePermission($activitiesModule->getId()) && in_array($request->getModule(), $parentModules)) {
            $moduleName = $request->getModule();
            $recordId = $request->get('record');
            $pageNumber = $request->get('page', 1);
            $pageLimit = $request->get('limit', 5);
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $pageNumber);
            $pagingModel->set('limit', $pageLimit);

            if (!$this->record) {
                $this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
            }

            $currentUser = Users_Record_Model::getCurrentUserModel();
            $skipCalendarStatus = ['Cancelled'];

            if (!$currentUser->isEmpty('hidecompletedevents')) {
                $skipCalendarStatus[] = 'Completed';
            }

            $recordModel = $this->record->getRecord();
            /** @var Vtiger_RelationListView_Model $relationListView */
            $relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $activitiesModuleName, '');
            $relationListView->set('whereCondition', [
                'calendar_status' => ['its4you_calendar.calendar_status', 'n', $skipCalendarStatus, 'picklist'],
            ]);
            $relationListView->set('orderby', 'datetime_start');
            $relationListView->set('sortorder', 'ASC');

            if (!$relationListView->getRelationModel()) {
                return '';
            }

            $relatedActivities = $relationListView->getEntries($pagingModel) ?? [];
            $parentFieldNames = [
                'Accounts' => 'account_id',
                'Contacts' => 'contact_id',
            ];

            $viewer = $this->getViewer($request);
            $viewer->assign('RECORD', $recordModel);
            $viewer->assign('MODULE_NAME', $moduleName);
            $viewer->assign('PAGING_MODEL', $pagingModel);
            $viewer->assign('PAGE_NUMBER', $pageNumber);
            $viewer->assign('ACTIVITIES', $relatedActivities);
            $viewer->assign('ACTIVITIES_MODULE_NAME', $activitiesModuleName);
            $viewer->assign('ACTIVITIES_MODULE', $activitiesModule);
            $viewer->assign('RELATION_LIST_URL', $relationListView->getRelationModel()->getListUrl($recordModel));
            $viewer->assign('PARENT_FIELD_NAME', $parentFieldNames[$moduleName] ?? $parentFieldName);

            return $viewer->view('RelatedEvents.tpl', $moduleName, true);
        }

        return '';
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function showTagsModalWindow(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $tagsList = Vtiger_Tag_Model::getAllAccessible($currentUserModel->getId(), $moduleName, $recordId);
        $allUserTags = Vtiger_Tag_Model::getAllUserTags($currentUserModel->getId());

        $viewer = $this->getViewer($request);
        $viewer->assign('TAGS_LIST', $tagsList);
        $viewer->assign('ALL_USER_TAGS', $allUserTags);
        $viewer->assign('DELETE_OLD_TAGS', $request->get('deleteOldTags'));
        $viewer->view('AddTagUI.tpl', $request->getModule());
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function showAllTagsModalWindow(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $tagsList = Vtiger_Tag_Model::getAllAccessible($currentUserModel->getId(), $moduleName, $recordId);
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

        $viewer = $this->getViewer($request);
        $viewer->assign('TAGS_LIST', $tagsList);
        $viewer->assign('RECORD_NAME', $recordModel ? $recordModel->getName() : '');
        $viewer->assign('VIEW', 'Detail');
        $viewer->view('AllTagUI.tpl', $request->getModule());
    }
}
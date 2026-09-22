<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class CustomView_EditAjax_View extends Vtiger_IndexAjax_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'source_module', 'action' => 'DetailView'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->get('source_module');
        $module = $request->getModule();
        $record = $request->get('record');
        $sourceRecord = $request->get('source_viewname');
        $isListFilterDraft = empty($record) && $request->has('list_search_params');
        $mode = '';
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);

        if (!empty($record)) {
            $customViewModel = CustomView_Record_Model::getInstanceById($record);
            $mode = 'edit';
        } elseif (!empty($sourceRecord)) {
            $customViewModel = clone CustomView_Record_Model::getInstanceById($sourceRecord);
        } else {
            $customViewModel = new CustomView_Record_Model();
            $customViewModel->setModule($moduleName);
        }

        if ($isListFilterDraft) {
            if (!$customViewModel->isListFilterSource($moduleName)) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
            }

            $advanceCriteria = $customViewModel->getCriteriaWithListSearch($request->get('list_search_params'));
            // A new private copy must not inherit the source view's name or sharing.
            $customViewModel->set('viewname', '')
                ->set('status', CustomView_Record_Model::CV_STATUS_PRIVATE);
        } else {
            $advanceCriteria = $customViewModel->transformToNewAdvancedFilter();
        }

        $dateFilters = Vtiger_Field_Model::getDateFilterTypes();

        foreach ($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'], $module);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }

        $advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $recordStructure = $recordStructureInstance->getStructure();
        $sortRecordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
        $sortRecordStructure = $sortRecordStructureInstance->getStructure();

        $orderBy = $customViewModel->fetchOrderBy();
        $orderByField = '';
        $sortOrder = '';

        if (!empty($orderBy)) {
            $orderByField = $orderBy['orderby'];
            $sortOrder = $orderBy['sortorder'];
        }

        $allCustomViews = CustomView_Record_Model::getAllByGroup($moduleName);
        $allViewNames = [];

        foreach ($allCustomViews as $views) {
            foreach ($views as $view) {
                if ($currentUserModel->getId() == $view->get('userid')) {
                    $allViewNames[$view->getId()] = strtolower($view->getDisplayName());
                }
            }
        }

        $customViewSharedMembers = $isListFilterDraft ? [] : $customViewModel->getMembers();
        $listShared = $customViewModel->get('status') == CustomView_Record_Model::CV_STATUS_PUBLIC;

        foreach ($customViewSharedMembers as $membersList) {
            if (php7_count($membersList) > 0) {
                $listShared = true;
                break;
            }
        }

        $viewer = $this->getViewer($request)
            ->assign('LIST_FILTER_DRAFT', $isListFilterDraft)
            ->assign('MODE', $mode)
            ->assign('ADVANCE_CRITERIA', $advanceCriteria)
            ->assign('CURRENTDATE', date('Y-n-j'))
            ->assign('DATE_FILTERS', $dateFilters)
            ->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions())
            ->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType)
            ->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance)
            ->assign('RECORD_STRUCTURE', $recordStructure)
            ->assign('CUSTOMVIEW_MODEL', $customViewModel)
            ->assign('RECORD_ID', $record)
            ->assign('MODULE', $module)
            ->assign('SOURCE_MODULE', $moduleName)
            ->assign('USER_MODEL', $currentUserModel)
            ->assign('CV_PRIVATE_VALUE', CustomView_Record_Model::CV_STATUS_PRIVATE)
            ->assign('CV_PENDING_VALUE', CustomView_Record_Model::CV_STATUS_PENDING)
            ->assign('CV_PUBLIC_VALUE', CustomView_Record_Model::CV_STATUS_PUBLIC)
            ->assign('MODULE_MODEL', $moduleModel)
            ->assign('SORT_RECORD_STRUCTURE', $sortRecordStructure)
            ->assign('ORDER_BY', $orderByField)
            ->assign('SORT_ORDER', $sortOrder)
            ->assign('CUSTOM_VIEWS_LIST', $allViewNames)
            ->assign('LIST_SHARED', $listShared)
            ->assign('SELECTED_MEMBERS_GROUP', $customViewSharedMembers)
            ->assign('MEMBER_GROUPS', Settings_Groups_Member_Model::getAll());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        echo $viewer->view('EditView.tpl', $module, true);
    }
}

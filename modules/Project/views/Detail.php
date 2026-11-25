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

class Project_Detail_View extends Vtiger_Detail_View
{
	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showChart');
	}

	public function showModuleSummaryView($request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('SUMMARY_INFORMATION', $recordModel->getSummaryInfo());
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);

		return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
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
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$whereCondition = $request->get('whereCondition');
		$moduleName = $request->getModule();
		$relatedModuleInstance = Vtiger_Module_Model::getInstance($relatedModuleName);

		if ($relatedModuleName === 'ProjectTask') {
			$project = Vtiger_Record_Model::getInstanceById($parentId, 'Project');
			$viewer = $this->getViewer($request);
			$viewer->assign('TASK_SUMMARY', (new Project_SummaryTask_Model())->getSummaryData($project));

			return $viewer->view('ProjectTaskSummaryWidgetContents.tpl', 'Project', true);
		}

		if ($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		$relatedModuleModel = $relationListView->getRelationModel()->getRelationModuleModel();

		if (!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder', $sortOrder);
		}

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		if ($whereCondition) {
			$relationListView->set('whereCondition', $whereCondition);
		}

		$models = $relationListView->getEntries($pagingModel);
		$header = $relationListView->getHeaders();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleName);
		$viewer->assign('RELATED_MODULE_MODEL', $relatedModuleInstance);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('RELATION_LIST_URL', $relationListView->getRelationModel()->getListUrl($parentRecordModel));

		return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
	}

	/**
	 * Function to show Gantt chart
	 *
	 * @param Vtiger_Request $request
	 */
    public function showChart(Vtiger_Request $request)
    {
        $project = Vtiger_Record_Model::getInstanceById($request->get('record'), 'Project');
        $chart = new Project_Chart_Model();
        $user = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $viewer->assign('PARENT_ID', $project->getId())
            ->assign('MODULE', 'Project')
            ->assign('PROJECT_CHART', $chart->getChartData($project))
            ->assign('USER_DATE_FORMAT', $user->get('date_format'))
            ->assign('CHART_LOCALE', $user->get('language'))
            ->assign('CHART_PRINT', false);

        return $viewer->view('ShowChart.tpl', 'Project', true);
    }

    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $scripts = parent::getHeaderScripts($request);
        $local = $this->checkAndConvertJsScripts([
            'modules.ProjectTask.resources.Edit',
            '~/vendor/dhtmlx/gantt/codebase/dhtmlxgantt.js',
            'modules.Project.resources.Chart',
            '~/libraries/jquery/colorpicker/js/colorpicker.js',
        ]);

        return array_merge($scripts, $local);
    }

    public function getHeaderCss(Vtiger_Request $request): array
    {
        $styles = parent::getHeaderCss($request);
        $local = $this->checkAndConvertCssStyles([
            '~/vendor/dhtmlx/gantt/codebase/dhtmlxgantt.css',
            '~/layouts/d1/modules/Project/resources/Chart.css',
            '~/layouts/d1/modules/Project/resources/Summary.css',
            '~/libraries/jquery/colorpicker/css/colorpicker.css',
        ]);

        return array_merge($styles, $local);
    }
}

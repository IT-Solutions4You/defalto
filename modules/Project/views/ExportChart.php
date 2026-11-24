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

class Project_ExportChart_View extends Vtiger_Index_View
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $recordId = $request->get('record');

        if (getSalesEntityType($recordId) !== 'Project'
            || !Users_Privileges_Model::isPermitted('Project', 'DetailView', $recordId)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
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

    function process(Vtiger_request $request)
    {
        $this->GetPrintReport($request);
    }

    /**
     * Function displays the report in printable format
     *
     * @param Vtiger_Request $request
     */
    function GetPrintReport(Vtiger_Request $request)
    {
        $parentId = $request->get('record');
        $moduleName = $request->getModule();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
        $chart = new Project_Chart_Model();
        $printView = $chart->getPrintView($chart->getChartData($parentRecordModel), [
            'scope' => $request->get('chart_scope'), 'search' => $request->get('chart_search'),
            'owner' => $request->get('chart_owner'), 'status' => $request->get('chart_status'),
            'from' => $request->get('chart_from'), 'to' => $request->get('chart_to'), 'scale' => $request->get('chart_scale', 1),
        ]);
        $clock = new DateTimeImmutable('now', new DateTimeZone($currentUserModel->get('time_zone') ?: 'UTC'));
        $projectName = $parentRecordModel->getModule()->getField('projectname');
        $title = $projectName && $projectName->isViewable() ? $parentRecordModel->get('projectname') : vtranslate('SINGLE_Project', 'Project');
        $timeFormat = $currentUserModel->get('hour_format') === '12' ? ' h:i a' : ' H:i';
        $generated = $clock->format(strtr($currentUserModel->get('date_format'), ['yyyy' => 'Y', 'mm' => 'm', 'dd' => 'd']) . $timeFormat);
        $viewer = $this->getViewer($request);
        $viewer->assign('PARENT_ID', $parentId);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('PROJECT_CHART', $printView['data']);
        $viewer->assign('PRINT_VIEW', $printView);
        $viewer->assign('PROJECT_TITLE', $title);
        $viewer->assign('PRINT_GENERATED', $generated);
        $viewer->assign('CHART_PRINT', true);
        $viewer->assign('CHART_FILTERS', $printView['filters']);
        $viewer->assign('CHART_LOCALE', $currentUserModel->get('language'));
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('USER_DATE_FORMAT', $currentUserModel->get('date_format'));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'GetPrintReport', $request->getModule(), $viewer, $request);

        $viewer->view('ShowChartPrintView.tpl', $moduleName);
    }

    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return $this->checkAndConvertJsScripts([
            '~/layouts/d1/lib/jquery/jquery.min.js',
            '~/layouts/d1/lib/jquery/jquery.class.min.js',
            'modules.Vtiger.resources.Class',
            '~/vendor/dhtmlx/gantt/codebase/dhtmlxgantt.js',
            'modules.Project.resources.Chart',
            'modules.Project.resources.ExportChart',
        ]);
    }

    public function getHeaderCss(Vtiger_Request $request): array
    {
        return $this->checkAndConvertCssStyles([
            '~/vendor/dhtmlx/gantt/codebase/dhtmlxgantt.css',
            '~/layouts/d1/modules/Project/resources/Chart.css',
        ]);
    }
}

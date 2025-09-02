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

class Project_DetailView_Model extends Vtiger_DetailView_Model
{
    public function getKeyMetricsWidgetInfo(): array
    {
        return [
            'linktype'      => 'DETAILVIEWWIDGET',
            'link_template' => 'SummaryKeyMetrics.tpl',
        ];
    }

    public function getTasksWidgetInfo(): array
    {
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $projectTaskInstance = Vtiger_Module_Model::getInstance('ProjectTask');
        $projectTaskId = $projectTaskInstance->getId();

        if ($userPrivilegesModel->hasModuleActionPermission($projectTaskId, 'DetailView') && $userPrivilegesModel->hasModulePermission($projectTaskId)) {
            return [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'LBL_TASKS',
                'linkName' => $projectTaskInstance->getName(),
                'linkurl' => 'module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getRecord()->getId() . '&relatedModule=ProjectTask&mode=showRelatedRecords&page=1&limit=5',
                'action' => $userPrivilegesModel->hasModuleActionPermission($projectTaskId, 'CreateView') ? ['Add'] : [],
                'actionURL' => $projectTaskInstance->getQuickCreateUrl(),
            ];
        }

        return $this->getPlaceholderWidgetInfo();
    }

    public function getMilestonesWidgetInfo(): array
    {
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $projectMileStoneInstance = Vtiger_Module_Model::getInstance('ProjectMilestone');
        $projectMileStoneId = $projectMileStoneInstance->getId();

        if ($userPrivilegesModel->hasModuleActionPermission($projectMileStoneId, 'DetailView') && $userPrivilegesModel->hasModulePermission($projectMileStoneId)) {
            return [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'LBL_MILESTONES',
                'linkName' => $projectMileStoneInstance->getName(),
                'linkurl' => 'module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getRecord()->getId() . '&relatedModule=ProjectMilestone&mode=showRelatedRecords&page=1&limit=5',
                'action' => $userPrivilegesModel->hasModuleActionPermission($projectMileStoneId, 'CreateView') ? ['Add'] : [],
                'actionURL' => $projectMileStoneInstance->getQuickCreateUrl(),
            ];
        }

        return $this->getPlaceholderWidgetInfo();
    }

    public function getTicketsWidgetInfo(): array
    {
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $helpDeskInstance = Vtiger_Module_Model::getInstance('HelpDesk');
        $helpDeskId = $helpDeskInstance->getId();

        if ($userPrivilegesModel->hasModuleActionPermission($helpDeskId, 'DetailView') && $userPrivilegesModel->hasModulePermission($helpDeskId)) {
            return [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'HelpDesk',
                'linkName' => $helpDeskInstance->getName(),
                'linkurl' => 'module=' . $this->getModuleName() . '&view=Detail&record=' . $this->getRecord()->getId() . '&relatedModule=HelpDesk&mode=showRelatedRecords&page=1&limit=5',
                'action' => $userPrivilegesModel->hasModuleActionPermission($helpDeskId, 'CreateView') ? ['Add'] : [],
                'actionURL' => $helpDeskInstance->getQuickCreateUrl(),
            ];
        }

        return $this->getPlaceholderWidgetInfo();
    }

    /**
     * Function to get the detail view widgets
     * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
     */
    public function getWidgets()
    {
        $widgetLinks = [];
        $widgets = [];
        $widgets[] = $this->getKeyMetricsWidgetInfo();
        $widgets[] = $this->getTasksWidgetInfo();
        $widgets[] = $this->getKeyFieldsWidgetInfo();
        $widgets[] = $this->getMilestonesWidgetInfo();
        $widgets[] = $this->getPlaceholderWidgetInfo();
        $widgets[] = $this->getAppointmentsWidgetInfo();
        $widgets[] = $this->getDocumentsWidgetInfo();
        $widgets[] = $this->getCommentWidgetInfo();
        $widgets[] = $this->getPlaceholderWidgetInfo();
        $widgets[] = $this->getTicketsWidgetInfo();

        foreach ($widgets as $widgetDetails) {
            $widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
        }

        return $widgetLinks;
    }

    /**
     * Function to get the detail view related links
     * @return <array> - list of links parameters
     */
    public function getDetailViewRelatedLinks()
    {
        $relatedLinks = parent::getDetailViewRelatedLinks();
        $recordModel = $this->getRecord();
        $moduleName = $recordModel->getModuleName();
        $relatedLinks[] = [
            'linktype'  => 'DETAILVIEWTAB',
            'linklabel' => vtranslate('LBL_CHART', $moduleName),
            'linkurl'   => $recordModel->getDetailViewUrl() . '&mode=showChart',
            'linkicon'  => '<i class="fa-solid fa-chart-gantt"></i>'
        ];

        return $relatedLinks;
    }
}
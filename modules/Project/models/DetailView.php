<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Project_DetailView_Model extends Vtiger_DetailView_Model {

    public function getKeyMetricsWidgetInfo(): array
    {
        return [
            'linktype' => 'DETAILVIEWWIDGET',
            'link_template' => 'SummaryKeyMetrics.tpl',
        ];
    }

    /**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 */
	public function getWidgets() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = [];
		$widgets = array();
        $widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($this->getKeyMetricsWidgetInfo());
        $widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($this->getPlaceholderWidgetInfo());
        $widgetLinks = array_merge($widgetLinks, parent::getWidgets());

		$helpDeskInstance = Vtiger_Module_Model::getInstance('HelpDesk');
		if($userPrivilegesModel->hasModuleActionPermission($helpDeskInstance->getId(), 'DetailView')) {
			$createPermission = $userPrivilegesModel->hasModuleActionPermission($helpDeskInstance->getId(), 'CreateView');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'HelpDesk',
					'linkName'	=> $helpDeskInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=HelpDesk&mode=showRelatedRecords&page=1&limit=5',
					'action'	=>	($createPermission == true) ? array('Add') : array(),
					'actionURL' =>	$helpDeskInstance->getQuickCreateUrl()
				);
		}

		$projectMileStoneInstance = Vtiger_Module_Model::getInstance('ProjectMilestone');
		if($userPrivilegesModel->hasModuleActionPermission($projectMileStoneInstance->getId(), 'DetailView') && $userPrivilegesModel->hasModulePermission($projectMileStoneInstance->getId())) {
			$createPermission = $userPrivilegesModel->hasModuleActionPermission($projectMileStoneInstance->getId(), 'CreateView');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_MILESTONES',
					'linkName'	=> $projectMileStoneInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=ProjectMilestone&mode=showRelatedRecords&page=1&limit=5',
					'action'	=>	($createPermission == true) ? array('Add') : array(),
					'actionURL' =>	$projectMileStoneInstance->getQuickCreateUrl()
			);
		}

		$projectTaskInstance = Vtiger_Module_Model::getInstance('ProjectTask');
		if($userPrivilegesModel->hasModuleActionPermission($projectTaskInstance->getId(), 'DetailView') && $userPrivilegesModel->hasModulePermission($projectTaskInstance->getId())) {
			$createPermission = $userPrivilegesModel->hasModuleActionPermission($projectTaskInstance->getId(), 'CreateView');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_TASKS',
					'linkName'	=> $projectTaskInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=ProjectTask&mode=showRelatedRecords&page=1&limit=5',
					'action'	=>	($createPermission == true) ? array('Add') : array(),
					'actionURL' =>	$projectTaskInstance->getQuickCreateUrl()
			);
		}

		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return $widgetLinks;
	}

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks() {
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$relatedLinks[] = array(
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => vtranslate('LBL_CHART', $moduleName),
			'linkurl' => $recordModel->getDetailViewUrl().'&mode=showChart',
			'linkicon' => '<i class="fa-solid fa-chart-gantt"></i>'
			);

		return $relatedLinks;
	}
}
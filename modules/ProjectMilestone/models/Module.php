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

class ProjectMilestone_Module_Model extends Vtiger_Module_Model
{
	public function getSideBarLinks($linkParams)
	{
		$linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);

		$quickLinks = [
			[
				'linktype'  => 'SIDEBARLINK',
				'linklabel' => 'LBL_PROJECTS_LIST',
				'linkurl'   => $this->getProjectsListUrl(),
				'linkicon'  => '',
			],
			[
				'linktype'  => 'SIDEBARLINK',
				'linklabel' => 'LBL_TASKS_LIST',
				'linkurl'   => $this->getTasksListUrl(),
				'linkicon'  => '',
			],
			[
				'linktype'  => 'SIDEBARLINK',
				'linklabel' => 'LBL_MILESTONES_LIST',
				'linkurl'   => $this->getListViewUrl(),
				'linkicon'  => '',
			],
		];
		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $links;
	}

	public function getProjectsListUrl()
	{
		$taskModel = Vtiger_Module_Model::getInstance('Project');

		return $taskModel->getListViewUrl();
	}

	public function getTasksListUrl()
	{
		$taskModel = Vtiger_Module_Model::getInstance('ProjectTask');

		return $taskModel->getListViewUrl();
	}

	/*
     * Function to get supported utility actions for a module
     */
	function getUtilityActionsNames()
	{
		return ['Import', 'Export', 'DuplicatesHandling'];
	}
}
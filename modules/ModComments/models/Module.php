<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class ModComments_Module_Model extends Vtiger_Module_Model
{
    public static array $commentFields = [
        'lastComment' => 'Last Comment',
        'lastCommentSummary' => 'Last Comment Summary',
        'last5Comments' => 'Last 5 Comments',
        'allComments' => 'All Comments',
    ];

    public static array $commentFieldsSummary = [
        'lastCommentSummary',
        'last5Comments',
        'allComments',
    ];

    public static array $commentFieldsLimit = [
        'lastComment' => 1,
        'lastCommentSummary' => 1,
        'last5Comments' => 5,
    ];

    /**
     * @return array
     */
    public static function getCommentFields(): array
    {
        return self::$commentFields;
    }

    /**
     * @return array
     */
    public static function getCommentFieldNames(): array
    {
        return array_keys(self::$commentFields);
    }

    /**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);
		return $links;
	}

	/**
	 * Function to get the create url with parent id set
	 * @param <type> $parentRecord	- parent record for which comment need to be added
	 * @return <string> Url
	 */
	public function getCreateRecordUrlWithParent($parentRecord) {
		$createRecordUrl = $this->getCreateRecordUrl();
		$createRecordUrlWithParent = $createRecordUrl.'&parent_id='.$parentRecord->getId();
		return $createRecordUrlWithParent;
	}

	/**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks(){
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

		$editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = array();


		if(VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_WORKFLOWS',
					'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule='.$this->getName(),
					'linkicon' => $editWorkflowsImagePath
			);
		}
		return $settingsLinks;
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
		return array();
	}

	static function storeRollupSettingsForUser($currentUserModel, $request) {
		$db = PearDatabase::getInstance();
		$userid = $currentUserModel->id;
		$tabid = getTabid($request->get('parent'));
		$rollupid = $request->get('rollupid');
		$rollupStatus = $request->get('rollup_status');
		$rollupSettings = array('rollupid' => $rollupid, 'rollup_status' => $rollupStatus);

		if (!$rollupid) {
			$params = array($userid, $tabid, $rollupStatus);
			$query = "INSERT INTO vtiger_rollupcomments_settings(userid, tabid, rollup_status)"
					. " VALUES(" . generateQuestionMarks($params) . ")";
			$db->pquery($query, $params);
			return ModComments_Module_Model::getRollupSettingsForUser($currentUserModel, $request->get('parent'));
		} else {
			$params = array($rollupStatus, $userid, $tabid);
			$query = "UPDATE vtiger_rollupcomments_settings set rollup_status=?"
					. " WHERE userid=? AND tabid=?";
			$db->pquery($query, $params);
		}

		return $rollupSettings;
	}

	static function getRollupSettingsForUser($currentUserModel, $modulename) {
		$db = PearDatabase::getInstance();
		$userid = $currentUserModel->id;
		$tabid = getTabid($modulename);

		$query = 'SELECT rollupid, rollup_status FROM vtiger_rollupcomments_settings WHERE userid=? AND tabid=?';
		$result = $db->pquery($query, array($userid, $tabid));
		$count = $db->num_rows($result);
		$rollupSettings = array();

		if ($count) {
			$rollupSettings['rollup_status'] = $db->query_result($result, 0, 'rollup_status');
			$rollupSettings['rollupid'] = $db->query_result($result, 0, 'rollupid');
			return $rollupSettings;
		}
		return $rollupSettings;
	}

	function isStarredEnabled() {
		return false;
	}

	function isTagsEnabled() {
		return false;
	}

    /**
     * @param string|int $height
     * @return string
     */
    public function getModuleIcon($height = ''): string
    {
        return sprintf('<i style="font-size: %s" class="fa-solid fa-comment"></i>', $height);
    }
}

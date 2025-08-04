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

require_once 'modules/Webforms/model/WebformsModel.php';
require_once 'include/Webservices/DescribeObject.php';

class Webforms extends CRMExtension
{
	public string $moduleName = 'Webforms';
	public string $parentName = '';
	public $LBL_WEBFORMS = 'Webforms';

	// Cache to speed up describe information store
	protected static $moduleDescribeCache = [];

	public function vtlib_handler($moduleName, $eventType)
	{
		Core_Install_Model::getInstance($eventType, $moduleName)->install();
	}

	function updateSettings()
	{
		global $adb;

		$fieldid = $adb->getUniqueID('vtiger_settings_field');
		$blockid = getSettingsBlockId('LBL_OTHER_SETTINGS');
		$seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid = ?", [$blockid]);
		if ($adb->num_rows($seq_res) > 0) {
			$cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
			if ($cur_seq != null) {
				$seq = $cur_seq + 1;
			}
		}

		$result = $adb->pquery('SELECT 1 FROM vtiger_settings_field WHERE name=?', [$this->LBL_WEBFORMS]);
		if (!$adb->num_rows($result)) {
			$adb->pquery(
				'INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence)
				VALUES (?,?,?,?,?,?,?)',
				[
					$fieldid,
					$blockid,
					$this->LBL_WEBFORMS,
					'modules/Webforms/img/Webform.png',
					'Allows you to manage Webforms',
					'index.php?module=Webforms&action=index&parenttab=Settings',
					$seq
				]
			);
		}
	}

	static function checkAdminAccess($user)
	{
		if (is_admin($user)) {
			return;
		}

		echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
		echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
		<tbody><tr>
		<td rowspan='2' width='11%'><img src= " . vtiger_imageurl('denied.gif', $theme) . " ></td>
		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'>
			<span class='genHeaderSmall'>$app_strings[LBL_PERMISSION]</span></td>
		</tr>
		<tr>
		<td class='small' align='right' nowrap='nowrap'>
		<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>
		</td>
		</tr>
		</tbody></table>
		</div>";
		echo "</td></tr></table>";
		exit;
	}

	static function getModuleDescribe($module)
	{
		if (!isset(self::$moduleDescribeCache[$module])) {
			global $adb, $log, $current_user;
			self::$moduleDescribeCache[$module] = vtws_describe($module, $current_user);
		}

		return self::$moduleDescribeCache[$module];
	}

	static function getFieldInfo($module, $fieldname)
	{
		$describe = self::getModuleDescribe($module);
		foreach ($describe['fields'] as $index => $fieldInfo) {
			if ($fieldInfo['name'] == $fieldname) {
				return $fieldInfo;
			}
		}

		return false;
	}

	static function getFieldInfos($module)
	{
		$describe = self::getModuleDescribe($module);
		foreach ($describe['fields'] as $index => $fieldInfo) {
			if ($fieldInfo['name'] == 'id') {
				unset($describe['fields'][$index]);
			}
		}

		return $describe['fields'];
	}
}
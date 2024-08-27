<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MenuEditor_Module_Model extends Settings_Vtiger_Module_Model {

	public $name = 'MenuEditor';
    public static $ignoredHiddenModules = [
        'Google', 'Dashboard', 'Users', 'ModTracker', 'WSAPP', 'Import', 'Webforms', 'CustomerPortal', 'ModComments', 'Documents', 'MailManager'
    ];

	/**
	 * Function to save the menu structure
	 */
	public function saveMenuStruncture() {
		$db = PearDatabase::getInstance();
		$selectedModulesList = $this->get('selectedModulesList');

		$updateQuery = "UPDATE vtiger_tab SET tabsequence = CASE tabid ";

		foreach ($selectedModulesList as $sequence => $tabId) {
			$updateQuery .= " WHEN $tabId THEN $sequence ";
		}
		$updateQuery .= "ELSE -1 END";

		$db->pquery($updateQuery, array());
	}

	/**
	 * Function to get all the modules which are hidden for an app
	 * @param <string> $appName
	 * @return <array> $modules
	 */
    public static function getHiddenModulesForApp($appName)
    {
        $activeModules = Vtiger_Module_Model::getAll(['0'], self::$ignoredHiddenModules);
        $modules = [];

        foreach ($activeModules as $activeModule) {
            if ($activeModule->isActive()) {
                $modules[$activeModule->getName()] = $activeModule->getName();
            }
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT tabid FROM vtiger_app2tab WHERE appname = ? AND visible = ?', [$appName, 1]);

        while ($row = $db->fetchByAssoc($result)) {
            $moduleName = getTabModuleName($row['tabid']);

            unset($modules[$moduleName]);
        }

        return $modules;
    }

    public static function getAllVisibleModules()
    {
        $modules = [];
        $presence = ['0', '2'];
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_app2tab WHERE visible = ? ORDER BY appname,sequence', [1]);
        $count = $db->num_rows($result);
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $tabId = $db->query_result($result, $i, 'tabid');
                $moduleName = getTabModuleName($tabId);
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

                if (empty($moduleModel)) {
                    continue;
                }

                $sequence = $db->query_result($result, $i, 'sequence');
                $appName = $db->query_result($result, $i, 'appname');
                $moduleModel->set('app2tab_sequence', $sequence);

                if (($userPrivilegesModel->isAdminUser() ||
                        $userPrivilegesModel->hasGlobalReadPermission() ||
                        $userPrivilegesModel->hasModulePermission($moduleModel->getId())) && in_array($moduleModel->get('presence'), $presence)) {
                    $modules[$appName][$moduleName] = $moduleModel;
                }
            }
        }

        return $modules;
    }

    public static function addModuleToApp($moduleName, $parent)
    {
        if (empty($moduleName) || empty($parent)) {
            return;
        }

        $db = PearDatabase::getInstance();
        $parent = strtoupper($parent);
        $oldToNewAppMapping = Vtiger_MenuStructure_Model::getOldToNewAppMapping();

        if (!empty($oldToNewAppMapping[$parent])) {
            $parent = $oldToNewAppMapping[$parent];
        }

        $ignoredModules = Vtiger_MenuStructure_Model::getIgnoredModules();

        if (!in_array($moduleName, $ignoredModules)) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $result = $db->pquery('SELECT * FROM vtiger_app2tab WHERE tabid = ? AND appname = ?', [$moduleModel->getId(), $parent]);
            $sequence = self::getMaxSequenceForApp($parent) + 1;

            if ($db->num_rows($result) == 0) {
                $db->pquery('INSERT INTO vtiger_app2tab(tabid,appname,sequence) VALUES(?,?,?)', [$moduleModel->getId(), $parent, $sequence]);
            }
        }
    }

    public static function updateModuleApp($moduleName, $parent, $oldParent = false) {
		$db = PearDatabase::getInstance();

		$parent = strtoupper($parent);
		$oldToNewAppMapping = Vtiger_MenuStructure_Model::getOldToNewAppMapping();
		if (!empty($oldToNewAppMapping[$parent])) {
			$parent = $oldToNewAppMapping[$parent];
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$query = "UPDATE vtiger_app2tab SET appname=? WHERE tabid=?";
		$params = array($parent, $moduleModel->getId());
		if ($oldParent) {
			$query .= ' AND appname=?';
			array_push($params, strtoupper($oldParent));
		}
		$db->pquery($query, $params);
	}

	/**
	 * Function to get the max sequence number for an app
	 * @param <string> $appName
	 * @return <integer>
	 */
	public static function getMaxSequenceForApp($appName) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT MAX(sequence) AS maxsequence FROM vtiger_app2tab WHERE appname=?', array($appName));
		$sequence = 0;
		if ($db->num_rows($result) > 0) {
			$sequence = $db->query_result($result, 0, 'maxsequence');
		}

		return $sequence;
	}

    public static function getActiveApp($app = '')
    {
        if (!empty($app)) {
            $_SESSION['MenuEditor']['app'] = $app;
        }

        if (empty($app) && !empty($_SESSION['MenuEditor']['app'])) {
            $app = $_SESSION['MenuEditor']['app'];
        }

        if (empty($app)) {
            $app = 'HOME';
        }

        return $app;
    }

    public static function setVisible(string $module, string $app, int $visible): void
    {
        $db = PearDatabase::getInstance();
        $db->pquery('UPDATE vtiger_app2tab SET visible = ? WHERE tabid = ? AND appname = ?', [$visible, getTabid($module), $app]);
    }

    public static function setSequence(string $module, string $app, int $sequence): void
    {
        $db = PearDatabase::getInstance();
        $db->pquery('UPDATE vtiger_app2tab SET sequence = ? WHERE tabid = ? AND appname = ?', [$sequence, getTabid($module), $app]);
    }
}

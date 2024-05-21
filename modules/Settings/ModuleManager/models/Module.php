<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_ModuleManager_Module_Model extends Vtiger_Module_Model {

	public static function getNonVisibleModulesList() {
		return array('ModTracker', 'Users', 'Integration', 'WSAPP', 'ModComments', 'Dashboard', 'ConfigEditor', 'CronTasks',
						'Import', 'Tooltip', 'CustomerPortal', 'Home', 'VtigerBackup', 'FieldFormulas');
	}

	/**
	 * Function to get the url of new module import
	 */
	public static function getNewModuleImportUrl() {
        return 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport';
	}

	/**
	 * Function to get the url of new module import 
	 */
	public static function getUserModuleFileImportUrl() {
		return 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1'; 
	}

	/**
	 * Function to disable a module 
	 * @param type $moduleName - name of the module
	 */
	public function disableModule($moduleName) {
		//Handling events after disable module
		$this->vtlib_toggleModuleAccess($moduleName, false);
	}

	/**
	 * Function to enable the module
	 * @param type $moduleName -- name of the module
	 */
	public function enableModule($moduleName) {
		//Handling events after enable module
		$this->vtlib_toggleModuleAccess($moduleName, true);
	}


	/**
	 * Static Function to get the instance of Vtiger Module Model for all the modules
	 * @return <Array> - List of Vtiger Module Model or sub class instances
	 */
	public static function getAll($presence = array(), $restrictedModulesList = array(),$sequenced = false) {
        if(empty($presence)){
            $presence = array(0,1);
        }
        if(empty($restrictedModulesList)){
            $restrictedModulesList = self::getNonVisibleModulesList();
        }
		 return parent::getAll($presence, $restrictedModulesList);
	}

	/**
	 * Function which will get count of modules
	 * @param <Boolean> $onlyActive - if true get count of only active modules else all the modules
	 * @return <integer> number of modules
	 */
	public static function getModulesCount($onlyActive = false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT * FROM vtiger_tab';
		$params = array();
		if($onlyActive) {
			$presence = array(0);
			$nonVisibleModules = self::getNonVisibleModulesList();
			$query .= ' WHERE presence IN ('. generateQuestionMarks($presence) .')';
			$query .= ' AND name NOT IN ('.generateQuestionMarks($nonVisibleModules).')';
			array_push($params, $presence,$nonVisibleModules);
		}
		$result = $db->pquery($query, $params);
		return $db->num_rows($result);
	}

	/**
	 * Function that returns all those modules that support Module Sequence Numbering
	 * @global PearDatabase $db - database connector
	 * @return <Array of Vtiger_Module_Model>
	 */
	public static function getModulesSupportingSequenceNumbering() {
		$db = PearDatabase::getInstance();
		$sql="SELECT tabid, name FROM vtiger_tab WHERE isentitytype = 1 AND presence = 0 AND tabid IN
			(SELECT DISTINCT tabid FROM vtiger_field WHERE uitype = '4')";
		$result = $db->pquery($sql, array());

		$moduleModels = array();
		for($i=0; $i<$db->num_rows($result); ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$moduleModels[$row['name']] = self::getInstanceFromArray($row);
		}
		return $moduleModels;
	}

	/**
	 * Function to get restricted modules list
	 * @return <Array> List module names
	 */
	public static function getActionsRestrictedModulesList() {
		return array('Home');
	}

    /**
     * Toggle the module (enable/disable)
     */
    protected function vtlib_toggleModuleAccess($modules, $enable_disable)
    {
        global $adb, $__cache_module_activeinfo;

        include_once('vtlib/Vtiger/Module.php');

        if (is_string($modules)) {
            $modules = [$modules];
        }
        $event_type = false;

        if ($enable_disable === true) {
            $enable_disable = 0;
            $event_type = Vtiger_Module::EVENT_MODULE_ENABLED;
        } elseif ($enable_disable === false) {
            $enable_disable = 1;
            $event_type = Vtiger_Module::EVENT_MODULE_DISABLED;
            //Update default landing page to dashboard if module is disabled.
            $adb->pquery('UPDATE vtiger_users SET defaultlandingpage = ? WHERE defaultlandingpage IN(' . generateQuestionMarks($modules) . ')', array_merge(['Home'], $modules));
        }

        $checkResult = $adb->pquery('SELECT name FROM vtiger_tab WHERE name IN (' . generateQuestionMarks($modules) . ')', [$modules]);
        $rows = $adb->num_rows($checkResult);
        for ($i = 0; $i < $rows; $i++) {
            $existingModules[] = $adb->query_result($checkResult, $i, 'name');
        }

        foreach ($modules as $module) {
            if (in_array($module, $existingModules)) { // check if module exists then only update and trigger events
                $adb->pquery('UPDATE vtiger_tab set presence = ? WHERE name = ?', [$enable_disable, $module]);
                $__cache_module_activeinfo[$module] = $enable_disable;
                Vtiger_Module::fireEvent($module, $event_type);
                Vtiger_Cache::flushModuleCache($module);
            }
        }

        create_tab_data_file();
        create_parenttab_data_file();

        // UserPrivilege file needs to be regenerated if module state is changed from
        // vtiger 5.1.0 onwards
        global $vtiger_current_version;
        if (version_compare($vtiger_current_version, '5.0.4', '>')) {
            vtlib_RecreateUserPrivilegeFiles();
        }
    }
}

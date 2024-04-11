<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

require_once 'include/Webservices/Query.php';

class MailManager {

    public $moduleName = 'MailManager';
    public $parentName = 'Tools';

	static function updateMailAssociation($mailuid, $emailid, $crmid) {
		global $adb;
		$adb->pquery("INSERT INTO vtiger_mailmanager_mailrel (mailuid, emailid, crmid) VALUES (?,?,?)", array($mailuid, $emailid, $crmid));
	}

	static function lookupMailInVtiger($searchTerm, $user) {
		$handler = vtws_getModuleHandlerFromName('ITS4YouEmails', $user);
		$meta = $handler->getMeta();
		$moduleFields = $meta->getModuleFields();
		$parentIdFieldInstance = $moduleFields['parent_id'];
		$referenceModules = $parentIdFieldInstance->getReferenceList();

		$filteredResult = array();
		foreach($referenceModules as $referenceModule) {
			$referenceModuleHandler = vtws_getModuleHandlerFromName($referenceModule, $user);
			$referenceModuleMeta = $referenceModuleHandler->getMeta();
			$referenceModuleEmailFields = $referenceModuleMeta->getEmailFields();
			$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
			if($referenceModuleModel){
				$referenceModuleEntityFieldsArray = $referenceModuleModel->getNameFields();
			}
			$searchFieldList = array_merge($referenceModuleEmailFields, $referenceModuleEntityFieldsArray);
			if(!empty($searchFieldList) && !empty($referenceModuleEmailFields)) {
				$searchFieldListString = implode(',', $referenceModuleEmailFields);
				$where = null;
				for($i=0; $i<php7_count($searchFieldList); $i++) {
					if($i == php7_count($searchFieldList) - 1) {
						$where .= sprintf($searchFieldList[$i]." like '%s'", $searchTerm);
					} else {
						$where .= sprintf($searchFieldList[$i]." like '%s' or ", $searchTerm);
					}
				}
				if(!empty($where)) $where = "WHERE $where";
				if($referenceModule == 'Users' && !is_admin($user)){
					//Have to do seperate query since webservices will throw permission denied for users module for non admin users
					global $adb;
					$where .= " AND vtiger_users.status='Active'";
					$query = "select $searchFieldListString,id from vtiger_users $where";
					$dbResult = $adb->pquery($query,array());
					$num_rows = $adb->num_rows($dbResult);
					$result = array();
					for($i=0;$i<$num_rows;$i++) {
						$row = $adb->query_result_rowdata($dbResult,$i);
						$id = $row['id'];
						$webserviceId = vtws_getWebserviceEntityId($referenceModule, $id);
						$row['id'] = $webserviceId;
						$result[] = $row;
					}
				}else{
					$result = vtws_query("select $searchFieldListString from $referenceModule $where;", $user);
				}


				foreach($result as $record) {
					foreach($searchFieldList as $searchField) {
						if(!empty($record[$searchField])) {
							$filteredResult[] = array('id'=> $record[$searchField], 'name'=>$record[$searchField]." - ".getTranslatedString($referenceModule),
													'record'=>$record['id'], 'module'=>$referenceModule);
						}
					}
				}
			}
		}
		return $filteredResult;
	}

	static function lookupMailAssociation($mailuid) {
		global $adb;

		// Mail could get associated with two-or-more records if they get deleted after linking.
		$result = $adb->pquery(
			"SELECT vtiger_mailmanager_mailrel.* FROM vtiger_mailmanager_mailrel INNER JOIN
			vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_mailmanager_mailrel.crmid AND vtiger_crmentity.deleted=0
			AND vtiger_mailmanager_mailrel.mailuid=? LIMIT 1", array(decode_html($mailuid)));
		if ($adb->num_rows($result)) {
			$resultrow = $adb->fetch_array($result);
			return $resultrow;
		}
		return false;
	}

	static function lookupVTEMailAssociation($emailId) {
		global $adb;
		$result = $adb->pquery(
			"SELECT vtiger_mailmanager_mailrel.* FROM vtiger_mailmanager_mailrel INNER JOIN
			vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_mailmanager_mailrel.crmid AND vtiger_crmentity.deleted=0
			AND vtiger_mailmanager_mailrel.mailuid=? LIMIT 1", array(decode_html($mailuid)));
		if ($adb->num_rows($result)) {
			$resultrow = $adb->fetch_array($result);
			return $resultrow;
		}
		return false;
	}

	static function checkModuleWriteAccessForCurrentUser($module) {
		global $current_user;
		if (isPermitted($module, 'CreateView') == "yes" && vtlib_isModuleActive($module)) {
			return true;
		}
		return false;
	}

	/**
	 * function to check the read access for the current user
	 * @global Users Instance $current_user
	 * @param String $module - Name of the module
	 * @return Boolean
	 */
	static function checkModuleReadAccessForCurrentUser($module) {
		global $current_user;
		if (isPermitted($module, 'DetailView') == "yes" && vtlib_isModuleActive($module)) {
			return true;
		}
		return false;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String $modulename - Module name
	 * @param String $event_type - Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type)
	{
	}
}
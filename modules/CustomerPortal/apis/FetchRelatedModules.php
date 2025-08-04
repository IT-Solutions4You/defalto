<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class CustomerPortal_FetchRelatedModules extends CustomerPortal_API_Abstract {
	function process(CustomerPortal_API_Request $request) {
		$current_user = $this->getActiveUser();
		$response = new CustomerPortal_API_Response();

		if ($current_user) {
			$module = $request->get("module");

			if (!CustomerPortal_Utils::isModuleActive($module)) {
				throw new Exception("Module not accessible", 1412);
				exit;
			}
			global $adb;
			$sql = "SELECT  vtiger_customerportal_relatedmoduleinfo.relatedmodules FROM  vtiger_customerportal_relatedmoduleinfo 
                    INNER JOIN vtiger_tab ON  vtiger_customerportal_relatedmoduleinfo.tabid = vtiger_tab.tabid 
                    WHERE vtiger_tab.name= ?";
			$result = $adb->pquery($sql, array($module));
			if ($adb->num_rows($result) > 0) {
				$relatedModulesJSON = $adb->query_result($result, 0, 'relatedmodules');
				$data = Zend_Json::decode(decode_html($relatedModulesJSON));
				$relatedModules = array();
				foreach ($data as $module) {
					if ($module["value"] == 1 && CustomerPortal_Utils::isModuleActive($module["name"])) {
						$relatedModules[] = $module["name"];
					}
				}
				$response->setResult($relatedModules);
			}
		}
		return $response;
	}
}
<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

#[\AllowDynamicProperties] class Vtiger_Cache_Connector_Memory {
	function set($key, $value) {
		$this->$key = $value;
	}
	function get($key) {
		return isset($this->$key)? $this->$key : false;
	}

	function flush(){
		return true;
	}

	function delete($key){
		$this->$key = null;
	}

	public static function getInstance() {
		static $singleton = NULL;
		if ($singleton === NULL) {
			$singleton = new self();
		}
		return $singleton;
	}
}

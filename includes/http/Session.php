<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Override default user-session storage functions if custom session connector exist.
 */
$runtime_configs = Vtiger_Runtime_Configs::getInstance();
$custom_session_handlerclass = $runtime_configs->getConnector('session');
if($custom_session_handlerclass) {
	$handler = $custom_session_handlerclass::getInstance();
	session_set_save_handler($handler, true);
}

// Import dependencies
include_once 'libraries/HTTP_Session2/HTTP/Session2.php';

/**
 * Session class
 */
class Vtiger_Session {

	/**
	 * Constructor
	 * Avoid creation of instances.
	 */
	private function __construct() {
	}

	/**
	 * Destroy session
	 */
	static function destroy($sessionid = false) {
		HTTP_Session2::destroy($sessionid);
	}

	/**
	 * Initialize session
	 */
	static function init($sessionid = false) {
		if(empty($sessionid)) {
			HTTP_Session2::start(null, null);
			$sessionid = HTTP_Session2::id();
		} else {
			HTTP_Session2::start(null, $sessionid);
		}

		if(HTTP_Session2::isIdle() || HTTP_Session2::isExpired()) {
			return false;
		}
		return $sessionid;
	}

	/**
	 * Is key defined in session?
	 */
	static function has($key) {
		$val = static::get($key, null);
		return $val === null;
	}

	/**
	 * Get value for the key.
	 */
	static function get($key, $defvalue = '') {
		return HTTP_Session2::get($key, $defvalue);
	}

	/**
	 * Set value for the key.
	 */
	static function set($key, $value) {
		HTTP_Session2::set($key, $value);
	}

	static function readonly() {
		HTTP_Session2::pause();
	}
}
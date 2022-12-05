<?php

/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Settings_EMAILMaker_Record_Model extends Settings_Vtiger_Record_Model {

	function getId() {
		return $this->get('templateid');
	}

	function getName() {
		return $this->get('templatename');
	}

	public static function getInstance() {
		return new self();
	}
}

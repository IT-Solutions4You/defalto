<?php

/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Settings_EMAILMaker_Module_Model extends Settings_Vtiger_Module_Model {

	/**
	 * Function retruns List of email templates
	 * @return string
	 */
	function getListViewUrl() {
		return 'module=EMAILMaker&parent=Settings&view=List';
	}

	/**
	 * Function returns all the email template Models
	 * @return <Array of EmailTemplates_Record_Model>
	 */
	function getAll($formodule) {
                global $_REQUEST;
		$db = PearDatabase::getInstance();
		
                $emailTemplateModels = array();
                $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
                $request = new Vtiger_Request($_REQUEST, $_REQUEST);
                $return_data = $EMAILMaker->GetListviewData("templateid", "asc", $formodule, true, $request); 
 
                foreach ($return_data AS $templates_data) {
                    if ($templates_data["status"] == "1" && $templates_data["is_listview"] == "0") {
                        $emailTemplateModel = Settings_EMAILMaker_Record_Model::getInstance();
                        $templates_data["type"] = "EMAILMaker";
                        $emailTemplateModel->setData($templates_data);
                        $emailTemplateModels[] = $emailTemplateModel;
                    }
                }
 
                $result = $db->pquery('SELECT * FROM vtiger_emailtemplates WHERE deleted = 0', array());

		for($i=0; $i<$db->num_rows($result); $i++) {
                    $emailTemplateModel = Settings_EMAILMaker_Record_Model::getInstance();
                    $emailTemplateModel->setData($db->query_result_rowdata($result, $i));
                    $emailTemplateModels[] = $emailTemplateModel;
		}

		return $emailTemplateModels;
	}
}

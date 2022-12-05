<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EMAILMaker_MassDelete_Action extends Vtiger_MassDelete_Action
{

    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $recordIds = $this->getRecordsListFromRequest($request);
        if ($moduleModel->CheckPermissions("DELETE")) {
            $adb = PearDatabase::getInstance();

            $checkSql = "select templateid, module from vtiger_emakertemplates where templateid IN (" . generateQuestionMarks($recordIds) . ")";
            $checkRes = $adb->pquery($checkSql, $recordIds);

            $checkArr = array();
            while ($checkRow = $adb->fetchByAssoc($checkRes)) {
                $checkArr[$checkRow["templateid"]] = $checkRow["module"];
            }

            if (count($checkArr) > 0) {
                foreach ($checkArr as $templateid => $tmodule) {
                    $Template_Permissions_Data = $moduleModel->returnTemplatePermissionsData($tmodule, $templateid);

                    if ($Template_Permissions_Data["delete"] === false) {
                        $this->DieDuePermission();
                    }
                    $adb->pquery("UPDATE vtiger_emakertemplates SET deleted = ? WHERE templateid=?", array('1', $templateid));
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(array('viewname' => '1', 'module' => $moduleName));
        $response->emit();
    }
}

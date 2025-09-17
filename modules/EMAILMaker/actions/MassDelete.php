<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_MassDelete_Action extends Vtiger_MassDelete_Action
{
    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $EMAILMaker = EMAILMaker_EMAILMaker_Model::getInstance();
        $recordIds = $this->getRecordsListFromRequest($request);
        if ($EMAILMaker->CheckPermissions("DELETE")) {
            $adb = PearDatabase::getInstance();

            $checkSql = "select templateid, module from vtiger_emakertemplates where templateid IN (" . generateQuestionMarks($recordIds) . ")";
            $checkRes = $adb->pquery($checkSql, $recordIds);

            $checkArr = [];
            while ($checkRow = $adb->fetchByAssoc($checkRes)) {
                $checkArr[$checkRow["templateid"]] = $checkRow["module"];
            }

            if (count($checkArr) > 0) {
                foreach ($checkArr as $templateid => $tmodule) {
                    $Template_Permissions_Data = $EMAILMaker->returnTemplatePermissionsData($tmodule, $templateid);

                    if ($Template_Permissions_Data["delete"] === false) {
                        $this->DieDuePermission();
                    }
                    $adb->pquery("UPDATE vtiger_emakertemplates SET deleted = ? WHERE templateid=?", ['1', $templateid]);
                }
            }
        }
        $response = new Vtiger_Response();
        $response->setResult(['viewname' => '1', 'module' => $moduleName]);
        $response->emit();
    }
}
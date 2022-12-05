<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class EMAILMaker_DeleteAjax_Action extends Vtiger_Save_Action
{

    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $cvId = '';

        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $EMAILMakerModel = Vtiger_Module_Model::getInstance('EMAILMaker');

        if ($EMAILMakerModel->CheckPermissions("DELETE") == false) {
            throw new Exception(vtranslate("LBL_PERMISSION", "EMAILMaker"));
        }
        $adb = PearDatabase::getInstance();

        if ($request->has('record') && !$request->isEmpty('record')) {
            $templateid = $request->get('record');
            $checkRes = $adb->pquery("select module from vtiger_emakertemplates where templateid=?", array($templateid));
            $checkRow = $adb->fetchByAssoc($checkRes);

            $EMAILMakerModel->CheckTemplatePermissions($checkRow["module"], $templateid);
            $adb->pquery("UPDATE vtiger_emakertemplates SET deleted = ? where templateid=?", array('1', $templateid));
        } else {
            $idlist = $request->get('idlist');
            $id_array = explode(';', $idlist);
            $checkRes = $adb->pquery("select templateid, module from vtiger_emakertemplates where templateid IN (" . generateQuestionMarks($id_array) . ")", $id_array);
            $checkArr = array();
            while ($checkRow = $adb->fetchByAssoc($checkRes)) {
                $checkArr[$checkRow["templateid"]] = $checkRow["module"];
            }
            for ($i = 0; $i < count($id_array) - 1; $i++) {
                $EMAILMakerModel->CheckTemplatePermissions($checkArr[$id_array[$i]], $id_array[$i]);
                $sql = "UPDATE vtiger_emakertemplates SET deleted = ? where templateid=?";
                $adb->pquery($sql, array('1', $id_array[$i]));
            }
        }
        $response = new Vtiger_Response();

        if ($request->get('action') == 'Delete') {
            $response->setResult("index.php?module=EMAILMaker&view=List");
        } else {
            $response->setResult(array('viewname' => $cvId, 'module' => $moduleName));
        }


        $response->emit();
    }
}
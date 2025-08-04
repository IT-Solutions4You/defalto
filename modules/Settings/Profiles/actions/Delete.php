<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Profiles_Delete_Action extends Settings_Vtiger_Basic_Action
{
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $recordId = $request->get('record');
        $transferRecordId = $request->get('transfer_record');

        $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
        $recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);
        $transferToProfile = Settings_Profiles_Record_Model::getInstanceById($transferRecordId);
        if ($recordModel && $transferToProfile) {
            $recordModel->delete($transferToProfile);
        }

        $response = new Vtiger_Response();
        $result = ['success' => true];

        $response->setResult($result);
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
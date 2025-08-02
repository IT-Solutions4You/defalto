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

class Settings_MailConverter_DeleteRule_Action extends Settings_Vtiger_Index_Action
{
    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $recordId = $request->get('record');
        $scannerId = $request->get('scannerId');

        if (!$recordId || !$scannerId) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $qualifiedModuleName = $request->getModule(false);

        $recordModel = Settings_MailConverter_RuleRecord_Model::getInstanceById($recordId);
        $scannerId = $recordModel->getScannerId();
        $response = new Vtiger_Response();

        if ($scannerId === (int)$request->get('scannerId')) {
            $recordModel->delete();
            $response->setResult(vtranslate('LBL_DELETED_SUCCESSFULLY', $qualifiedModuleName));
        } else {
            $response->setError(vtranslate('LBL_RULE_DELETION_FAILED', $qualifiedModuleName));
        }
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
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

class Settings_MailConverter_SaveRule_Action extends Settings_Vtiger_Index_Action
{
    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $recordId = $request->get('record');
        $scannerId = $request->get('scannerId');

        if (!$scannerId) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $scannerId = $request->get('scannerId');
        $action = $request->get('action1');
        $request->set('action', $action);
        $qualifiedModuleName = $request->getModule(false);

        if ($recordId) {
            $recordModel = Settings_MailConverter_RuleRecord_Model::getInstanceById($recordId);
        } else {
            $recordModel = Settings_MailConverter_RuleRecord_Model::getCleanInstance($scannerId);
        }

        $recordModel->assignedTo = $request->get('assignedTo');
        $recordModel->cc = $request->get('cc');
        $recordModel->bcc = $request->get('bcc');
        $fieldsList = $recordModel->getFields();
        foreach ($fieldsList as $fieldName) {
            $recordModel->set($fieldName, $request->get($fieldName));
        }
        $recordModel->set('newAction', $request->get('action'));

        $ruleId = $recordModel->save();

        $response = new Vtiger_Response();
        $response->setResult(['message' => vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName), 'id' => $ruleId, 'scannerId' => $scannerId]);
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
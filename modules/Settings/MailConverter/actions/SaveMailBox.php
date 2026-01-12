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

class Settings_MailConverter_SaveMailBox_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('record');
        $qualifiedModuleName = $request->getModule(false);

        if (!empty($recordId)) {
            $recordModel = Settings_MailConverter_Record_Model::getInstanceById($recordId);
        } else {
            $recordModel = Settings_MailConverter_Record_Model::getCleanInstance();
        }

        $recordModel->set('scannerOldName', $request->get('scannerOldName'));
        $fieldsList = $recordModel->getModule()->getFields();

        foreach ($fieldsList as $fieldName => $fieldModel) {
            $recordModel->set($fieldName, $request->get($fieldName));
        }

        $status = $recordModel->save();
        $response = new Vtiger_Response();

        if ($status) {
            $result = ['message' => vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName)];
            $result['id'] = $recordModel->getId();
            $result['listViewUrl'] = $recordModel->getListUrl();
            $response->setResult($result);
        } else {
            $response->setError(vtranslate($recordModel->get('save_error_message'), $qualifiedModuleName));
        }

        $response->emit();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
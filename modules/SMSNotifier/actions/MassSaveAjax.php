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

class SMSNotifier_MassSaveAjax_Action extends Vtiger_Mass_Action
{
    /**
     * Function that saves SMS records
     *
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $recordIds = $this->getRecordsListFromRequest($request);
        $phoneFieldList = $request->get('fields');
        $message = $request->get('message');

        foreach ($recordIds as $recordId) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $numberSelected = false;
            foreach ($phoneFieldList as $fieldname) {
                $fieldValue = $recordModel->get($fieldname);
                if (!empty($fieldValue)) {
                    $toNumbers[] = $fieldValue;
                    $numberSelected = true;
                }
            }
            if ($numberSelected) {
                $recordIds[] = $recordId;
            }
        }

        $response = new Vtiger_Response();

        if (!empty($toNumbers)) {
            $id = SMSNotifier_Record_Model::SendSMS($message, $toNumbers, $currentUserModel->getId(), $recordIds, $moduleName);
            $statusDetails = SMSNotifier::getSMSStatusInfo($id);
            $response->setResult(['id' => $id, 'statusdetails' => $statusDetails[0]]);
        } else {
            $response->setResult(false);
        }

        return $response;
    }
}
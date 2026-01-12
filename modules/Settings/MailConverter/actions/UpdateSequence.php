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

class Settings_MailConverter_UpdateSequence_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);
        $scannerId = $request->get('scannerId');

        if (!$scannerId) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $scannerId = $request->get('scannerId');
        $sequencesList = $request->get('sequencesList');

        $scannerModel = Settings_MailConverter_Record_Model::getInstanceById($scannerId);

        $response = new Vtiger_Response();
        if ($sequencesList) {
            $scannerModel->updateSequence($sequencesList);
            $response->setResult(vtranslate('LBL_SEQUENCE_UPDATED_SUCCESSFULLY', $qualifiedModuleName));
        } else {
            $response->setError(vtranslate('LBL_RULES_SEQUENCE_INFO_IS_EMPTY', $qualifiedModuleName));
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
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

class Settings_Vtiger_ConfigEditorSaveAjax_Action extends Settings_Vtiger_Basic_Action
{
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $qualifiedModuleName = $request->getModule(false);
        $updatedFields = $request->get('updatedFields');
        $moduleModel = Settings_Vtiger_ConfigModule_Model::getInstance();

        if ($updatedFields) {
            $moduleModel->set('updatedFields', $updatedFields);
            $status = $moduleModel->save();

            if ($status === true) {
                $response->setResult([$status]);
            } else {
                $response->setError(vtranslate($status, $qualifiedModuleName));
            }
        } else {
            $response->setError(vtranslate('LBL_FIELDS_INFO_IS_EMPTY', $qualifiedModuleName));
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
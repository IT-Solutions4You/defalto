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

class Settings_PickListDependency_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request)
    {
        $sourceModule = $request->get('sourceModule');
        $sourceField = $request->get('sourceField');
        $targetField = $request->get('targetField');
        $recordModel = Settings_PickListDependency_Record_Model::getInstanceWith($sourceModule, $sourceField, $targetField);

        $response = new Vtiger_Response();
        try {
            $result = $recordModel->save($request->get('mapping'));
            $response->setResult(['success' => $result]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
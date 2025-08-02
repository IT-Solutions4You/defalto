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

class Settings_Currency_DeleteAjax_Action extends Settings_Vtiger_Basic_Action
{
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        try {
            $record = $request->get('record');
            $transforCurrencyToId = $request->get('transform_to_id');
            if (empty($transforCurrencyToId)) {
                throw new Exception('Transfer currency id cannot be empty');
            }

            /** @var Settings_Currency_Module_Model $currencyModel */
            $currencyModel = Settings_Vtiger_Module_Model::getInstance('Settings:Currency');
            $currencyModel::tranformCurrency($record, $transforCurrencyToId);
            $currencyModel->delete($record);

            $response->setResult(['success' => 'true']);
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
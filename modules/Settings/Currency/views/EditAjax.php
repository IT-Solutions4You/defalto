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

class Settings_Currency_EditAjax_View extends Settings_Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $record = $request->get('record');
        if (!empty($record)) {
            $recordModel = Settings_Currency_Record_Model::getInstance($record);
        } else {
            $recordModel = new Settings_Currency_Record_Model();
        }

        $allCurrencies = Settings_Currency_Record_Model::getAllNonMapped($record);
        $otherExistingCurrencies = Settings_Currency_Record_Model::getAll($record);

        foreach ($otherExistingCurrencies as $currencyModel) {
            if ($currencyModel->isBaseCurrency()) {
                $baseCurrencyModel = $currencyModel;
                break;
            }
        }
        $viewer = $this->getViewer($request);

        $qualifiedName = $request->getModule(false);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedName);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('ALL_CURRENCIES', $allCurrencies);
        $viewer->assign('OTHER_EXISTING_CURRENCIES', $otherExistingCurrencies);
        $viewer->assign('BASE_CURRENCY_MODEL', $baseCurrencyModel);

        $viewer->view('EditAjax.tpl', $qualifiedName);
    }
}
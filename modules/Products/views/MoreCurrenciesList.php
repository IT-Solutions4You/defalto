<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Products_MoreCurrenciesList_View extends Vtiger_IndexAjax_View
{
    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $record = $request->get('record');

        $actionName = ($record) ? 'EditView' : 'CreateView';
        $permissions[] = ['module_parameter' => 'module', 'action' => $actionName, 'record_parameter' => 'record'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $currencyName = $request->get('currency');

        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $priceDetails = $recordModel->getPriceDetails();
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $priceDetails = $recordModel->getPriceDetails();

            foreach ($priceDetails as $key => $currencyDetails) {
                if ($currencyDetails['curname'] === $currencyName) {
                    $baseCurrencyConversionRate = $currencyDetails['conversionrate'];
                    break;
                }
            }

            foreach ($priceDetails as $key => $currencyDetails) {
                if ($currencyDetails['curname'] === $currencyName) {
                    $currencyDetails['conversionrate'] = 1;
                    $currencyDetails['is_basecurrency'] = 1;
                } else {
                    $currencyDetails['conversionrate'] = $currencyDetails['conversionrate'] / $baseCurrencyConversionRate;
                    $currencyDetails['is_basecurrency'] = 0;
                }
                $priceDetails[$key] = $currencyDetails;
            }
        }

        $viewer = $this->getViewer($request);

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('PRICE_DETAILS', $priceDetails);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('MoreCurrenciesList.tpl', 'Products');
    }
}
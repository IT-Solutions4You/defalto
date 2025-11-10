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

class PurchaseOrder_CompanyDetails_Action extends Core_Controller_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getCompanyDetails');
        $this->exposeMethod('getAddressDetails');
    }

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $mode = $request->getMode();
        if (!empty($mode)) {
            switch ($mode) {
                case 'getCompanyDetails':
                    $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];
                    break;
                case 'getAddressDetails':
                    $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'recordId'];
                    break;
                default:
                    break;
            }
        }

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);

            return;
        }

        return false;
    }

    function getCompanyDetails($request)
    {
        $companyModel = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyDetails = [
            'street'  => decode_html($companyModel->get('organizationname') . ' ' . $companyModel->get('address')),
            'city'    => decode_html($companyModel->get('city')),
            'state'   => decode_html($companyModel->get('state')),
            'code'    => decode_html($companyModel->get('code')),
            'country' => decode_html($companyModel->get('country')),
        ];
        $response = new Vtiger_Response();
        $response->setResult($companyDetails);
        $response->emit();
    }

    function getAddressDetails($request)
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('recordId'));
        $addressType = $request->get('type');
        $code = 'code';
        if ($recordModel->getModuleName() == 'Vendors') {
            $addressType = '';
            $code = 'postalcode';
        } elseif ($recordModel->getModuleName() == 'Contacts') {
            $code = 'zip';
            if ($addressType == 'bill') {
                $addressType = 'mailing';
            } else {
                $addressType = 'other';
            }
        } else {
            $addressType = $addressType . '_';
        }
        $addressDetails = [
            'street'  => html_entity_decode($recordModel->get($addressType . 'street'), ENT_COMPAT, 'UTF-8'),
            'city'    => html_entity_decode($recordModel->get($addressType . 'city'), ENT_COMPAT, 'UTF-8'),
            'state'   => html_entity_decode($recordModel->get($addressType . 'state'), ENT_COMPAT, 'UTF-8'),
            'code'    => html_entity_decode($recordModel->get($addressType . $code), ENT_COMPAT, 'UTF-8'),
            'pobox'   => html_entity_decode($recordModel->get($addressType . 'pobox'), ENT_COMPAT, 'UTF-8'),
            'country' => html_entity_decode($recordModel->get($addressType . 'country'), ENT_COMPAT, 'UTF-8'),
        ];
        $response = new Vtiger_Response();
        $response->setResult($addressDetails);
        $response->emit();
    }
}
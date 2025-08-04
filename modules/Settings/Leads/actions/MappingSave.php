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

class Settings_Leads_MappingSave_Action extends Settings_Vtiger_Index_Action
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $mapping = $request->get('mapping');

        //removing csrf token from mapping array because it'll cause query failure
        $csrfKey = '__vtrftk';
        if (array_key_exists($csrfKey, $mapping)) {
            unset($mapping[$csrfKey]);
        }

        $mappingModel = Settings_Leads_Mapping_Model::getCleanInstance();

        $response = new Vtiger_Response();
        if ($mapping) {
            $mappingModel->save($mapping);
            $response->setResult([vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName)]);
        } else {
            $response->setError(vtranslate('LBL_INVALID_MAPPING', $qualifiedModuleName));
        }
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}
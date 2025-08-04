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

class Settings_Leads_MappingDelete_Action extends Settings_Vtiger_Index_Action
{
    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('mappingId');
        $qualifiedModuleName = $request->getModule(false);

        $response = new Vtiger_Response();
        if ($recordId) {
            Settings_Leads_Mapping_Model::deleteMapping([$recordId]);
            $response->setResult([vtranslate('LBL_DELETED_SUCCESSFULLY', $qualifiedModuleName)]);
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
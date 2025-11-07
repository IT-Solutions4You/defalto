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

class Settings_SharingAccess_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if ($currentUser->isAdminUser()) {
            return true;
        }

        throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
    }

    public function process(Vtiger_Request $request)
    {
        $modulePermissions = $request->get('permissions');

        foreach ($modulePermissions as $tabId => $permission) {
            $moduleModel = Settings_SharingAccess_Module_Model::getInstance($tabId);
            $moduleModel->set('permission', $permission);

            try {
                $moduleModel->save();
            } catch (Exception $e) {
            }
        }
        Settings_SharingAccess_Module_Model::recalculateSharingRules();

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->emit();
    }
}
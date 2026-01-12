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

class Migration_DisableModules_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $modulesList = $request->get('modulesList');
        if ($modulesList) {
            $moduleManagerModel = new Settings_ModuleManager_Module_Model();
            foreach ($modulesList as $moduleName) {
                $moduleManagerModel->disableModule($moduleName);
            }
        }

        header('Location: migrate/index.php');
    }
}
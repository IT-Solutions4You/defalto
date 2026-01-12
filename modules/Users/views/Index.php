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

class Users_Index_View extends Vtiger_Basic_View
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        parent::preProcess($request);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if ($currentUserModel->isAdminUser()) {
            $settingsIndexView = new Settings_Vtiger_Index_View();
            $settingsIndexView->preProcessSettings($request);
        }
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $settingsIndexView = new Settings_Vtiger_Index_View();
            $settingsIndexView->postProcessSettings($request);
        }

        parent::postProcess($request);
    }

    public function process(Vtiger_Request $request)
    {
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Vtiger.resources.Vtiger',
            "modules.$moduleName.resources.$moduleName",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
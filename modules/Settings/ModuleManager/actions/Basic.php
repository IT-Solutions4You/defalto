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

require_once('vtlib/Vtiger/Layout.php');

class Settings_ModuleManager_Basic_Action extends Settings_Vtiger_IndexAjax_View
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('updateModuleStatus');
        $this->exposeMethod('importUserModuleStep3');
        $this->exposeMethod('updateUserModuleStep3');
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function updateModuleStatus(Vtiger_Request $request)
    {
        $moduleName = $request->get('forModule');
        $updateStatus = $request->get('updateStatus');

        $moduleManagerModel = new Settings_ModuleManager_Module_Model();

        if ($updateStatus == 'true') {
            $moduleManagerModel->enableModule($moduleName);
        } else {
            $moduleManagerModel->disableModule($moduleName);
        }
        $response = new Vtiger_Response();
        $response->emit();
    }

    public function importUserModuleStep3(Vtiger_Request $request)
    {
        $importModuleName = $request->get('module_import_name');
        $uploadFile = $request->get('module_import_file');
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $uploadFileName = "$uploadDir/$uploadFile";
        checkFileAccess($uploadFileName);

        $importType = $request->get('module_import_type');
        if (strtolower($importType) == 'language') {
            $package = new Vtiger_Language();
        } elseif (strtolower($importType) == 'layout') {
            $package = new Vtiger_Layout();
        } else {
            $package = new Vtiger_Package();
        }

        $package->import($uploadFileName);
        checkFileAccessForDeletion($uploadFileName);
        unlink($uploadFileName);

        $result = ['success' => true, 'importModuleName' => $importModuleName];
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function updateUserModuleStep3(Vtiger_Request $request)
    {
        $importModuleName = $request->get('module_import_name');
        $uploadFile = $request->get('module_import_file');
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $uploadFileName = "$uploadDir/$uploadFile";
        checkFileAccess($uploadFileName);

        $importType = $request->get('module_import_type');
        if (strtolower($importType) == 'language') {
            $package = new Vtiger_Language();
        } elseif (strtolower($importType) == 'layout') {
            $package = new Vtiger_Layout();
        } else {
            $package = new Vtiger_Package();
        }

        if (strtolower($importType) == 'language' || strtolower($importType) == 'layout') {
            $package->import($uploadFileName);
        } else {
            $package->update(Vtiger_Module::getInstance($importModuleName), $uploadFileName);
        }

        checkFileAccessForDeletion($uploadFileName);
        unlink($uploadFileName);

        $result = ['success' => true, 'importModuleName' => $importModuleName];
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
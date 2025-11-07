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

class Settings_ModuleManager_ModuleImport_View extends Settings_Vtiger_Index_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('step2');
        $this->exposeMethod('step3');
        $this->exposeMethod('importUserModuleStep1');
        $this->exposeMethod('importUserModuleStep2');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        $EXTENSIONS = Settings_ModuleManager_Extension_Model::getAll();
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('EXTENSIONS', $EXTENSIONS);
        $viewer->assign('EXTENSIONS_AVAILABLE', (php7_count($EXTENSIONS) > 0) ? true : false);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Step1.tpl', $qualifiedModuleName);
    }

    public function step2(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $upgradeError = true;
        $qualifiedModuleName = $request->getModule(false);
        $extensionId = $request->get('extensionId');
        $moduleAction = $request->get('moduleAction');    //Import/Upgrade

        $extensionModel = Settings_ModuleManager_Extension_Model::getInstanceById($extensionId);
        if ($extensionModel) {
            $package = $extensionModel->getPackage();
            if ($package) {
                $importedModuleName = $package->getModuleName();
                $isLanguagePackage = $package->isLanguageType();

                if ($moduleAction === 'Upgrade') {
                    $targetModuleName = $request->get('extensionName');
                    if (($isLanguagePackage && (trim(
                                    $package->xpath_value('prefix')
                                ) == $targetModuleName)) || (!$isLanguagePackage && $importedModuleName === $targetModuleName)) {
                        $upgradeError = false;
                    }
                } else {
                    $upgradeError = false;
                }
                if (!$upgradeError) {
                    if (!$isLanguagePackage) {
                        $moduleModel = Vtiger_Module_Model::getInstance($importedModuleName);
                        $viewer->assign('MODULE_EXISTS', ($moduleModel) ? true : false);
                        $viewer->assign('MODULE_DIR_NAME', '../modules/' . $importedModuleName);

                        if (!$extensionModel->isUpgradable()) {
                            $viewer->assign('SAME_VERSION', true);
                        }
                    }

                    $viewer->assign('EXTENSION_ID', $extensionId);
                    $viewer->assign('MODULE_NAME', $importedModuleName);
                    $viewer->assign('MODULE_ACTION', $moduleAction);

                    $viewer->assign('MODULE_TYPE', $package->type());
                    $viewer->assign('FILE_NAME', $extensionModel->getFileName());
                    $viewer->assign('MODULE_LICENSE', (string)$package->getLicense());
                    $viewer->assign('SUPPORTED_VTVERSION', $package->getDependentVtigerVersion());
                } else {
                    $viewer->assign('ERROR', true);
                    $viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_FILE', $qualifiedModuleName));
                }
            } else {
                $viewer->assign('ERROR', true);
                $viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_FILE', $qualifiedModuleName));
            }
        } else {
            $viewer->assign('ERROR', true);
            $viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_FILE', $qualifiedModuleName));
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'step2', $request->getModule(), $viewer, $request);

        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->view('Step2.tpl', $qualifiedModuleName);
    }

    public function step3(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        global $Vtiger_Utils_Log;
        $viewer->assign('VTIGER_UTILS_LOG', $Vtiger_Utils_Log);
        $Vtiger_Utils_Log = true;
        $qualifiedModuleName = $request->getModule(false);

        $fileName = $request->get('fileName');
        $moduleType = $request->get('moduleType');
        $extensionId = $request->get('extensionId');
        $targetModuleName = $request->get('targetModule');
        $moduleAction = $request->get('moduleAction');

        if ($extensionId) {
            if ($moduleAction !== 'Upgrade') {
                $extensionModel = Settings_ModuleManager_Extension_Model::getInstanceById($extensionId, $fileName);
                $extensionModel->installTrackDetails();
            }

            if (strtolower($moduleType) === 'language') {
                $package = new Vtiger_Language();
            } else {
                $package = new Vtiger_Package();
            }

            $viewer->assign('MODULE_ACTION', $moduleAction);
            $viewer->assign('MODULE_PACKAGE', $package);
            $viewer->assign('TARGET_MODULE_INSTANCE', Vtiger_Module_Model::getInstance($targetModuleName));
            $viewer->assign('MODULE_FILE_NAME', Settings_ModuleManager_Extension_Model::getUploadDirectory() . '/' . $fileName);
        } else {
            $viewer->assign('ERROR', true);
            $viewer->assign('ERROR_MESSAGE', vtranslate('LBL_INVALID_MODULE_INFO', $qualifiedModuleName));
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'step3', $request->getModule(), $viewer, $request);

        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->view('Step3.tpl', $qualifiedModuleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.Settings.$moduleName.resources.ModuleImport"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    public function importUserModuleStep1(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'importUserModuleStep1', $request->getModule(), $viewer, $request);

        $viewer->view('ImportUserModuleStep1.tpl', $qualifiedModuleName);
    }

    public function importUserModuleStep2(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $qualifiedModuleName = $request->getModule(false);

        $uploadFile = 'usermodule_' . time() . '.zip';
        $uploadFileName = "$uploadDir/$uploadFile";
        checkFileAccess($uploadDir);
        if (!move_uploaded_file($_FILES['moduleZip']['tmp_name'], $uploadFileName)) {
            $viewer->assign('MODULEIMPORT_FAILED', true);
        } else {
            $package = new Vtiger_Package();
            $importModuleName = $package->getModuleNameFromZip($uploadFileName);
            $importModuleDepVtVersion = $package->getDependentVtigerVersion();

            if ($importModuleName == null) {
                $viewer->assign('MODULEIMPORT_FAILED', true);
                $viewer->assign("MODULEIMPORT_FILE_INVALID", true);
                checkFileAccessForDeletion($uploadFileName);
                unlink($uploadFileName);
            } else {
                // We need these information to push for Update if module is detected to be present.
                $moduleLicence = vtlib_purify($package->getLicense());

                $viewer->assign("MODULEIMPORT_FILE", $uploadFile);
                $viewer->assign("MODULEIMPORT_TYPE", $package->type());
                $viewer->assign("MODULEIMPORT_NAME", $importModuleName);
                $viewer->assign("MODULEIMPORT_DEP_VTVERSION", $importModuleDepVtVersion);
                $viewer->assign("MODULEIMPORT_LICENSE", $moduleLicence);

                if (!$package->isLanguageType() && !$package->isModuleBundle()) {
                    $moduleInstance = Vtiger_Module::getInstance($importModuleName);
                    $moduleimport_exists = ($moduleInstance) ? "true" : "false";
                    $moduleimport_dir_name = "modules/$importModuleName";
                    $moduleimport_dir_exists = (is_dir($moduleimport_dir_name) ? "true" : "false");
                    $viewer->assign("MODULEIMPORT_EXISTS", $moduleimport_exists);
                    $viewer->assign("MODULEIMPORT_DIR", $moduleimport_dir_name);
                    $viewer->assign("MODULEIMPORT_DIR_EXISTS", $moduleimport_dir_exists);
                }
            }
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'importUserModuleStep2', $request->getModule(), $viewer, $request);

        $viewer->view('ImportUserModuleStep2.tpl', $qualifiedModuleName);
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateReadAccess();
    }
}
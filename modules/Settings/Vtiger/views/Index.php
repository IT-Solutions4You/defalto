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

class Settings_Vtiger_Index_View extends Vtiger_Basic_View
{
    function __construct()
    {
        parent::__construct();
    }

    function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);
        $this->preProcessSettings($request, $display);
    }

    public function preProcessSettings(Vtiger_Request $request, $display = true)
    {
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $selectedMenuId = $request->get('block');
        $fieldId = $request->get('fieldid');
        $settingsModel = Settings_Vtiger_Module_Model::getInstance();
        $menuModels = $settingsModel->getMenus();

        if (!empty($selectedMenuId)) {
            $selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($selectedMenuId);
        } elseif (!empty($moduleName) && $moduleName != 'Vtiger') {
            $fieldItem = Settings_Vtiger_Index_View::getSelectedFieldFromModule($menuModels, $moduleName);
            if ($fieldItem) {
                $selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($fieldItem->get('blockid'));
                $fieldId = $fieldItem->get('fieldid');
            } else {
                reset($menuModels);
                $firstKey = key($menuModels);
                $selectedMenu = $menuModels[$firstKey];
            }
        } else {
            reset($menuModels);
            $firstKey = key($menuModels);
            $selectedMenu = $menuModels[$firstKey];
        }

        $settingsMenItems = [];
        foreach ($menuModels as $menuModel) {
            $menuItems = $menuModel->getMenuItems();
            foreach ($menuItems as $menuItem) {
                $settingsMenItems[$menuItem->get('name')] = $menuItem;
            }
        }
        $viewer->assign('SETTINGS_MENU_ITEMS', $settingsMenItems);

        $activeBLock = Settings_Vtiger_Module_Model::getActiveBlockName($request);
        $viewer->assign('ACTIVE_BLOCK', $activeBLock);

        $restrictedModules = ['Vtiger', 'CustomerPortal', 'Roles', 'ExchangeConnector', 'LoginHistory', 'SharingAccess'];

        if (!in_array($moduleName, $restrictedModules)) {
            if ($moduleName === 'Users') {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            } else {
                $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
            }
            $this->setModuleInfo($request, $moduleModel);
        }

        $viewer->assign('SELECTED_FIELDID', $fieldId);
        $viewer->assign('SELECTED_MENU', $selectedMenu);
        $viewer->assign('SETTINGS_MENUS', $menuModels);
        $viewer->assign('MODULE', $moduleName);

        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    protected function preProcessTplName(Vtiger_Request $request)
    {
        return 'SettingsMenuStart.tpl';
    }

    public function postProcessSettings(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $viewer->view('SettingsMenuEnd.tpl', $qualifiedModuleName);
    }

    public function postProcess(Vtiger_Request $request)
    {
        $this->postProcessSettings($request);
        parent::postProcess($request);
    }

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $usersCount = Users_Record_Model::getCount(true);
        $activeWorkFlows = Settings_Workflows_Module_Model::getActiveWorkflowCount();
        $activeModules = Settings_ModuleManager_Module_Model::getModulesCount(true);
        $pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();

        $viewer->assign('USERS_COUNT', $usersCount);
        $viewer->assign('ACTIVE_WORKFLOWS', $activeWorkFlows);
        $viewer->assign('ACTIVE_MODULES', $activeModules);
        $viewer->assign('SETTINGS_SHORTCUTS', $pinnedSettingsShortcuts);
        $viewer->assign('MODULE', $qualifiedModuleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Index.tpl', $qualifiedModuleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $jsFileNames = [
            'modules.Vtiger.resources.Vtiger',
            'modules.Settings.Vtiger.resources.Vtiger',
            'modules.Settings.Vtiger.resources.Edit',
            "modules.Settings.$moduleName.resources.$moduleName",
            'modules.Settings.Vtiger.resources.Index',
            "modules.Settings.$moduleName.resources.Index",
            "~layouts/$layout/lib/jquery/Lightweight-jQuery-In-page-Filtering-Plugin-instaFilta/instafilta.min.js",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    public static function getSelectedFieldFromModule($menuModels, $moduleName)
    {
        if ($menuModels) {
            foreach ($menuModels as $menuModel) {
                $menuItems = $menuModel->getMenuItems();
                foreach ($menuItems as $item) {
                    $linkTo = $item->getUrl();
                    if (stripos($linkTo, '&module=' . $moduleName) !== false || stripos($linkTo, '?module=' . $moduleName) !== false) {
                        return $item;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Setting module related Information to $viewer (for Vtiger7)
     *
     * @param type $request
     * @param type $moduleModel
     */
    public function setModuleInfo($request, $moduleModel)
    {
        $fieldsInfo = [];
        $basicLinks = [];
        $viewer = $this->getViewer($request);

        if (method_exists($moduleModel, 'getFields')) {
            $moduleFields = $moduleModel->getFields();
            foreach ($moduleFields as $fieldName => $fieldModel) {
                $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
            }
            $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
        }

        if (method_exists($moduleModel, 'getModuleBasicLinks')) {
            $moduleBasicLinks = $moduleModel->getModuleBasicLinks();
            foreach ($moduleBasicLinks as $basicLink) {
                $basicLinks[] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
            }
            $viewer->assign('MODULE_BASIC_ACTIONS', $basicLinks);
        }
    }

    public function getPageTitle(Vtiger_Request $request)
    {
        $pageTitle = parent::getPageTitle($request);

        if ($pageTitle == 'Vtiger') {
            $pageTitle = vtranslate($request->get('parent'), $request->getModule());
        }

        return $pageTitle;
    }
}
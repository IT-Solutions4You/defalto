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

class Users_Detail_View extends Users_PreferenceDetail_View
{
    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        parent::preProcess($request, false);
        $this->preProcessSettings($request);
    }

    public function preProcessSettings(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
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

        //Specific change for Vtiger7
        $settingsMenItems = [];
        foreach ($menuModels as $menuModel) {
            $menuItems = $menuModel->getMenuItems();
            foreach ($menuItems as $menuItem) {
                $settingsMenItems[$menuItem->get('name')] = $menuItem;
            }
        }
        $viewer->assign('SETTINGS_MENU_ITEMS', $settingsMenItems);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $this->setModuleInfo($request, $moduleModel);
        $viewer->assign('ACTIVE_BLOCK', [
            'block' => 'LBL_USER_MANAGEMENT',
            'menu'  => 'LBL_USERS'
        ]);

        $moduleFields = $moduleModel->getFields();
        foreach ($moduleFields as $fieldName => $fieldModel) {
            $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
        }
        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));

        $viewer->assign('SELECTED_FIELDID', $fieldId);
        $viewer->assign('SELECTED_MENU', $selectedMenu);
        $viewer->assign('SETTINGS_MENUS', $menuModels);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('CURRENT_USER_MODEL', $currentUserModel);
        $viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
    }

    public function postProcessSettings(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $qualifiedModuleName = $request->getModule(false);
        $viewer->view('SettingsMenuEnd.tpl', $qualifiedModuleName);
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        $this->postProcessSettings($request);
        parent::postProcess($request);
    }

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->view('UserViewHeader.tpl', $request->getModule());
        parent::process($request);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            'modules.Settings.Vtiger.resources.Index'
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /**
     * Function to get Ajax is enabled or not
     *
     * @param Vtiger_Record_Model record model
     *
     * @return <boolean> true/false
     */
    function isAjaxEnabled($recordModel)
    {
        if ($recordModel->get('status') != 'Active') {
            return false;
        }

        return $recordModel->isEditable();
    }

    /**
     * @inheritDoc
     */
    public function getPageTitle(Vtiger_Request $request): string
    {
        return vtranslate($request->getModule(), $request->getModule());
    }
}
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

class Users_Settings_View extends Vtiger_Basic_View
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        parent::preProcess($request, false);
        $this->preProcessSettings($request, $display);
    }

    public function preProcessSettings(Vtiger_Request $request, $display = true)
    {
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        $fieldId = $request->get('fieldid');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $this->setModuleInfo($request, $moduleModel);

        $viewer->assign('SELECTED_FIELDID', $fieldId);
        $viewer->assign('MODULE', $moduleName);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    /**
     * @inheritDoc
     */
    protected function preProcessTplName(Vtiger_Request $request): string
    {
        return 'UsersSettingsMenuStart.tpl';
    }

    public function process(Vtiger_Request $request)
    {
        //Redirect to My Preference Page
        $userModel = Users_Record_Model::getCurrentUserModel();
        header('Location: ' . $userModel->getPreferenceDetailViewUrl());
    }

    public function postProcessSettings(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule(false);
        $viewer->view('UsersSettingsMenuEnd.tpl', $moduleName);
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        $this->postProcessSettings($request);
        parent::postProcess($request);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.$moduleName.resources.Settings",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
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

        $moduleFields = $moduleModel->getFields();
        foreach ($moduleFields as $fieldName => $fieldModel) {
            $fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
    }
}
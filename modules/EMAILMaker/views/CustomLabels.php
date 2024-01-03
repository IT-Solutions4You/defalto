<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ******************************************************************************* */

class EMAILMaker_CustomLabels_View extends EMAILMaker_Index_View
{

    public function checkPermission(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if (!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        list($oLabels, $languages) = $EMAILMaker->GetCustomLabels();
        $currLang = array();
        foreach ($languages as $langId => $langVal) {
            if ($langVal["prefix"] == $currentLanguage) {
                $currLang["id"] = $langId;
                $currLang["name"] = $langVal["name"];
                $currLang["label"] = $langVal["label"];
                $currLang["prefix"] = $langVal["prefix"];
                break;
            }
        }

        $viewLabels = array();
        foreach ($oLabels as $lblId => $oLabel) {
            $viewLabels[$lblId]["key"] = $oLabel->GetKey();
            $viewLabels[$lblId]["lang_values"] = $oLabel->GetLangValsArr();
        }

        $viewer->assign("LABELS", $viewLabels);
        $viewer->assign("LANGUAGES", $languages);
        $viewer->assign("CURR_LANG", $currLang);
        $viewer->view('CustomLabels.tpl', 'EMAILMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = array(
            "layouts.$layout.modules.EMAILMaker.resources.CustomLabels"
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}
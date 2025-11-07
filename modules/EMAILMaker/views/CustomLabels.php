<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_CustomLabels_View extends EMAILMaker_Index_View
{
    public function checkPermission(Vtiger_Request $request)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $currentLanguage = Vtiger_Language_Handler::getLanguage();

        [$oLabels, $languages] = $EMAILMaker->GetCustomLabels();
        $currLang = [];
        foreach ($languages as $langId => $langVal) {
            if ($langVal["prefix"] == $currentLanguage) {
                $currLang["id"] = $langId;
                $currLang["name"] = $langVal["name"];
                $currLang["label"] = $langVal["label"];
                $currLang["prefix"] = $langVal["prefix"];
                break;
            }
        }

        $viewLabels = [];
        foreach ($oLabels as $lblId => $oLabel) {
            $viewLabels[$lblId]["key"] = $oLabel->GetKey();
            $viewLabels[$lblId]["lang_values"] = $oLabel->GetLangValsArr();
        }

        $viewer->assign("LABELS", $viewLabels);
        $viewer->assign("LANGUAGES", $languages);
        $viewer->assign("CURR_LANG", $currLang);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('CustomLabels.tpl', 'EMAILMaker');
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = [
            "layouts.$layout.modules.EMAILMaker.resources.CustomLabels"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
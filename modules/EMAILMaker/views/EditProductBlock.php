<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ******************************************************************************* */

class EMAILMaker_EditProductBlock_View extends EMAILMaker_Index_View
{
    public $cu_language = "";

    public function process(Vtiger_Request $request)
    {
        $current_language = '';
        $mod_strings = [];

        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        if ($EMAILMaker->CheckPermissions("EDIT") == false) {
            $EMAILMaker->DieDuePermission();
        }

        $viewer = $this->getViewer($request);
        $emode = "";
        $template = array();
        $tplid = $request->get('tplid');
        $mode = $request->get('mode');
        if (isset($tplid) && $tplid != "") {
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery("SELECT * FROM vtiger_emakertemplates_productbloc_tpl WHERE id=?", array($tplid));
            $row = $adb->fetchByAssoc($result);
            if ($mode != "duplicate") {
                $template["id"] = $row["id"];
            }
            $template["name"] = $row["name"];
            $template["body"] = $row["body"];
            $emode = "edit";
        }
        $viewer->assign("EDIT_TEMPLATE", $template);
        $ProductBlockFields = $EMAILMaker->GetProductBlockFields();
        foreach ($ProductBlockFields as $smarty_key => $pbFields) {
            $viewer->assign($smarty_key, $pbFields);
        }
        $cu_model = Users_Record_Model::getCurrentUserModel();
        $this->cu_language = $cu_model->get('language');
        $app_strings_big = Vtiger_Language_Handler::getModuleStringsFromFile($this->cu_language);
        $app_strings = $app_strings_big['languageStrings'];
        $global_lang_labels = array_flip($app_strings);
        $global_lang_labels = array_flip($global_lang_labels);
        asort($global_lang_labels);
        $viewer->assign("GLOBAL_LANG_LABELS", $global_lang_labels);
        list($custom_labels, $languages) = $EMAILMaker->GetCustomLabels();
        $currLangId = "";
        foreach ($languages as $langId => $langVal) {
            if ($langVal["prefix"] == $current_language) {
                $currLangId = $langId;
                break;
            }
        }
        $vcustom_labels = array();
        if (count($custom_labels) > 0) {
            foreach ($custom_labels as $oLbl) {
                $currLangVal = $oLbl->GetLangValue($currLangId);
                if ($currLangVal == "") {
                    $currLangVal = $oLbl->GetFirstNonEmptyValue();
                }

                $vcustom_labels[$oLbl->GetKey()] = $currLangVal;
            }
            asort($vcustom_labels);
        } else {
            $vcustom_labels = $mod_strings["LBL_SELECT_MODULE_FIELD"];
        }
        $viewer->assign("CUSTOM_LANG_LABELS", $vcustom_labels);
        $type = "professional";

        $viewer->assign("TYPE", $type);
        $viewer->assign("EMODE", $emode);
        $viewer->assign("MODE", $mode);
        $viewer->view('EditProductBlock.tpl', 'EMAILMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $jsFileNames = array(
            "modules.$moduleName.resources.ckeditor.ckeditor",
            "libraries.jquery.ckeditor.adapters.jquery",
            "libraries.jquery.jquery_windowmsg",
            "layouts.v7.modules.$moduleName.resources.ProductBlocks"
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
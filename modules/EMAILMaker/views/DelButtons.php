<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Buttons_View extends Vtiger_Index_View
{

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        Vtiger_Basic_View::preProcess($request, false);
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();

        $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
        $linkModels = $EMAILMaker->getSideBarLinks($linkParams);
        $viewer->assign('QUICK_LINKS', $linkModels);

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
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

        $Modules_List = $this->getModulesList();
        $viewer->assign("MODULES_LIST", $Modules_List);
        $viewer->view('Buttons.tpl', 'EMAILMaker');
    }

    public function getModulesList()
    {
        $Modules_List = array();
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery("SELECT * FROM vtiger_tab WHERE isentitytype=1 AND tabid NOT IN (9, 10, 16, 28) AND name != ?", array('EMAILMaker'));
        while ($row = $adb->fetchByAssoc($result)) {
            $links_a = "";
            $links_b = "";
            $tabid = $row['tabid'];
            $tablabel = getTranslatedString($row['tablabel'], $row['name']);
            if ($tablabel == "") {
                $tablabel = $row['tablabel'];
            }
            $result2 = $adb->pquery("SELECT * FROM vtiger_links WHERE tabid = ? AND linktype = ? AND linkurl LIKE ?", array($tabid, 'DETAILVIEWSIDEBARWIDGET', 'module=EMAILMaker&view=GetEMAILActions&record=%'));
            $num_rows2 = $adb->num_rows($result2);

            if ($num_rows2 > 0) {
                $links_a = "checked";
            }

            $result3 = $adb->pquery("SELECT * FROM vtiger_links WHERE tabid = ? AND linktype = ? AND linkurl LIKE ?", array($tabid, 'LISTVIEWMASSACTION', 'javascript:EMAILMaker_Actions_Js.getListViewPopup(this,%'));
            $num_rows3 = $adb->num_rows($result3);

            if ($num_rows3 != 0) {
                $links_b = "checked";
            }

            $Modules_List[] = array(
                "name" => $row['name'],
                "tabid" => $tabid,
                "tablabel" => $tablabel,
                "link_type_a" => $links_a,
                "link_type_b" => $links_b
            );
        }

        return $Modules_List;
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "layouts.vlayout.modules.EMAILMaker.resources.Buttons"
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}
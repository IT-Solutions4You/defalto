<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_ProductBlocks_View extends EMAILMaker_Index_View
{
    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $currentLanguage = Vtiger_Language_Handler::getLanguage();
        $result = $adb->pquery("SELECT * FROM vtiger_emakertemplates_productbloc_tpl", []);
        while ($row = $adb->fetchByAssoc($result)) {
            $templates[$row["id"]]["name"] = $row["name"];
            $templates[$row["id"]]["body"] = html_entity_decode($row["body"], ENT_QUOTES);
        }
        $viewer->assign("PB_TEMPLATES", $templates);
        $viewer->view('ProductBlocks.tpl', 'EMAILMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = [
            "layouts.$layout.modules.EMAILMaker.resources.ProductBlocks"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }
}
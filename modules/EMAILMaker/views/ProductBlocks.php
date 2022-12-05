<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ******************************************************************************* */

class EMAILMaker_ProductBlocks_View extends EMAILMaker_Index_View
{
    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $viewer = $this->getViewer($request);
        $currentLanguage = Vtiger_Language_Handler::getLanguage();
        $result = $adb->pquery("SELECT * FROM vtiger_emakertemplates_productbloc_tpl", array());
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

        $jsFileNames = array(
            'layouts.v7.modules.EMAILMaker.resources.ProductBlocks'
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}
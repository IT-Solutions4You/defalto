<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ******************************************************************************* */

class EMAILMaker_Extensions_View extends EMAILMaker_Index_View
{
    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $extensions = array();

        $EMAILMakerModel = new EMAILMaker_EMAILMaker_Model();

        $link = "index.php?module=EMAILMaker&action=IndexAjax&mode=downloadFile&parenttab=Tools&extid=";
        $extname = "Workflow";
        $extensions[$extname]["label"] = "LBL_WORKFLOW";
        $extensions[$extname]["desc"] = "LBL_WORKFLOW_DESC";
        $extensions[$extname]["exinstall"] = "";
        $extensions[$extname]["manual"] = "";
        $extensions[$extname]["download"] = "";

        $control = $EMAILMakerModel->controlWorkflows();
        if ($control) {
            $extensions[$extname]["install_info"] = vtranslate("LBL_WORKFLOWS_ARE_ALREADY_INSTALLED", "EMAILMaker");
            $extensions[$extname]["install"] = "";
        } else {
            $extensions[$extname]["install_info"] = "";
            $extensions[$extname]["install"] = $link . $extname . "&type=install";
        }

        $extname = 'UnsubscribeEmail';
        $extensions[$extname]['label'] = 'LBL_UNSUBSCRIBE_EMAIL';
        $extensions[$extname]['desc'] = 'LBL_UNSUBSCRIBE_EMAIL_DESC';
        $extensions[$extname]['exinstall'] = '';
        $extensions[$extname]['manual_label'] = 'LBL_UNSUBSCRIBE_MANUAL';
        $extensions[$extname]['manual'] = 'https://it-solutions4you.com/manuals/vtiger7/email-maker-vtiger-7/#unsubscribe-email';
        $extensions[$extname]['download'] = 'https://www.its4you.sk/en/images/extensions/EmailMaker/src/UnsubscribeEmail.zip';


        $extname = 'ITS4YouStyles';
        $extensions[$extname]['label'] = 'ITS4YouStyles';
        $extensions[$extname]['desc'] = 'LBL_ITS4YOUSTYLES_DESC';

        if (vtlib_isModuleActive('ITS4YouStyles')) {
            $extensions[$extname]['install_info'] = vtranslate('LBL_ITS4YOUSTYLES_ARE_ALREADY_INSTALLED', 'EMAILMaker');
            $extensions[$extname]['install'] = '';
        } else {
            $extensions[$extname]['install_info'] = vtranslate('LBL_ITS4YOUSTYLES_INSTALLEW_INFO', 'EMAILMaker');
            $extensions[$extname]['install'] = 'index.php?module=ITS4YouInstaller&parent=Settings&view=Extensions';
        }

        $viewer->assign("EXTENSIONS_ARR", $extensions);
        $download_error = $request->get('download_error');

        if (isset($download_error) && $download_error != "") {
            $viewer->assign("ERROR", "true");
        }

        $viewer->view('Extensions.tpl', 'EMAILMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            'modules.Vtiger.resources.Vtiger',
            "modules.$moduleName.resources.Extensions",
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
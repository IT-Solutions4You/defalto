<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_Extensions_View extends EMAILMaker_Index_View
{
    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();

        $adb = PearDatabase::getInstance();
        $viewer = $this->getViewer($request);
        $extensions = [];

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

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('Extensions.tpl', 'EMAILMaker');
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Vtiger.resources.Vtiger',
            "modules.$moduleName.resources.Extensions",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
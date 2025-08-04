<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_Index_View extends Vtiger_Index_View
{
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        Vtiger_Basic_View::preProcess($request, false);

        $EMAILMakerModel = new EMAILMaker_EMAILMaker_Model();
        $moduleName = $request->getModule();
        $linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
        $linkModels = $EMAILMakerModel->getSideBarLinks($linkParams);

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        $viewer->assign('QUICK_LINKS', $linkModels);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));

        $settingsLinks = [];
        $basicLinks = [];
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        foreach ($moduleModel->getSettingLinks() as $settingsLink) {
            $settingsLink['linklabel'] = sprintf(vtranslate($settingsLink['linklabel'], $moduleName), vtranslate($moduleName, $moduleName));
            $settingsLinks[] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
        }

        foreach ($moduleModel->getModuleBasicLinks() as $basicLink) {
            $basicLink['linklabel'] = sprintf(vtranslate($basicLink['linklabel'], $moduleName), vtranslate($moduleName, $moduleName));
            $basicLinks[] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
        }

        $viewer->assign('MODULE_BASIC_ACTIONS', $basicLinks);
        $viewer->assign('MODULE_SETTING_ACTIONS', $settingsLinks);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }
}
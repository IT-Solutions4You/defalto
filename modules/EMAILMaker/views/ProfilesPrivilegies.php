<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_ProfilesPrivilegies_View extends EMAILMaker_Index_View
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
        $EMAILMaker = EMAILMaker_EMAILMaker_Model::getInstance();
        $viewer = $this->getViewer($request);
        $permissions = $EMAILMaker->GetProfilesPermissions();
        $profilesActions = $EMAILMaker->GetProfilesActions();
        $actionEDIT = getActionid($profilesActions["EDIT"]);
        $actionDETAIL = getActionid($profilesActions["DETAIL"]);
        $actionDELETE = getActionid($profilesActions["DELETE"]);
        $actionEXPORT_RTF = getActionid($profilesActions["EXPORT_RTF"]);
        $mode = $request->get('mode');
        $viewer->assign("MODE", $mode);
        $permissionNames = [];
        foreach ($permissions as $profileid => $subArr) {
            $permissionNames[$profileid] = [];
            $profileName = Settings_Profiles_Record_Model::getProfileName($profileid);

            foreach ($subArr as $actionid => $perm) {
                $permStr = ($perm == "0" ? 'checked="checked"' : "");
                switch ($actionid) {
                    case $actionEDIT:
                        $permissionNames[$profileid][$profileName]["EDIT"]["name"] = 'priv_chk_' . $profileid . '_' . $actionEDIT;
                        $permissionNames[$profileid][$profileName]["EDIT"]["checked"] = $permStr;
                        break;
                    case $actionDETAIL:
                        $permissionNames[$profileid][$profileName]["DETAIL"]["name"] = 'priv_chk_' . $profileid . '_' . $actionDETAIL;
                        $permissionNames[$profileid][$profileName]["DETAIL"]["checked"] = $permStr;
                        break;
                    case $actionDELETE:
                        $permissionNames[$profileid][$profileName]["DELETE"]["name"] = 'priv_chk_' . $profileid . '_' . $actionDELETE;
                        $permissionNames[$profileid][$profileName]["DELETE"]["checked"] = $permStr;
                        break;
                    case $actionEXPORT_RTF:
                        $permissionNames[$profileid][$profileName]["EXPORT_RTF"]["name"] = 'priv_chk_' . $profileid . '_' . $actionEXPORT_RTF;
                        $permissionNames[$profileid][$profileName]["EXPORT_RTF"]["checked"] = $permStr;
                        break;
                }
            }
        }
        $viewer->assign("PERMISSIONS", $permissionNames);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ProfilesPrivilegies.tpl', 'EMAILMaker');
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = [
            "layouts.$layout.modules.$moduleName.resources.ProfilesPrivilegies",
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
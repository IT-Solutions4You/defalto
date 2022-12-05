<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ******************************************************************************* */

class EMAILMaker_ProfilesPrivilegies_View extends EMAILMaker_Index_View
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
        $permissions = $EMAILMaker->GetProfilesPermissions();
        $profilesActions = $EMAILMaker->GetProfilesActions();
        $actionEDIT = getActionid($profilesActions["EDIT"]);
        $actionDETAIL = getActionid($profilesActions["DETAIL"]);
        $actionDELETE = getActionid($profilesActions["DELETE"]);
        $actionEXPORT_RTF = getActionid($profilesActions["EXPORT_RTF"]);
        $mode = $request->get('mode');
        $viewer->assign("MODE", $mode);
        $permissionNames = array();
        foreach ($permissions as $profileid => $subArr) {
            $permissionNames[$profileid] = array();
            $profileName = $this->getProfileName($profileid);
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
        $viewer->view('ProfilesPrivilegies.tpl', 'EMAILMaker');
    }

    public function getProfileName($profileid)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery("select * from vtiger_profile where profileid=?", array($profileid));
        $profilename = $adb->query_result($result, 0, "profilename");
        return $profilename;
    }
}

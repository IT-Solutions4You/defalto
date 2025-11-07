<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_EditSharingRecord_View extends Vtiger_Edit_View
{
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $record = $request->get('record');

        $recordModel = Core_SharingRecord_Model::getInstance($record);
        $viewer->assign('MODE', 'edit');

        $recordName = $recordModel->getRecordName($record);
        $memberGroups = Settings_Groups_Member_Model::getAll(false);

        if (false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany') && false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany')->isActive()) {
            $allCompany = ITS4YouMultiCompany_Module_Model::getCompaniesList('all');
            $viewer->assign('MULTICOMPANY4YOU', 1);

            foreach ($allCompany as $companyId => $company) {
                $type = 'MultiCompany4you';
                $qualifiedId = $type . ':' . $companyId;
                $member = new Vtiger_Base_Model();
                $memberGroups['MultiCompany4you'][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $company['companyname']);
            }
        }

        $viewer->assign('MEMBER_GROUPS', $memberGroups);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('RECORD_ID', $record);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RECORD_NAME', $recordName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('EditSharingRecord.tpl', $qualifiedModuleName);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $modulePopUpFile = 'modules.' . $moduleName . '.resources.Popup';
        $moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';

        unset($headerScriptInstances[$modulePopUpFile], $headerScriptInstances[$moduleEditFile]);

        $jsFileNames = [
            'modules.Vtiger.resources.Vtiger',
            'modules.Settings.Vtiger.resources.Vtiger',
            'modules.Settings.Vtiger.resources.Edit',
            "modules.Settings.Groups.resources.Groups",
            'modules.Settings.Vtiger.resources.Index',
            'modules.Settings.Groups.resources.Index',
            'modules.Vtiger.resources.EditSharingRecord',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
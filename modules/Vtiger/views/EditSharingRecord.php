<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_EditSharingRecord_View extends Vtiger_Edit_View
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

        $recordModel = Vtiger_SharingRecord_Model::getInstance($record);
        $viewer->assign('MODE', 'edit');

        $recordName = $recordModel->getRecordName($record);
        $memberGroups = Settings_Groups_Member_Model::getAll(false);

        if (false !== Vtiger_Module_Model::getInstance('MultiCompany4you') && false !== Vtiger_Module_Model::getInstance('MultiCompany4you')->isActive()) {
            $allCompany = MultiCompany4you_Module_Model::getCompaniesList('all');
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

        $viewer->view('EditSharingRecord.tpl', $qualifiedModuleName);
    }

    /**
     * Function to get the list of Script models to be included
     *
     * @param Vtiger_Request $request
     *
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    public function getHeaderScripts(Vtiger_Request $request)
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
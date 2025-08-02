<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Groups_Edit_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $record = $request->get('record');

        if (!empty($record)) {
            $recordModel = Settings_Groups_Record_Model::getInstance($record);
            $viewer->assign('MODE', 'edit');
        } else {
            $recordModel = new Settings_Groups_Record_Model();
            $viewer->assign('MODE', '');
        }

        $viewer->assign('MEMBER_GROUPS', Settings_Groups_Member_Model::getAll());
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('RECORD_ID', $record);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->view('EditView.tpl', $qualifiedModuleName);
    }

    /**
     * Function to get the list of Script models to be included
     *
     * @param Vtiger_Request $request
     *
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            "modules.Settings.$moduleName.resources.Edit"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    /**
     * Setting module related Information to $viewer (for Vtiger7)
     *
     * @param type $request
     * @param type $moduleModel
     */
    public function setModuleInfo($request, $moduleModel)
    {
        $record = $request->get('record');
        if ($record) {
            $viewer = $this->getViewer($request);
            $listViewModel = Settings_Vtiger_ListView_Model::getInstance($request->getModule(false));
            $linkParams = ['MODULE' => $request->getModule(false), 'ACTION' => $request->get('view')];

            if (!$this->listViewLinks) {
                $this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
            }
            $viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);
        }
    }
}
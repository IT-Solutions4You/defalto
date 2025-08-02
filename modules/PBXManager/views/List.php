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

class PBXManager_List_View extends Vtiger_List_View
{
    function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $viewName = $request->get('viewname');
        if ($viewName) {
            $this->viewName = $viewName;
        }

        $this->initializeListViewContents($request, $viewer);
        $this->assignCustomViews($request, $viewer);
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('RECORD_ACTIONS', $this->getRecordActionsFromModule($moduleModel));
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->assign('IS_CREATE_PERMITTED', false);
        $viewer->assign('IS_MODULE_EDITABLE', false);
        $viewer->assign('IS_MODULE_DELETABLE', false);
        $viewer->view('ListViewContents.tpl', $moduleName);
    }

    public function getRecordActionsFromModule($moduleModel)
    {
        $recordActions = [];
        $recordActions['edit'] = false;
        $recordActions['delete'] = false;

        return $recordActions;
    }
}
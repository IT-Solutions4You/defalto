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

class Settings_Roles_EditAjax_View extends Settings_Roles_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $record = $request->get('record');
        $parentRoleId = $request->get('parent_roleid');

        if (!empty($record)) {
            $recordModel = Settings_Roles_Record_Model::getInstanceById($record);
            $viewer->assign('MODE', 'edit');
        } else {
            $recordModel = new Settings_Roles_Record_Model();
            $recordModel->setParent(Settings_Roles_Record_Model::getInstanceById($parentRoleId));
            $viewer->assign('MODE', '');
        }

        $viewer->assign('ALL_PROFILES', Settings_Profiles_Record_Model::getAll());
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('RECORD_ID', $record);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->view('EditView.tpl', $qualifiedModuleName);
    }
}
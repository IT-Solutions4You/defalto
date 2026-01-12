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

class Settings_PBXManager_Edit_View extends Vtiger_Edit_View
{
    function __construct()
    {
        $this->exposeMethod('showPopup');
    }

    public function process(Vtiger_Request $request)
    {
        $this->showPopup($request);
    }

    public function showPopup(Vtiger_Request $request)
    {
        $id = $request->get('id');
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);
        if ($id) {
            $recordModel = Settings_PBXManager_Record_Model::getInstanceById($id, $qualifiedModuleName);
            $gateway = $recordModel->get('gateway');
        } else {
            $recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
        }
        $viewer->assign('RECORD_ID', $id);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('MODULE', $request->getModule(false));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showPopup', $request->getModule(), $viewer, $request);

        $viewer->view('Edit.tpl', $request->getModule(false));
    }
}
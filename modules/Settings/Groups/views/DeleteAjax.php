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

class Settings_Groups_DeleteAjax_View extends Settings_Vtiger_Index_View
{
    function preProcess(Vtiger_Request $request, $display = true)
    {
        return;
    }

    function postProcess(Vtiger_Request $request)
    {
        return;
    }

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $recordId = $request->get('record');

        $recordModel = Settings_Groups_Record_Model::getInstance($recordId);

        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('RECORD_MODEL', $recordModel);

        $viewer->assign('ALL_USERS', Users_Record_Model::getAll());
        $viewer->assign('ALL_GROUPS', Settings_Groups_Record_Model::getAll());

        echo $viewer->view('DeleteTransferForm.tpl', $qualifiedModuleName, true);
    }
}

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

class Settings_CronTasks_EditAjax_View extends Settings_Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

        $recordModel = Settings_CronTasks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
        $viewer = $this->getViewer($request);

        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RECORD', $recordId);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->view('EditAjax.tpl', $qualifiedModuleName);
    }
}
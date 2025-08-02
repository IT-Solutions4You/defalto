<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Project_EditAjax_View extends Vtiger_IndexAjax_View
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('editColor');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function editColor(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->get('module');
        $parentRecordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('STATUS', $request->get('status'));
        $viewer->assign('TASK_STATUS', Vtiger_Util_Helper::getPickListValues('projecttaskstatus'));
        $viewer->assign('TASK_STATUS_COLOR', $parentRecordModel->getStatusColors());
        $viewer->view('EditColor.tpl', $moduleName);
    }
}
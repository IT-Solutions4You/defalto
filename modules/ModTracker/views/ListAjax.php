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

class ModTracker_ListAjax_View extends Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $parentRecordId = $request->get('parent_id');
        $pageNumber = $request->get('page');
        $limit = $request->get('limit');
        $moduleName = $request->getModule();

        if (empty($pageNumber)) {
            $pageNumber = 1;
        }

        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $pageNumber);
        if (!empty($limit)) {
            $pagingModel->set('limit', $limit);
        }

        $recentActivities = ModTracker_Record_Model::getRecentActivities($parentRecordId, $pagingModel);
        $pagingModel->calculatePageRange($recentActivities);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECENT_ACTIVITIES', $recentActivities);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('PAGING_MODEL', $pagingModel);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        echo $viewer->view('RecentActivities.tpl', $moduleName, true);
    }
}
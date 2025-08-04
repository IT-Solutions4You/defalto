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

class Potentials_TotalRevenuePerSalesPerson_Dashboard extends Vtiger_IndexAjax_View
{
    function getSearchParams($assignedto, $dates)
    {
        $listSearchParams = [];
        $conditions = [['assigned_user_id', 'e', decode_html(urlencode(escapeSlashes($assignedto)))], ["sales_stage", "e", "Closed Won"]];
        if (!empty($dates)) {
            array_push($conditions, ['createdtime', 'bw', $dates['start'] . ' 00:00:00,' . $dates['end'] . ' 23:59:59']);
        }
        $listSearchParams[] = $conditions;

        return '&search_params=' . json_encode($listSearchParams);
    }

    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $linkId = $request->get('linkid');

        $dates = $request->get('createdtime');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $data = $moduleModel->getTotalRevenuePerSalesPerson($dates);
        $listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
        for ($i = 0; $i < php7_count($data); $i++) {
            $data[$i]["links"] = $listViewUrl . $this->getSearchParams($data[$i]["last_name"], $request->get('dateFilter')) . '&nolistcache=1';
        }

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

        $viewer->assign('WIDGET', $widget);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('DATA', $data);
        $viewer->assign('YAXIS_FIELD_TYPE', 'currency');

        //Include special script and css needed for this widget
        $viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('CURRENTUSER', $currentUser);

        $content = $request->get('content');
        if (!empty($content)) {
            $viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
        } else {
            $viewer->view('dashboards/TotalRevenuePerSalesPerson.tpl', $moduleName);
        }
    }
}
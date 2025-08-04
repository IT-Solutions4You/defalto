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

class Vtiger_KeyMetrics_Dashboard extends Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $linkId = $request->get('linkid');

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

        // TODO move this to models
        $keyMetrics = $this->getKeyMetricsWithCount();

        $viewer->assign('WIDGET', $widget);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('KEYMETRICS', $keyMetrics);

        $content = $request->get('content');
        if (!empty($content)) {
            $viewer->view('dashboards/KeyMetricsContents.tpl', $moduleName);
        } else {
            $viewer->view('dashboards/KeyMetrics.tpl', $moduleName);
        }
    }

    // NOTE: Move this function to appropriate model.
    protected function getKeyMetricsWithCount()
    {
        global $current_user, $adb;
        $current_user = Users_Record_Model::getCurrentUserModel();

        require_once 'modules/CustomView/ListViewTop.php';
        $metriclists = getMetricList();

        foreach ($metriclists as $key => $metriclist) {
            $metricresult = null;
            $queryGenerator = new EnhancedQueryGenerator($metriclist['module'], $current_user);
            $queryGenerator->initForCustomViewById($metriclist['id']);
            $metricsql = $queryGenerator->getQuery();
            $metricresult = $adb->query(Vtiger_Functions::mkCountQuery($metricsql));
            if ($metricresult) {
                $rowcount = $adb->fetch_array($metricresult);
                $metriclists[$key]['count'] = $rowcount['count'];
            }
        }

        return $metriclists;
    }
}
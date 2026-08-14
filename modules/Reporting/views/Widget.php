<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Widget_View extends Core_Widget_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showReport');
    }

    public function showReport(Vtiger_Request $request): string
    {
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($request->getRecord(), $moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('BLOCKS', Vtiger_Block_Model::getAllForModule($moduleModel));

        if ($recordModel->isSummaryReport()) {
            $chartData = $recordModel->getChartData();
            $viewer->assign('HAS_CHART_DATA', !empty($chartData['data']['labels']));
            $viewer->assign('CHART_DATA_JSON', json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showReport', $moduleName, $viewer, $request);

        return $viewer->view('ReportWidget.tpl', $moduleName, true);
    }
}

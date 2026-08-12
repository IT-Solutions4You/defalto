<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Detail_View extends Vtiger_Detail_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getReport');
        $this->exposeMethod('getReportXLS');
        $this->exposeMethod('getReportPDF');
        $this->exposeMethod('showChart');
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function getReportXLS(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

        if ($recordModel->isSummaryReport()) {
            $tableData = $recordModel->getGroupedExportTableData();
        } else {
            $tableData = $recordModel->getExportTableData();
        }

        $instance = Reporting_XLS_Model::getInstance();
        $instance->setCellValues($tableData);
        $url = $instance->getXLXS();

        header('location:' . $url);
    }

    public function getReportPDF(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $blockModels = Vtiger_Block_Model::getAllForModule($moduleModel);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('BLOCKS', $blockModels);
        $viewer->assign('IS_SUMMARY_REPORT', $recordModel->isSummaryReport());
        $viewer->assign('PDF_CHART', Reporting_PDFChart_Helper::getPDFData($recordModel, $recordModel->getChartData()));
        $table = $viewer->view('ReportPDF.tpl', $moduleName, true);

        $instance = Reporting_PDF_Model::getInstance();
        $instance->setContent($table);
        $url = $instance->getPDF();

        header('Location: ' . $url);
    }

    public function getReport(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $blockModels = Vtiger_Block_Model::getAllForModule($moduleModel);

        $viewer = $this->getViewer($request);

        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('BLOCKS', $blockModels);

        if ($recordModel->isSummaryReport()) {
            $chartData = $recordModel->getChartData();
            $viewer->assign('HAS_CHART_DATA', !empty($chartData['data']['labels']));
            $viewer->assign(
                'CHART_DATA_JSON',
                json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            );
        }

        return $viewer->view('ReportWidget.tpl', $moduleName, true);
    }

    /**
     * @throws Exception
     */
    public function showChart(Vtiger_Request $request): string
    {
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($request->getRecord(), $moduleName);
        $chartData = $recordModel->getChartData();
        $viewer = $this->getViewer($request);

        $viewer->assign('HAS_CHART_DATA', !empty($chartData));
        $viewer->assign(
            'CHART_DATA_JSON',
            json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        );
        $viewer->assign('MODULE_NAME', $moduleName);

        return $viewer->view('ReportChart.tpl', $moduleName, true);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        return array_merge($headerScriptInstances, $this->getChartScripts());
    }

    public function getOverlayHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(parent::getOverlayHeaderScripts($request), $this->getChartScripts());
    }

    protected function getChartScripts(): array
    {
        return $this->checkAndConvertJsScripts([
            'modules.Reporting.resources.Table',
            '~/vendor/defalto/libraries/chartjs/dist/chart.umd.min.js',
        ]);
    }

}

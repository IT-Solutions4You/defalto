<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporting_Detail_View extends Vtiger_Detail_View {
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getReport');
        $this->exposeMethod('getReportXLS');
        $this->exposeMethod('getReportPDF');
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function getReportXLS(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $tableData = $recordModel->getTableData();

        if ($recordModel->hasCalculations()) {
            $tableData = array_merge($tableData, $recordModel->getTableCalculations());
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
        $viewer->assign('HAS_CALCULATIONS', $recordModel->hasCalculations());
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

        return $viewer->view('ReportWidget.tpl', $moduleName, true);
    }
}
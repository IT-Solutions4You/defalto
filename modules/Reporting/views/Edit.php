<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Reporting_Edit_View extends Vtiger_Edit_View
{
    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(parent::getHeaderScripts($request), $this->getTableScripts());
    }

    /**
     * @inheritDoc
     */
    public function getOverlayHeaderScripts(Vtiger_Request $request)
    {
        return array_merge(parent::getOverlayHeaderScripts($request), $this->getTableScripts());
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('renderTable');
        $mode = $request->get('mode');

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        $this->getViewer($request)->assign(
            'USE_DEFAULT_REPORT_FIELDS',
            empty($request->getRecord()) && !$request->has('fields')
        );
        $this->setDefaultEditCurrency($request);
        $this->setDefaultEditSharingType($request);
        parent::process($request);
    }

    protected function setDefaultEditCurrency(Vtiger_Request $request): void
    {
        if ($request->has('currency_id') && '' !== (string)$request->get('currency_id')) {
            return;
        }

        $moduleName = $request->getModule();
        $recordId = $request->getRecord();

        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

            if (!empty($recordModel->get('currency_id'))) {
                return;
            }
        }

        $request->set('currency_id', Users_Record_Model::getCurrentUserModel()->getCurrencyId());
    }

    protected function setDefaultEditSharingType(Vtiger_Request $request): void
    {
        if ($request->has('sharing_type') && '' !== trim((string)$request->get('sharing_type'))) {
            return;
        }

        $recordId = $request->getRecord();

        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $request->getModule());

            if ('' !== trim((string)$recordModel->get('sharing_type'))) {
                return;
            }
        }

        $request->set('sharing_type', 'private');
    }

    protected function setDefaultReportingCurrency(Vtiger_Record_Model $recordModel): void
    {
        if (empty($recordModel->get('currency_id'))) {
            $recordModel->set('currency_id', Users_Record_Model::getCurrentUserModel()->getCurrencyId());
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function renderTable(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        }

        $fieldModelList = $moduleModel->getFields();

        foreach ($fieldModelList as $fieldName => $fieldModel) {
            $fieldValue = $request->get($fieldName, null);
            $fieldValue = $fieldModel->getUITypeModel()->getRequestValue($fieldValue);

            if (null !== $fieldValue) {
                $recordModel->set($fieldName, $fieldValue);
            }
        }

        $this->setDefaultReportingCurrency($recordModel);

        $recordModel->set('max_entries', 5);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('TABLE_GROUPS_COLLAPSIBLE', $recordModel->isSummaryReport());
        $viewer->assign('TABLE_SCROLLABLE', true);

        if ($recordModel->isSummaryReport()) {
            $viewer->assign('TABLE_DATA', $recordModel->getGroupedTableData());
            $viewer->assign('TABLE_ROW_TYPES', $recordModel->getGroupedTableRowTypes());
            $viewer->assign('TABLE_STYLE', $recordModel->getTableStyle());
            $chartData = $recordModel->getChartData();
            $viewer->assign('IS_SUMMARY_REPORT', true);
            $viewer->assign('HAS_CHART_DATA', !empty($chartData['data']['labels']));
            $viewer->assign(
                'CHART_DATA_JSON',
                json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
            );
        } else {
            $viewer->assign('TABLE_DATA', $recordModel->getTableData());
            $viewer->assign('TABLE_ROW_TYPES', $recordModel->getTableRowTypes());
            $viewer->assign('TABLE_STYLE', $recordModel->getTableStyle());
            $viewer->assign('IS_SUMMARY_REPORT', false);
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('ReportPreview.tpl', $moduleName);
    }

    protected function getTableScripts(): array
    {
        return $this->checkAndConvertJsScripts([
            'modules.Reporting.resources.Table',
            '~/vendor/defalto/libraries/chartjs/dist/chart.umd.min.js',
        ]);
    }

}

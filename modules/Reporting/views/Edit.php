<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporting_Edit_View extends Vtiger_Edit_View
{
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

        parent::process($request);
    }

    /**
     * @param Vtiger_Request $request
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

        $recordModel->set('max_entries', 5);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('TABLE_DATA', $recordModel->getTableData());
        $viewer->assign('TABLE_STYLE', $recordModel->getTableStyle());
        $viewer->view('ReportTable.tpl', $moduleName);
    }
}
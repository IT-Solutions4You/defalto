<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Core_Kanban_View extends Vtiger_Index_View
{

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $view = $request->get('view');
        $layout = Vtiger_Viewer::getLayoutName();
        $jsFileNames = [
            "layouts.$layout.modules.Vtiger.resources.$view",
            "layouts.$layout.modules.$moduleName.resources.$view",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge(parent::getHeaderScripts($request), $jsScriptInstances);
    }

    /**
     * @throws AppException
     */
    public function getProcess(Vtiger_Request $request)
    {
        $module = $request->getModule();
        $qualifiedModule = $request->getModule(false);

        $kanbanModel = Core_Kanban_Model::getInstance($module);
        $kanbanModel->retrieveRequestInfo($request);

        $fieldValues = $kanbanModel->getFieldValues();
        $recordsCount = $recordsInfo = $recordsHeader = array();

        foreach ($fieldValues as $fieldValue) {
            $kanbanModel->filterRecordsByFieldValue($fieldValue);
            $recordsInfo = array_merge($recordsInfo, $kanbanModel->getRecordsInfo());

            $recordsCount[$fieldValue] = $kanbanModel->getRecordsCount();
            $recordsHeader[$fieldValue] = $kanbanModel->getRecordsHeader();
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('KANBAN_MODEL', $kanbanModel);
        $viewer->assign('FIELD_NAME', $kanbanModel->getFieldName());
        $viewer->assign('FIELD_VALUES', $fieldValues);
        $viewer->assign('FIELD_VALUES_COLOR', $kanbanModel->getFieldValuesColor());
        $viewer->assign('RECORDS_INFO', $recordsInfo);
        $viewer->assign('RECORDS_COUNT', $recordsCount);
        $viewer->assign('RECORDS_HEADER', $recordsHeader);
        $viewer->assign('CUSTOM_VIEWS', $kanbanModel->getCustomViewFilters());
        $viewer->assign('CUSTOM_VIEW_ID', $kanbanModel->getCustomViewId());
        $viewer->assign('ASSIGNED_USERS', $kanbanModel->getAssignedUsers());
        $viewer->assign('NEW_RECORD_LINK', $kanbanModel->getNewRecordLink());
        $viewer->assign('LIST_VIEW_LINK', $kanbanModel->getListLink());
        $viewer->assign('KANBAN_ID', $kanbanModel->getKanbanId());

        $viewer->view('KanbanView.tpl', $qualifiedModule);
    }

    public function getRecords(Vtiger_Request $request)
    {
        $module = $request->getModule();

        $kanbanModel = Core_Kanban_Model::getInstance($module);
        $kanbanModel->retrieveRequestInfo($request);

        $recordsInfo = array();
        $fieldValues = $kanbanModel->getFieldValues();

        foreach ($fieldValues as $fieldValue) {
            $kanbanModel->filterRecordsByFieldValue($fieldValue);
            $recordsInfo = array_merge($recordsInfo, $kanbanModel->getRecordsInfo());
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => true,
            'records_info' => $recordsInfo,
        ]);
        $response->emit();
    }

    protected function postProcessTplName(Vtiger_Request $request): string
    {
        return 'KanbanViewPostProcess.tpl';
    }

    protected function preProcessTplName(Vtiger_Request $request): string
    {
        return 'KanbanViewPreProcess.tpl';
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }

        $this->getProcess($request);
    }

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getProcess');
        $this->exposeMethod('getRecords');
    }
}
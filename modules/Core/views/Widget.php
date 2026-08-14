<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Widget_View extends Vtiger_IndexAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('showKeyFields');
        $this->exposeMethod('showAppointments');
        $this->exposeMethod('showComments');
        $this->exposeMethod('showRelatedRecords');
        $this->exposeMethod('showList');
    }

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $recordModule = $request->get('sourceModule') ?: $request->getModule();
        $recordId = $request->get('sourceRecord') ?: $request->get('record');
        $request->set('widgetRecordModule', $recordModule);
        $request->set('widgetRecord', $recordId);

        $permissions = [
            [
                'module_parameter' => 'module',
                'action' => 'DetailView',
            ],
            [
                'module_parameter' => 'widgetRecordModule',
                'action' => 'DetailView',
                'record_parameter' => 'widgetRecord',
            ],
        ];

        if ($request->get('relatedModule')) {
            $permissions[] = ['module_parameter' => 'relatedModule', 'action' => 'DetailView'];
        }

        return $permissions;
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (empty($mode) || !$this->isMethodExposed($mode)) {
            throw new Exception(vtranslate('LBL_NOT_ACCESSIBLE'));
        }

        echo $this->invokeExposedMethod($mode, $request);
    }

    public function showKeyFields(Vtiger_Request $request): string
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $detailView = $this->getDetailView($moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_SUMMARY', $detailView->showModuleSummaryView($request));
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('RECORD', $recordModel);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'showKeyFields', $moduleName, $viewer, $request);

        return $viewer->view('WidgetKeyFields.tpl', 'Core', true);
    }

    public function showAppointments(Vtiger_Request $request): string
    {
        return (string)$this->getDetailView($request->getModule())->getEvents($request);
    }

    public function showComments(Vtiger_Request $request): string
    {
        return (string)$this->getDetailView($request->getModule())->showRecentComments($request);
    }

    public function showRelatedRecords(Vtiger_Request $request): string
    {
        return (string)$this->getDetailView($request->getModule())->showRelatedRecords($request);
    }

    public function showList(Vtiger_Request $request): string
    {
        $targetModule = trim((string)$request->get('relatedModule'));
        $sourceRecord = Vtiger_Record_Model::getInstanceById($request->getRecord(), $request->getModule());
        $displayFields = array_filter(array_map('trim', explode(',', (string)$request->get('fields'))));
        $widget = Core_SummaryWidgetList_Model::getInstance(
            $sourceRecord,
            $targetModule,
            (int)$request->get('filterId'),
            $displayFields,
            trim((string)$request->get('relationType')),
            trim((string)$request->get('referenceField')),
            (int)$request->get('relationId')
        );
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', max(1, (int)$request->get('page')));
        $pagingModel->set('limit', 5);
        $viewer = $this->getViewer($request);
        $viewer->assign('LIST_HEADERS', $widget->getHeaders());
        $viewer->assign('LIST_ENTRIES', $widget->getEntries($pagingModel));
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('RELATED_MODULE', $targetModule);

        return $viewer->view('WidgetList.tpl', 'Core', true);
    }

    protected function getDetailView(string $moduleName): Vtiger_Detail_View
    {
        $className = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);

        return new $className();
    }
}

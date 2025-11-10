<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_RelatedBlock_View extends Vtiger_Index_View
{
    public function isContentView(Vtiger_Request $request): bool
    {
        return in_array($request->get('mode'), ['content', 'iframe', 'options']);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('edit');
        $this->exposeMethod('list');
        $this->exposeMethod('preview');
        $this->exposeMethod('iframe');
        $this->exposeMethod('options');
        $this->exposeMethod('content');
        $this->exposeMethod('delete');
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        if ($this->isContentView($request)) {
            return;
        }

        parent::preProcess($request, $display);
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        if ($this->isContentView($request)) {
            return;
        }

        parent::postProcess($request);
    }

    /**
     * @throws Exception
     */
    public function edit(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        if (!empty($recordId)) {
            $relatedBlock = Core_RelatedBlock_Model::getInstanceById($recordId, $moduleName);
        } else {
            $relatedBlock = Core_RelatedBlock_Model::getInstance($moduleName);
        }

        $relatedBlock->retrieveFromRequest($request);

        $relatedModule = $relatedBlock->getRelatedModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('RELATED_BLOCK_MODEL', $relatedBlock);
        $viewer->assign('RECORD_ID', $relatedBlock->getId());
        $viewer->assign('DATE_FILTERS', Vtiger_Field_Model::getDateFilterTypes());

        if ($relatedModule) {
            $viewer->assign('RECORD_STRUCTURE', $relatedBlock->getRelatedRecordStructure());
            $viewer->assign('RELATED_MODULE_SORT_OPTIONS', $relatedBlock->getRelatedModuleSortOptions());
            $viewer->assign('SELECT_MODULE', $relatedBlock->getRelatedModuleSortOptions());
        }

        Core_Modifiers_Model::modifyForClass(get_class($this), 'edit', $request->getModule(), $viewer, $request);

        $viewer->view('RelatedBlockEdit.tpl', $request->getModule());
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function options(Vtiger_Request $request): void
    {
        $options = Core_RelatedBlock_Model::getAllOptions($request->getModule());

        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'options' => $options]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function delete(Vtiger_Request $request): void
    {
        $relatedBlock = Core_RelatedBlock_Model::getInstanceById($request->getRecord(), $request->getModule());
        $success = false;

        if ($relatedBlock->getId()) {
            $success = true;
            $relatedBlock->delete();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => $success]);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function content(Vtiger_Request $request): void
    {
        $relatedBlock = Core_RelatedBlock_Model::getInstanceById($request->getRecord(), $request->getModule());

        echo $relatedBlock->getTemplateContent();
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function preview(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $sourceRecordId = (int)$request->get('sourceRecord');

        $viewer = $this->getViewer($request);
        $viewer->assign('IFRAME_URL', sprintf('index.php?module=%s&view=RelatedBlock&mode=iframe&record=%d&sourceRecord=%d', $moduleName, $recordId, $sourceRecordId));
        $viewer->view('relatedblock/Preview.tpl', $moduleName);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function iframe(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $sourceRecordId = (int)$request->get('sourceRecord');

        if (empty($sourceRecordId)) {
            throw new Exception(vtranslate('Empty related block source record', $moduleName) . ': [&sourceRecord=]');
        }

        if (empty($recordId)) {
            $records = Core_RelatedBlock_Model::getAll($moduleName);
        } else {
            $records = [
                Core_RelatedBlock_Model::getInstanceById($recordId, $moduleName)
            ];
        }

        $testContent = '';

        foreach ($records as $record) {
            $record->setSourceRecordId($sourceRecordId);
            $record->retrieveSourceRecord();

            $testContent .= $record->getName() . '<br><br>';
            $testContent .= $record->getTemplateContent() . '<hr>';
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('TEMPLATE_CONTENT', $testContent);
        $viewer->assign('RECORD_MODEL', $records[0]->getSourceRecord());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'iframe', $request->getModule(), $viewer, $request);

        $viewer->view('relatedblock/Iframe.tpl', $moduleName);
    }

    /**
     * @throws Exception
     */
    public function list(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('RELATED_BLOCK_MODELS', Core_RelatedBlock_Model::getAll($moduleName));
        $viewer->assign('RELATED_BLOCK_MODEL', Core_RelatedBlock_Model::getInstance($moduleName));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'list', $request->getModule(), $viewer, $request);

        $viewer->view('RelatedBlockList.tpl', $request->getModule());
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $viewName = $request->get('view');
        $jsFileNames = [
            'modules.Vtiger.resources.Vtiger',
            "modules.$moduleName.resources.$moduleName",
            "modules.Core.resources.$viewName",
            "modules.Vtiger.resources.$viewName",
            "modules.$moduleName.resources.$viewName",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
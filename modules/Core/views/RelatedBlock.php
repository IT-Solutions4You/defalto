<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_RelatedBlock_View extends Vtiger_Index_View {
    public function isContentView(Vtiger_Request $request): bool
    {
        return in_array($request->get('mode'), ['content', 'iframe', 'options']);
    }

    /**
     * @param Vtiger_Request $request
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
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, $display = true)
    {
        if ($this->isContentView($request)) {
            return;
        }

        parent::preProcess($request, $display);
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function postProcess(Vtiger_Request $request)
    {
        if ($this->isContentView($request)) {
            return;
        }

        parent::postProcess($request);
    }

    /**
     * @throws AppException
     */
    public function edit(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        if(!empty($recordId)) {
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

        if($relatedModule) {
            $viewer->assign('RECORD_STRUCTURE', $relatedBlock->getRelatedRecordStructure());
            $viewer->assign('RELATED_MODULE_SORT_OPTIONS', $relatedBlock->getRelatedModuleSortOptions());
            $viewer->assign('SELECT_MODULE', $relatedBlock->getRelatedModuleSortOptions());
        }

        $viewer->view('RelatedBlockEdit.tpl', $request->getModule());
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
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
     * @return void
     * @throws AppException
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
     * @return void
     * @throws AppException
     */
    public function content(Vtiger_Request $request): void
    {
        $relatedBlock = Core_RelatedBlock_Model::getInstanceById($request->getRecord(), $request->getModule());

        echo $relatedBlock->getTemplateContent();
    }


    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     */
    public function preview(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $sourceRecordId = (int)$request->get('sourceRecord');

        $viewer = $this->getViewer($request);
        $viewer->assign('IFRAME_URL', sprintf('index.php?module=Invoice&view=RelatedBlock&mode=iframe&record=%d&sourceRecord=%d', $recordId, $sourceRecordId));
        $viewer->view('relatedblock/Preview.tpl', $moduleName);
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     */
    public function iframe(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->getRecord();
        $sourceRecordId = (int)$request->get('sourceRecord');

        if(empty($recordId) || empty($sourceRecordId)) {
            throw new AppException(vtranslate('Empty related block record or source record', $moduleName));
        }

        $relatedBlock = Core_RelatedBlock_Model::getInstanceById($recordId, $moduleName);
        $relatedBlock->setSourceRecordId($sourceRecordId);
        $relatedBlock->retrieveSourceRecord();

        $testContent = $relatedBlock->getTemplateContent();

        $viewer = $this->getViewer($request);
        $viewer->assign('RELATED_BLOCK_MODEL', $relatedBlock);
        $viewer->assign('TEMPLATE_CONTENT', $testContent);
        $viewer->assign('RECORD_MODEL', $relatedBlock->getSourceRecord());
        $viewer->view('relatedblock/Iframe.tpl', $moduleName);
    }

    /**
     * @throws AppException
     */
    public function list(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('RELATED_BLOCK_MODELS', Core_RelatedBlock_Model::getAll($moduleName));
        $viewer->view('RelatedBlockList.tpl', $request->getModule());
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();
        $viewName = $request->get('view');
        $jsFileNames = array(
            'modules.Vtiger.resources.Vtiger',
            "modules.$moduleName.resources.$moduleName",
            "modules.Core.resources.$viewName",
            "modules.Vtiger.resources.$viewName",
            "modules.$moduleName.resources.$viewName",
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
}
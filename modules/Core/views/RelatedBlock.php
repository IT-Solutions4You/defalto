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
    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('edit');
        $this->exposeMethod('list');
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
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

        if($relatedModule) {
            $viewer->assign('RECORD_STRUCTURE', $relatedBlock->getRelatedRecordStructure());
            $viewer->assign('RELATED_MODULE_SORT_OPTIONS', $relatedBlock->getRelatedModuleSortOptions());
            $viewer->assign('SELECT_MODULE', $relatedBlock->getRelatedModuleSortOptions());
        }

        $viewer->view('RelatedBlockEdit.tpl', $request->getModule());
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
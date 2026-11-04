<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_InventoryItemsBlock_View extends Core_RelatedBlock_View
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        if ('Core' !== $request->getModule()) {
            return parent::checkPermission($request);
        }

        $currentUser = Users_Record_Model::getCurrentUserModel();

        if (!$currentUser || !$currentUser->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        if ('Core' === $request->getModule()) {
            Vtiger_Basic_View::preProcess($request, false);

            $viewer = $this->getViewer($request);
            $viewer->assign('MODULE_BREADCRUMB_LABEL', vtranslate('LBL_PRODUCTBLOCKTPL', 'Core'));
            $viewer->assign('MODULE_BREADCRUMB_URL', 'index.php?module=Core&view=ProductBlocks');

            if ($display) {
                $this->preProcessDisplay($request);
            }

            return;
        }

        parent::preProcess($request, $display);
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        if ('Core' === $request->getModule()) {
            Vtiger_Basic_View::postProcess($request);

            return;
        }

        parent::postProcess($request);
    }

    /**
     * @throws Exception
     */
    public function list(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $sourceModule = $request->get('source_module');
        $inventoryModules = [];

        foreach (InventoryItem_Utils_Helper::getInventoryItemModules() as $inventoryModule) {
            $inventoryModules[$inventoryModule] = vtranslate($inventoryModule, $inventoryModule);
        }

        if (empty($sourceModule) && isset($inventoryModules[$moduleName])) {
            $sourceModule = $moduleName;
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('INVENTORY_BLOCK_MODELS', Core_InventoryItemsBlock_Model::getAll($sourceModule));
        $viewer->assign('INVENTORY_MODULES', $inventoryModules);
        $viewer->assign('SOURCE_MODULE', $sourceModule);
        $viewer->assign('MANAGEMENT_MODULE', $moduleName);
        $viewer->view('InventoryItemsBlockList.tpl', 'Core');
    }

    public function edit(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $sourceModule = $request->get('source_module');
        $recordId = $request->get('record');

        if (empty($sourceModule) && in_array($moduleName, InventoryItem_Utils_Helper::getInventoryItemModules())) {
            $sourceModule = $moduleName;
        }

        if (!empty($recordId)) {
            $relatedBlock = Core_InventoryItemsBlock_Model::getInstanceById($recordId, $sourceModule);
            $sourceModule = $relatedBlock->getModuleName();
        } else {
            $relatedBlock = Core_InventoryItemsBlock_Model::getInstance($sourceModule);
        }

        $relatedBlock->retrieveFromRequest($request);

        $viewer = $this->getViewer($request);
        $viewer->assign('RELATED_BLOCK_MODEL', $relatedBlock);
        $viewer->assign('RECORD_ID', $relatedBlock->getId());
        $viewer->assign('SOURCE_MODULE', $sourceModule);
        $viewer->assign('MANAGEMENT_MODULE', $moduleName);
        $viewer->assign('DATE_FILTERS', Vtiger_Field_Model::getDateFilterTypes());
        $viewer->assign('RECORD_STRUCTURE', $relatedBlock->getRelatedRecordStructure());
        $viewer->assign('SELECT_MODULE', $relatedBlock->getRelatedModuleSortOptions());

        $viewer->view('InventoryItemsBlockEdit.tpl', $request->getModule());
    }

    /**
     * @throws Exception
     */
    public function delete(Vtiger_Request $request): void
    {
        $inventoryBlock = Core_InventoryItemsBlock_Model::getInstanceById($request->getRecord(), $request->getModule());
        $success = false;

        if ($inventoryBlock->getId()) {
            $inventoryBlock->delete();
            $success = true;
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => $success]);
        $response->emit();
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        unset($headerScriptInstances['modules.Core.resources.InventoryItemsBlock']);

        $jsFileNames = [
            'modules.Core.resources.RelatedBlock',
            'modules.Core.resources.InventoryItemsBlock',
        ];

        return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts($jsFileNames));
    }
}

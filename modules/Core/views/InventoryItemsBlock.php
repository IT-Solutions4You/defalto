<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    public function edit(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        if (!empty($recordId)) {
            $relatedBlock = Core_InventoryItemsBlock_Model::getInstanceById($recordId, $moduleName);
        } else {
            $relatedBlock = Core_InventoryItemsBlock_Model::getInstance($moduleName);
        }

        $relatedBlock->retrieveFromRequest($request);

        $viewer = $this->getViewer($request);
        $viewer->assign('RELATED_BLOCK_MODEL', $relatedBlock);
        $viewer->assign('RECORD_ID', $relatedBlock->getId());
        $viewer->assign('DATE_FILTERS', Vtiger_Field_Model::getDateFilterTypes());
        $viewer->assign('RECORD_STRUCTURE', $relatedBlock->getRelatedRecordStructure());
        $viewer->assign('SELECT_MODULE', $relatedBlock->getRelatedModuleSortOptions());

        $viewer->view('InventoryItemsBlockEdit.tpl', $request->getModule());
    }


    public function getHeaderScripts(Vtiger_Request $request)
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
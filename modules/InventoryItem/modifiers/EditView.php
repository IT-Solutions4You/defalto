<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_EditView_Modifier implements Core_Modifier_Interface
{
    /**
     * @inheritDoc
     */
    public function modifyProcess(Vtiger_Viewer $viewer, Vtiger_Request $request): void
    {
        $isSimplified = InventoryItem_Module_Model::isSimplifiedBlock();
        $viewer->assign('SIMPLIFIED_BLOCK', $isSimplified);

        if (!$isSimplified) {
            return;
        }

        $recordId = (int)$request->get('record');
        $rows = InventoryItem_SimplifiedItems_Model::getRows($recordId ?: InventoryItem_SimplifiedItems_Model::getSourceRecordId($request));

        if (!$recordId) {
            // Items copied from another document become new rows of the new document
            $rows = array_map(static fn(array $row) => array_merge($row, ['id' => 0]), $rows);
        }

        $viewer->assign('SIMPLIFIED_ITEMS', $rows);
        $viewer->assign('ITEM_MODULES', InventoryItem_ItemModules_Model::getItemModules());
        // Same rule as the standard block: the buttons that add items are available when the user can edit the document
        $viewer->assign('ITEMS_EDITABLE', !$recordId || InventoryItem_ParentEntity_Model::isParentEditable($recordId));
        InventoryItem_SimplifiedItems_Model::assignCommon($viewer);
        $viewer->assign('SIMPLIFIED_DECIMALS', InventoryItem_Utils_Helper::fetchDecimals());
    }

    /**
     * Modifies an array of .js files that should be loaded so that the InventoryItem block could provide its functionality
     *
     * @param array          $jsFileNames
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function modifyGetHeaderScripts(array &$jsFileNames, Vtiger_Request $request): void
    {
        $myJsFileNames = [
            'modules.InventoryItem.resources.InventoryItemEdit',
        ];

        if (InventoryItem_Module_Model::isSimplifiedBlock()) {
            $myJsFileNames[] = 'modules.InventoryItem.resources.SimplifiedEdit';
        }

        $jsFileNames = array_merge($jsFileNames, $myJsFileNames);
    }
}
<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_DeleteProductLine_Action extends Vtiger_Delete_Action
{
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $moduleName = $request->getModule();
            $recordId = $request->get('lineItemId');
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $sequence = $recordModel->get('sequence');
            $parentId = (int)$recordModel->get('parentid');
            $recordModel->delete();
            $response->setResult('OK');

            $allItems = InventoryItem_Module_Model::fetchItemsForId($parentId, true);

            foreach ($allItems as $item) {
                $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');

                if ($itemModel->get('sequence') > $sequence) {
                    $itemModel->set('sequence', $itemModel->get('sequence') - 1);
                    $itemModel->set('mode', 'edit');
                    $itemModel->save();
                }

                unset($itemModel);
            }

            InventoryItem_ParentEntity_Model::updateTotals($parentId);
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }

        $response->emit();
    }
}
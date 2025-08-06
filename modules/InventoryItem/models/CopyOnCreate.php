<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_CopyOnCreate_Model extends Vtiger_Base_Model
{
    public static function run(CRMEntity $entity, int $forceConversionFrom = 0): void
    {
        if (!empty($entity->mode) && $entity->mode !== 'create' && $entity->mode !== '') {
            return;
        }

        if ($forceConversionFrom) {
            $sourceRecord = $forceConversionFrom;
        } else {
            $request = new Vtiger_Request($_REQUEST, $_REQUEST);
            $sourceModule = $request->get('sourceModule');
            $sourceRecord = (int)$request->get('sourceRecord');

            if (empty($sourceModule) || empty($sourceRecord)) {
                return;
            }
        }

        $allItems = InventoryItem_Module_Model::fetchItemsForId($sourceRecord, true);

        foreach ($allItems as $item) {
            $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
            $itemModel->set('mode', '');
            $itemModel->set('id', '');
            $itemModel->set('recordId', '');
            $itemModel->set('parentid', $entity->id);
            $itemModel->set('parentitemid', $item['inventoryitemid']);
            $itemModel->save();
            unset($itemModel);
        }

        InventoryItem_ParentEntity_Model::updateTotals($entity->id);
    }
}
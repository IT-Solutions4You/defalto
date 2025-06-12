<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_CopyOnCreate_Model extends Vtiger_Base_Model
{
    public static function run(Vtiger_Record_Model $recordModel): void
    {
        if (!empty($recordModel->get('mode')) && $recordModel->get('mode') !== 'create') {
            return;
        }

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $sourceModule = $request->get('sourceModule');
        $sourceRecord = (int)$request->get('sourceRecord');

        if (empty($sourceModule) || empty($sourceRecord)) {
            return;
        }

        $allItems = InventoryItem_Module_Model::fetchItemsForId($sourceRecord, true);

        foreach ($allItems as $item) {
            $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
            $itemModel->set('mode', '');
            $itemModel->set('id', '');
            $itemModel->set('recordId', '');
            $itemModel->set('parentid', $recordModel->get('id'));
            $itemModel->set('parentitemid', $item['inventoryitemid']);
            $itemModel->save();
            unset($itemModel);
        }

        InventoryItem_ParentEntity_Model::updateTotals($recordModel->get('id'));
    }
}
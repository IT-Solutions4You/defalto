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

        if (!empty($allItems)) {
            $sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord);
            $recordModel = Vtiger_Record_Model::getInstanceById($entity->id);
            $changed = false;

            if ($recordModel->get('currency_id') !== $sourceRecordModel->get('currency_id')) {
                $recordModel->set('currency_id', $sourceRecordModel->get('currency_id'));
                $recordModel->set('conversion_rate', $sourceRecordModel->get('conversion_rate'));
                $entity->column_fields['currency_id'] = $recordModel->get('currency_id');
                $entity->column_fields['conversion_rate'] = $recordModel->get('conversion_rate');
                $changed = true;
            }

            if ($recordModel->get('pricebookid') !== $sourceRecordModel->get('pricebookid')) {
                $recordModel->set('pricebookid', $sourceRecordModel->get('pricebookid'));
                $changed = true;
            }

            if ($recordModel->get('region_id') !== $sourceRecordModel->get('region_id')) {
                $recordModel->set('region_id', $sourceRecordModel->get('region_id'));
                $changed = true;
            }

            if ($changed) {
                $recordModel->set('mode', 'edit');
                $recordModel->save();
            }

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
}
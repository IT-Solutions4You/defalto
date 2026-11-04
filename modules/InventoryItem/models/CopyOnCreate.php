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
            $changes = [];

            if ($recordModel->get('currency_id') !== $sourceRecordModel->get('currency_id')) {
                $changes['currency_id'] = $sourceRecordModel->get('currency_id');
                $changes['conversion_rate'] = $sourceRecordModel->get('conversion_rate');
                $entity->column_fields['currency_id'] = $changes['currency_id'];
                $entity->column_fields['conversion_rate'] = $changes['conversion_rate'];
            }

            if ($recordModel->get('pricebookid') !== $sourceRecordModel->get('pricebookid')) {
                $changes['pricebookid'] = $sourceRecordModel->get('pricebookid');
            }

            if ($recordModel->get('region_id') !== $sourceRecordModel->get('region_id')) {
                $changes['region_id'] = $sourceRecordModel->get('region_id');
            }

            if (!empty($changes)) {
                // Only the changed columns are written. Saving the whole re-fetched record would overwrite the values of the new document.
                $assignments = implode(', ', array_map(static fn(string $column) => $column . ' = ?', array_keys($changes)));
                $adb = PearDatabase::getInstance();
                $adb->pquery(
                    'UPDATE ' . $entity->table_name . ' SET ' . $assignments . ' WHERE ' . $entity->table_index . ' = ?',
                    array_merge(array_values($changes), [$entity->id])
                );
            }

            $targetModule = $recordModel->getModuleName();
            $reviewedItems = InventoryItem_Conversion_Model::getStoredItems($targetModule, $sourceRecord);

            foreach ($allItems as $item) {
                $isProductItem = !empty($item['productid']);
                $reviewedItem = $reviewedItems[(int)$item['inventoryitemid']] ?? null;

                // Product rows removed in the conversion modal are not copied. Text rows are not part of the modal and are always copied.
                if (null !== $reviewedItems && $isProductItem && null === $reviewedItem) {
                    continue;
                }

                $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
                $itemModel->set('mode', '');
                $itemModel->set('id', '');
                $itemModel->set('recordId', '');
                $itemModel->set('parentid', $entity->id);
                $itemModel->set('parentitemid', $item['inventoryitemid']);

                // The row is recalculated on save, so the tax and discounts of the source row apply to the changed values.
                foreach ((array)$reviewedItem as $column => $value) {
                    $itemModel->set($column, $value);
                }

                $itemModel->save();
                $itemModel->saveTaxId((int)$item['tax_id']);
                unset($itemModel);
            }

            InventoryItem_Conversion_Model::clearStoredItems($targetModule, $sourceRecord);
            InventoryItem_ParentEntity_Model::updateTotals($entity->id);
        }
    }
}
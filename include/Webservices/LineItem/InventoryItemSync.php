<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/InventoryItem/helpers/Utils.php';
require_once 'modules/InventoryItem/models/Module.php';
require_once 'modules/InventoryItem/models/ParentEntity.php';
require_once 'modules/Core/models/Tax.php';
require_once 'include/Webservices/LineItem/InventoryItemHelpers.php';

class InventoryItem_Webservice_Sync
{
    /**
     * @param string $moduleName
     * @return bool
     */
    public static function isInventoryModule(string $moduleName): bool
    {
        try {
            return InventoryItem_Utils_Helper::isInventoryModule($moduleName);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param string $moduleName
     * @param int $parentId
     * @param array $lineItems
     * @param array $parentElement
     * @return void
     * @throws Exception
     */
    public static function syncLineItems(string $moduleName, int $parentId, array $lineItems, array $parentElement = []): void
    {
        if (empty($lineItems)) {
            return;
        }

        $parentRecord = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
        $parentAssigned = $parentElement['assigned_user_id'] ?? null;
        $assignedUserId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($parentAssigned);

        if (!$assignedUserId && $parentRecord) {
            $assignedUserId = (int)$parentRecord->get('assigned_user_id');
        }

        $priceBookId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($parentElement['pricebookid'] ?? null);

        if (!$priceBookId && $parentRecord) {
            $priceBookId = (int)$parentRecord->get('pricebookid');
        }

        $existing = InventoryItem_Module_Model::fetchItemsForId($parentId, true);
        $existingMap = self::buildExistingMap($existing);

        $sequence = 1;

        foreach ($lineItems as $lineItem) {
            $productId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($lineItem['productid'] ?? null);
            $itemText = InventoryItem_Webservice_Helpers::resolveItemText($lineItem, $productId);

            if (empty($productId) && empty($itemText)) {
                continue;
            }

            $key = self::buildKey($productId, $itemText);
            $itemModel = null;

            if (!empty($existingMap[$key])) {
                $itemModel = array_shift($existingMap[$key]);
                $itemModel->set('mode', 'edit');
            } else {
                $itemModel = Vtiger_Record_Model::getCleanInstance('InventoryItem');
            }

            $sequenceValue = $lineItem['sequence_no'] ?? $lineItem['sequence'] ?? null;

            if ($sequenceValue === null || $sequenceValue === '') {
                $sequenceValue = $sequence;
            }

            $description = $lineItem['description'] ?? $lineItem['comment'] ?? null;
            $unit = $lineItem['unit'] ?? null;
            $price = $lineItem['listprice'] ?? $lineItem['price'] ?? null;
            $quantity = $lineItem['quantity'] ?? null;
            $linePriceBookId = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($lineItem['pricebookid'] ?? null) ?: $priceBookId;

            if (!empty($itemText)) {
                $itemModel->set('item_text', $itemText);
            }

            if ($productId) {
                $itemModel->set('productid', $productId);
            }

            if ($quantity !== null) {
                $itemModel->set('quantity', $quantity);
            }

            if ($price !== null) {
                $itemModel->set('price', $price);
            }

            if ($unit !== null) {
                $itemModel->set('unit', $unit);
            }

            if ($description !== null) {
                $itemModel->set('description', $description);
            }

            if ($sequenceValue !== null) {
                $itemModel->set('sequence', $sequenceValue);
            }

            if ($linePriceBookId) {
                $itemModel->set('pricebookid', $linePriceBookId);
            }

            if ($assignedUserId) {
                $itemModel->set('assigned_user_id', $assignedUserId);
            }

            $taxData = InventoryItem_Webservice_Helpers::resolveTaxForLineItem($lineItem, $productId);

            if ($taxData['percentage'] !== null) {
                $itemModel->set('tax', $taxData['percentage']);
            }

            InventoryItem_Webservice_Helpers::applyDiscountData($itemModel, $lineItem);

            $itemModel->set('parentid', $parentId);

            if (!empty($lineItem['parentitemid'])) {
                $itemModel->set('parentitemid', InventoryItem_Webservice_Helpers::getCrmIdFromWsId($lineItem['parentitemid']));
            }

            $itemModel->save();

            if (!empty($taxData['taxId'])) {
                $itemModel->saveTaxId((int)$taxData['taxId']);
            } elseif (!empty($taxData['percentage'])) {
                try {
                    $taxes = InventoryItem_TaxesForItem_Model::fetchTaxes((int)$itemModel->getId(), (int)$productId, $parentId);

                    if (!empty($taxes)) {
                        $taxId = array_key_first($taxes);

                        if (!empty($taxId)) {
                            $itemModel->saveTaxId((int)$taxId);
                        }
                    }
                } catch (Exception $e) {
                    // ignore tax fallback failure
                }
            }

            $sequence++;
        }

        foreach ($existingMap as $items) {
            foreach ($items as $itemModel) {
                $itemModel->delete();
            }
        }

        InventoryItem_ParentEntity_Model::updateTotals($parentId);
    }

    /**
     * @param array $items
     * @return array
     */
    private static function buildExistingMap(array $items): array
    {
        $map = [];

        foreach ($items as $item) {
            $itemModel = Vtiger_Record_Model::getInstanceById($item['inventoryitemid'], 'InventoryItem');
            $productId = (int)$itemModel->get('productid');
            $itemText = (string)$itemModel->get('item_text');
            $key = self::buildKey($productId, $itemText);

            if (!isset($map[$key])) {
                $map[$key] = [];
            }

            $map[$key][] = $itemModel;
        }

        return $map;
    }

    /**
     * @param int|null $productId
     * @param string|null $itemText
     * @return string
     */
    private static function buildKey(?int $productId, ?string $itemText): string
    {
        $pid = $productId ? (int)$productId : 0;
        $text = strtolower(trim((string)$itemText));

        return $pid . '|' . $text;
    }
}
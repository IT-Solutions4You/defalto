<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class InventoryItem_Migratino_Model extends Vtiger_Base_Model
{
    protected array $inventoryModules = ['Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice'];

    public function migrate()
    {
        $this->migrateCustomFields();
        $this->migrateRecords();
    }

    protected function migrateCustomFields()
    {
    }

    protected function migrateRecords()
    {
        $db = PearDatabase::getInstance();

        foreach ($this->inventoryModules as $inventoryModule) {
            $entriesRes = $db->pquery('SELECT crmid, smownerid FROM vtiger_crmentity WHERE setype = ? AND deleted = 0', [$inventoryModule]);

            while ($entry = $db->fetch_array($entriesRes)) {
                $entityFocus = CRMEntity::getInstance($inventoryModule);
                $entityFocus->id = $entry['crmid'];
                $entityFocus->retrieve_entity_info($entry['crmid'], $inventoryModule);
                $taxType = $entityFocus->column_fields->offsetGet('hdnTaxType');

                if ('individual' == $taxType) {
                    $discountPercent = $entityFocus->column_fields->offsetGet('discount_percent');
                    $discountAmount = $entityFocus->column_fields->offsetGet('discount_amount');

                    if (empty($discountAmount)) {
                        $discountAmount = 0;
                    }

                    if (!empty($discountPercent)) {
                        $discountAmount = $entityFocus->column_fields['hdnSubTotal'] * $discountPercent / 100;
                    }

                    if ($discountAmount > 0) {
                        $adjustment = (int)trim($entityFocus->column_fields->offsetGet('txtAdjustment'), '+');
                        $adjustment -= $discountAmount;
                        $db->pquery('UPDATE ' . $entityFocus->basetable . ' SET adjustment = ?, discount_percent = NULL, discount_amount = NULL WHERE crmid = ?', [$adjustment, $entry['crmid']]);
                    }
                }

                $productsRes = $db->pquery('SELECT * FROM vtiger_inventoryproductrel WHERE id = ?', [$entry['crmid']]);

                while ($productsRow = $db->fetchByAssoc($productsRes)) {
                    $inventoryItem = CRMEntity::getInstance('InventoryItem');
                    $inventoryItem->column_fields['assigned_user_id'] = $entry['smownerid'];
                    $inventoryItem->column_fields['parentid'] = $entry['crmid'];
                    $inventoryItem->column_fields['productid'] = $productsRow['productid'];
                    $inventoryItem->column_fields['quantity'] = $productsRow['quantity'];
                    $inventoryItem->column_fields['sequence'] = $productsRow['sequence_no'];
                    $inventoryItem->column_fields['price'] = $productsRow['listprice'];
                    $inventoryItem->column_fields['subtotal'] = (float)$productsRow['listprice'] * (float)$productsRow['quantity'];
                    $inventoryItem->column_fields['discount'] = $productsRow['discount_percent'];
                    $inventoryItem->column_fields['discount_type'] = '';

                    if (!empty($productsRow['discount_amount'])) {
                        $inventoryItem->column_fields['discount_amount'] = $productsRow['discount_amount'];
                        $inventoryItem->column_fields['discount_type'] = 'Direct';
                    } elseif (!empty($productsRow['discount_percent'])) {
                        $inventoryItem->column_fields['discount_amount'] = $inventoryItem->column_fields['subtotal'] * $productsRow['discount_percent'] / 100;
                        $inventoryItem->column_fields['discount_type'] = 'Percentage';
                    } else {
                        $inventoryItem->column_fields['discount_amount'] = 0;
                    }

                    $inventoryItem->column_fields['price_after_discount'] = $inventoryItem->column_fields['subtotal'] - $inventoryItem->column_fields['discount_amount'];
                    $inventoryItem->column_fields['description'] = $productsRow['comment'];
                    $inventoryItem->column_fields['purchase_cost'] = $productsRow['purchase_cost'];

                    $productServiceEntityType = getSalesEntityType($productsRow['productid']);
                    $productServiceFocus = CRMEntity::getInstance($productServiceEntityType);
                    $productServiceFocus->retrieve_entity_info($productsRow['productid'], $productServiceEntityType);
                    $inventoryItem->column_fields['item_text'] = $productServiceFocus->column_fields['productname'];

                    switch ($productServiceEntityType) {
                        case 'Services':
                            $inventoryItem->column_fields['unit'] = $productServiceFocus->column_fields['service_usageunit'];
                            break;
                        default:
                            $inventoryItem->column_fields['unit'] = $productServiceFocus->column_fields['usageunit'];
                            break;
                    }

                    $discountAmount = 0;

                    if ('group' == $taxType) {
                        $discountPercent = $entityFocus->column_fields->offsetGet('discount_percent');
                        $discountAmount = $entityFocus->column_fields->offsetGet('discount_amount');

                        if (!empty($discountPercent)) {
                            $discountAmount = ($inventoryItem->column_fields['price_after_discount'] * $discountPercent) / 100;
                        } elseif (!empty($discountAmount)) {
                            $discountPercent = round(($discountAmount / $entityFocus->column_fields['hdnSubTotal']) * 100, 2);
                            $discountAmount = ($inventoryItem->column_fields['price_after_discount'] * $discountPercent) / 100;
                        } else {
                            $discountPercent = 0;
                            $discountAmount = 0;
                        }

                        $inventoryItem->column_fields['overall_discount'] = $discountPercent;
                        $inventoryItem->column_fields['overall_discount_amount'] = $discountAmount;
                    }

                    $inventoryItem->column_fields['price_after_overall_discount'] = $inventoryItem->column_fields['price_after_discount'] - $discountAmount;
                    $inventoryItem->column_fields['price_total'] = $inventoryItem->column_fields['price_after_overall_discount'];
                    $inventoryItem->save('InventoryItem');
                }
            }
        }
    }
}
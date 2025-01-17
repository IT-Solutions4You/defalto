<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20240619081559')) {
    class Migration_20240619081559 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         */
        public function migrate(string $fileName): void
        {
            global $current_user;
            $previous_user = $current_user;
            $current_user = Users::getActiveAdminUser();

            $inventoryModules = ['Quotes', 'SalesOrder', 'PurchaseOrder', 'Invoice'];
            $newBlockUiType = Core_BlockUiType_Model::addBlockUiType('InventoryItem');

            foreach ($inventoryModules as $inventoryModule) {
                $module = Vtiger_Module::getInstance($inventoryModule);
                $block = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $module);
                $block->changeBlockUiType($newBlockUiType);

                $entriesRes = $this->db->pquery('SELECT crmid, smownerid FROM vtiger_crmentity WHERE setype = ? AND deleted = 0', [$inventoryModule]);

                while ($entry = $this->db->fetch_array($entriesRes)) {
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
                            $baseTable = $entityFocus->basetable;
//                                $this->db->pquery('UPDATE ' . $baseTable . ' SET adjustment = ?, discount_percent = NULL, discount_amount = NULL WHERE crmid = ?', [$adjustment, $entry['crmid']]);
                        }
                    }

                    $productsRes = $this->db->pquery('SELECT * FROM vtiger_inventoryproductrel WHERE id = ?', [$entry['crmid']]);

                    while ($productsRow = $this->db->fetchByAssoc($productsRes)) {
                        $inventoryItem = CRMEntity::getInstance('InventoryItem');
                        $inventoryItem->column_fields['assigned_user_id'] = $entry['smownerid'];
                        $inventoryItem->column_fields['parentid'] = $entry['crmid'];
                        $inventoryItem->column_fields['productid'] = $productsRow['productid'];
                        $inventoryItem->column_fields['quantity'] = $productsRow['quantity'];
                        $inventoryItem->column_fields['sequence'] = $productsRow['sequence_no'];
                        $inventoryItem->column_fields['price'] = $productsRow['listprice'];
                        $inventoryItem->column_fields['subtotal'] = (float)$productsRow['listprice'] * (float)$productsRow['quantity'];
                        $inventoryItem->column_fields['discount'] = $productsRow['discount_percent'];

                        if (!empty($productsRow['discount_amount'])) {
                            $inventoryItem->column_fields['discount_amount'] = $productsRow['discount_amount'];
                        } elseif (!empty($productsRow['discount_percent'])) {
                            $inventoryItem->column_fields['discount_amount'] = $inventoryItem->column_fields['subtotal'] * $productsRow['discount_percent'] / 100;
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

                        /**
                         * Tiez musim zistit ake su v systeme dane, zistit si spravne nazvy stlpcov, spocitat si ich, na zaklade toho naplnit polia tax a tax amount
                         * Nakoniec vycislujem Total - to je vlastne cena po dani
                         */

                        $inventoryItem->column_fields['price_total'] = $inventoryItem->column_fields['price_after_overall_discount'];

                        $inventoryItem->save('InventoryItem');
                    }
                }

            }

            $current_user = $previous_user;
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
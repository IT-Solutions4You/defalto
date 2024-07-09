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
            $newBlockUiType = Vtiger_BlockUiType_Model::addBlockUiType('InventoryItem');

            foreach ($inventoryModules as $inventoryModule) {
                $module = Vtiger_Module::getInstance($inventoryModule);
                $block = Vtiger_Block::getInstance('LBL_ITEM_DETAILS', $module);
                $block->changeBlockUiType($newBlockUiType);

                $entriesRes = $this->db->pquery('SELECT crmid, smownerid FROM vtiger_crmentity WHERE setype = ? AND deleted = 0', [$inventoryModule]);

                while ($entry = $this->db->fetch_array($entriesRes)) {
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

                        $inventoryItem->column_fields['total_after_discount'] = $inventoryItem->column_fields['subtotal'] - $inventoryItem->column_fields['discount_amount'];
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

                        /**
                         * V pripade, ze je zadana v ponuke/objed/fakture celkova zlava, tak z nej vycislujem overall discount pri polozkach a teda naplnam overall_discount, overall_discount_amount a total_after_overall_discount
                         * Ak je Cekova zlava percento, tak dat spravne percenta; ak je celkova suma, tak to hodit do halieroveho vyrovnania
                         * Ak aj nie je celkova zlava, tak total_after_overall_discount musim naplnit tym co je v total_after_discount:
                         * Tiez musim zistit ake su v systeme dane, zistit si spravne nazvy stlpcov, spocitat si ich, na zaklade toto naplnit polia tax a tax amount
                         * Nakoniec vycislujem Total - to je vlastne cena po dani
                         */

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
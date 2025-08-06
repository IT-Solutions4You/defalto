<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20250709123459')) {
    class Migration_20250709123459 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         */
        public function migrate(string $fileName): void
        {
            $menu = Settings_Vtiger_Menu_Model::getInstance('LBL_INVENTORY');

            if (!$menu) {
                $menu = Settings_Vtiger_Menu_Model::getInstanceFromArray(['label' => 'LBL_INVENTORY']);
                $menu->save();
            }

            $link = Settings_Vtiger_MenuItem_Model::getInstance('Inventory Item Block', $menu);

            if (!$link) {
                $link = Settings_Vtiger_MenuItem_Model::getInstanceFromArray(
                    ['name' => 'Inventory Item Block', 'blockid' => $menu->getId(), 'linkto' => 'index.php?module=InventoryItem&parent=Settings&view=Index']
                );
                $link->save();
            }

            $this->db->pquery(
                'INSERT INTO df_inventoryitemcolumns VALUES(?,?) ON DUPLICATE KEY UPDATE columnslist = columnslist',
                [0, 'productid,quantity,unit,price,subtotal,discounts_amount,price_after_overall_discount,tax,tax_amount,price_total']
            );
            $this->db->pquery('INSERT INTO df_inventoryitem_itemmodules (tabid) VALUES (?), (?) ON DUPLICATE KEY UPDATE tabid = tabid', [getTabid('Products'), getTabid('Services')]
            );
            $this->db->pquery(
                'INSERT INTO df_inventoryitem_quantitydecimals (field, decimals) VALUES (?, ?), (?, ?) ON DUPLICATE KEY UPDATE decimals = decimals',
                ['price', 2, 'quantity', 2]
            );

            $inventoryModules = ['Invoice', 'PurchaseOrder', 'Quotes', 'SalesOrder'];

            foreach ($inventoryModules as $inventoryModuleName) {
                $tabId = getTabid($inventoryModuleName);
                $this->db->pquery('DELETE FROM vtiger_field WHERE tabid = ? AND tablename = ?', [$tabId, 'vtiger_inventoryproductrel']);
                $this->db->pquery(
                    'UPDATE vtiger_ws_entity SET handler_path = ?,  handler_class = ? WHERE name = ?',
                    ['include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', $inventoryModuleName]
                );
            }

            $changeFields = [
                'pre_tax_total' => ['price_after_overall_discount', 'Price After Overall Discount', 'Pre Tax Total'],
                'hdnDiscountAmount' => ['discount_amount', 'Discount Amount', 'Discount Amount'],
                'hdnGrandTotal' => ['price_total', 'Total', 'Total'],
                'txtAdjustment' => ['adjustment', 'Adjustment', 'Adjustment'],
                'hdnSubTotal' => ['subtotal', 'Sub Total', 'Sub Total'],
            ];

            foreach ($inventoryModules as $inventoryModuleName) {
                $updateCvColumnlistSql = 'UPDATE vtiger_cvcolumnlist SET columnname = REPLACE(columnname, ?, ?) WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype = ?)';
                $updateCvAdvFilterSql = 'UPDATE vtiger_cvadvfilter SET columnname = REPLACE(columnname, ?, ?) WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype = ?)';
                $updateWorkflowsSql = 'UPDATE com_vtiger_workflows SET `test` = REPLACE(`test`, ?, ?) WHERE module_name = ?';

                foreach ($changeFields as $oldFieldName => $changeFieldData) {
                    $newFieldName = $changeFieldData[0];
                    $newFieldLabel = $changeFieldData[1];
                    $oldLabel = $changeFieldData[2];
                    $crazyOldLabel = $inventoryModuleName . '_' . str_replace(' ', '_', $oldLabel);
                    $crazyNewLabel = $inventoryModuleName . '_' . str_replace(' ', '_', $newFieldLabel);

                    $this->db->pquery($updateCvColumnlistSql, [$oldFieldName . ':' . $crazyOldLabel, $newFieldName . ':' . $crazyNewLabel, $inventoryModuleName]);
                    $this->db->pquery($updateCvAdvFilterSql, [$oldFieldName . ':' . $crazyOldLabel, $newFieldName . ':' . $crazyNewLabel, $inventoryModuleName]);
                    $this->db->pquery($updateWorkflowsSql, ['"' . $oldFieldName . '"', '"' . $newFieldName . '"', $inventoryModuleName]);
                }
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
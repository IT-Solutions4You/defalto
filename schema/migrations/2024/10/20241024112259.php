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

if (!class_exists('Migration_20241024112259')) {
    class Migration_20241024112259 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         *
         * @throws AppException
         * @throws Exception
         */
        public function migrate(string $strFileName): void
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

            $this->db->query(
                'CREATE TABLE IF NOT EXISTS `df_inventoryitemcolumns` (
                    `tabid` int(11) NOT NULL,
                    `columnslist` varchar(500) DEFAULT NULL,
                    PRIMARY KEY (`tabid`)
                ) ENGINE=innodb DEFAULT CHARSET=utf8'
            );
            $this->db->pquery('INSERT INTO df_inventoryitemcolumns VALUES(?,?)', [0, 'productid,quantity,unit,price,subtotal,discounts_amount,price_after_overall_discount,tax,tax_amount,price_total']);

            $this->db->query(
                'CREATE TABLE IF NOT EXISTS `df_inventoryitem_itemmodules` (
                  `tabid` int(19) NOT NULL,
                  PRIMARY KEY (`tabid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
            );
            $this->db->pquery('INSERT INTO df_inventoryitem_itemmodules (tabid) VALUES (?), (?)', [getTabid('Products'), getTabid('Services')]);

            $this->db->query(
                'CREATE TABLE IF NOT EXISTS `df_inventoryitem_quantitydecimals` (
                  `field` VARCHAR(255) NOT NULL,
                  `decimals` INT(19) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
            );
            $this->db->pquery('INSERT INTO df_inventoryitem_quantitydecimals (decimals) VALUES (?, ?), (?, ?)', ['price', 2, 'quantity', 2]);

            $this->db->pquery(
                'UPDATE vtiger_field SET displaytype = 1, presence = 0, quickcreate = 1 WHERE fieldname = ? AND tablename IN (?, ?, ?, ?)',
                ['currency_id', 'vtiger_quotes', 'vtiger_purchaseorder', 'vtiger_salesorder', 'vtiger_invoice']
            );
            $inventoryModules = ['Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice'];

            foreach ($inventoryModules as $inventoryModuleName) {
                $tabId = getTabid($inventoryModuleName);
                $this->db->pquery('DELETE FROM vtiger_field WHERE tabid = ? AND tablename = ?', [$tabId, 'vtiger_inventoryproductrel']);
                $this->db->pquery('UPDATE vtiger_ws_entity SET handler_path = ?,  handler_class = ? WHERE name = ?', ['include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', $inventoryModuleName]);

                $inventoryModuleEntity = CRMEntity::getInstance($inventoryModuleName);
                $inventoryModule = Vtiger_Module::getInstance($inventoryModuleName);
                $priceBookIdField = Vtiger_Field::getInstance('pricebookid', $inventoryModule);

                if (!$priceBookIdField) {
                    $blocks = Vtiger_Block::getAllForModule($inventoryModule);
                    $itemsBlock = false;

                    foreach ($blocks as $block) {
                        if ($block->label === 'LBL_ITEM_DETAILS') {
                            $itemsBlock = $block;
                            break;
                        }
                    }

                    if (!$itemsBlock) {
                        continue;
                    }

                    $priceBookId = new Vtiger_Field();
                    $priceBookId->table = $inventoryModuleEntity->basetable;
                    $priceBookId->name = 'pricebookid';
                    $priceBookId->column = 'pricebookid';
                    $priceBookId->label = 'Price Book';
                    $priceBookId->uitype = 10;
                    $priceBookId->presence = 0;
                    $priceBookId->sequence = 10;
                    $priceBookId->columntype = 'INT(11)';
                    $priceBookId->typeofdata = 'V~O';
                    $priceBookId->quickcreate = 1;
                    $priceBookId->masseditable = 0;
                    $priceBookId->summaryfield = 0;
                    $priceBookId->save($itemsBlock);
                    $priceBookId->setRelatedModules(['PriceBooks']);
                }
            }
            
            $accountsEntity = CRMEntity::getInstance('Accounts');
            $accountsModule = Vtiger_Module::getInstance('Accounts');
            $priceBookIdField = Vtiger_Field::getInstance('pricebookid', $accountsModule);

            if (!$priceBookIdField) {
                $blocks = Vtiger_Block::getAllForModule($accountsModule);
                $block = $blocks[0];
                $priceBookId = new Vtiger_Field();
                $priceBookId->table = $accountsEntity->basetable;
                $priceBookId->name = 'pricebookid';
                $priceBookId->column = 'pricebookid';
                $priceBookId->label = 'Price Book';
                $priceBookId->uitype = 10;
                $priceBookId->presence = 0;
                $priceBookId->sequence = 50;
                $priceBookId->columntype = 'INT(11)';
                $priceBookId->typeofdata = 'V~O';
                $priceBookId->quickcreate = 1;
                $priceBookId->masseditable = 0;
                $priceBookId->summaryfield = 0;
                $priceBookId->save($block);
                $priceBookId->setRelatedModules(['PriceBooks']);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
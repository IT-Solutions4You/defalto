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
                $link = Settings_Vtiger_MenuItem_Model::getInstanceFromArray(['name' => 'Inventory Item Block', 'blockid' => $menu->getId(), 'linkto' => 'index.php?module=InventoryItem&parent=Settings&view=Index']);
                $link->save();
            }

            $this->db->query('
                CREATE TABLE IF NOT EXISTS `its4you_inventoryitemcolumns` (
                    `tabid` int(11) NOT NULL,
                    `columnslist` varchar(500) DEFAULT NULL,
                    PRIMARY KEY (`tabid`)
                ) ENGINE=innodb DEFAULT CHARSET=utf8
            ');

            $this->db->query('
                CREATE TABLE IF NOT EXISTS `df_inventoryitem_itemmodules` (
                  `tabid` int(19) NOT NULL,
                  PRIMARY KEY (`tabid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ');

            $this->db->pquery('INSERT INTO df_inventoryitem_itemmodules (tabid) VALUES (?), (?)', [getTabid('Products'), getTabid('Services')]);

            $this->db->pquery('UPDATE vtiger_field SET displaytype = 1, presence = 0, quickcreate = 1 WHERE fieldname = ? AND tablename IN (?, ?, ?, ?)', ['currency_id', 'vtiger_quotes', 'vtiger_purchaseorder', 'vtiger_salesorder', 'vtiger_invoice']);
            $tabId = getTabid('Quotes');
            $this->db->pquery('DELETE FROM vtiger_field WHERE tabid = ? AND tablename = ?', [$tabId, 'vtiger_inventoryproductrel']);

            /*$itemDetailsBlockSql = 'SELECT blockid FROM vtiger_blocks WHERE tabid = ? AND blocklabel = ?';
            $itemDetailsBlockResult = $this->db->pquery($itemDetailsBlockSql, [$tabId, 'LBL_ITEM_DETAILS']);

            if ($this->db->num_rows($itemDetailsBlockResult)) {
                $itemDetailsBlockRow = $this->db->fetchByAssoc($itemDetailsBlockResult);

                $firstBlockSql = 'SELECT blockid FROM vtiger_blocks WHERE tabid = ? AND blocklabel != ? ORDER BY sequence LIMIT 0,1';
                $firstBlockResult = $this->db->pquery($firstBlockSql, [$tabId, 'LBL_ITEM_DETAILS']);

                if ($this->db->num_rows($firstBlockResult)) {
                    $firstBlockRow = $this->db->fetchByAssoc($firstBlockResult);

                    $this->db->pquery('UPDATE vtiger_field SET block = ?, displaytype = 3 WHERE block = ?', [$firstBlockRow['blockid'], $itemDetailsBlockRow['blockid']]);
                }
            }*/

            $this->db->pquery('UPDATE vtiger_ws_entity SET handler_class = ? WHERE name = ?', ['VtigerModuleOperation', 'Quotes']);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
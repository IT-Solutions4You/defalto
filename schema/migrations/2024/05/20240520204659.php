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

if (!class_exists('Migration_20240520204659')) {
    class Migration_20240520204659 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $this->db->pquery('ALTER TABLE com_vtiger_workflows MODIFY COLUMN schannualdates VARCHAR(500)');
            $this->db->pquery('ALTER TABLE vtiger_shorturls MODIFY COLUMN handler_data TEXT');
            $this->db->pquery(
                'UPDATE vtiger_field SET masseditable = 0 WHERE columnname IN (?, ?) AND tablename IN (?, ?, ?, ?)',
                ['discount_percent', 'discount_amount', 'vtiger_quotes', 'vtiger_purchaseorder', 'vtiger_salesorder', 'vtiger_invoice']
            );
            $this->db->pquery('UPDATE vtiger_inventorycharges SET value = 0 WHERE  name = ? and value IS NULL', ['Shipping & Handling']);
            $this->db->pquery('ALTER TABLE vtiger_quotes MODIFY s_h_percent DECIMAL(25,3)');
            $this->db->pquery('ALTER TABLE vtiger_purchaseorder MODIFY s_h_percent DECIMAL(25,3)');
            $this->db->pquery('ALTER TABLE vtiger_salesorder MODIFY s_h_percent DECIMAL(25,3)');
            $this->db->pquery('ALTER TABLE vtiger_invoice MODIFY s_h_percent DECIMAL(25,3)');
            // Make hidden mandatory fields optional
            $this->db->pquery("UPDATE vtiger_field SET typeofdata = replace(typeofdata,'~M','~O') where presence =1 and typeofdata like '%~M%'");

        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
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
            // Make hidden mandatory fields optional
            $this->db->pquery("UPDATE vtiger_field SET typeofdata = replace(typeofdata,'~M','~O') where presence =1 and typeofdata like '%~M%'");

        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
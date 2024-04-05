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

if (!class_exists('Migration_20240405120317')) {
    class Migration_20240405120317 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_inventorynotification_seq');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_inventorynotification');

            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE tabid NOT IN (SELECT tabid FROM vtiger_tab)');

            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label = ?', ['Sales Stage History']);
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_potstagehistory');

            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label = ?', ['Quote Stage History']);
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_quotestagehistory');

            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label = ?', ['PurchaseOrder Status History']);
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_postatushistory');

            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label = ?', ['SalesOrder Status History']);
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_sostatushistory');

            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label = ?', ['Invoice Status History']);
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_invoicestatushistory');

            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label = ?', ['Ticket History']);
            $fieldModel = Vtiger_Field_Model::getInstance('update_log', Vtiger_Module_Model::getInstance('HelpDesk'));

            if ($fieldModel) {
                $fieldModel->delete();
            }

            $this->db->pquery('ALTER TABLE vtiger_troubletickets DROP COLUMN update_log');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
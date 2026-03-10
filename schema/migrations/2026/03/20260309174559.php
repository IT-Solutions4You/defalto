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

if (!class_exists('Migration_20260206223359')) {
    class Migration_20260206223359 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $this->db->pquery('DELETE FROM vtiger_ws_entity WHERE name = ?', ['LineItem']);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}